@extends('layout.app')
@section('Profile','title')
@php
    @$title = 'Profile';
@endphp
@section('admin.breadcrumb')
    @include('sweetalert::alert')
    <li class="breadcrumb-item" aria-current="page">User Profile</li>
@endsection
@section('content')
    <div class="row">
        <form class="forms-sample" action="{{route('update-profile')}}" method="post">
            @csrf
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h3 class="mb-3 mb-md-0">Update User Profile</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3 mb-md-0">
                                <label for="company_id" class="form-label">Name</label>
                                <input class="form-control productInput" type="text"
                                       name="name"
                                       value="{{auth()->user()->name}}"
                                       placeholder="User Name">

                            </div>
                            <div class="col-12 col-md-6 mb-3 mb-md-0">
                                <label for="company_id" class="form-label">User Phone</label>
                                <input class="form-control productInput" value="{{auth()->user()->phone}}" type="text" name="phone"
                                       placeholder="User phone">
                            </div>
                            <div class="col-12 col-md-6 mt-2 mb-3 mb-md-0">
                                <label for="company_id" class="form-label">User Email</label>
                                <input class="form-control productInput" type="text" name="email"
                                       value="{{auth()->user()->email}}"
                                       placeholder="User email">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <label for="name" class="form-label">User Address</label>
                                <textarea name="address" class="form-control" id="exampleFormControlTextarea1" rows="5" spellcheck="false">{{auth()->user()->address}}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Update Profile</button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <div class="row">
        <form class="forms-sample" action="{{ route('update-Password') }}" method="POST">
            @csrf
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <h3 class="mb-3 mb-md-0">Change Password</h3>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <!-- Old Password -->
                            <div class="col-12 mb-3" x-data="{ show: false }">
                                <label class="form-label">Old Password</label>
                                <div class="position-relative">
                                    <input
                                        class="form-control"
                                        :type="show ? 'text' : 'password'"
                                        name="old_pass"
                                        placeholder="Old password"
                                    >
                                    <span
                                        @click="show = !show"
                                        class="fa fa-fw"
                                        :class="show ? 'fa-eye-slash' : 'fa-eye'"
                                        style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer; font-size: 16px;">
                                    </span>
                                </div>
                                @error('old_pass') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- New Password -->
                            <div class="col-12 mb-3" x-data="{ show: false }">
                                <label class="form-label">New Password</label>
                                <div class="position-relative">
                                    <input
                                        class="form-control"
                                        :type="show ? 'text' : 'password'"
                                        name="password"
                                        placeholder="New password"
                                    >
                                    <span
                                        @click="show = !show"
                                        class="fa fa-fw"
                                        :class="show ? 'fa-eye-slash' : 'fa-eye'"
                                        style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer; font-size: 16px;">
                                    </span>
                                </div>
                                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-12 mb-3" x-data="{ show: false }">
                                <label class="form-label">Confirm Password</label>
                                <div class="position-relative">
                                    <input
                                        class="form-control"
                                        :type="show ? 'text' : 'password'"
                                        name="password_confirmation"
                                        placeholder="Confirm password"
                                    >
                                    <span
                                        @click="show = !show"
                                        class="fa fa-fw"
                                        :class="show ? 'fa-eye-slash' : 'fa-eye'"
                                        style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer; font-size: 16px;">
                                    </span>
                                </div>
                                @error('password_confirmation') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Change Password</button>
                    </div>
                </div>
            </div>
        </form>

    </div>
    @if(Auth::user()->role == ADMIN_ROLE)
        <div class="row">
            <form class="forms-sample" action="{{ route('update-access-Password') }}" method="POST">
                @csrf
                <div class="col-md-12 grid-margin stretch-card">
                    <div class="card overflow-hidden">
                        <div class="card-header">
                            <h3 class="mb-3 mb-md-0">Change Access Password</h3>
                        </div>
                        <div class="card-body">

                            <div class="row">
                                <!-- Old Password -->
                                <div class="col-12 mb-3" x-data="{ show: false }">
                                    <label class="form-label">Old Password</label>
                                    <div class="position-relative">
                                        <input
                                            class="form-control"
                                            :type="show ? 'text' : 'password'"
                                            name="access_old_pass"
                                            placeholder="Old password"
                                        >
                                        <span
                                            @click="show = !show"
                                            class="fa fa-fw"
                                            :class="show ? 'fa-eye-slash' : 'fa-eye'"
                                            style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer; font-size: 16px;">
                                    </span>
                                    </div>
                                    @error('access_old_pass') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- New Password -->
                                <div class="col-12 mb-3" x-data="{ show: false }">
                                    <label class="form-label">New Password</label>
                                    <div class="position-relative">
                                        <input
                                            class="form-control"
                                            :type="show ? 'text' : 'password'"
                                            name="access_password"
                                            placeholder="New password"
                                        >
                                        <span
                                            @click="show = !show"
                                            class="fa fa-fw"
                                            :class="show ? 'fa-eye-slash' : 'fa-eye'"
                                            style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer; font-size: 16px;">
                                    </span>
                                    </div>
                                    @error('access_password') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-12 mb-3" x-data="{ show: false }">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="position-relative">
                                        <input
                                            class="form-control"
                                            :type="show ? 'text' : 'password'"
                                            name="access_password_confirmation"
                                            placeholder="Confirm password"
                                        >
                                        <span
                                            @click="show = !show"
                                            class="fa fa-fw"
                                            :class="show ? 'fa-eye-slash' : 'fa-eye'"
                                            style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer; font-size: 16px;">
                                    </span>
                                    </div>
                                    @error('access_password_confirmation') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary mt-3">Change Password</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    @endif
@endsection
