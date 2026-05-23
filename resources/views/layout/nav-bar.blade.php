<!-- partial:partials/_navbar.html -->
<nav class="navbar">
    <div class="navbar-content">

        <ul class="navbar-nav">
            <li class="theme-switcher-wrapper nav-item">
                <input type="checkbox" value="" id="theme-switcher">
                <label for="theme-switcher">
                    <div class="box">
                        <div class="ball"></div>
                        <div class="icons">
                            <i class="feather icon-sun"></i>
                            <i class="feather icon-moon"></i>
                        </div>
                    </div>
                </label>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="w-30px h-30px ms-1 rounded-circle" src="{{asset('assets/images/faces/face1.jpg')}}" alt="profile">
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                    <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                        <div class="mb-3">
                            <img class="w-80px h-80px rounded-circle" src="{{asset('assets/images/faces/face1.jpg')}}" alt="">
                        </div>
                        <div class="text-center">
                            <p class="fs-16px fw-bolder">{{auth()->user()->name}}</p>
                            <p class="fs-12px text-secondary">{{auth()->user()->email}}</p>
                        </div>
                    </div>
                    <ul class="list-unstyled p-1">

                        <li class="dropdown-item py-2">
                            <a href="{{route('user.profile')}}" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="edit"></i>
                                <span>Profile</span>
                            </a>
                        </li>
                        <li class="dropdown-item py-2">
                            <a href="{{\Illuminate\Support\Facades\Auth::user()->role == USER_ROLE ? route('user.logout') : route('admin.logout')}}" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="log-out"></i>
                                <span>Log Out</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>

        <a href="#" class="sidebar-toggler">
            <i data-feather="menu"></i>
        </a>

    </div>
</nav>
<!-- partial -->
