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
        <li class="breadcrumb-item" aria-current="page">{{ $title1 }}</li>
    @endsection

    <div class="row" id="stockItemSection">
        <form wire:submit.prevent="saveMedicineStock">
            <div class="col-12 col-xl-12 grid-margin stretch-card">
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3>{{ $title1 }}</h3>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Order Title</label>
                            <input wire:model="name" type="text" class="form-control" id="name"
                                placeholder="Order title" x-on:input="debounceSave()">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="short_description" class="form-label">Short Description</label>
                            <textarea wire:model="short_description" class="form-control" id="exampleFormControlTextarea1" rows="5"
                                x-on:input="debounceSave()"></textarea>
                            @error('short_description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-12 grid-margin stretch-card">
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3>{{ $title2 }}</h3>

                            @if ($stockStatus != ACTIVE_STATUS)
                                <button wire:click="updateProductStock" type="submit"
                                    class="btn btn-success approve_btn ">
                                    <span>Approve Stock</span>
                                </button>
                            @endif

                        </div>
                    </div>
                    <div class="card-body">
                        <div class="modal-body">
                            <div class="row">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Medicine Company</label>
                                    <div wire:ignore>
                                        <select wire:model="company_id" class="form-select js-example-basic-single1"
                                            id="companySelect2" x-on:input="debounceSave()">
                                            <option value="" selected>Select Company</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @error('company_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div id="stock-item-section">
                                @if ($stockItems)
                                    @foreach ($stockItems as $key => $product)
                                        @php
                                            if(!empty($product->name)){
                                                 $productName =  $product->name .' '.  $product->strength .' '. \Illuminate\Support\Str::limit($product->type,3,'') .' '.'-'.' '. \Illuminate\Support\Str::limit($product->company->name,3,'');
                                            }else{
                                               $productName =  $product->product->name .' '. $product->product->strength.' '.\Illuminate\Support\Str::limit($product->product->type,3,'').' '.'-'.' '. \Illuminate\Support\Str::limit($product->company->name,3,'');
                                            }
                                        @endphp
                                        <div class="row stock-item">
                                            <div class="col-12 col-lg-3 col-md-4 col-sm-6">
                                                <div class="mb-3">
                                                    <label for="product_id-{{$product->id}}" class="form-label">Medicine Name</label>
                                                    <input wire:model="medicine_name.{{ $key }}"
                                                        value="{{$productName}}" readonly
                                                        type="text" class="form-control" placeholder="Medicine name">
                                                    <input readonly wire:model="product_id.{{ $key }}"
                                                        type="hidden" class="form-control" placeholder="Medicine id">
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-3 col-md-4 col-sm-6">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="mb-3">
                                                            <label for="product_id" class="form-label">Total Box</label>
                                                            <input readonly type="number"
                                                                value="{{ getQuotient($product->stock, $product->box_per_pic) ?? getQuotient($product->product->stock, $product->product->box_per_pic) }}"
                                                                step="0.01" class="form-control"
                                                                placeholder="Medicine stock">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="mb-3">
                                                            <label for="product_id" class="form-label">Total
                                                                Pieces</label>
                                                            <input readonly type="number"
                                                                value="{{ getRemainder($product->stock, $product->box_per_pic) ?? getRemainder($product->product->stock, $product->product->box_per_pic) }}"
                                                                step="0.01" class="form-control"
                                                                placeholder="Medicine stock">
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-12 col-lg-3 col-md-4 col-sm-6">
                                                <div class="mb-3">
                                                    <label for="box_{{ $key }}" class="form-label">Medicine
                                                        Box</label>
                                                    <input wire:model="box.{{ $key }}" type="number"
                                                        step="0.01" class="form-control medicine-box"
                                                        placeholder="Medicine box" x-on:input="debounceSave()">
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-3 col-md-4 col-sm-6">
                                                <div class="mb-3">
                                                    <label for="pieces_{{ $key }}" class="form-label">Medicine
                                                        Pieces</label>
                                                    <input wire:model="pieces.{{ $key }}" type="number"
                                                        step="0.01" class="form-control medicine-pieces"
                                                        placeholder="Medicine pieces" x-on:input="debounceSave()">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-3">
                            <button type="submit" class="btn btn-primary me-3">
                                <span wire:loading.remove>Save</span>
                                <span wire:loading>Saving...</span>
                            </button>
                            @if ($stockStatus != ACTIVE_STATUS)
                                <button wire:click="updateProductStock" type="button"
                                    class="btn btn-success approve_btn">
                                    <span>Approve Stock</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div> <!-- row -->
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:navigated', function() {

            $(".js-example-basic-single1").select2({
                dropdownParent: $('#stockItemSection')
            });

            $('#companySelect2').on('change', function(e) {
                let value = $(this).val();
                Livewire.dispatch('changeEvent', [value, 'changeCompany']);
            });

            const approveBtns = document.querySelectorAll('.approve_btn');

            function checkInputs() {
                let boxes = document.querySelectorAll('.medicine-box');
                let pieces = document.querySelectorAll('.medicine-pieces');
                let show = false;

                boxes.forEach(input => {
                    if (input.value.trim() !== '') {
                        show = true;
                    }
                });

                pieces.forEach(input => {
                    if (input.value.trim() !== '') {
                        show = true;
                    }
                });

                approveBtns.forEach(btn => {
                    if (show) {
                        btn.classList.remove('d-none');
                        btn.classList.add('d-block');
                    } else {
                        btn.classList.add('d-none');
                        btn.classList.remove('d-block');
                    }
                });
            }

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('medicine-box') || e.target.classList.contains(
                        'medicine-pieces')) {
                    checkInputs();
                }
            });

        });
    </script>

    <script>
        $('#companySelect2').on('change', function() {
            Livewire.dispatch('changeEvent', {
                value: $(this).val(),
                type: 'companyId'
            });
        });

        Livewire.on('resetSelect2', function() {
            $('.js-example-basic-single1').val(null).trigger('change');
        });
    </script>

{{--    <script>--}}
{{--        function debounceSave() {--}}
{{--            clearTimeout(this.timer);--}}
{{--            this.timer = setTimeout(() => {--}}
{{--                @this.call('autoSave');--}}
{{--            }, 5000); //5s--}}
{{--        }--}}
{{--    </script>--}}
@endpush
