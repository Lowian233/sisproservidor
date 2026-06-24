<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidatePaymentRequest;
use App\Services\PaymentValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentValidationController extends Controller
{
    /**
     * @var PaymentValidationService
     */
    protected $validationService;

    /**
     * @param PaymentValidationService $validationService
     */
    public function __construct(PaymentValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Validate a payment receipt.
     *
     * @param ValidatePaymentRequest $request
     * @return JsonResponse
     */
    public function validatePayment(ValidatePaymentRequest $request): JsonResponse
    {
        Log::info("Starting payment validation for request", $request->except('file'));

        $file = $request->file('file');
        $data = $request->validated();

        $result = $this->validationService->validate($data, $file);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }
}
