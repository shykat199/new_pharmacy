<!-- partial:partials/_sidebar.html -->
@php
    $is_user = auth()->user()->role == USER_ROLE;

@endphp
<nav class="sidebar">
    <div class="sidebar-header">

        <a wire:navigate href="{{ $is_user ? route('user.dashboard') : route('admin.dashboard') }}" class="sidebar-brand">
            <h3>{{ !empty($siteSettings['shopName']) ? $siteSettings['shopName'] : env('APP_NAME') }}</h3>
        </a>

        <div class="sidebar-toggler">
            <span></span>
            <span></span>
            <span></span>
        </div>

    </div>
    <div class="sidebar-body">
        <ul class="nav" id="sidebarNav">
            @if(auth()->user()->role == ADMIN_ROLE || auth()->user()->role == USER_ROLE)
                <li class="nav-item">
                    <a wire:navigate href="{{ $is_user ? route('user.dashboard') : route('admin.dashboard') }}"
                        class="nav-link">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Dashboard</span>
                    </a>
                </li>
            @endif

            @can('get user list')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#users" role="button" aria-expanded="false"
                        aria-controls="users">
                        <i class="link-icon" data-feather="users"></i>
                        <span class="link-title">Users</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>
                    <div class="collapse" data-bs-parent="#sidebarNav" id="users">
                        <ul class="nav sub-menu">
                            <li class="nav-item">
                                <a wire:navigate href="{{ route('customer.list') }}" class="nav-link">Customers</a>
                            </li>
                            <li class="nav-item">
                                <a wire:navigate href="{{ route('staff.list') }}" class="nav-link">Staffs</a>
                            </li>

                        </ul>
                    </div>
                </li>
            @endcan

            @can('get company list')
                <li class="nav-item">
                    <a wire:navigate href="{{ route('admin.company') }}" class="nav-link">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Companies</span>
                    </a>
                </li>
            @endcan

            @can('get medicine list')
                <li class="nav-item">
                    <a wire:navigate href="{{ route('admin.medicine') }}" class="nav-link">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Medicines</span>
                    </a>
                </li>

                    <li class="nav-item">
                        <a wire:navigate href="{{route('admin.medicine',['type'=>'outofstock'])}}" class="nav-link">
                            <i class="link-icon" data-feather="box"></i>
                            <span class="link-title">Out Of Stock Medicine</span>
                        </a>
                    </li>

                <li class="nav-item">
                    <a wire:navigate href="{{ route('admin.low-medicine-stock', ['type' => 'lowstock']) }}"
                        class="nav-link">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Low Medicines Stock</span>
                    </a>
                </li>
            @endcan

            @can('get pre medicine stock list')
                <li class="nav-item">
                    <a wire:navigate href="{{ route('admin.pre-product-medicine-stock') }}" class="nav-link">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Pre Medicine Stock</span>
                    </a>
                </li>
            @endcan

            @can('get invoice list')
                <li class="nav-item">
                    <a wire:navigate href="{{ $is_user ? route('user.invoice-list') : route('admin.user-order-invoice') }}"
                        class="nav-link">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Order Invoice</span>
                    </a>
                </li>

{{--                <li class="nav-item">--}}
{{--                    <a wire:navigate href="{{ route('admin.draft-order-invoice') }}" class="nav-link">--}}
{{--                        <i class="link-icon" data-feather="box"></i>--}}
{{--                        <span class="link-title">Drafted Invoice</span>--}}
{{--                    </a>--}}
{{--                </li>--}}

                <li class="nav-item">
                    <a wire:navigate href="{{ route('user.pending-invoice-list') }}" class="nav-link">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Pending Invoice</span>
                    </a>
                </li>

                @if (auth()->user()->role == ADMIN_ROLE)
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('admin.staff-pending-invoices') }}" class="nav-link">
                            <i class="link-icon" data-feather="box"></i>
                            <span class="link-title">Staff Pending Invoice</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('admin.customer-pending-invoices') }}" class="nav-link">
                            <i class="link-icon" data-feather="box"></i>
                            <span class="link-title">Customer Pending Invoice</span>
                        </a>
                    </li>
                @endif

                @can('get staff pending list')
                    <li class="nav-item">
                        <a wire:navigate
                            href="{{ $is_user ? route('user.invoice-list') : route('admin.user-order-invoice') }}"
                            class="nav-link">
                            <i class="link-icon" data-feather="box"></i>
                            <span class="link-title">Staff Pending Invoice</span>
                        </a>
                    </li>
                @endcan

            @endcan

            @can('get acl list')
                <li class="nav-item">
                    <a wire:navigate href="{{ route('admin.site-setting') }}" class="nav-link">
                        <i class="link-icon" data-feather="settings"></i>
                        <span class="link-title">Site Setting</span>
                    </a>
                </li>
            @endcan

            @can('get site setting')
                <li class="nav-item">
                    <a wire:navigate href="{{ route('admin.acl') }}" class="nav-link">
                        <i class="link-icon" data-feather="list"></i>
                        <span class="link-title">Acl</span>
                    </a>
                </li>
            @endcan

        </ul>
    </div>
</nav>
<!-- partial -->
