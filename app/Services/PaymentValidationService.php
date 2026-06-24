<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use ThiagoAlessio\TesseractOCR\TesseractOCR;
use Carbon\Carbon;

class PaymentValidationService
{
    /**
     * @var Parser
     */
    protected $pdfParser;

    public function __construct()
    {
        $this->pdfParser = new Parser();
    }

    /**
     * Validate the payment receipt.
     *
     * @param array $data
     * @param UploadedFile $file
     * @return array
     */
    public function validate(array $data, UploadedFile $file): array
    {
        try {
            // 1. Save file (preparing for future integration)
            $filePath = $this->storeFile($file, $data['idClienteEnBD'] ?? 'unknown');

            // 2. Extract text
            $text = $this->extractText($file);
            $normalizedText = $this->normalizeText($text);

            Log::info("Extracted text from file: " . substr($normalizedText, 0, 500));

            // 3. Validate fields
            $nitValid = $this->validateNit($normalizedText, $data['expected_nit']);
            $amountValid = $this->validateAmount($normalizedText, $data['expected_amount']);
            $dateValid = $this->validateDate($normalizedText, $data['expected_date']);

            return [
                'success' => true,
                'nit_valid' => $nitValid,
                'amount_valid' => $amountValid,
                'date_valid' => $dateValid,
                'extracted_text' => $text,
                'file_path' => $filePath
            ];
        } catch (Exception $e) {
            Log::error("Payment Validation Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ];
        }
    }

    /**
     * Store the file in the specified path.
     *
     * @param UploadedFile $file
     * @param string $clientId
     * @return string
     */
    protected function storeFile(UploadedFile $file, string $clientId): string
    {
        $year = date('Y');
        $day = date('d');
        $extension = $file->getClientOriginalExtension();
        $fileName = 'comprobantePago_' . time() . '.' . $extension;
        
        // public/documentos/{idClienteEnBD}/año/dia/comprobantePago.extension
        $path = "documentos/{$clientId}/{$year}/{$day}";
        
        return $file->storeAs($path, $fileName, 'public');
    }

    /**
     * Extract text from PDF or Image.
     *
     * @param UploadedFile $file
     * @return string
     * @throws Exception
     */
    protected function extractText(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'pdf') {
            return $this->extractFromPdf($file);
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'bmp', 'tiff'])) {
            return $this->extractFromImage($file);
        }

        throw new Exception("Unsupported file type: {$extension}");
    }

    /**
     * Extract text from PDF using Smalot PdfParser.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function extractFromPdf(UploadedFile $file): string
    {
        try {
            $pdf = $this->pdfParser->parseFile($file->getRealPath());
            return $pdf->getText();
        } catch (Exception $e) {
            Log::error("PDF Parsing Error: " . $e->getMessage());
            throw new Exception("Could not parse PDF file: " . $e->getMessage());
        }
    }

    /**
     * Extract text from Image using Tesseract OCR.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function extractFromImage(UploadedFile $file): string
    {
        try {
            $ocr = new TesseractOCR($file->getRealPath());
            // You can specify languages if needed: ->lang('spa', 'eng')
            return $ocr->run();
        } catch (Exception $e) {
            Log::error("OCR Error: " . $e->getMessage());
            throw new Exception("OCR validation failed. Ensure Tesseract is installed on the system.");
        }
    }

    /**
     * Normalize text for better matching.
     *
     * @param string $text
     * @return string
     */
    protected function normalizeText(string $text): string
    {
        // Convert to lowercase
        $text = mb_strtolower($text, 'UTF-8');

        // Remove special characters except common separators if needed
        // Here we'll be aggressive as requested: "eliminar espacios, caracteres especiales"
        // But for dates and amounts, we might need some structure.
        // Let's create a version for general search and one for specific extraction if needed.
        
        // General normalization for basic containment check
        return preg_replace('/[^a-z0-9]/', '', $text);
    }

    /**
     * Validate NIT existence in text.
     *
     * @param string $normalizedText
     * @param string $expectedNit
     * @return bool
     */
    protected function validateNit(string $normalizedText, string $expectedNit): bool
    {
        $normalizedNit = preg_replace('/[^0-9]/', '', $expectedNit);
        return str_contains($normalizedText, $normalizedNit);
    }

    /**
     * Validate Amount existence in text.
     *
     * @param string $normalizedText
     * @param mixed $expectedAmount
     * @return bool
     */
    protected function validateAmount(string $normalizedText, $expectedAmount): bool
    {
        // Normalize amount to digits only
        $normalizedAmount = preg_replace('/[^0-9]/', '', (string)$expectedAmount);
        
        if (empty($normalizedAmount)) return false;

        return str_contains($normalizedText, $normalizedAmount);
    }

    /**
     * Validate Date existence in text.
     *
     * @param string $normalizedText
     * @param string $expectedDate
     * @return bool
     */
    protected function validateDate(string $normalizedText, string $expectedDate): bool
    {
        try {
            $date = Carbon::parse($expectedDate);
            
            // Try different formats
            $formats = [
                $date->format('Ymd'),
                $date->format('dmY'),
                $date->format('dmy'),
                $date->format('Y-m-d'), // will be normalized to Ymd
            ];

            foreach ($formats as $format) {
                $normalizedFormat = preg_replace('/[^0-9]/', '', $format);
                if (str_contains($normalizedText, $normalizedFormat)) {
                    return true;
                }
            }

            return false;
        } catch (Exception $e) {
            Log::warning("Date validation error: " . $e->getMessage());
            return false;
        }
    }
}
