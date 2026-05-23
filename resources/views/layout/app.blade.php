<!DOCTYPE html>
<html lang="en" >
@php
$siteSettings = getSettingsData(['shopName','site_favicon','site_logo']);
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Pharmacy Invoice">
    <meta name="author" content="Pharmacy Invoice">
    <meta name="keywords" content="Pharmacy Invoice">

    <title>{{@$title}} | {{env('APP_NAME')}}</title>

    <!-- core:css -->
    <link rel="stylesheet" href="{{asset('assets/vendors/core/core.css')}}">
    <!-- endinject -->
    <link rel="stylesheet" href="{{asset('assets/vendors/sweetalert2/sweetalert2.min.css')}}" >

    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{asset('assets/vendors/flatpickr/flatpickr.min.css')}}">
    <!-- End plugin css for this page -->

    <!-- Layout styles -->
    <link rel="stylesheet" href="{{asset('assets/css/demo1/style.css')}}">
    <!-- End layout styles -->

    <link rel="shortcut icon" href="{{!empty($siteSettings['site_favicon']) ?  asset('storage/'.$siteSettings['site_favicon'])  :asset('assets/images/favicon.png')}}" />
{{--    <link rel="stylesheet" href="{{asset('assets/css/font-awesome.min.css')}}" data-navigate-track>--}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" >
    <link rel="stylesheet" href="{{asset('assets/vendors/select2/select2.min.css')}}">


    @livewireStyles

    @stack('style')
    @vite('resources/js/app.js')
</head>
<body>
<div class="main-wrapper">
    @include('layout.sidebar')

    <div class="page-wrapper">

        @include('layout.nav-bar')

        <div class="page-content">

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-dot">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Dashboard</a></li>
                    @yield('admin.breadcrumb')
                </ol>
            </nav>

            @section('content')

            @show

            {{@$slot}}

        </div>

    </div>
</div>


<!-- color-modes:js -->
<script src="{{asset('assets/js/color-modes.js')}}" data-navigate-once></script>
<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}" data-navigate-once></script>
<!-- endinject -->

<!-- core:js -->
<script src="{{asset('assets/vendors/core/core.js')}}" data-navigate-once></script>
<!-- endinject -->

<!-- Plugin js for this page -->
<script src="{{asset('assets/vendors/flatpickr/flatpickr.min.js')}}"></script>
{{--<script src="{{asset('assets/vendors/apexcharts/apexcharts.min.js')}}"></script>--}}
<!-- End plugin js for this page -->

<!-- inject:js -->
<script src="{{asset('assets/vendors/feather-icons/feather.min.js')}}" data-navigate-once></script>
<script src="{{asset('assets/js/app.js')}}"></script>
<!-- endinject -->

<!-- Custom js for this page -->
{{--<script src="{{asset('assets/js/dashboard.js')}}"></script>--}}
<!-- End custom js for this page -->
<script src="{{asset('assets/vendors/sweetalert2/sweetalert2.min.js')}}" data-navigate-once></script>
<script src="{{asset('assets/js/template.js')}}"></script>
<script src="{{asset('assets/vendors/select2/select2.min.js')}}" data-navigate-once></script>
<script src="{{asset('assets/js/sweetalert2.js')}}" data-navigate-once></script>
{{--<script src="{{asset('assets/js/select2.js')}}" data-navigate-once></script>--}}

@livewireScripts
@stack('scripts')

<script>
    window.addEventListener('toast',event =>{
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        Toast.fire({
            icon: event.detail.type,
            title: event.detail.message
        });
    });

    window.addEventListener('deleteEvent',event =>{

        const id = event.detail.id;
        const stock = event.detail.stock;

        if(stock == 'release_stock'){
            Swal.fire({
                title: 'Are you sure?',
                text: "This will automatically sync with existence medicine!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, do it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.Livewire.dispatch('releaseStock',[id]);
                }
            });
        }else {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.Livewire.dispatch('delete',[id]);
                }
            });
        }
    });


    window.addEventListener('deleteInvoiceItemEvent',event =>{

        const id = event.detail[0].id;
        const position = event.detail[0].position;


        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.Livewire.dispatch('deleteItem',[id,position]);
            }
        });
    });


    window.addEventListener('editEvent',event =>{
        const data = event.detail.id;

    });

    window.addEventListener('openModal', (event) => {

        let data = event.detail;

        if (Array.isArray(data)) {
            data = data[0];
        }
        console.log(data,'data')
        if (data.edit_modal){
            Livewire.dispatch('editProduct', [data.productId]);
        }else if (data.modalType == 'addStock'){
            Livewire.dispatch('addStock', [data.id]);
        }

        $(`#${data.modalId}`).modal('show');
    });

    window.addEventListener('closeModal', (event) => {
        let data = event.detail;
        if (Array.isArray(data)) {
            data = data[0];
        }

        const modal = $(`#${data.modalId}`);
        modal.modal('hide');

        // Reset Select2 inside the modal
        modal.find('select').each(function() {
            const select = $(this);
            if (select.hasClass('select2-hidden-accessible')) {
                // Reset the value and trigger change to update Select2
                select.val('').trigger('change');
            }
        });

        Livewire.dispatch('resetField');
    });

    Livewire.on('refresh-browser', () => {
        location.reload();
    });

</script>

<script>
    window.addEventListener('downloadPdf', (event) => {
        let data = event.detail;
        let fileUrl = '';

        if (Array.isArray(data)) {
            fileUrl = data[0][0];
        }

        if (fileUrl) {
            // Open PDF in a small centered preview window (like print preview)
            const width = 900;
            const height = 600;
            const left = (window.innerWidth / 2) - (width / 2);
            const top = (window.innerHeight / 2) - (height / 2);

            window.open(
                fileUrl,
                '_blank',
                `toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=${width},height=${height},top=${top},left=${left}`
            );

            Livewire.dispatch('fileDownloaded', [fileUrl]);
        } else {
            console.error('PDF file URL not found');
        }
    });

    window.addEventListener('errorMessage', event => {
        alert(event.detail.message);
    });

    window.addEventListener('reloadPage',function (){
        setTimeout(function (){
            location.reload();
        },2000)
    })
</script>

<script>
    window.addEventListener('DOMContentLoaded', () => {

        document.addEventListener('livewire:navigated',function (){

            const body = document.body;
            const sidebar = document.querySelector('.sidebar');
            const sidebarBody = document.querySelector('.sidebar .sidebar-body');

            // Close sidebar on click outside in phone/tablet
            const mainWrapper = document.querySelector('.main-wrapper');

            // Sidebar toggle to sidebar-folded
            const sidebarTogglers = document.querySelectorAll('.sidebar-toggler');
            // there are two sidebar togglers.
            // 1: on sidebar - for min-width 992px (laptop, desktop)
            // 2: on navbar - for max-width 991px (mobile phone, tablet)

            if (sidebarTogglers.length) {

                sidebarTogglers.forEach( toggler => {

                    toggler.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.querySelector('.sidebar .sidebar-toggler').classList.toggle('active');
                        if (window.matchMedia('(min-width: 992px)').matches) {
                            body.classList.toggle('sidebar-folded');
                        } else if (window.matchMedia('(max-width: 991px)').matches) {
                            body.classList.toggle('sidebar-open');
                        }
                    });

                });

                // To avoid layout issues, remove body and toggler classes on window resize.
                window.addEventListener('resize', function(event) {
                    body.classList.remove('sidebar-folded', 'sidebar-open');
                    document.querySelector('.sidebar .sidebar-toggler').classList.remove('active');
                }, true);

            }

            if (sidebar) {
                document.addEventListener('touchstart', function(e) {
                    if (e.target === mainWrapper && body.classList.contains('sidebar-open')) {
                        body.classList.remove('sidebar-open');
                        document.querySelector('.sidebar .sidebar-toggler').classList.remove('active');
                    }
                });
            }
        })

    })
</script>

<script>
    document.addEventListener("livewire:navigated", function () {
        const dateInput = document.getElementById('invoiceDatePicker');

        if (dateInput) {
            dateInput.addEventListener('change', function () {

                const rawDate = this.value;

                let dateObj = new Date(rawDate);
                const year = dateObj.getFullYear();
                const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                const day = String(dateObj.getDate()).padStart(2, '0');

                const formattedDate = `${year}-${month}-${day}`;
                console.log(formattedDate,'formattedDate')

                Livewire.dispatch('dateSelected', [formattedDate]);
            });
        }
    });
</script>
</body>
</html>
