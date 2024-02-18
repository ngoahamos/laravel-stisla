@php use Illuminate\Support\Facades\Auth; @endphp

@php
    $userCompany = (Auth::check() && !Auth::user()->isSuper()) ? Auth::user()->company->logo : null;
    $companyName = $userCompany ?? 'SUSU APP';

@endphp
<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand d-flex justify-content-center" >
            <a href="/" >
                @if($companyName != 'SUSU APP')
                <div class="gallery">
                    <div class="gallery-item"
                         data-image="{{ $companyName }}"
                         data-title="Logo"></div>
                </div>
                @else
                    {{$companyName}}
                @endif


            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="/">SUSU</a>
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



        <ul class="sidebar-menu">
            <li class="nav-item dropdown {{ active_menu_class('loans') }}">
                <a href="#"
                   class="nav-link has-dropdown"><i class="fas fa-file"></i><span>Loans</span></a>
                <ul class="dropdown-menu">
                    <li class='{{ (Request::is('loans')) ? 'active' : '' }}'>
                        <a class="nav-link"
                           href="{{ route('loans.index') }}">Loan Application</a>
                    </li>

                    <li class='{{ (Request::is('loans/request')) ? 'active' : '' }}'>
                        <a class="nav-link"
                           href="{{ route('loans.request') }}">Loan Request</a>
                    </li>

                    <li class='{{ (Request::is('loans/active')) ? 'active' : '' }}'>
                        <a class="nav-link"
                           href="{{ route('loans.active') }}">Active Loans</a>
                    </li>

                    <li class='{{ (Request::is('loans/transactions')) ? 'active' : '' }}'>
                        <a class="nav-link"
                           href="{{ route('loans.transactions') }}">Transactions</a>
                    </li>


                </ul>
            </li>

        </ul>

        <ul class="sidebar-menu">
            <li class="nav-item dropdown {{ active_menu_class('reports') }}">
                <a href="#"
                   class="nav-link has-dropdown"><i class="fas fa-line-chart"></i><span>Reports</span></a>
                <ul class="dropdown-menu">
                    <li class='{{ (Request::is('reports/pending-approval')) ? 'active' : '' }}'>
                        <a class="nav-link"
                           href="{{ route('reports.pending-approval') }}">Pending Approval</a>
                    </li>

                    <li class='{{ (Request::is('reports/defaulted')) ? 'active' : '' }}'>
                        <a class="nav-link"
                           href="{{ route('reports.defaulted') }}">Defaulted</a>
                    </li>

                    <li class='{{ (Request::is('reports/bad-debts')) ? 'active' : '' }}'>
                        <a class="nav-link"
                           href="{{ route('reports.bad-debts') }}">Bad Debts</a>
                    </li>
                </ul>
            </li>

        </ul>

        <ul class="sidebar-menu">
            <li class="nav-item dropdown {{ active_menu_class('settings') }}">
                <a href="#"
                   class="nav-link has-dropdown"><i class="fas fa-gears"></i><span>Settings</span></a>
                <ul class="dropdown-menu">
                    <li class='{{ (Request::is('settings') || Request::is('settings/loan-categories') || Request::is('settings/create-loan-category') || Request::is('settings/edit-loan-category/*')) ? 'active' : '' }}'>
                        <a class="nav-link"
                           href="{{ route('settings.loan-categories') }}">Loan Category</a>
                    </li>


                </ul>
            </li>

        </ul>


    </aside>
</div>
