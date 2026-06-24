<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ClienteStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // if ($errors->has('PersEmail.unique') || $errors->has('CliNit.unique') || $errors->has('PersDocNumber.unique')) {
            return true;
        // }else{
            // return false;
        // }
    }

    /**
     * Normaliza CliProcedencia para evitar fallos por encoding/Unicode en producción.
     * Siempre reemplaza por el valor canónico cuando se detecta "Redes Sociales"
     * o variantes, así Rule::in() compara contra nuestro literal.
     */
    protected function prepareForValidation()
    {
        $procedencia = $this->input('CliProcedencia');
        if (!is_string($procedencia)) {
            return;
        }
        $procedencia = trim($procedencia);
        if ($procedencia === '') {
            return;
        }
        $procedenciaNorm = $this->normalizarUnicode(mb_strtolower($procedencia));

        // Mapear variantes de "Campaña Redes Sociales" al valor canónico (evita NFC/NFD, encoding)
        $esCampanaRedes = (str_contains($procedenciaNorm, 'campaña') || str_contains($procedenciaNorm, 'campana'))
            && str_contains($procedenciaNorm, 'redes')
            && str_contains($procedenciaNorm, 'sociales');
        if ($esCampanaRedes) {
            $this->merge(['CliProcedencia' => 'Redes Sociales']);
        }
    }

    private function normalizarUnicode(string $s): string
    {
        if (class_exists(\Normalizer::class)) {
            $n = \Normalizer::normalize($s, \Normalizer::FORM_C);
            return $n !== false ? $n : $s;
        }
        return $s;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'CliNit' => ['required','min:13','max:13',Rule::unique('clientes')->where(function ($query) use ($request){
                $Cliente = DB::table('clientes')
                    ->select('clientes.CliNit')
                    ->where('CliNit', $request->input('CliNit'))
                    ->where('CliCategoria', 'Cliente')
                    ->where('CliDelete', 0)
                    ->first();

                if(isset($Cliente->CliNit)){
                    $query->where('clientes.CliNit','=', $Cliente->CliNit);
                }else{
                    $query->where('clientes.CliNit','=', null);
                }
            })],
            'CliName'       => 'required|max:255|min:1',
            'CliShortname'  => 'alpha_num|max:255|min:1',
            'CliProcedencia' => [
                'required',
                Rule::in([
                    'Visita',
                    'Llamada',
                    'Contacto en frío',
                    'Campaña Redes Sociales',
                    'Referido',
                    'Otro',
                ]),
            ],
            'CliCategoria'  => 'max:32|alpha|nullable',
            'CliRut'        => 'mimes:pdf|max:5120|nullable',
            'CliCamaraComercio'         => 'mimes:pdf|max:5120|nullable',
            'CliRepresentanteLegal'     => 'mimes:pdf|max:5120|nullable',
            'CliCertificaionBancaria'   => 'mimes:pdf|max:5120|nullable',
            'CliCertificaionComercial'  => 'mimes:pdf|max:5120|nullable',
            'CliCertificaionComercial2' => 'mimes:pdf|max:5120|nullable',

            'SedeName'      => 'required|max:128|min:1',
            'SedeAddress'   => 'required|max:255|min:1',
            'SedePhone1'    => 'max:11|min:11|nullable',
            'SedeExt1'      => 'min:2|max:5|nullable',
            'SedePhone2'    => 'max:11|min:11|nullable',
            'SedeExt2'      => 'min:2|max:5|nullable',
            'SedeEmail'     => 'required|email|max:128',
            'SedeCelular'   => 'required|min:12|max:12',
            'FK_SedeMun'    => 'required|numeric|min:1|max:1122',

            'AreaName'      => 'required|max:128',

            'CargName'      => 'required|max:128',

            'PersFirstName' => 'required|max:64|min:1',
            'PersLastName'  => 'required|max:64|min:1',
            'PersEmail'     => 'required|email|max:255|unique:personals,PersEmail|unique:users,email',
            'PersSecondName'=> 'max:64|nullable',
            'PersDocNumber' => 'required|max:64|min:6|unique:personals,PersDocNumber',
            'PersDocType'   => 'required|in:CC,CE,NIT,RUT',
            'PersCellphone' => 'required|max:12|min:12',
        ];
    }
    public function messages()
    {
        return [
            'PersEmail.unique' => 'El campo "Correo Electrónico de la Persona de Contacto" ya está en uso en el sistema.',
            'CliShortname.required' => 'Error de validación (CliShortname)',
        ];
    }
}