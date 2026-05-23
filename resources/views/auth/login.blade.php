<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Responsive HTML Admin Dashboard Template based on Bootstrap 5">
    <meta name="author" content="NobleUI">
    <meta name="keywords" content="nobleui, bootstrap, bootstrap 5, bootstrap5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

    <title>Login | {{env('APP_NAME')}}</title>

    <script src="{{asset('assets/js/color-modes.js')}}"></script>


    <link rel="stylesheet" href="{{asset('assets/vendors/core/core.css')}}">

    <link rel="stylesheet" href="{{asset('assets/fonts/feather-font/css/iconfont.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/demo1/style.css')}}">

    <link rel="shortcut icon" href="{{asset('assets/images/favicon.png')}}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        .swal2-popup {
            background-color: #19222c !important;
            color: #ffffff !important;
        }
        .swal2-title {
            color: #ffffff !important;
        }
        .swal2-content {
            color: #dcdcdc !important;
        }
        .swal2-confirm {
            background-color: #4CAF50 !important;
            border-color: #4CAF50 !important;
        }
        .swal2-cancel {
            background-color: #d33 !important;
            border-color: #d33 !important;
        }
    </style>
</head>
<body>
@include('sweetalert::alert')
<div class="main-wrapper">
    <div class="page-wrapper full-page">
        <div class="page-content d-flex align-items-center justify-content-center">

            <div class="row w-100 mx-0 auth-page">
                <div class="col-md-10 col-lg-8 col-xl-6 mx-auto">
                    <div class="card">
                        <div class="row">

                            <div class="col-md-12 ps-md-0">
                                <div class="auth-form-wrapper px-4 py-5">
                                    <div class="d-flex justify-content-center">
                                        <div class="text-center">
                                            <a href="#" class="nobleui-logo d-block mb-2">
                                                {{ getSettingsData('shopName') ?? 'Pharmacy' }}
                                            </a>
                                            <h5 class="text-secondary fw-normal mb-4">
                                                Welcome back! Log in to your account.
                                            </h5>
                                        </div>
                                    </div>
                                    <form class="forms-sample" action="{{route('auth.login')}}" method="post">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="userEmail" class="form-label">Email address/Phone Number</label>
                                            <input type="text" name="email" class="form-control" id="userEmail" placeholder="Email/Phone">
                                            @error('email')
                                            <span class="text-danger">{{$message}}</span>
                                            @enderror
                                        </div>
{{--                                        <div class="mb-3">--}}
{{--                                            <label for="userPassword" class="form-label">Password</label>--}}
{{--                                            <input type="password" name="password" class="form-control" id="userPassword" autocomplete="current-password" placeholder="Password">--}}
{{--                                            @error('password')--}}
{{--                                            <span class="text-danger">{{$message}}</span>--}}
{{--                                            @enderror--}}
{{--                                        </div>--}}

                                        <div class="position-relative mb-3">
                                            <label for="userPassword" class="form-label">Password</label>
                                            <input type="password" name="password" id="password" class="form-control" autocomplete="current-password" placeholder="Password">

                                            <span toggle="#password" class="fa fa-fw fa-eye toggle-password"
                                                  style="position: absolute;top: 70%;right: 15px;transform: translateY(-50%);cursor: pointer;padding: 6px;border-radius: 5px;font-size: 16px;color: #333;">
                                            </span>

                                            @error('password') <span class="text-danger">{{$message}}</span> @enderror
                                        </div>

                                        <div>
                                            <button type="submit" class="btn btn-primary me-2 mb-2 mb-md-0 text-white">Login</button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="{{asset('assets/vendors/core/core.js')}}"></script>
<script src="{{asset('assets/vendors/feather-icons/feather.min.js')}}"></script>
<script src="{{asset('assets/js/app.js')}}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.querySelector('.toggle-password');
        const passwordField = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Toggle icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });
</script>

</body>

</html>
