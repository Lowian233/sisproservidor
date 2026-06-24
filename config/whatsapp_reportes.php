<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Números predefinidos para envío de reporte de cotizaciones Express
    |--------------------------------------------------------------------------
    | Formato: código de país + número, sin espacios ni +
    | Ejemplo: '573001234567'
    */
    'numeros' => [
        '573001234567',
        '573009876543',
    ],

    /*
    |--------------------------------------------------------------------------
    | Mensaje predefinido
    |--------------------------------------------------------------------------
    | Usa {fecha} para insertar la fecha actual automáticamente.
    */
    'mensaje' => "¡Hola! 👋 Te enviamos el reporte de cotizaciones Express de *PROSARC S.A. ESP* del {fecha}.\n\nEncuentras adjunto el archivo Excel con el detalle de las solicitudes.\n\n_Prosarc – Protección, Servicios Ambientales, Respel de Colombia S.A. ESP_",
];
