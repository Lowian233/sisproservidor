<head>
    <meta charset="UTF-8">
    <title>@yield('htmlheader_title', 'Your title here')</title>
    <link rel="shortcut icon" href="{{ secure_asset('/img/LogoProsarc.ico') }}">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <link href="{{ secure_asset('/css/all.css') }}" rel="stylesheet" type="text/css" />
     <!-- Dependencias -->
    <link href="{{ secure_asset('/css/dependencias.css') }}" rel="stylesheet" type="text/css">

    @if(Route::currentRouteName()=='vehicle-programacion.create'||Route::currentRouteName()=='programacion-express.create')
        {{-- Full Calendar --}}
         <link href="{{ secure_asset('/css/fullcalendar.css') }}" rel="stylesheet" type="text/css">
    @endif

     {{-- Chart --}}
    <link href="{{ secure_asset('/css/chart.css') }}" rel="stylesheet" type="text/css">

     {{-- Moment --}}
     {{-- <link href="{{ asset('/css/moment.css') }}" rel="stylesheet" type="text/css"> --}}
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="{{ secure_asset('/css/datatable-depen.css') }}">

    {{-- plugins de datatables --}}
    <link rel="stylesheet" type="text/css" href="{{ secure_asset('/css/datatable-plugins.css') }}">

    {{-- Stilos Personalizados --}}
    <link href="{{ secure_asset('/css/stilosPersonalizados.css') }}" rel="stylesheet" type="text/css" />
    @if(Route::currentRouteName()=='programacion-express.create')
    {{-- Full Calendar VERDE --}}
    <link href="{{ secure_asset('/css/calendarioPersonalizado.css') }}" rel="stylesheet" type="text/css" />
    @endif

    {{-- fuentes de google --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <style>
        /* Ajustes globales para evitar desbordes en vistas con tablas/listados. */
        @media (max-width: 991px) {
            .content {
                overflow-x: auto;
            }

            .table {
                display: block;
                width: 100%;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>

    {{-- Stack para estilos adicionales --}}
    @stack('styles')

    {{-- script de idioma --}}
    <script>
        //See https://laracasts.com/discuss/channels/vue/use-trans-in-vuejs
        window.trans = @php
            // copy all translations from /resources/lang/CURRENT_LOCALE/* to global JS variable
            $lang_files = File::files(resource_path() . '/lang/' . App::getLocale());
            $trans = [];
            foreach ($lang_files as $f) {
                $filename = pathinfo($f)['filename'];
                $trans[$filename] = __($filename);
            }
            $trans['adminlte_message'] = __('adminlte::message');
            echo json_encode($trans);
            // echo $trans;
        @endphp
    </script>

    {{-- script para validar si javascript esta desactivado en el navegador --}}
    <noscript>
        <META HTTP-EQUIV="Refresh" CONTENT="0;URL=../noscriptpage">
        {{-- @include('layouts.partials.noscript') --}}
    </noscript>
</head>
