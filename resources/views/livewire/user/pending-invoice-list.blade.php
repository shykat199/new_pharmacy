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
        <li class="breadcrumb-item" aria-current="page">{{ $page }}</li>
    @endsection
    <div class="row">
        <div class="col-12 col-xl-12 grid-margin stretch-card">
            <div class="card overflow-hidden">
                <div class="card-header" id="stockItemSection">
                    @if(Auth::user()->role == ADMIN_ROLE)
                        @php
                            $customers = \App\Models\User::where('role',USER_ROLE)->orderBy('name')->get();
                        @endphp
                        <div wire:ignore>
                            <select class="js-example-basic-single1" id="companySearch" style="width: 300px;" onchange="customerSelected()" placeholder="Select customer...">
                                <option value="">All Customer</option>
                                @foreach($customers as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between align-items-center flex-wrap mt-2">
                        <h3>{{ $page }}</h3>
{{--                        <a wire:click.prevent="addProduct" class="btn btn-primary btn-sm btn-icon-text"><i--}}
{{--                                class="fa fa-plus-circle"></i> &nbsp; Create Invoice</a>--}}

                        @if(auth()->user()->role == USER_ROLE)
                            <a  href="{{route('user.create-invoice')}}" class="btn btn-primary btn-sm btn-icon-text"><i
                                    class="fa fa-plus-circle"></i> &nbsp; Create Invoice</a>
                        @else
                            <a href="{{route('admin.create-invoice')}}" class="btn btn-primary btn-sm btn-icon-text"><i
                                    class="fa fa-plus-circle"></i> &nbsp; Create Invoice</a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @livewire('pending-invoice-list', ['authUser' => Auth::user()])
                </div>
            </div>
        </div>
    </div>

    @if ($showEditModal)
        <div wire:ignore.self class="modal fade" id="createModal" role="dialog" tabindex="-1"
            aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"
            style="background: rgba(0, 0, 0, 0.5);">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form class="forms-sample" wire:submit.prevent="updateInvoice">
                        <div class="modal-header">
                            <h5 class="modal-title">Update invoice</h5>
                            <button wire:click.prevent="closeModal" type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="btn-close"></button>
                        </div>
                        <div class="modal-body">

                            <form>
                                <div class="card-body">
                                    <div class="table-responsive">

                                        <table class="table">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>Product / Medicine</th>
                                                    <th>Quantity/Pieces</th>
                                                    <th>Unit Price</th>
                                                    <th>Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>

                                            <tbody id="table-body">
                                                @foreach ($rows as $index => $row)
                                                    <tr>
                                                        <td style="width: 280px; position: relative;">
                                                            @if (!empty($row['product']))
                                                                <input class="form-control productInput" type="text"
                                                                    value="{{ $row['product'] }}"
                                                                    placeholder="Search products..." required readonly>
                                                            @else
                                                                <livewire:admin.invoice-produt-search
                                                                    wire:key="product-search-{{ $index }}"
                                                                    :index="$index" />
                                                            @endif
                                                            <input wire:model.live="rows.{{ $index }}.companyId"
                                                                type="hidden" class="form-control">
                                                            <input wire:model.live="rows.{{ $index }}.product_id"
                                                                type="hidden" class="form-control">
                                                            <input
                                                                wire:model.live="rows.{{ $index }}.order_item_id"
                                                                type="hidden" class="form-control">
                                                        </td>
                                                        <td>
                                                            <div class="d-flex">
                                                                <input wire:model.live="rows.{{ $index }}.qty"
                                                                    type="number"
                                                                    wire:change="updateField({{ $index }}, 'qty','update')"
                                                                    class="form-control me-2" placeholder="Box"
                                                                    style="width: 50%;">
                                                                @error("rows.$index.qty")
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                                <input
                                                                    wire:model.live="rows.{{ $index }}.pieces"
                                                                    type="number"
                                                                    wire:change="updateField({{ $index }}, 'pieces','update')"
                                                                    class="form-control" placeholder="Pieces"
                                                                    style="width: 50%;">
                                                                @error("rows.$index.pieces")
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input wire:model.live="rows.{{ $index }}.price"
                                                                type="text" disabled class="form-control"
                                                                placeholder="Enter price" required>
                                                        </td>
                                                        <td>
                                                            <input wire:model="rows.{{ $index }}.total" disabled
                                                                type="text" class="form-control" placeholder="Total"
                                                                style="width: 90px">
                                                        </td>
                                                        <td>
                                                            <button type="button"
                                                                wire:click="removeRow({{ $index }})"
                                                                class="btn btn-danger btn-xs"
                                                                @if (count($rows) === 1) disabled @endif>
                                                                <i class="fa-solid fa-delete-left"></i>
                                                            </button>

                                                            <button wire:click="addRow" type="button"
                                                                class="btn btn-primary btn-xs" id="add-row-btn"><i
                                                                    class="fa-solid fa-plus-circle"></i></button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>

                                            <tfoot>
                                                <tr>

                                                    <td colspan="3">
                                                        <div
                                                            class="d-flex justify-content-start flex-column align-items-start">
                                                            <label for="note" class="mb-1">Note</label>
                                                            <textarea id="note" wire:model="note" class="form-control" rows="4" placeholder="Enter your note here..."></textarea>
                                                        </div>
                                                    </td>

                                                    <td colspan="5">
                                                        <div
                                                            class="d-flex justify-content-end flex-column align-items-end">

                                                            <!--Total -->
                                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                                <label class="fw-bold me-2">Total:</label>
                                                                <input wire:model="totalMedicinePrice" type="number"
                                                                    id="totalMedicinePrice"
                                                                    class="form-control text-end" placeholder="0.00"
                                                                    readonly style="max-width: 95px;">
                                                            </div>

                                                            <!-- Grand Total -->
                                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                                <label class="fw-bold me-2">Grand Total:</label>
                                                                <input wire:model="grandTotal" type="number"
                                                                    class="form-control text-end grandTotal"
                                                                    placeholder="0.00" readonly
                                                                    style="max-width: 95px;">
                                                            </div>


                                                            <!-- Due Amount -->
                                                            <div class="d-flex align-items-center gap-2">
                                                                <label class="fw-bold me-2">Due Amount:</label>
                                                                <input wire:model="dueAmount" type="number"
                                                                    class="form-control text-end" placeholder="0.00"
                                                                    readonly style="max-width: 95px;">
                                                            </div>
                                                        </div>
                                                    </td>

                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <!-- Add More Button -->
                                    <div class="d-flex justify-content-end">
                                        <button wire:click="addRow" type="button"
                                            class="btn btn-primary mt-2 btn-xs" id="add-row-btn"><i
                                                class="fa-solid fa-plus-circle"></i></button>
                                    </div>

                                    <!-- Submit Button -->
                                    <button type="submit" class="btn btn-primary submit mt-3">Update Invoice And
                                        Print &nbsp; <i class="fa fa-print"></i></button>
                                </div>
                            </form>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click.prevent="closeModal"
                                data-bs-dismiss="modal">Close
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div wire:ignore.self class="modal fade" id="createModal" role="dialog" tabindex="-1"
            aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static"
            data-bs-keyboard="false" style="background: rgba(0, 0, 0, 0.5);">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form class="forms-sample" wire:submit.prevent="createInvoice">
                        <div class="modal-header">
                            <h5 class="modal-title">Add new invoice</h5>
                            <button wire:click.prevent="closeModal" type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="btn-close"></button>
                        </div>
                        <div class="modal-body">

                            <form>
                                <div class="card-body">
                                    <div class="table-responsive">

                                        <table class="table add-new-invoice-table">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>Product / Medicine</th>
                                                    <th>Box/Pieces</th>
                                                    <th>Unit Price</th>
                                                </tr>
                                            </thead>

                                            <tbody id="table-body">
                                                @foreach ($rows as $index => $row)
                                                    <tr>
                                                        <td style="width: 280px; position: relative;">
                                                            @if (!empty($row['product']))
                                                                <input class="form-control" type="text"
                                                                    value="{{ $row['product'] }}"
                                                                    placeholder="Search products..." required readonly>
                                                            @else
                                                                <livewire:admin.invoice-produt-search
                                                                    wire:key="product-search-{{ $index }}"
                                                                    :index="$index" />
                                                            @endif
                                                            <input
                                                                wire:model.live="rows.{{ $index }}.companyId"
                                                                type="hidden" class="form-control">
                                                            <input
                                                                wire:model.live="rows.{{ $index }}.product_id"
                                                                type="hidden" class="form-control">
                                                            <input
                                                                wire:model.live="rows.{{ $index }}.order_item_id"
                                                                type="hidden" class="form-control">
                                                        </td>
                                                        <td>
                                                            <div class="d-flex">
                                                                <input wire:model.live="rows.{{ $index }}.qty"
                                                                    type="number"
                                                                    wire:change="updateField({{ $index }}, 'qty')"
                                                                    class="form-control me-2" placeholder="Box"
                                                                    style="width: 70px">
                                                                @error("rows.$index.qty")
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                                <input
                                                                    wire:model.live="rows.{{ $index }}.pieces"
                                                                    type="number"
                                                                    wire:change="updateField({{ $index }}, 'pieces')"
                                                                    class="form-control" placeholder="Pieces"
                                                                    style="width: 85px">
                                                                @error("rows.$index.pieces")
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input wire:model.live="rows.{{ $index }}.price"
                                                                type="text" disabled class="form-control"
                                                                placeholder="Enter price">
                                                        </td>

                                                        <td>
                                                            <input wire:model="rows.{{ $index }}.total"
                                                                disabled type="hidden" class="form-control"
                                                                placeholder="Total" style="width: 80px">
                                                        </td>

                                                    </tr>
                                                @endforeach
                                            </tbody>

                                            <input wire:model="totalMedicinePrice" type="hidden"
                                                id="totalMedicinePrice" class="form-control text-end"
                                                placeholder="0.00" readonly style="max-width: 95px;">

                                            <!-- Grand Total -->
                                            <input wire:model="grandTotal" type="hidden"
                                                class="form-control text-end grandTotal" placeholder="0.00" readonly
                                                style="max-width: 95px;">


                                            <!-- Due Amount -->
                                            <input wire:model="dueAmount" type="hidden"
                                                class="form-control text-end" placeholder="0.00" readonly
                                                style="max-width: 95px;">

                                        </table>
                                    </div>

                                    <!-- Add More Button -->

                                    <!-- Submit Button -->
                                    <button type="submit" class="btn btn-primary submit mt-3">Create Invoice</button>
                                </div>
                            </form>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click.prevent="closeModal"
                                data-bs-dismiss="modal">Close
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:navigated', function() {

            $(".js-example-basic-single1").select2({
                dropdownParent: $('#stockItemSection'),
            });

            $('#companySelect2').on('change', function(e) {
                let value = $(this).val();
                Livewire.dispatch('changeEvent', [value, 'dropdown']);
            });
        });
    </script>

    <script>
        // document.addEventListener('livewire:navigated', function () {
        window.lastKeyTime = window.lastKeyTime || 0;

        window.addEventListener('keydown', function(event) {
            if (event.code === 'Space') {
                let currentTime = new Date().getTime();
                if (currentTime - lastKeyTime < 300) { // 300ms threshold for double press
                    Livewire.dispatch('doubleSpacePressed');
                }
                lastKeyTime = currentTime;
            }
        });

        document.addEventListener('livewire:navigated', function() {

            const paidInput = document.querySelector('#paidAmount');
            const otherChargeInput = document.querySelector('#otherCharge');

            if (paidInput) {
                document.querySelector('#paidAmount').addEventListener('input', function(event) {
                    let amount = parseFloat(event.target.value) || 0;
                    let totalAmount = parseFloat(document.querySelector('.grandTotal').value) || 0;

                    if (amount < 0) {
                        amount = 0;
                        event.target.value = 0;
                    }

                    if (amount > totalAmount) {
                        event.target.value = totalAmount;
                        amount = totalAmount;
                    }
                    Livewire.dispatch('updateDueAmount', [amount]);
                })
            }

            if (otherChargeInput) {

                document.querySelector('#otherCharge').addEventListener('input', function(event) {

                    let amount = parseFloat(event.target.value) || 0;
                    if (amount < 0) {
                        event.target.value = 0;
                    }
                    Livewire.dispatch('updateDueAmount', [event.target.value, 'addOtherCharge']);
                    Livewire.dispatch('updateGrandTotal', [event.target.value]);
                })
            }
        });
    </script>
    <script>
        function customerSelected() {
            const company = document.getElementById('companySearch').value;

            Livewire.dispatch('companySelected', [company]);
        }
    </script>
@endpush
