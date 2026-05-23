@extends('layout.app')
@section('page','Add Role Permission')
@php
    $title = 'Add Role Permission';
@endphp
@section('page-title',$title)
@push('style')
    <style>
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .item {
            flex: 1 1 10%;
            box-sizing: border-box;
            padding: 10px;
        }
    </style>
@endpush
@section('content')
    @include('sweetalert::alert')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <form action="{{route('update-user-role-permission',$role->id)}}" method="post">
                @csrf
                <div class="row">
                    <div class="col-lg">
                        <input class="form-control" name="role" readonly placeholder="Input box"
                               value="{{ucfirst($role->name)}}" type="text">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-lg">
                        <div class="card rounded-0">
                            <div class="card-header card-header-default">
                                Permission
                            </div>

                            <div class="card-body">
                                <div class="container">
                                    @foreach ($chunkData as $chunk)
                                        <div class="row">
                                            @foreach ($chunk as $permission)

                                                <div class="item">
                                                    <label class="ckbox" id="permission-{{ $permission['id'] }}">
                                                        <input name="permission[]"
                                                               {{isset($rolePermissionsIds) && !empty($rolePermissionsIds) && in_array($permission['id'] ,$rolePermissionsIds) ?'checked':''}}
                                                               id="{{ $permission['id'] }}"
                                                               value="{{ $permission['id'] }}"
                                                               type="checkbox">
                                                        <span data-orginalPermissionName="{{$permission['name']}}">{{ ucfirst(str_replace('-',' ',$permission['name']))}}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach

                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-2">
                                        <button class="btn btn-primary btn-block">Update</button>
                                    </div>
                                </div>


                            </div>


                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $(function() {

            showSwal = function(type,url) {
                'use strict';
                if (type === 'passing-parameter-execute-cancel') {
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-danger me-2'
                        },
                        buttonsStyling: false,
                    })

                    swalWithBootstrapButtons.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonClass: 'me-2',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'No, cancel!',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.value) {

                            window.location.href = url;
                            // swalWithBootstrapButtons.fire(
                            //     'Deleted!',
                            //     'Your file has been deleted.',
                            //     'success'
                            // ).then(() => {
                            //     window.location.href = url;
                            // });

                        } else if (
                            result.dismiss === Swal.DismissReason.cancel
                        ) {
                            swalWithBootstrapButtons.fire(
                                'Cancelled',
                                'Your imaginary file is safe :)',
                                'error'
                            )
                        }
                    })
                }
            }

        });
    </script>
@endpush
