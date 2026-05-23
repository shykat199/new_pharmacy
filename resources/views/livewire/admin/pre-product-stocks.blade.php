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
                        @can('add pre medicine stock')

                            <a href="" wire:click.prevent="addMedicineStock" class="btn btn-primary btn-sm btn-icon-text"><i
                                    class="fa fa-plus-circle"></i> &nbsp; Add New</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    @livewire('admin.pre-product-stocks-table')
                </div>
            </div>
        </div>
    </div> <!-- row -->

    <div wire:ignore.self class="modal fade" id="createModal" role="dialog" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="background: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form class="forms-sample" wire:submit.prevent="{{$showEditModal?'updateMedicineStock':'saveMedicineStock'}}">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $showEditModal ? 'Update medicine stock':'Add new medicine stock' }}</h5>
                        <button wire:click.prevent="closeModal" type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Order Title</label>
                            <input wire:model="name" type="text" class="form-control" id="name"
                                   placeholder="Order title">
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Short Description</label>
                            <textarea wire:model="short_description" class="form-control" id="exampleFormControlTextarea1" rows="5"></textarea>
                            @error('short_description') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>


{{--                        <div class="mb-3">--}}
{{--                            <label for="status" class="form-label">Status</label>--}}
{{--                            <select wire:model="status" class="form-select" id="productStatus">--}}
{{--                                <option disabled value="">Select status</option>--}}
{{--                                <option value="{{ACTIVE_STATUS}}">Active</option>--}}
{{--                                <option value="{{PENDING_STATUS}}">Pending</option>--}}
{{--                            </select>--}}
{{--                            @error('status') <span class="text-danger">{{ $message }}</span> @enderror--}}
{{--                        </div>--}}

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click.prevent="closeModal"
                                data-bs-dismiss="modal">Close
                        </button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
