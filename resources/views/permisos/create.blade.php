@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.user') }}
@endsection
@section('contentheader_title')
{{ __('adminlte::message.user') }}
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{ __('adminlte::message.userregister') }}</h3>
				</div>
				<div class="box box-info">
					<form role="form" action="/permisos" method="POST" enctype="multipart/form-data" data-toggle="validator">
						@csrf
						@if ($errors->any())
						<div class="alert alert-danger" role="alert">
							<ul>
								@foreach ($errors->all() as $error)
									<p>{{$error}}</p>
								@endforeach
							</ul>
						</div>
						@endif
						<div class="box-body">
                            <div class="form-group col-md-6">
                                <label for="FK_UserPers" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.userpersonadd') }}</b>" data-content="En esta lista aparecen las personas registradas, por su empresa en el sistema <b>SisPRO</b>, que no tienen un Usuario asignado <br><br> <b>Nota:</b> Si la lista esta vacia primero deberá registrar una persona nueva, desde la lista de personal de su Empresa, para luego poder asignar esa persona durante la creacion de su nuevo Usuario"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.userpersonadd') }}</label>
                                <small class="help-block with-errors"></small>
								<select required class="form-control select" id="FK_UserPers" name="FK_UserPers">
                                    <option value="">{{ __('adminlte::message.select') }}</option>
									@foreach ($Personals as $Personal)		
                                        <option value="{{$Personal->PersSlug}}" {{ old('FK_UserPers') == $Personal->PersSlug ? 'selected' : '' }}>{{$Personal->PersFirstName}} {{$Personal->PersLastName}}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group col-md-6">
                                <label for="name">{{ __('adminlte::message.username') }}</label></label><small class="help-block with-errors">*</small>
                                <input type="text" class="form-control inputText" id="name" name="name" value="{{ old('name') }}" maxlength="255" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="UsRolDesc">{{ __('adminlte::message.userrol') }}</label></label><small class="help-block with-errors">*</small>
								<select class="form-control select" id="UsRolDesc" name="UsRolDesc" required>
                                    <option value="">{{ __('adminlte::message.select') }}</option>
									@foreach ($Roles as $Rol)		
                                        <option value="{{$Rol->RolDesc}}" {{ old('UsRolDesc') == $Rol->RolDesc ? 'selected' : '' }}>{{$Rol->RolDesc}}</option>
									@endforeach
								</select>
							</div>
                            <div class="form-group col-md-6">
                                <label for="UsRolDesc2">{{ __('adminlte::message.userrol2') }}</label></label><small class="help-block with-errors"></small>
								<select class="form-control select" id="UsRolDesc2" name="UsRolDesc2">
                                    <option value="">{{ __('adminlte::message.select') }}</option>
									@foreach ($Roles as $Rol)		
                                        <option value="{{$Rol->RolDesc}}" {{ old('UsRolDesc2') == $Rol->RolDesc ? 'selected' : '' }}>{{$Rol->RolDesc}}</option>
									@endforeach
								</select>
							</div>
                            <div class="form-group col-md-6">
                                <label for="email">{{ __('adminlte::message.emailaddress') }}</label></label><small class="help-block with-errors">*</small>
                                <input type="email" class="form-control" id="email" name="email" maxlength="255" placeholder="{{ __('adminlte::message.emailplaceholder') }}" value="{{ old('email') }}" required>
							</div>
							<div class="form-group col-md-6">
								<label for="UsAvatar">{{ __('adminlte::message.useravatar') }}</label></label><small class="help-block with-errors"></small>
								<input type="file" class="form-control" id="UsAvatar" name="UsAvatar" accept=".jpg, .png, .svg,.gif" data-accept="jpg, jpeg, png, svg, gif" data-filesize="5120" value="{{ old('UsAvatar') }}">
                            </div>
							<div class="form-group col-md-6">
								<label for="password">{{ __('adminlte::message.password') }}</label></label><small class="help-block with-errors">*</small>
                                <input type="password" class="form-control" id="password" name="password" data-minlength="8" maxlength="255"  value="{{ old('password') }}" required>
							</div>
							
							<div class="form-group col-md-6 col-xs-12">
								<label for="newpassword_confirmation">{{ __('adminlte::message.confirmpassword') }}</label><small class="help-block with-errors">*</small>
								<input required name="password_confirmation" data-minlength="8" maxlength="255" data-match="#password" class="form-control" type="password" id="newpassword_confirmation">
							</div>
						</div>
						<div class="box box-info">
							<div class="box-footer">
								<button type="submit" class="btn btn-success pull-right">{{ __('adminlte::message.register') }}</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
