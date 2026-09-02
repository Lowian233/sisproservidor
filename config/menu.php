<?php

use Spatie\Menu\Laravel\Menu;
use Spatie\Menu\Laravel\Html;
use Spatie\Menu\Laravel\Link;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\userController;
use App\Cliente;
use App\Permisos;


Menu::macro('adminlteMenu', function () {
	return Menu::new()
		->addClass('sidebar-menu tree')->setAttribute('data-widget','tree');
});

Menu::macro('adminlteSeparator', function ($title) {
	return Html::raw($title)->addParentClass('header');
});
Menu::macro('sidebar', function () {//COMIENZO DEL SIDEBAR EN VERSION DE MENU
	if (! Auth::guest()) {
		$user = Auth::user();

		// Compatibilidad: algunos usuarios quedaron con rol genérico "Administrador".
		// El menú ya trabaja con "AdministradorBogota" / "AdministradorPlanta".
		if (($user->UsRol ?? null) === 'Administrador') {
			$user->UsRol = 'AdministradorBogota';
		}
		if (($user->UsRol2 ?? null) === 'Administrador') {
			$user->UsRol2 = 'AdministradorBogota';
		}

		$esRolCliente = in_array($user->UsRol, Permisos::CLIENTE) || in_array($user->UsRol2, Permisos::CLIENTE);
		$emailVerificado = $user->email_verified_at <> null || app()->environment('local');
		$perfilAsociado = !$esRolCliente || $user->FK_UserPers <> null;
		$clienteIdUsuario = userController::IDClienteSegunUsuario();
		$clienteSlugUsuario = null;
		if ($clienteIdUsuario) {
			$clienteSlugUsuario = optional(Cliente::where('ID_Cli', $clienteIdUsuario)->first())->CliSlug;
		}

		if ($emailVerificado && $perfilAsociado) {
			/* Cliente en rol principal o secundario: el menú cliente debe ser coherente (antes solo "Documentos" comprobaba UsRol2). */
			$esRolCliente = in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE);
			return Menu::adminlteMenu()

				/*INICIO DEL MENU1 PARA PROSARC*/
					/*PESTAÑA DE MI EMPRESA*/
					->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC),
						(Menu::new()
							->prepend('<a href="#"><i class="fas fa-user-shield"></i> <span>Prosarc</span><i class="fas fa-angle-left pull-right" style="color:#FFFFFF;" width="18" height="18"></i></a>')
							->addParentClass('treeview')
							/*PESTAÑA DE LAS SEDES DE PROSARC*/
							->addIf(!empty($clienteSlugUsuario), Link::toUrl(url('/cliente/'.$clienteSlugUsuario), '<i class="fas fa-building"></i> <span>'.  __('adminlte::message.MenuSedes').'</span>'))
							/*PESTAÑA DE AREAS DE PROSARC*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::AREAS) || in_array(Auth::user()->UsRol2, Permisos::AREAS), Link::toUrl('/areasInterno', '<i class="fas fa-archive"></i> <span>'. __('adminlte::message.MenuPersAreas').' </span>'))
							/*PESTAÑA DE CARGOS DE PROSARC*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::CARGOS) || in_array(Auth::user()->UsRol2, Permisos::CARGOS), Link::toUrl('/cargosInterno', '<i class="fas fa-tools"></i> <span>'. __('adminlte::message.MenuPersCarg').' </span>'))
							/*PESTAÑA DE PERSONAL*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC),(Link::toUrl('/personalInterno', '<i class="fas fa-users"></i> <span>'. __('adminlte::message.MenuPersonal').'</span>')))
							/*PESTAÑA TIPOS DE DOCUMENTO (certificados, licencias para conductores/manipulación residuos)*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC), (Link::toUrl('/capacitacion', '<i class="fas fa-file-alt"></i> <span>Tipos de documento</span>')))
							/*PESTAÑA DE LOS CONTRATOS*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::CONTRATOS) || in_array(Auth::user()->UsRol2, Permisos::CONTRATOS), (Link::toUrl('/contratos', '<i class="fas fa-file-contract"></i> <span>'. /* __('adminlte::message.MenuContactos')*/'Contratos'.'</span>')))
							->addClass('treeview-menu')
						)
					)
					/*PESTAÑA DE CONTACTOS*/
					->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC),
						(Menu::new()
							->prepend('<a href="#"><i style="font-size: 1.2em; color: #ffbb33;" class="fas fa-address-book"></i> <span>Contactos</span><i class="fas fa-angle-left pull-right" style="color:#FFFFFF;" width="18" height="18"></i></a>')
							->addParentClass('treeview')
							/*PESTAÑA DE LOS PROVEEDORES*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::CONTACTOS) || in_array(Auth::user()->UsRol2, Permisos::CONTACTOS), (Link::toUrl('/contactos', '<i class="fas fa-handshake"></i> <span>'.  __('adminlte::message.MenuContactos').'</span>')))
							/*PESTAÑA DE LISTA DE CLIENTES*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::LISTACLIENTES) || in_array(Auth::user()->UsRol2, Permisos::LISTACLIENTES), (Link::toUrl('/clientes', '<i class="fas fa-users"></i> <span>'.  __('adminlte::message.MenuClien').'</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::LISTACLIENTES) || in_array(Auth::user()->UsRol2, Permisos::LISTACLIENTES), (Link::toUrl('/clientexpress', '<i style="color: #66B032;" class="fas fa-users"></i> <span>Clientes Express</span>')))
							/*PESTAÑA DE PERSONAL DEL CLIENTE*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::PERSONALCLIENTE) || in_array(Auth::user()->UsRol2, Permisos::PERSONALCLIENTE),(Link::toUrl('/personal', '<i class="fas fa-id-card"></i> <span>'. __('adminlte::message.MenuPersonal2').'</span>')))
							/*PESTAÑA DE GENERADORES*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::LISTAGENERADORES) || in_array(Auth::user()->UsRol2, Permisos::PUEDE_SOLICITAR_PLANTA),(Link::toUrl('/generadores', '<i class="fa fa-industry"></i> <span>'.  __('adminlte::message.MenuGenerClien').'</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol2, Permisos::PROGRAMADOR), (Link::toUrl('/UsersClientes', '<i class="fas fa-user-tag"></i> <span>'. /* __('adminlte::message.MenuContactos')*/'Usuarios Externos'.'</span>')))
							->addClass('treeview-menu')
						)
					)
					/*PESTAÑA DE RESIDUOS*/
					->addIf(in_array(Auth::user()->UsRol, Permisos::LISTARESIDUOS) || in_array(Auth::user()->UsRol2, Permisos::LISTARESIDUOS) || in_array(Auth::user()->UsRol, Permisos::INGDETURNO),
						(Menu::new()
							->prepend('<a href="#"><i style="font-size: 1.2em; color: #ff4444;" class="fas fa-biohazard"></i> <span>Residuos</span><i class="fas fa-angle-left pull-right" width="18" height="18"></i></a>')
							->addParentClass('treeview')
							/*PESTAÑA DE LAS SEDES DE PROSARC*/
						    ->addIf(in_array(Auth::user()->UsRol, Permisos::LISTARESIDUOS) || in_array(Auth::user()->UsRol2, Permisos::LISTARESIDUOS) || in_array(Auth::user()->UsRol, Permisos::INGDETURNO), Link::toUrl('/respels', '<i style="color: #ff4444;" class="fas fa-biohazard"></i> <span>'. __('adminlte::message.MenuRespelList').' </span>'))
							/*PESTAÑA DE RESIDUOS EXPRESS*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::LISTARESIDUOS) || in_array(Auth::user()->UsRol2, Permisos::LISTARESIDUOS), Link::toUrl('/respelsexpress', '<i style="color: #66B032;" class="fas fa-biohazard"></i> <span>Residuos Express </span>'))
							/*PESTAÑA DE TRATAMIENTOS*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::LISTARESIDUOS) || in_array(Auth::user()->UsRol2, Permisos::LISTARESIDUOS), Link::toUrl('/tratamiento', '<i class="fas fa-vial"></i> <span>'. __('adminlte::message.MenuTrataRespel').' </span>'))
							/*PESTAÑA DE CARGOS DE PRETRATAMIENTOS*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::LISTARESIDUOS) || in_array(Auth::user()->UsRol2, Permisos::LISTARESIDUOS), Link::toUrl('/pretratamiento', '<i class="fab fa-stack-overflow"></i> <span>'. __('adminlte::message.MenuPreTrataRespel').' </span>'))
							->addClass('treeview-menu')
						)
					)
					/*PESTAÑA DE TRATAMIENTOS*/
					// ->addIf(in_array(Auth::user()->UsRol, Permisos::TRATAMIENTOS) || in_array(Auth::user()->UsRol2, Permisos::TRATAMIENTOS),(Link::toUrl('/tratamiento', '<i style="font-size: 1.2em; color: YELLOW;" class="fas fa-vial"></i> <span>'. __('adminlte::message.MenuTrataRespel').'</span>')))
					/*PESTAÑA DE PRETRATAMIENTOS*/
					// ->addIf(in_array(Auth::user()->UsRol, Permisos::PRETRATAMIENTOS) || in_array(Auth::user()->UsRol2, Permisos::TRATAMIENTOS),(Link::toUrl('/pretratamiento', '<i style="font-size: 1.2em; color: YELLOW;" class="fab fa-stack-overflow"></i> <span>'. __('adminlte::message.MenuPreTrataRespel').'</span>')))
					->addIf(in_array(Auth::user()->UsRol, Permisos::RESPELPUBLIC) || in_array(Auth::user()->UsRol2, Permisos::RESPELPUBLIC),
						(Menu::new()
							->prepend('<a href="#"><i style="font-size: 1.2em; color: #66B032;" class="fas fa-globe-americas"></i> <span>'.  __('adminlte::message.RPMenu').'</span><i class="fas fa-angle-left pull-right" width="18" height="18"></i></a>')
							->addParentClass('treeview')
							/*PESTAÑA DE LISTA DE RESIDUOS COMUNES*/
							->add(Link::toUrl('/respelspublic', '<i style="color: #66B032;" class="fas fa-globe-americas"></i> <span>'.  __('adminlte::message.RPList').'</span>'))
							/*PESTAÑA DE CATEGORIAS DE RESIDUOS COMUNES*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::RESPELPUBLIC) || in_array(Auth::user()->UsRol2, Permisos::RESPELPUBLIC), Link::toUrl('/categorypublic', '<i class="fas fa-object-group"></i> <span>'. __('adminlte::message.CategoryRPMenu').' </span>'))
							/*PESTAÑA DE SUBCATEGORIAS DE RESIDUOS COMUNES*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::RESPELPUBLIC) || in_array(Auth::user()->UsRol2, Permisos::RESPELPUBLIC), Link::toUrl('/subcategorypublic', '<i class="fas fa-object-ungroup"></i> <span> SubCategorías</span>'))
							->addClass('treeview-menu')
						)
					)
					/*PESTAÑA DE VEHICULOS (incluye Conductor: lista, registrar gasolina, registrar preoperacional)*/
					->addIf(in_array(Auth::user()->UsRol, Permisos::VEHICULOS) || in_array(Auth::user()->UsRol2, Permisos::VEHICULOS) || in_array(Auth::user()->UsRol, Permisos::CombustibleVehiculo) || in_array(Auth::user()->UsRol2, Permisos::CombustibleVehiculo),
						(Menu::new()
							->prepend('<a href="#"><i style="font-size: 1.2em; color: #33b5e5;" class="fas fa-truck-moving"></i> <span>'. __('adminlte::message.MenuVehicleTitle').'</span><i class="fas fa-angle-left pull-right" style="color:#FFFFFF;" width="18" height="18"></i></a>')
							->addParentClass('treeview')
							->addIf(in_array(Auth::user()->UsRol, Permisos::INFORME_VEHICULOS) || in_array(Auth::user()->UsRol2, Permisos::INFORME_VEHICULOS), Link::toUrl('/vehicle', '<i class="fas fa-list-alt"></i> '. __('adminlte::message.MenuVehiclelist')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::CombustibleVehiculo) || in_array(Auth::user()->UsRol2, Permisos::CombustibleVehiculo), (Link::toUrl(route('vehicle.combustible.create.standalone'), '<i class="fa fa-gas-pump"></i> <span>Registrar gasolina</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR), (Link::toUrl('/vehicle-programacion/preoperacional/create', '<i class="fas fa-clipboard-check"></i> <span>Registrar preoperacional</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::VEHICULOS) || in_array(Auth::user()->UsRol2, Permisos::VEHICULOS), (Link::toUrl('/vehicle-mantenimiento', '<i class="fas fa-tools"></i> '. __('adminlte::message.MenuMantVehic'))))
							->addIf(in_array(Auth::user()->UsRol, Permisos::VEHICULOS) || in_array(Auth::user()->UsRol2, Permisos::VEHICULOS), (Link::toUrl('/Calificaciones.index', '<i class="fas fa-star"></i> Calificaciones y Comentarios')))
							->addClass('treeview-menu')
						)
					)
					/*PESTAÑA DE CRM - Nuevo sistema (incluye Cotizaciones)*/
					->addIf(in_array(Auth::user()->UsRol, Permisos::COMERCIAL) || in_array(Auth::user()->UsRol2, Permisos::COMERCIAL) || in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol2, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol, Permisos::SEDECOMERCIAL) || in_array(Auth::user()->UsRol2, Permisos::SEDECOMERCIAL) || in_array(Auth::user()->UsRol, Permisos::COTIZACION) || in_array(Auth::user()->UsRol2, Permisos::COTIZACION),
						(Menu::new()
							->prepend('<a href="#"><i style="font-size: 1.2em; color: #00C851;" class="fas fa-handshake"></i> <span>CRM</span><i class="fas fa-angle-left pull-right" style="color:#FFFFFF;" width="18" height="18"></i></a>')
							->addParentClass('treeview')
							->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::SEDECOMERCIAL) || in_array(Auth::user()->UsRol2, Permisos::SEDECOMERCIAL), Link::toUrl(route('crm.dashboard'), '<i class="fas fa-chart-line"></i> <span>Dashboard</span>'))
							->addIf(in_array(Auth::user()->UsRol, Permisos::JefeComercial) || in_array(Auth::user()->UsRol2, Permisos::JefeComercial), Link::toUrl(route('crm.gerencia'), '<i class="fas fa-chart-pie"></i> <span>Vista de Gerencia</span>'))
							->addIf(in_array(Auth::user()->UsRol, Permisos::JefeComercial) || in_array(Auth::user()->UsRol2, Permisos::JefeComercial), Link::toUrl(route('crm.gerencia.clientes-nuevos-mes'), '<i class="fas fa-user-plus"></i> <span>Clientes Nuevos por Mes</span>'))
							->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::SEDECOMERCIAL) || in_array(Auth::user()->UsRol2, Permisos::SEDECOMERCIAL) || in_array(Auth::user()->UsRol, ['Comercial', 'Comercialap', 'Ejecutivo Comercial']) || in_array(Auth::user()->UsRol2, ['Comercial', 'Comercialap', 'Ejecutivo Comercial']), Link::toUrl(route('home'), '<i class="fas fa-calendar-alt"></i> <span>Calendario</span>'))
							->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::SEDECOMERCIAL) || in_array(Auth::user()->UsRol2, Permisos::SEDECOMERCIAL), Link::toUrl(route('crm.clientes.index'), '<i class="fas fa-users"></i> <span>Mis Clientes</span>'))
							->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::SEDECOMERCIAL) || in_array(Auth::user()->UsRol2, Permisos::SEDECOMERCIAL), Link::toUrl(route('crm.actividades.index'), '<i class="fas fa-tasks"></i> <span>Actividades</span>'))
							->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::SEDECOMERCIAL) || in_array(Auth::user()->UsRol2, Permisos::SEDECOMERCIAL), Link::toUrl(route('crm.oportunidades.index'), '<i class="fas fa-project-diagram"></i> <span>Oportunidades</span>'))
							->addIf(in_array(Auth::user()->UsRol, Permisos::COTIZACION) || in_array(Auth::user()->UsRol2, Permisos::COTIZACION), Link::toUrl('/cotizacion', '<i class="fas fa-receipt"></i> <span>'. __('adminlte::message.MenuCotizacionesTitle').'</span>'))
							
							->addClass('treeview-menu')
						)
					)
                
					/*PESTAÑA DE PROGRAMACIONES DE SERVICIOS*/
					->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::PUEDE_SOLICITAR_PLANTA),
						(Menu::new()
							->prepend('<a href="#"><i style="font-size: 1.2em; color: #aa66cc;" class="fas fa-people-carry"></i> <span>Servicios</span><i class="fas fa-angle-left pull-right" style="color:#FFFFFF;" width="18" height="18"></i></a>')
							->addParentClass('treeview')
							->addIf(in_array(Auth::user()->UsRol, Permisos::SERVICIOS) || in_array(Auth::user()->UsRol2, Permisos::SERVICIOS) || in_array(Auth::user()->UsRol, Permisos::PUEDE_SOLICITAR_PLANTA), (Link::toUrl('/solicitud-servicio', '<i class="fas fa-file-invoice"></i> <span>'.  __('adminlte::message.MenuServTitleSidebar').'</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::ProgVehic2) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic2), (Link::toUrl('/serviciosRecepcionado', '<i class="fas fa-bookmark"></i> <span>Recordatorios</span>')))
						->addIf(in_array(Auth::user()->UsRol, Permisos::PROGRAMACIONES) || in_array(Auth::user()->UsRol2, Permisos::PROGRAMACIONES), (Link::toUrl('/vehicle-programacion', '<i class="fas fa-calendar-alt"></i> <span>'.  __('adminlte::message.MenuPrograVehicSidebar').'</span>')))
						->addIf(in_array(Auth::user()->UsRol, Permisos::ProgVehic1) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic1), (Link::toUrl('/vehicle-programacion/create', '<i class="fas fa-calendar-alt"></i> <span>Calendario</span>')))
						->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR), (Link::toUrl('/vehicle-programacion/historial-preoperacional', '<i class="fas fa-history"></i> <span>Historial Preoperacionales</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::ALMACENAMIENTO) || in_array(Auth::user()->UsRol2, Permisos::ALMACENAMIENTO), (Link::toUrl('/almacenamiento', '<i class="fas fa-pallet"></i> <span>'.  __('adminlte::message.MenuAlmacenSidebar').'</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::SEDECOMERCIAL) || in_array(Auth::user()->UsRol2, Permisos::SEDECOMERCIAL), (Link::toUrl('/prefacturas', '<i class="fas fa-receipt"></i> <span>Prefacturas</span>')))
							->addClass('treeview-menu')
						)
					)
					
					/*PESTAÑA DE PROGRAMACIONES DE SERVICIOS EXPRESS*/
					->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC),
						(Menu::new()
							->prepend('<a href="#"><i style="font-size: 1.2em; color: #66B032;"	 class="fas fa-shipping-fast"></i> <span>Servicios Express</span><i class="fas fa-angle-left pull-right" style="color:#FFFFFF;" width="18" height="18"></i></a>')
							->addParentClass('treeview')
							->addIf(in_array(Auth::user()->UsRol, Permisos::EXPRESS) || in_array(Auth::user()->UsRol2, Permisos::EXPRESS), (Link::toUrl('/rutadeldia', '<i class="fas fa-route fa-lg" style="color: #66B032";></i> <span>Ruta</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::EXPRESS) || in_array(Auth::user()->UsRol2, Permisos::EXPRESS), (Link::toUrl('/serviciosexpress', '<i  style="color: #66B032;" class="fas fa-file-invoice"></i> <span>Solicitudes</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::PROGRAMACIONES) || in_array(Auth::user()->UsRol2, Permisos::PROGRAMACIONES), (Link::toUrl('/programacion-express', '<i  style="color: #66B032;" class="fas fa-calendar-alt"></i> <span>'.  __('adminlte::message.MenuPrograVehicSidebar').'</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::ProgVehic1) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic1), (Link::toUrl('/programacion-express/create', '<i  style="color: #66B032;" class="fas fa-calendar-alt"></i> <span>Calendario</span>')))
							/*PESTAÑA DE COTIZACIONES EXPRESS*/
				             	->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC),(Link::toUrl('/cotizacion-expres', '<i style="font-size: 1.2em; color: #0066cc;" class="fas fa-quote-left"></i> <span>Cotizaciones Express</span>')))
							->addClass('treeview-menu')
						)
					)
					
					/*PESTAÑA DE DOCUMENTOS*/
					->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC),
						(Menu::new()
							->prepend('<a href="#"><i style="font-size: 1.2em; color: #A34B1F;" class="fas fa-file-pdf"></i> <span>Documentos</span><i class="fas fa-angle-left pull-right" style="color:#FFFFFF;" width="18" height="18"></i></a>')
							->addParentClass('treeview')
							->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC), (Link::toUrl('/certificados', '<i class="fas fa-file-contract"></i> <span>'.  __('adminlte::message.MenuCertificados').'</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC), (Link::toUrl('/recibomaterial', '<i style="color: #0000FF;" class="fas fa-file-contract"></i> <span>'. 'Recibos Material'.'</span>')))
						->addIf(
							in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) ||
							in_array(Auth::user()->UsRol, Permisos::EXPRESS) || in_array(Auth::user()->UsRol2, Permisos::EXPRESS) ||
							in_array(Auth::user()->UsRol, Permisos::CONDUCTOREXPRESS) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOREXPRESS) ||
							in_array(Auth::user()->UsRol, Permisos::SEDECOMERCIAL) || in_array(Auth::user()->UsRol2, Permisos::SEDECOMERCIAL) ||
							in_array(Auth::user()->UsRol, Permisos::ADMINISTRADORPLANTA) || in_array(Auth::user()->UsRol2, Permisos::ADMINISTRADORPLANTA)
						, (Link::toUrl(route('recibomaterialexpress.index'), '<i style="color: #66B032;" class="fas fa-file-contract"></i> <span>'. 'Recibos Material Express'.'</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC), (Link::toUrl('/certificadosexpress', '<i style="color: #66B032;" class="fas fa-file-contract"></i> <span>Certificados Express</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC), (Link::toUrl('/fotos-cliente', '<i style="color: #ff6b6b;" class="fas fa-images"></i> <span>Fotos Adicionales</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::ProgVehic2) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic2), (Link::toUrl('/verifycodes', '<i class="fas fa-hashtag"></i> <span>Códigos de Verificación</span>')))
							// ->addIf(in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol2, Permisos::PROGRAMADOR), (Link::toUrl('/manifiestos', '<i class="fas fa-file-invoice"></i> <span>'.  __('adminlte::message.MenuManifiestos').'</span>')))
							->addClass('treeview-menu')
						)
					)
				/*FIN DEL MENU1 PARA PROSARC*/

					// CODIGO PARA CREAR CABEZERA EN EL MENU
					// ->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE), (Html::raw( __('adminlte::message.MenuClienTitle'))->addParentClass('header')))

				/*INICIO DEL MENU PARA CLIENTE*/
					/*PESTAÑA DE MI CLIENTE*/
					->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE),(Menu::new()
							->prepend('<a href="#"><i class="fas fa-user-shield"></i> <span>'.  __('adminlte::message.MenuClien2').'</span><i class="fas fa-angle-left pull-right" style="color:#FFFFFF;" width="18" height="18"></i></a>')
							->addParentClass('treeview')
							/*PESTAÑA DE LAS SEDES DE PROSARC*/
							->addIf(!empty($clienteSlugUsuario), Link::toUrl(url('/cliente/'.$clienteSlugUsuario), '<i class="fas fa-building"></i> <span>'.  __('adminlte::message.MenuSedes').'</span>'))
							/*PESTAÑA DE AREAS DE PROSARC*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::AREAS), Link::toUrl('/areas', '<i class="fas fa-archive"></i> <span>'. __('adminlte::message.MenuPersAreas').' </span>'))
							/*PESTAÑA DE CARGOS DE PROSARC*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CARGOS), Link::toUrl('/cargos', '<i class="fas fa-tools"></i> <span>'. __('adminlte::message.MenuPersCarg').' </span>'))
							/*PESTAÑA DE PERSONAL*/
							->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC),(Link::toUrl('/personal', '<i class="fas fa-users"></i> <span>'. __('adminlte::message.MenuPersonal').'</span>')))
							->addClass('treeview-menu')
						)
					)
					/*PESTAÑA DE RESIDUOS*/
					->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE),(Link::toUrl('/respels', '<i style="font-size: 1.2em; color: #ff4444;" class="fa fa-biohazard"></i> <span>'.  __('adminlte::message.MenuRespelList').'</span>')))
					/*PESTAÑA DE GENERADORES*/
				    ->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol, Permisos::INGDETURNO),
					(Link::toUrl('/generadores', '<i style="font-size: 1.2em; color: #33b5e5;" class="fa fa-industry"></i> <span>'.  __('adminlte::message.MenuGenerClientitle').'</span>')))
					/*PESTAÑA DE RESIDUOS COMUNES*/
					/* ->addif(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol, Permisos::INGDETURNO),(Link::toUrl('/respelspublic', '<i style="font-size: 1.2em; color: #00C851;" class="fas fa-globe-americas"></i> <span>'. __('adminlte::message.RPList').'</span>'))) */
					/*PESTAÑA DE PERSONAL*/
					// ->addif(in_array(Auth::user()->UsRol, Permisos::CLIENTE), Link::toUrl('/areas', '<i style="font-size: 1.2em; color: #FB9902;" class="fas fa-archive"></i> <span>'. __('adminlte::message.MenuPersAreas').'</span>'))
					// ->addif(in_array(Auth::user()->UsRol, Permisos::CLIENTE), Link::toUrl('/cargos', '<i style="font-size: 1.2em; color: #8601AF;" class="fas fa-tools"></i> <span>'. __('adminlte::message.MenuPersCarg').'</span>'))
					// ->addif(in_array(Auth::user()->UsRol, Permisos::CLIENTE), Link::toUrl('/personal', '<i style="font-size: 1.2em; color: #FC600A;" class="fas fa-users"></i> <span>'. __('adminlte::message.MenuPersonal').'</span>'))
					/*PESTAÑA DE SOLICITUD*/
				    ->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol, Permisos::INGDETURNO),(Link::toUrl('/solicitud-servicio', '<i style="font-size: 1.2em; color: #aa66cc;" class="fas fa-people-carry"></i> <span>'. __('adminlte::message.MenuServTitle').'<span>')))
					// ->addif(in_array(Auth::user()->UsRol, Permisos::CLIENTE), Link::toUrl('/preguntas-frecuentes', '<i class="fas fa-question-circle"></i> <span>'. __('adminlte::message.frequent questions').'</span>'))
					/*PESTAÑA DE CALIFICACIONES PENDIENTES*/
					->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE), (Link::toUrl('/calificaciones/pendientes', '<i style="font-size: 1.2em; color: #ffc107;" class="fas fa-star"></i> <span>Calificaciones Pendientes</span>')))
					/*PESTAÑA DE DOCUMENTOS*/
					->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE),
						(Menu::new()
							->prepend('<a href="#"><i style="font-size: 1.2em; color: #A34B1F;" class="fas fa-file-pdf"></i> <span>Documentos</span><i class="fas fa-angle-left pull-right" style="color:#FFFFFF;" width="18" height="18"></i></a>')
							->addParentClass('treeview')
							->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE), (Link::toUrl('/certificados', '<i class="fas fa-file-contract"></i> <span>'.  __('adminlte::message.MenuCertificados').'</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE), (Link::toUrl('/recibomaterial', '<i style="color: #0000FF;" class="fas fa-file-contract"></i> <span>'. 'Recibos Material'.'</span>')))
							->addIf(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE), (Link::toUrl('/fotos-cliente', '<i style="color: #ff6b6b;" class="fas fa-images"></i> <span>Fotos Adicionales</span>')))
							// ->addIf(in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol2, Permisos::PROGRAMADOR), (Link::toUrl('/manifiestos', '<i class="fas fa-file-invoice"></i> <span>'.  __('adminlte::message.MenuManifiestos').'</span>')))
							->addClass('treeview-menu')

						
						)
					)
				/*FIN DEL MENU PARA EL CLIENTE*/

			->setActiveFromRequest();
		}
		else{
			return Menu::adminlteMenu()
				/*ECABEZAMIENTO TITULO*/
				->add(Html::raw( __('adminlte::message.header'))->addParentClass('header'))
				/*PESTAÑA DE INICIO / HOME*/
				->action('HomeController@index', '<i class="fa fa-home"></i> <span>'. __('adminlte::message.home').'</span>')
				// PREGUNTAS FRECUENTES
				// ->add(Link::toUrl('/preguntas-frecuentes', '<i class="fas fa-question-circle"></i> <span>'. __('adminlte::message.frequent questions').'</span>'))

				->setActiveFromRequest();
		}
	}
	else{
		return Menu::adminlteMenu()
				/*ECABEZAMIENTO TITULO*/
				->add(Html::raw( __('adminlte::message.header'))->addParentClass('header'))
				/*PESTAÑA DE INICIO / HOME*/
				->action('HomeController@index', '<i class="fa fa-home"></i> <span>'. __('adminlte::message.home').'</span>')
				// PREGUNTAS FRECUENTES
				// ->add(Link::toUrl('/preguntas-frecuentes', '<i class="fas fa-question-circle"></i> <span>'. __('adminlte::message.frequent questions').'</span>'))

				->setActiveFromRequest();
	}
});
/*VALIDACION DEL ROL CON EL QUE INGRESO*/
// ->addIf("CONDICION", LINK/ADD)