<!-- Main Header -->
<header class="main-header" style="height: 50px;">

	<!-- Logo -->
	<a href="{{ url('/home') }}" class="logo" style="height: 100%;">
		<!-- mini logo for sidebar mini 50x50 pixels -->
		<span class="logo-mini"><img src="{{ asset('/img/LogoProsarc.png')}}" style="width: 60%; margin: 5px; border-radius: 50%;"></span>
		<!-- logo for regular state and mobile devices -->
		<span class="logo-lg"><b>{{ __('adminlte::message.Appname') }}</b><img src="{{ asset('/img/LogoProsarc.png')}}" style="width: 1.8em; margin: 5px; border-radius: 50%;"></span>
	</a>

	<!-- Header Navbar -->
	<nav id="topLogo" class="navbar navbar-static-top" role="navigation" style="height: 100%">
		<!-- Sidebar toggle button-->
		<a href="#" class="fas fa-bars" data-toggle="push-menu" role="button" style="cursor: pointer; color: #ffffff; font-size: 20px; margin: 15px">
			
		</a>
		{{-- <a href="#" class="fa fa-bars" data-toggle="push-menu" role="button" style="cursor: pointer; font-size: 14px; box-sizing: border-box; padding: 15px 15px; overflow: visible; color:#FFFFFF; float: left;">
			<i class="fas fa-bars"></i>
		</a> --}}
		<!-- Navbar Right Menu -->
		<div class="navbar-custom-menu" style="height: 100%;">
			<ul class="nav navbar-nav" style="height: 100%;">
				<!-- Messages: style can be found in dropdown.less-->
				{{-- <li class="dropdown messages-menu">
					<!-- Menu toggle button -->
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-envelope"></i>
						<span class="label label-success">10    </span>
					</a>
					<ul class="dropdown-menu">
						<li class="header">{{ __('adminlte::message.tabmessages') }}</li>
						<li>
							<!-- inner menu: contains the messages -->
							<ul class="menu">
								<li><!-- start message -->
									<a href="#">
										<div class="pull-left">
											<!-- User Image -->
											<img src="{{ asset('../../../images/{{ Auth::user()->UsAvatar }}')}}" class="img-circle" alt="User Image"/>
										</div>
										<!-- Message title and timestamp -->
										<h4>
											{{ __('adminlte::message.supteam') }}
											<small><i class="fa fa-clock"></i> 5 mins</small>
										</h4>
										<!-- The message -->
										<p>{{ __('adminlte::message.awesometheme') }}</p>
									</a>
								</li><!-- end message -->
							</ul><!-- /.menu -->
						</li>
						<li class="footer"><a href="#">c</a></li>
					</ul>
				</li> --}}<!-- /.messages-menu -->

				<!-- Notifications Menu -->
				{{-- <li class="dropdown notifications-menu">
					<!-- Menu toggle button -->
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-bell"></i>
						<span class="label label-warning">10</span>
					</a>
					<ul class="dropdown-menu">
						<li class="header">{{ __('adminlte::message.notifications') }}</li>
						<li>
							<!-- Inner Menu: contains the notifications -->
							<ul class="menu">
								<li><!-- start notification -->
									<a href="#">
										<i class="fa fa-users text-aqua"></i> {{ __('adminlte::message.newmembers') }}
									</a>
								</li><!-- end notification -->
							</ul>
						</li>
						<li class="footer"><a href="#">{{ __('adminlte::message.viewall') }}</a></li>
					</ul>
				</li> --}}
				<!-- Tasks Menu -->
				{{-- <li class="dropdown tasks-menu">
					<!-- Menu Toggle Button -->
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-flag"></i>
						<span class="label label-danger">9</span>
					</a>
					<ul class="dropdown-menu">
						<li class="header">{{ __('adminlte::message.tasks') }}</li>
						<li>
							<!-- Inner menu: contains the tasks -->
							<ul class="menu">
								<li><!-- Task item -->
									<a href="#">
										<!-- Task title and progress text -->
										<h3>
											{{ __('adminlte::message.tasks') }}
											<small class="pull-right">20%</small>
										</h3>
										<!-- The progress bar -->
										<div class="progress xs">
											<!-- Change the css width attribute to simulate progress -->
											<div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
												<span class="sr-only">20% {{ __('adminlte::message.complete') }}</span>
											</div>
										</div>
									</a>
								</li><!-- end task item -->
							</ul>
						</li>
						<li class="footer">
							<a href="#">{{ __('adminlte::message.alltasks') }}</a>
						</li>
					</ul>
				</li> --}}
			   {{--  @if (Auth::guest())
					<li><a href="{{ url('/register') }}">{{ __('adminlte::message.register') }}</a></li>
					<li><a href="{{ url('/login') }}">{{ __('adminlte::message.login') }}</a></li>
				@else --}}
			</ul>
		</div>
	</nav>
</header>