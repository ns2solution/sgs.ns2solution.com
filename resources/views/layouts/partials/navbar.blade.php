<nav class="navbar">
	<a href="#" class="sidebar-toggler">
		<i data-feather="menu"></i>
	</a>
	<div class="navbar-content">
		<div>
			<button class="btn btn-primary btn-gradient mr-1" style="transform:translateY(50%);color:#fff;pointer-events:none;">
				&nbsp;{{ Session::get('role') }}&nbsp;
			</button>
			@if(Session::get('warehouse') && Session::get('role') != 'Super')
				<button class="btn btn-primary btn-gradient-black" style="transform:translateY(50%);color:#fff;pointer-events:none;">
					&nbsp;{{ Session::get('warehouse') }}&nbsp;
				</button>
			@endif
		</div>
		<ul class="navbar-nav">
			<li class="nav-item dropdown nav-profile">
				@php 
					$img = Session::get('profile')->photo;
				@endphp
				<a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" draggable="false">
					<b>{{ Session::get('user')->fullname }}</b>&nbsp;
					<img src="{{ _getPhotoProfile($img) }}" style="object-fit:cover;" draggable="false">
				</a>
				<div class="dropdown-menu" aria-labelledby="profileDropdown">
					<div class="dropdown-header d-flex flex-column align-items-center">
						<div class="figure mb-3">
							<img src="{{ _getPhotoProfile($img) }}" style="object-fit:cover;" draggable="false">
						</div>
						<div class="info text-center">
							<p class="name font-weight-bold mb-0">{{ explode(' ', Session::get('user')->fullname)[0] }}</p>
							<p class="email text-muted mb-3">{{ Session::get('user')->email }}</p>
						</div>
					</div>
					<div class="dropdown-body">
						<ul class="profile-nav p-0 pt-3">
							<li class="nav-item">
								<a href="{{ route('my-profile') }}" class="nav-link">
									<i data-feather="user"></i>
									<span>Profil Saya</span>
								</a>
							</li>
							<li class="nav-item">
								<a href="{{ route('logout') }}" class="nav-link">
									<i data-feather="log-out"></i>
									<span>Keluar</span>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</li>
		</ul>
	</div>
</nav>