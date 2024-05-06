<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand d-flex justify-content-center" >
            <a href="/" >
                <div class="gallery">
                    <div class="gallery-item"
                         data-image="{{ asset('/img/logo.png') }}"
                         data-title="Logo"></div>
                </div>

            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="/">MYNURSEPAL</a>
        </div>
        <ul class="sidebar-menu">
            <li class="nav-item dropdown {{ active_menu_class('dashboards') }}">
                <a href="#"
                   class="nav-link has-dropdown"><i class="fas fa-dashboard"></i><span>Dashboard</span></a>
                <ul class="dropdown-menu">
                    <li class='{{ (Request::is('dashboards/home') or Request::is('dashboards/profile')) ? 'active' : '' }}'>
                        <a class="nav-link"
                           href="{{ route('dashboards.home') }}">Home</a>
                    </li>
                </ul>
            </li>

        </ul>

    </aside>
</div>
