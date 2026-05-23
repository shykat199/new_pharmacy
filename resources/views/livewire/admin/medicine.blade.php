@push('style')
    <style>

        .select2-selection {
            width: 100% !important;
            height: 40px !important;
        }
    </style>
@endpush

<div>
    @section('admin.breadcrumb')
        <li class="breadcrumb-item" aria-current="page">{{ $page }}</li>
    @endsection
    <div class="row" id="stockItemSection">
        <div class="col-12 col-xl-12 grid-margin stretch-card">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">

                        <div wire:ignore>
                            <select class="js-example-basic-single1" id="companySearch" style="width: 300px;" onchange="companySelected()" placeholder="Select a company...">
                                <option value="">All Companies</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if (!request()->get('type') == 'lowstock')
                            @can('add medicine')
                                <button wire:click="addProduct" class="btn btn-success btn-sm btn-icon-text">
                                    <i class="fa fa-plus-circle"></i> &nbsp; Add New
                                </button>
                            @endcan
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @livewire('admin.product-table',['requestType'=>request()->get('type')])
                </div>
            </div>
        </div>
    </div> <!-- row -->

    <div wire:ignore.self class="modal fade" id="createModal" role="dialog" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"
        style="background: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="stockItemSection2">
                <form class="forms-sample" wire:submit.prevent="{{ $showEditModal ? 'updateProduct' : 'saveMedicine' }}">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $showEditModal ? 'Update product' : 'Add new product' }}</h5>
                        <button wire:click.prevent="closeModal" type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Medicine Name</label>
                            <input wire:model="name" type="text" class="form-control" id="name"
                                placeholder="Medicine name">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="mb-3">
                                <label for="company_id" class="form-label">Medicine Company</label>
                                <div wire:ignore>
                                    <select wire:click="changeEvent($event.target.value,'companyId')"
                                            class="js-example-basic-single2 form-select" id="companySelect2">
                                        <option value="" selected>Select Company</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('company_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Medicine Type</label>
                            <div wire:ignore>
                                <select onchange="productType()" class="js-example-basic-single3 form-select" id="companySelect3">
                                    <option value="" selected>Select Medicine Type</option>
                                    @foreach ($medicineType as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('m_type')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Medicine Strength</label>
                            <div wire:ignore>
                                <select onchange="productStrength()" class="js-example-basic-single4 form-select" id="companySelect4">
                                    <option value="" selected>Select Medicine Strength</option>
                                    @foreach ($medicineStrength as $strength)
                                        <option value="{{ $strength }}">{{ $strength }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('strength')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Unit Price</label>
                            <input wire:model="unitPrice" type="number" step="any" class="form-control"
                                id="unitPrice" placeholder="Medicine unit price">
                            @error('unitPrice')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Per Box Pieces</label>
                            <input wire:model="box_per_pic" type="number" step="any" class="form-control"
                                id="box_per_pic" placeholder="Medicine per box pieces">
                            @error('box_per_pic')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Low Stock Alert</label>
                            <input wire:model="low_stock" type="number" step="any" class="form-control"
                                id="low_stock" placeholder="Low Stock Alert">
                            @error('low_stock')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select wire:click="changeEvent($event.target.value)" class="form-select"
                                id="productStatus">
                                <option value="{{ ACTIVE_STATUS }}">
                                    Active
                                </option>
                                <option value="{{ PENDING_STATUS }}">
                                    Inactive
                                </option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

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

    <div wire:ignore.self class="modal fade" id="addStock" role="dialog" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"
        style="background: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form class="forms-sample" wire:submit.prevent="updateProductStock">
                    <div class="modal-header">
                        <h5 class="modal-title">Update {{ $productName ?? 'Product' }} Stock</h5>
                        <button wire:click.prevent="closeModal" type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-check form-check-inline">
                                        <input wire:model="type" type="radio" value="add"
                                            class="form-check-input" name="radioInline" id="radioInline">
                                        <label class="form-check-label" for="radioInline">
                                            Plus/Add Stock
                                        </label>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="form-check form-check-inline">
                                        <input wire:model="type" type="radio" value="deduct"
                                            class="form-check-input" name="radioInline" id="radioInline1">
                                        <label class="form-check-label" for="radioInline1">
                                            Minus/Deduct Stock
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-6">
                                    @error('type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-6">
                                    @error('type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Total Box</label>
                                        <input type="number" value="{{ getQuotient($stock, (int) $boxPerPic) }}"
                                            step="any" readonly class="form-control" placeholder="Total box">
                                    </div>
                                </div><!-- Col -->
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Total Pieces</label>
                                        <input type="number" value="{{ getRemainder($stock, (int) $boxPerPic) }}"
                                            step="1" readonly class="form-control" placeholder="Total pieces">
                                    </div>
                                </div><!-- Col -->
                            </div>

                            <div class="row mt-4">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Box</label>
                                        <input wire:model="box" type="number" step="1"
                                            class="form-control medicine-box" placeholder="Medicine box">
                                    </div>
                                </div><!-- Col -->
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Pieces</label>
                                        <input wire:model="pieces" type="number" step="1"
                                            class="form-control medicine-pieces" placeholder="Medicine pieces">
                                    </div>
                                </div><!-- Col -->
                            </div>

                        </div>
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
        window.addEventListener('showProductEditModalEvent', (event) => {

            let data = event.detail[0];
            $("#companySelect2").select2("val", data);

        });

        window.addEventListener('changeProductStrength', (event) => {
            let strength = event.detail[0];
            $('#companySelect4').val(strength).trigger('change');
        });

        window.addEventListener('changeProductType', (event) => {
            let type = event.detail[0];
            $('#companySelect3').val(type).trigger('change');
        });

        window.addEventListener('changeProductStatus', (event) => {

            let data = event.detail;
            if (Array.isArray(data)) {
                data = data[0];
            }

            const statusSelect = document.getElementById('productStatus');

            if (statusSelect) {
                statusSelect.value = data;
                statusSelect.dispatchEvent(new Event('change'));
            } else {
                console.warn('Status select not found in DOM at the time of dispatch.');
            }

        });

        $('#companySelect2').on('change', function() {
            Livewire.dispatch('changeEvent', {
                value: $(this).val(),
                type: 'companyId'
            });
        });

        Livewire.on('resetSelect2', function() {
            $('#companySelect2').val(null).trigger('change');
        });
    </script>

    <script>
        document.addEventListener('livewire:navigated', function() {
            $(".js-example-basic-single1").select2({
                dropdownParent: $('#stockItemSection'),
            });

            $(".js-example-basic-single2").select2({
                dropdownParent: $('#stockItemSection2'),
                width:'100%'
            });

            $(".js-example-basic-single3").select2({
                dropdownParent: $('#stockItemSection2'),
                width: '100%'
            });
            $(".js-example-basic-single4").select2({
                dropdownParent: $('#stockItemSection2'),
                width: '100%'
            });
        })
    </script>

    <script>

        function companySelected() {
            const company = document.getElementById('companySearch').value;

            Livewire.dispatch('companySelected', [company]);
        }

        function productType() {
            const medicineType = document.getElementById('companySelect3').value;

            Livewire.dispatch('updateType', [medicineType]);
        }

        function productStrength() {
            const strength = document.getElementById('companySelect4').value;

            Livewire.dispatch('updateStrength', [strength]);
        }
    </script>

@endpush
