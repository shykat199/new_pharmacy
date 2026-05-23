@push('style')
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-selection {
            width: 100% !important;
            height: 40px !important;
        }

    </style>
@endpush
<div>
    @section('admin.breadcrumb')
        <li class="breadcrumb-item" aria-current="page">{{$page}}</li>
    @endsection
    <div class="row">
        <div class="col-12 col-xl-12 grid-margin stretch-card">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>{{$page}}</h3>
                        @can('add invoice')

                            <a href="{{route('admin.create-invoice')}}"  class="btn btn-primary btn-sm btn-icon-text"><i
                                    class="fa fa-plus-circle"></i> &nbsp; Create Invoice</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    @livewire('staff-pending-table',['authUser'=>Auth::user()])
                </div>
            </div>
        </div>
    </div> <!-- row -->

</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:navigated', function () {

            $(".js-example-basic-single1").select2({
                dropdownParent: $('#createModal')
            });

            $('#companySelect2').on('change', function (e) {
                let value = $(this).val();
                Livewire.dispatch('changeEvent', [value, 'dropdown']);
            });
        });
    </script>

    <script>
        window.addEventListener('showProductEditModalEvent', (event) => {

            let data = event.detail;

            if (Array.isArray(data)) {
                data = data[0];
            }
            $("#companySelect2").select2("val", data);
            // $('#companySelect2').val(data).trigger('change');
        });

        $('#companySelect2').on('change', function () {
            Livewire.dispatch('changeEvent', {value: $(this).val(), type: 'companyId'});
        });

        Livewire.on('resetSelect2', function () {
            $('#companySelect2').val(null).trigger('change');
        });
    </script>
@endpush
