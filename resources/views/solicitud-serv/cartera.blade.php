@extends('layouts.app')
@section('htmlheader_title')
@endsection
@section('main-content')

<main class="container" >

    <div class="modal-dialog" role="document">
    <div class="modal-content" >
        <div class="modal-header" style="align-content: center">
            <button type="button" name
            
            "Boton1" id="CarteraCerrar" onclick="" style="text-align: center;" class="btn btn-warning">
                Cerrar</button>
                <script>
                   

                </script>
          {{--<button class="btn btn-warning" type="button" data-dismiss="modal">Cerrar</button>--}}
        </div>
        <div class="modal-body" style="text-align: center">
            <div class="alert success"></div>
            <img src="{{asset('storage/Anuncio-Cartera.jpeg')}}" style="width: 450px;"> 
        </div>
    </div>
    </div>
    </main>
    {{--FIN VENTANA MODAL--}}
    @endsection
