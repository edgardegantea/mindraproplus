<?php

namespace App\Support;

class PlusRequestHelper
{
    public static array $orgTypes = [
        'universidad'      => 'Universidad / Instituto educativo',
        'hospital'         => 'Hospital / Clínica',
        'centro_salud'     => 'Centro de salud mental',
        'empresa'          => 'Empresa privada',
        'gobierno'         => 'Institución de gobierno',
        'ong'              => 'ONG / Asociación civil',
        'laboratorio'      => 'Laboratorio / Centro de investigación',
        'consultorio'      => 'Consultorio privado',
        'otro'             => 'Otro',
    ];

    public static array $useCases = [
        'investigacion'  => 'Investigación académica',
        'clinico'        => 'Atención clínica / terapéutica',
        'corporativo'    => 'Bienestar corporativo',
        'educativo'      => 'Programas educativos',
        'salud_publica'  => 'Salud pública / gobierno',
        'telemedicina'   => 'Telemedicina / telepsicología',
        'otro'           => 'Otro',
    ];

    public static array $mexicoStates = [
        'AGU' => 'Aguascalientes',
        'BCN' => 'Baja California',
        'BCS' => 'Baja California Sur',
        'CAM' => 'Campeche',
        'CHP' => 'Chiapas',
        'CHH' => 'Chihuahua',
        'CMX' => 'Ciudad de México',
        'COA' => 'Coahuila',
        'COL' => 'Colima',
        'DUR' => 'Durango',
        'GUA' => 'Guanajuato',
        'GRO' => 'Guerrero',
        'HID' => 'Hidalgo',
        'JAL' => 'Jalisco',
        'MEX' => 'Estado de México',
        'MIC' => 'Michoacán',
        'MOR' => 'Morelos',
        'NAY' => 'Nayarit',
        'NLE' => 'Nuevo León',
        'OAX' => 'Oaxaca',
        'PUE' => 'Puebla',
        'QUE' => 'Querétaro',
        'ROO' => 'Quintana Roo',
        'SLP' => 'San Luis Potosí',
        'SIN' => 'Sinaloa',
        'SON' => 'Sonora',
        'TAB' => 'Tabasco',
        'TAM' => 'Tamaulipas',
        'TLA' => 'Tlaxcala',
        'VER' => 'Veracruz',
        'YUC' => 'Yucatán',
        'ZAC' => 'Zacatecas',
    ];

    public static array $countries = [
        'MX'   => 'México',
        'US'   => 'Estados Unidos',
        'CA'   => 'Canadá',
        'AR'   => 'Argentina',
        'BO'   => 'Bolivia',
        'BR'   => 'Brasil',
        'CL'   => 'Chile',
        'CO'   => 'Colombia',
        'CR'   => 'Costa Rica',
        'CU'   => 'Cuba',
        'DO'   => 'República Dominicana',
        'EC'   => 'Ecuador',
        'SV'   => 'El Salvador',
        'GT'   => 'Guatemala',
        'HN'   => 'Honduras',
        'NI'   => 'Nicaragua',
        'PA'   => 'Panamá',
        'PY'   => 'Paraguay',
        'PE'   => 'Perú',
        'PR'   => 'Puerto Rico',
        'UY'   => 'Uruguay',
        'VE'   => 'Venezuela',
        'ES'   => 'España',
        'PT'   => 'Portugal',
        'FR'   => 'Francia',
        'DE'   => 'Alemania',
        'IT'   => 'Italia',
        'GB'   => 'Reino Unido',
        'otro' => 'Otro',
    ];

    /** Enriquece el array de datos con labels legibles para los emails. */
    public static function withLabels(array $data): array
    {
        $data['org_type_label']    = self::$orgTypes[$data['org_type']   ?? ''] ?? ($data['org_type']   ?? '');
        $data['use_case_label']    = self::$useCases[$data['use_case']   ?? ''] ?? ($data['use_case']   ?? '');
        $data['org_country_name']  = self::$countries[$data['org_country'] ?? ''] ?? ($data['org_country'] ?? '');

        if (($data['org_country'] ?? '') === 'MX' && !empty($data['org_state_code'])) {
            $data['org_state_name'] = self::$mexicoStates[$data['org_state_code']] ?? $data['org_state_code'];
        } elseif (!empty($data['org_state_other'])) {
            $data['org_state_name'] = $data['org_state_other'];
        }

        // Construir dirección formateada
        $addr = [];
        if (!empty($data['org_street']))       $addr[] = $data['org_street'] . (!empty($data['org_ext_number']) ? ' #'.$data['org_ext_number'] : '') . (!empty($data['org_int_number']) ? ' Int.'.$data['org_int_number'] : '');
        if (!empty($data['org_neighborhood'])) $addr[] = 'Col. '.$data['org_neighborhood'];
        if (!empty($data['org_zip']))          $addr[] = 'C.P. '.$data['org_zip'];
        if (!empty($data['org_city']))         $addr[] = $data['org_city'];
        $stateName = $data['org_state_name'] ?? ($data['org_state_other'] ?? null);
        if ($stateName)                        $addr[] = $stateName;
        if (!empty($data['org_country_name'])) $addr[] = $data['org_country_name'];
        $data['org_full_address'] = implode(', ', array_filter($addr));

        return $data;
    }

    /** Reglas de validación compartidas. */
    public static function rules(): array
    {
        return [
            // 1. Solicitante
            'requester_name'       => 'required|string|max:255',
            'requester_position'   => 'nullable|string|max:150',
            'requester_email'      => 'required|email|max:255',
            'requester_phone'      => 'nullable|string|max:50',

            // 2. Institución
            'org_name'             => 'required|string|max:255',
            'org_type'             => 'required|string|in:universidad,hospital,centro_salud,empresa,gobierno,ong,laboratorio,consultorio,otro',
            'org_sector'           => 'nullable|string|max:150',
            'org_website'          => 'nullable|url|max:255',
            'org_country'          => 'required|string|max:10',
            'org_state_code'       => 'nullable|string|max:10',
            'org_state_other'      => 'nullable|string|max:150',
            'org_city'             => 'nullable|string|max:150',
            'org_street'           => 'nullable|string|max:255',
            'org_ext_number'       => 'nullable|string|max:20',
            'org_int_number'       => 'nullable|string|max:20',
            'org_neighborhood'     => 'nullable|string|max:150',
            'org_zip'              => 'nullable|string|max:10',

            // 3. Facturación
            'billing_rfc'          => 'nullable|string|max:13',
            'billing_razon_social' => 'nullable|string|max:255',
            'billing_regimen'      => 'nullable|string|max:100',
            'billing_cfdi'         => 'nullable|string|max:100',
            'billing_email'        => 'nullable|email|max:255',

            // 4. Proyecto
            'use_case'             => 'required|string|in:investigacion,clinico,corporativo,educativo,salud_publica,telemedicina,otro',
            'num_users'            => 'nullable|string|max:50',
            'project_description'  => 'required|string|max:3000',
            'how_found'            => 'nullable|string|max:255',
            'additional_comments'  => 'nullable|string|max:2000',
        ];
    }
}
