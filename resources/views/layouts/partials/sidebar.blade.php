<nav class="sidebar">
	<div class="sidebar-header">
		<a href="#" class="sidebar-brand" style="color:#606060;">
			<img src="{{ asset('icon.png') }}" style="height:30px;" class="mb-1">
			SGS<span style="color:#2A8FCC;">Warrior</span>
		</a>
		<div class="sidebar-toggler not-active">
			<span></span>
			<span></span>
			<span></span>
		</div>
	</div>
	<div class="sidebar-body">
		<ul class="nav">
			@if(_checkSidebar('dashboard/shoppingreport') || _checkSidebar('brandreport') || _checkSidebar('perwhreport') || _checkSidebar('shoppingperwarrreport') || _checkSidebar('mutationwarreport') || _checkSidebar('saldowpreport'))
				<li class="nav-item nav-category">Dashboard</li>
			@endif
			
			@if(_checkSidebar('dashboard/shoppingreport') || _checkSidebar('brandreport') || _checkSidebar('perwhreport') || _checkSidebar('shoppingperwarrreport') || _checkSidebar('mutationwarreport') || _checkSidebar('saldowpreport'))
			<li class="nav-item">
				<a class="nav-link collapsed" data-toggle="collapse" href="#emails" role="button" aria-expanded="false" aria-controls="emails">
					<i class="link-icon" data-feather="trello"></i>
					<span class="link-title">Reports</span>
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down link-arrow"><polyline points="6 9 12 15 18 9"></polyline></svg>
				</a>
				<div class="collapse" id="emails" style="">
				<ul class="nav sub-menu">
					@if(_checkSidebar('dashboard/shoppingreport'))
					<li class="nav-item">
					<a href="{{ route('shoppingreport') }}" class="nav-link">Belanja</a>
					</li>
					@endif
					@if(_checkSidebar('dashboard/brandreport'))
					<li class="nav-item">
					<a href="{{ route('brandreport') }}" class="nav-link">Brand</a>
					</li>
					@endif
					@if(_checkSidebar('dashboard/perwhreport'))
					<li class="nav-item">
					<a href="{{ route('perwhreport') }}" class="nav-link">Transaksi per Cabang</a>
					</li>
					@endif
					@if(_checkSidebar('dashboard/shoppingperwarrreport'))
					<li class="nav-item">
					<a href="{{ route('shoppingperwarrreport') }}" class="nav-link">Transaksi per Warriors</a>
					</li>
					@endif
					@if(_checkSidebar('dashboard/mutationwarreport'))
					<li class="nav-item">
					<a href="{{ route('mutationwarreport') }}" class="nav-link">Mutasi Warriors</a>
					</li>
					@endif
					@if(_checkSidebar('dashboard/saldowpreport'))
					<li class="nav-item">
					<a href="{{ route('saldowpreport') }}" class="nav-link">Warpay Users</a>
					</li>
					@endif
				</ul>
				</div>
		  	</li>
		  @endif
		  
			{{-- nav --}}
			@if(_checkSidebar('users') || _checkSidebar('profile') || _checkSidebar('buyers'))
				<li class="nav-item nav-category">User Management</li>
			@endif
			@if(_checkSidebar('users'))
				<li id="users_nav_menu" class="nav-item @if(Request::segment(1) == 'users') active @endif">
					<a href="{{ route('users') }}" class="nav-link">
						<i class="link-icon" data-feather="users"></i>
						<span class="link-title">Users</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('profile'))
				<li id="profile_nav_menu" class="nav-item @if(Request::segment(1) == 'profile') active @endif">
					<a href="{{ route('profile') }}" class="nav-link">
						<i class="link-icon" data-feather="user"></i>
						<span class="link-title">Profile</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('buyers'))
				<li id="users_nav_menu" class="nav-item @if(Request::segment(1) == 'buyers') active @endif">
					<a href="{{ route('buyers') }}" class="nav-link">
						<i class="link-icon" data-feather="users"></i>
						<span class="link-title">Warriors</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('buyers-view'))
				<li id="users_nav_menu" class="nav-item @if(Request::segment(1) == 'buyers-view') active @endif">
					<a href="{{ route('buyers-view') }}" class="nav-link">
						<i class="link-icon" data-feather="users"></i>
						<span class="link-title">Warriors</span>
					</a>
				</li>
			@endif
			{{-- nav --}}
			@if(_checkSidebar('order'))
				<li class="nav-item nav-category">Order Produk</li>
			@endif
			@if(_checkSidebar('order'))
				<li id="order_nav_menu" class="nav-item @if(Request::segment(1) == 'order') active @endif">
					<a href="{{ route('order') }}" class="nav-link">
						<i class="link-icon" data-feather="shopping-cart"></i>
						<span class="link-title">Order List</span> &nbsp;&nbsp;&nbsp;
						<div id="total-order" style="display:none;">{{-- JS --}}</div>
					</a>
				</li>
			@endif

			<!-- @if(_checkSidebar('order-point'))
				<li class="nav-item nav-category">Order Produk Point</li>
			@endif
			@if(_checkSidebar('order-point'))
				<li id="order_nav_menu" class="nav-item @if(Request::segment(1) == 'order-point') active @endif">
					<a href="{{ route('order-point') }}" class="nav-link">
						<i class="link-icon" data-feather="shopping-cart"></i>
						<span class="link-title">Order List</span> &nbsp;&nbsp;&nbsp;
					</a>
				</li>
			@endif -->

			{{-- nav --}}
			@if(_checkSidebar('products') || _checkSidebar('point') || _checkSidebar('stocks'))
				<li class="nav-item nav-category">Produk</li>
			@endif
			@if(_checkSidebar('products'))
				<li id="product_nav_menu" class="nav-item {{ Request::segment(1) === 'products' ? 'active' : '' }}">
					<a href="{{ route('products') }}" class="nav-link">
						<i class="link-icon" data-feather="package"></i>
						<span class="link-title">Produk</span>
					</a>
				</li>
			@endif

			@if(_checkSidebar('product-view'))
				<li id="product_nav_menu" class="nav-item @if(Request::segment(1) == 'product-view') active @endif">
					<a href="{{ route('product-view') }}" class="nav-link">
						<i class="link-icon" data-feather="package"></i>
						<span class="link-title">Produk</span>
					</a>
				</li>
			@endif

			@if(_checkSidebar('top-product'))
				<li id="top-product_nav_menu" class="nav-item @if(Request::segment(1) == 'top-product') active @endif">
					<a href="{{ route('top-product') }}" class="nav-link">
						<i class="link-icon" data-feather="heart"></i>
						<span class="link-title">Produk Terlaris</span>
					</a>
				</li>
			@endif


			@if(_checkSidebar('point'))
				<li id="produk-poin_nav_menu" class="nav-item @if(Request::segment(1) == 'point') active @endif">
					<a href="{{ route('product-point') }}" class="nav-link">
						<i class="link-icon" data-feather="box"></i>
						<span class="link-title">Produk Poin</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('stocks'))
	            <li id="stock_nav_menu" class="nav-item @if(Request::segment(1) == 'stocks') active @endif">
					<a href="{{ route('stocks') }}" class="nav-link">
						<i class="link-icon" data-feather="archive"></i>
						<span class="link-title">Stok Produk</span>
					</a>
				</li>
			@endif

			@if(_checkSidebar('product-point') || _checkSidebar('stockproductpoint') || _checkSidebar('topproductpoint'))
				<li class="nav-item nav-category">Produk Point</li>
			@endif

			@if(_checkSidebar('product-point'))
				<li id="productpoint_nav_menu" class="nav-item @if(Request::segment(1) == 'productpoint') a-ctive @endif">
					<a href="{{ route('product-point') }}" class="nav-link">
						<i class="link-icon" data-feather="package"></i>
						<span class="link-title">Produk Point</span>
					</a>
				</li>
			@endif

			@if(_checkSidebar('topproductpoint'))
				<li id="top-product_nav_menu" class="nav-item @if(Request::segment(1) == 'topproductpoint') active @endif">
					<a href="{{ route('topproductpoint') }}" class="nav-link">
						<i class="link-icon" data-feather="heart"></i>
						<span class="link-title">Produk Point Terlaris</span>
					</a>
				</li>
			@endif

			@if(_checkSidebar('stockproductpoint'))
	            <li id="stockproductpoint_nav_menu" class="nav-item @if(Request::segment(1) == 'stockproductpoint') active @endif">
					<a href="{{ route('stockproductpoint') }}" class="nav-link">
						<i class="link-icon" data-feather="archive"></i>
						<span class="link-title">Stok Produk Point</span>
					</a>
				</li>
			@endif
			


            {{-- nav --}}
			<li id="master_data_head_nav_menu" class="nav-item nav-category">Master Data</li>
			@if(_checkSidebar('promosi'))
				<li id="promosi_nav_menu" class="nav-item @if(Request::segment(1) == 'promosi') active @endif">
					<a href="{{ route('promosi') }}" class="nav-link">
						<i class="link-icon" data-feather="shopping-bag"></i>
						<span class="link-title">Promosi</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('category'))
				<li id="category_nav_menu" class="nav-item @if(Request::segment(1) == 'category') active @endif">
					<a href="{{ route('category') }}" class="nav-link">
						<i class="link-icon" data-feather="tag"></i>
						<span class="link-title">Kategori</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('warehouse'))
				<li id="warehouse_nav_menu" class="nav-item @if(Request::segment(1) == 'warehouse') active @endif">
					<a href="{{ route('warehouse') }}" class="nav-link">
						<i class="link-icon" data-feather="home"></i>
						<span class="link-title">Gudang</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('principle'))
				<li id="principle_nav_menu" class="nav-item @if(Request::segment(1) == 'principle') active @endif">
					<a href="{{ route('principle') }}" class="nav-link">
						<i class="link-icon" data-feather="briefcase"></i>
						<span class="link-title">Principle</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('brand'))
	            <li id="brand_nav_menu" class="nav-item @if(Request::segment(1) == 'brand') active @endif">
					<a href="{{ route('brand') }}" class="nav-link">
						<i class="link-icon" data-feather="layers"></i>
						<span class="link-title">Brand</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('courier'))
				<li id="courier_nav_menu" class="nav-item @if(Request::segment(1) == 'courier') active @endif">
					<a href="{{ route('courier') }}" class="nav-link">
						<i class="link-icon" data-feather="truck"></i>
						<span class="link-title">Kurir</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('alasan'))
				<li id="alasan_nav_menu" class="nav-item @if(Request::segment(1) == 'alasan') active @endif">
					<a href="{{ route('alasan') }}" class="nav-link">
						<i class="link-icon" data-feather="cast"></i>
						<span class="link-title">Alasan</span>
					</a>
				</li>
			@endif

			{{-- nav --}}
			@if(_checkSidebar('add-point') || _checkSidebar('convertion') || _checkSidebar('settings'))
				<li id="other_head_nav_menu" class="nav-item nav-category">Lain-Lain</li>
			@endif
			@if(_checkSidebar('topup-wp'))
				<li id="topup-wp_nav_menu" class="nav-item @if(Request::segment(1) == 'topup-wp') active @endif">
					<a href="{{ route('topup-wp') }}" class="nav-link">
						<i class="link-icon" data-feather="plus-circle"></i>
						<span class="link-title">Topup Warpay</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('add-point'))
				<li id="add-point_nav_menu" class="nav-item @if(Request::segment(1) == 'add-point') active @endif">
					<a href="{{ route('add-point') }}" class="nav-link">
						<i class="link-icon" data-feather="plus-circle"></i>
						<span class="link-title">Tambah Poin</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('convertion'))
				<li id="convertion_nav_menu" class="nav-item @if(Request::segment(1) == 'convertion') active @endif">
					<a href="{{ route('convertion') }}" class="nav-link">
						<i class="link-icon" data-feather="shuffle"></i>
						<span class="link-title">Konversi Warpay</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('terms-condition'))
				<li id="terms-condition_nav_menu" class="nav-item @if(Request::segment(1) == 'terms-condition') active @endif">
					<a href="{{ route('terms-condition') }}" class="nav-link">
						<i class="link-icon" data-feather="align-left"></i>
						<span class="link-title">Terms Condition</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('transfer-wp'))
				<li id="terms-condition_nav_menu" class="nav-item @if(Request::segment(1) == 'transfer-wp') active @endif">
					<a href="{{ route('transfer-wp') }}" class="nav-link">
						<i class="link-icon" data-feather="zap"></i>
						<span class="link-title">Transfer Warpay</span>
					</a>
				</li>
			@endif
			@if(_checkSidebar('settings'))
				<li id="aplikasi_nav_menu" class="nav-item @if(Request::segment(1) == 'settings') active @endif">
					<a href="javascript:void(0)" class="nav-link">
						<i class="link-icon" data-feather="settings"></i>
						<span class="link-title">Aplikasi</span>
					</a>
				</li>
			@endif
		</ul>
	</div>
</nav>