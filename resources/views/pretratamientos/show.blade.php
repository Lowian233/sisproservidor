@extends('layouts.app')
@if(Auth::user()->UsRol == "Programador"||Auth::user()->UsRol == "JefeOperacion"||Auth::user()->UsRol == "admin")
@section('htmlheader_title')
{{ __('adminlte::LangTratamiento.tratdetaillong') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FF856D, #CC0000); padding-right:30vw; position:relative; overflow:hidden;">
    {{ __('adminlte::LangTratamiento.pretratMenu') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
{{-- @component('layouts.partials.modal')
{{$tratamiento->ID_Respel}}
@endcomponent --}}
<div class="container-fluid spark-screen">
    <!-- row -->
    <div class="row">
        <!-- col md3 -->
        <div class="col-md-3">
            <!-- box -->
            <div class="box box-primary">
                <!-- box body -->
                <div class="box-body box-profile">
                    {{-- <img id="" class="profile-user-img img-responsive img-circle" src="../../dist/img/user4-128x128.jpg" alt="User profile picture"> --}}
                    <h3 class="profile-username text-center">{{$tratamiento->TratName}}</h3>
                    <p class="text-muted text-center">
                        @if($tratamiento->TratTipo=='1')
                            <td>{{ __('adminlte::LangTratamiento.tratInLong') }}</td>
                        @else
                            <td>{{ __('adminlte::LangTratamiento.tratOutLong') }}</td>
                        @endif
                    </p>
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>{{ __('adminlte::LangTratamiento.tratSince') }}</b>
                            <p class="pull-right" style="color:blue;">{{$tratamiento->created_at->diffForHumans()}}</p>
                        </li>
                    </ul>
                    <a href='/tratamiento/{{$tratamiento->ID_Trat}}/edit' class='btn btn-warning btn-block'><i class='fas fa-edit'></i> {{ __('adminlte::message.edit') }} </a>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box body -->
        </div>
        <!-- /.col md3 -->
        <!-- col md9 -->
        <div class="col-md-9">
            <!-- box -->
            <div class="box">
                <!-- box header -->
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('adminlte::LangTratamiento.tratdetaillong') }}</h3>
                </div>
                <!-- /.box header -->
                <!-- box body -->
                <div class="box-body">
                    <!-- nav-tabs-custom -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link" href="#Proveedorpane" data-toggle="tab">{{ __('adminlte::message.clientproveedor') }}</a>
                            </li>
                            <li class="nav-item active">
                                <a class="nav-link" href="#Pretratamientospane" data-toggle="tab">{{ __('adminlte::LangTratamiento.pretrat') }}s</a>
                            </li>
                        </ul>
                        <!-- nav-content -->
                        <div class="tab-content" style="min-height:40vh;">
                            <!-- tab-pane fade -->
                            <div class="tab-pane fade " id="Proveedorpane">
                                <!-- About Me Box -->
                                <div class="box box-info">
                                    <div class="box-body box-profile">
                                        <h3 class="profile-username text-center">{{$Sede->SedeName}}</h3>
                                        @if (Auth::user()->UsRol === __('adminlte::message.Administrador'))
                                        <p class="text-muted text-center">{{$Cliente->CliShortname}}</p>
                                        @endif
                                        <ul class="list-group list-group-unbordered">
                                            <li class="list-group-item">
                                                <b>{{ __('adminlte::message.address') }}</b>
                                                <a title="{{ __('adminlte::message.copy') }}" onclick="copiarAlPortapapeles('{{ __('adminlte::message.address') }}')"><i class="far fa-copy"></i></a>
                                                <a href="#" class="pull-right textpopover" id="{{ __('adminlte::message.address') }}" title="{{ __('adminlte::message.address') }}" data-toggle="popover" data-trigger="focus" data-html="true" data-placement="bottom" data-content="<p class='textolargo'>{{$Sede->SedeAddress}} - {{$Sede->MunName}}, {{$Sede->DepartName}}</p>">{{$Sede->SedeAddress}} - {{$Sede->MunName}}, {{$Sede->DepartName}}</a>
                                            </li>
                                            <li class="list-group-item">
                                                <b>{{ __('adminlte::message.mobile') }}</b> <a class="pull-right">{{$Sede->SedeCelular}}</a>
                                            </li>
                                            <li class="list-group-item">
                                                <b>{{ __('adminlte::message.phone') }}</b> <a class="pull-right">{{$Sede->SedePhone1}} - {{$Sede->SedeExt1}}</a>
                                            </li>
                                            <li class="list-group-item">
                                                <b>{{ __('adminlte::message.phone') }} 2</b> <a class="pull-right">{{$Sede->SedePhone2}} - {{$Sede->SedeExt2}}</a>
                                            </li>
                                            <li class="list-group-item">
                                                <b>{{ __('adminlte::message.emailaddress') }}</b>
                                                <a title="{{ __('adminlte::message.copy') }}" onclick="copiarAlPortapapeles('{{ __('adminlte::message.emailaddress') }}')"><i class="far fa-copy"></i></a>
                                                <a href="#" class="pull-right textpopover" id="{{ __('adminlte::message.emailaddress') }}" title="{{ __('adminlte::message.emailaddress') }}" data-toggle="popover" data-trigger="focus" data-html="true" data-placement="bottom" data-content="<p class='textolargo'>{{$Sede->SedeEmail}}</p>">{{$Sede->SedeEmail}}</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                                <!-- /.tab-content -->
                            </div>
                            <!-- tab-pane fade -->
                            <!-- tab-pane fade -->
                            <div class="tab-pane fade in active" id="Pretratamientospane">
                                <div class="form-horizontal">
                                    <ul class="list-group list-group-unbordered">
                                        @php
                                            $conteoDePretratamientos=0;
                                        @endphp 
                                                
                                        @foreach($tratamiento->pretratamientos as $pretratamiento)
                                            @if($pretratamiento->PreTratDelete == 0)
                                                <li class="list-group-item">
                                                    <b>{{$pretratamiento->PreTratName}}</b> <a href="#" class="pull-right textpopover" id="{{ __('adminlte::message.address') }}" title="Descripción del Pretratamiento" data-toggle="popover" data-trigger="focus" data-html="true" data-placement="bottom" data-content="{{$pretratamiento->PreTratDescription}}">{{$pretratamiento->PreTratDescription}}</a>
                                                </li>
                                                @php
                                                $conteoDePretratamientos = $conteoDePretratamientos + 1;
                                                @endphp
                                            @endif
                                        @endforeach
                                        @if($conteoDePretratamientos==0)
                                            <li class="list-group-item">
                                                <p class="text-center"><br><b>{{ __('adminlte::LangTratamiento.noPretrat') }}</b></p>
                                            </li>
                                        @endif 
                                    </ul>
                                </div>
                            </div>
                            <!-- /.tab-pane fade -->
                        </div>
                        <!-- /.tab-content -->
                    </div>
                    {{-- <div class="row">
                        <input class="btn btn-success  pull-right" type="submit" value="Actualizar" style="margin-right:3em" />
                    </div> --}}
                    <!-- /.nav-tabs-custom -->
                </div>
                <!-- /.box body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col md9 -->
    </div>
    <!-- /.row -->
</div>
@endsection
@endif
