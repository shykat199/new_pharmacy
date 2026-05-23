@extends('layout.app')
@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
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
@section('content')
    <div>
        @section('admin.breadcrumb')
            <li class="breadcrumb-item" aria-current="page">{{ $page }}</li>
        @endsection

        <div class="row">
            <form class="forms-sample" id="invoiceForm">
                @if (auth()->user()->role != USER_ROLE)
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card overflow-hidden">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3>{{ $page1 }}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                                        <label for="company_id" class="form-label">User Name</label>
                                        <input class="form-control productInput" type="text" readonly  name="userName" value="{{$userName}}" wire:model="userName" placeholder="User name">
                                        <input class="form-control productInput" type="hidden" readonly  name="userId" value="{{$userId}}" wire:model="userName" placeholder="User name">

                                    </div>
                                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                                        <label for="company_id" class="form-label">User Phone</label>
                                        <input class="form-control productInput" readonly type="text" value="{{$phone}}" name="phone" wire:model="phone" placeholder="User phone...">
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <label for="name" class="form-label">User Address</label>
                                        <textarea disabled wire:model="address" name="address" class="form-control" id="exampleFormControlTextarea1" rows="5" spellcheck="false">{{$address}}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-md-12 grid-margin stretch-card">
                    <div class="card overflow-hidden">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <h3 class="mb-3 mb-md-0">{{ $page }}</h3>
                                {{--                                @if($invoiceStatus == DRAFT_STATUS)--}}
                                {{--                                    <span style="font-size: 20px" class="badge bg-warning">Pending Invoice</span>--}}
                                {{--                                @else--}}

                                {{--                                @endif--}}
                                <span style="font-size: 20px" class="badge bg-secondary">Invoice Id:{{ $invoiceDetailsId }}</span>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">

                                <table class="table invoice-create-table">
                                    <thead>
                                    <tr class="text-center">
                                        <th>Product</th>
                                        <th>Quantity<br>Pieces</th>
                                        <th>Unit<br>Price</th>
                                        @if (auth()->user()->role != USER_ROLE)
                                            <th>Stock</th>
                                        @endif
                                        @can('apply invoice discount')
                                            <th>Discount<br>(%)</th>
                                        @endcan
                                        <th>Total</th>
                                    </tr>
                                    </thead>

                                    <tbody id="invoice-order-item">
                                    @foreach($orderItems as $index => $item)

                                        <tr>
                                            <td style="width: 280px;">
                                                <div class="position-relative">
                                                    <div>
                                                        <input type="text" name="rows[{{ $index }}][productName]"
                                                               value="{{$item->product->name.' '.$item->product->strength.' '.\Illuminate\Support\Str::limit($item->product->type,3,'') .' - '.\Illuminate\Support\Str::limit($item->product->company->name,3,''),}}"
                                                               class="form-control" placeholder="Product Name" readonly
                                                               style="width: 100%">

                                                    </div>
                                                    <div class="text-danger validation-error mt-1 select2-error"></div>
                                                </div>

                                                <!-- Hidden inputs -->
                                                <input value="{{$item->id}}" name="rows[{{ $index }}][orderItemId]" type="hidden"
                                                       class="form-control">
                                                <input value="{{$item->company_id}}" name="rows[{{ $index }}][companyId]" type="hidden"
                                                       class="form-control">
                                                <input value="{{$item->product->name}}" name="rows[{{ $index }}][productName]" type="hidden"
                                                       class="form-control">
                                                <input value="{{$item->product_id}}" name="rows[{{ $index }}][product_id]" type="hidden"
                                                       class="form-control">
                                                <input value="{{$item->product->box_per_pic}}" name="rows[{{ $index }}][box_per_pic]" type="hidden"
                                                       class="form-control">
                                                <input value="{{$item->product->unit_price}}" name="rows[{{ $index }}][unit_price]" type="hidden"
                                                       class="form-control">
                                            </td>

                                            <td>
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex">
                                                        <input readonly type="number" {{$invoiceDetails->status == ACTIVE_STATUS ? 'readonly' :''}} name="rows[{{ $index }}][qty]" value="{{$item->box_qty ?? ''}}"
                                                               class="form-control me-2 qty-field" placeholder="Box"
                                                               style="width: 50%;">
                                                        <input readonly type="number" {{$invoiceDetails->status == ACTIVE_STATUS ? 'readonly' :''}} step="any" name="rows[{{ $index }}][pieces]" value="{{$item->quantity ?? ''}}"
                                                               class="form-control pieces-field" placeholder="Pieces"
                                                               style="width: 50%;">
                                                    </div>
                                                    <div class="text-danger validation-error mt-1 qty-pieces-error"></div>
                                                </div>
                                            </td>

                                            <td>
                                                <input readonly name="rows[{{ $index }}][price]" {{$invoiceDetails->status == ACTIVE_STATUS ? 'readonly' :''}} step="any" type="number" value="{{$item->product->unit_price ?? ''}}"
                                                       class="form-control" placeholder="Enter price">
                                            </td>
                                            <td>
                                                <input readonly name="rows[{{ $index }}][stock]" type="text" value="{{$item->product->stock ?? ''}}"
                                                       class="form-control" placeholder="Stock" style="width: 80px">
                                            </td>
                                            @if (auth()->user()->role == ADMIN_ROLE)
                                                <td>
                                                    <input readonly type="number" {{$invoiceDetails->status == ACTIVE_STATUS ? 'readonly' :''}} name="rows[{{ $index }}][discount]" value="{{$item->discount ?? ''}}"
                                                           class="form-control" placeholder="Discount" step="any"
                                                           style="width: 80px">
                                                </td>
                                            @endif
                                            <td>
                                                <input readonly name="rows[{{ $index }}][total]" value="{{$item->final_total ?? ''}}"
                                                       type="text" class="form-control" placeholder="Total"
                                                       style="width: 90px">
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>

                                    <tfoot>
                                    <tr>
                                        @if (auth()->user()->role != USER_ROLE)
                                            <td colspan="2">
                                                <div
                                                    class="d-flex justify-content-start flex-column align-items-start">
                                                    <label for="note" class="mb-1">Note</label>
                                                    <textarea id="note" name="note" wire:model="note" class="form-control" rows="3"
                                                              placeholder="Enter your note here...">{{$note}}</textarea>
                                                </div>
                                            </td>
                                        @endif

                                        <td colspan="7">
                                            <div class="d-flex justify-content-end flex-column align-items-end">

                                                <input type="hidden" name="invoiceId" id="invoiceId" value="{{$invoiceId}}"
                                                       class="form-control text-end" placeholder="0.00"
                                                       style="max-width: 200px;">

                                                <!-- Total Amount Before Discount -->
                                                <input wire:model="total_amount_without_discount"
                                                       name="total_amount"
                                                       value="{{$total_amount_without_discount}}"
                                                       id="total_amount"
                                                       class="form-control text-end"
                                                       type="hidden"
                                                       placeholder="Total before discount"
                                                       style="max-width: 200px;" readonly>

                                                <!-- Total Discount -->
                                                <input wire:model="custom_total_discount"
                                                       name="custom_discount"
                                                       value="{{$custom_total_discount}}"
                                                       id="custom_discount"
                                                       class="form-control text-end"
                                                       type="hidden"
                                                       placeholder="Total discount"
                                                       style="max-width: 200px;" readonly>

                                                <!--Total -->
                                                @if (auth()->user()->role != USER_ROLE)
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <label class="fw-bold me-2">Total:</label>
                                                        <input  type="text" value="{{$totalMedicinePrice}}"
                                                                name="totalMedicinePrice"
                                                                id="totalMedicinePrice" class="form-control text-end"
                                                                placeholder="0.00" readonly style="max-width: 200px;">
                                                    </div>
                                                @else
                                                    <input wire:model="totalMedicinePrice" type="hidden"
                                                           name="totalMedicinePrice"  value="{{$totalMedicinePrice}}"
                                                           id="totalMedicinePrice" class="form-control text-end"
                                                           placeholder="0.00" readonly style="max-width: 200px;">
                                                @endif

                                                <!-- Other Charge -->
                                                @can('apply other charge')
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <label class="fw-bold me-2">Other Charge:</label>
                                                        <input readonly wire:model="otherCharge" name="otherCharge" type="text" value="{{$otherCharge}}"
                                                               step="any" {{$invoiceDetails->status == ACTIVE_STATUS ? 'readonly' :''}}
                                                               id="otherCharge" class="form-control text-end"
                                                               placeholder="Other charge" style="max-width: 200px;">
                                                    </div>
                                                @endcan

                                                <!-- Grand Total -->
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <label class="fw-bold me-2">Grand Total:</label>
                                                    <input readonly wire:model="grandTotal" type="text" name="grandTotal" value="{{$grandTotal}}"
                                                           id="grandTotal"
                                                           class="form-control text-end grandTotal"
                                                           placeholder="0.00" readonly style="max-width: 200px;">
                                                </div>

                                                <!-- Paid Amount -->
                                                @can('apply invoice paid amount')
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <label class="fw-bold me-2">Paid Amount:</label>
                                                        <input readonly wire:model="paidAmount" type="text" id="paidAmount" value="{{$paidAmount}}"
                                                               name="paidAmount" {{$invoiceDetails->status == ACTIVE_STATUS ? 'readonly' :''}}
                                                               step="0.01" class="form-control text-end"
                                                               placeholder="Paid amount" style="max-width: 200px;">
                                                    </div>
                                                    @error('paidAmount')
                                                    <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                @endcan

                                                @if (auth()->user()->role != USER_ROLE)
                                                    <!-- Due Amount -->
                                                    <div class="d-flex align-items-center gap-2">
                                                        <label class="fw-bold me-2">Due Amount:</label>
                                                        <input wire:model="dueAmount" type="text" name="dueAmount" value="{{$dueAmount}}"
                                                               class="form-control text-end" placeholder="0.00"
                                                               readonly style="max-width: 200px;">
                                                    </div>
                                                    <div class="text-danger validation-error mt-1 due-amount-error"></div>
                                                @else
                                                    <input wire:model="dueAmount" type="hidden" name="dueAmount" value="{{$dueAmount}}"
                                                           class="form-control text-end" placeholder="0.00" readonly
                                                           style="max-width: 200px;">
                                                @endif
                                            </div>
                                        </td>

                                    </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Add More Button -->

                            <!-- Submit Button -->
                            @if (auth()->user()->role == ADMIN_ROLE)
                                <button type="button" class="btn btn-secondary submit mt-3" onclick="generatePdf()">
                                    <span>Print Invoice Without Discount &nbsp; <i class="fa fa-print"></i></span>
                                </button>
                                <button type="button" class="btn btn-warning submit mt-3"
                                        onclick="generatePdf('withDiscount')">
                                    <span>Print Invoice With Discount &nbsp; <i class="fa fa-print"></i></span>
                                </button>
                            @endif

                        </div>


                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')

    <script>
        let currentIndex = `{{count($orderItems)}}`;
        document.addEventListener('livewire:init', initSelect2)
        Livewire.hook('message.processed', initSelect2)
        document.addEventListener('livewire:navigated', initSelect2)

        function initSelect2() {
            const $el = $('.js-example-basic-single3');

            if (!$el.length) return;

            $el.each(function () {
                const $this = $(this);


                if ($this.data('select2')) {
                    return
                }
                ;

                $this.select2({
                    ajax: {
                        url: '{{ route('admin.get-medicine-product') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.results
                            };
                        },
                        cache: true
                    },
                    // placeholder: 'Select Medicine Type',
                    dropdownParent: $('#invoice-order-item'),
                    minimumInputLength: 2,
                    width: '100%'
                }).on('select2:select', function (e) {
                    const product = e.params.data;

                    // Livewire.find($this.closest('[wire\\:id]').attr('wire:id'))
                    //     ?.set('medicineType', product.id);

                    // const row = $this.closest('td');
                    const row = $this.closest('tr');


                    row.find('input[name*="[companyId]"]').val(product.company_id);
                    row.find('input[name*="[product_id]"]').val(product.id);
                    row.find('input[name*="[productName]"]').val(product.productName);
                    row.find('input[name*="[box_per_pic]"]').val(product.box_per_pic);
                    row.find('input[name*="[unit_price]"]').val(product.unit_price);


                    row.closest('tr').find('input[name*="[price]"]').val(product.unit_price);
                    row.closest('tr').find('input[name*="[stock]"]').val(product.stock);
                });

            });
        }

        function addRow() {
            const tbody = document.getElementById('invoice-order-item');

            const row = document.createElement('tr');

            row.innerHTML = `
        <td style="width: 280px; position: relative;">
            <div wire:ignore>
                <select class="js-example-basic-single3 form-select" name="rows[${currentIndex}][product_id]">
                    <option value="">Select Medicine Type</option>
                </select>
                <div class="text-danger validation-error mt-1 select2-error"></div>
            </div>
            <input value="" name="rows[${currentIndex}][companyId]" type="hidden" class="form-control">
            <input value="" name="rows[${currentIndex}][productName]" type="hidden" class="form-control">
            <input value="" name="rows[${currentIndex}][product_id]" type="hidden" class="form-control">
            <input value="" name="rows[${currentIndex}][box_per_pic]" type="hidden" class="form-control">
            <input value="" name="rows[${currentIndex}][unit_price]" type="hidden" class="form-control">
        </td>
        <td>
            <div class="d-flex">
                <input type="number" name="rows[${currentIndex}][qty]" class="form-control me-2" placeholder="Box" style="width: 50%;">
                <input type="number" step="any" name="rows[${currentIndex}][pieces]" class="form-control" placeholder="Pieces" style="width: 50%;">
            </div>
             <div class="text-danger validation-error mt-1 qty-pieces-error"></div>
        </td>
        <td>
            <input name="rows[${currentIndex}][price]" step="any" type="number" class="form-control" placeholder="Enter price">
        </td>
        <td>
            <input readonly name="rows[${currentIndex}][stock]" type="text" class="form-control" placeholder="Stock" style="width: 80px">
        </td>
        @if (auth()->user()->role == ADMIN_ROLE)
            <td>
                <input type="number" name="rows[${currentIndex}][discount]" class="form-control" placeholder="Discount" step="any" style="width: 80px">
            </td>
        @endif
            <td>
                <input readonly name="rows[${currentIndex}][total]" type="text" class="form-control" placeholder="Total" style="width: 90px">
            </td>
            <td>
                <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-xs"><i class="fa-solid fa-delete-left"></i></button>
            </td>`;

            tbody.appendChild(row);

            const selectElement = $(row).find('.js-example-basic-single3').select2({
                ajax: {
                    url: '{{ route('admin.get-medicine-product') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {q: params.term};
                    },
                    processResults: function (data) {
                        return {results: data.results};
                    },
                    cache: true
                },
                // placeholder: 'Select Medicine Type',
                dropdownParent: $('#invoice-order-item'),
                width: '100%',
                minimumInputLength: 2
            }).on('select2:select', function (e) {
                const product = e.params.data;
                const thisRow = $(this).closest('tr');
                thisRow.find('input[name*="[companyId]"]').val(product.company_id);
                thisRow.find('input[name*="[product_id]"]').val(product.id);
                thisRow.find('input[name*="[productName]"]').val(product.productName);
                thisRow.find('input[name*="[box_per_pic]"]').val(product.box_per_pic);
                thisRow.find('input[name*="[unit_price]"]').val(product.unit_price);
                thisRow.find('input[name*="[price]"]').val(product.unit_price);
                thisRow.find('input[name*="[stock]"]').val(product.stock);
            });

            selectElement.select2('open');

            currentIndex++;
        }

        function removeRow(button) {
            const row = button.closest('tr');

            row.remove();

        }

        function generatePdf(type='',status=''){

            const form = $('#invoiceForm')[0];
            const formData = new FormData(form);
            formData.append('type', type);
            formData.append('status', status);

            $.ajax({
                url: '{{ route('admin.save-update-invoice-with-pdf') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (response) {
                    if(response.status){
                        // const Toast = Swal.mixin({
                        //     toast: true,
                        //     position: "top-end",
                        //     showConfirmButton: false,
                        //     timer: 3000,
                        //     timerProgressBar: true,
                        //     didOpen: (toast) => {
                        //         toast.onmouseenter = Swal.stopTimer;
                        //         toast.onmouseleave = Swal.resumeTimer;
                        //     }
                        // });
                        // Toast.fire({
                        //     icon: 'success',
                        //     title:'Invoice created successfully!'
                        // });

                        let generatedPdf = response.InvoicePdf;

                        showPdf(generatedPdf)

                    }else {
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
                            icon: 'error',
                            title: response.message
                        });
                    }

                },
                error: function (xhr) {
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
                        icon: 'error',
                        title: 'Invoice created successfully!'
                    });
                }
            });


            function showPdf (data){
                try {
                    let base64 = data.replace(/-/g, '+').replace(/_/g, '/');
                    const pad = base64.length % 4;
                    if (pad) {
                        if (pad === 1) {
                            throw new Error('Invalid base64 string');
                        }
                        base64 += new Array(5 - pad).join('=');
                    }

                    const binaryString = atob(base64);
                    const bytes = new Uint8Array(binaryString.length);

                    for (let i = 0; i < binaryString.length; i++) {
                        bytes[i] = binaryString.charCodeAt(i);
                    }

                    const blob = new Blob([bytes], {
                        type: 'application/pdf'
                    });
                    const url = URL.createObjectURL(blob);

                    // Create modal elements
                    const modal = document.createElement('div');
                    modal.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 80%;
                    height: 80%;
                    background: white;
                    z-index: 1000;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 0 20px rgba(0,0,0,0.5);
                `;

                    const closeButton = document.createElement('button');
                    closeButton.textContent = '×';
                    closeButton.style.cssText = `
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    cursor: pointer;
                    background: none;
                    border: none;
                    font-size: 24px;
                    padding: 5px 10px;
                    color: #333;
                    z-index: 1001;
                `;

                    const overlay = document.createElement('div');
                    overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 999;
                `;

                    const iframe = document.createElement('iframe');
                    iframe.style.width = '100%';
                    iframe.style.height = '100%';
                    iframe.style.border = 'none';
                    iframe.src = url;

                    // Build modal structure
                    modal.appendChild(closeButton);
                    modal.appendChild(iframe);
                    document.body.appendChild(overlay);
                    document.body.appendChild(modal);

                    // Close functionality
                    function closeModal() {
                        document.body.removeChild(modal);
                        document.body.removeChild(overlay);
                        URL.revokeObjectURL(url);
                    }

                    closeButton.addEventListener('click', closeModal);
                    overlay.addEventListener('click', closeModal);

                    // Cleanup after 10 minutes as fallback
                    setTimeout(() => {
                        if (document.body.contains(modal)) {
                            closeModal();
                        }
                    }, 600000);

                } catch (error) {
                    console.error('PDF Error:', error);
                    alert('Error opening PDF: ' + error.message);
                }
            }
        }

        function approveInvoice() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to approve this invoice!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, do it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.Livewire.dispatch('approveInvoice');
                }
            });
        }

        function sendAdminApproval() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to send for admin approval!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, do it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.Livewire.dispatch('sendAdminApproval');
                }
            });
        }

    </script>

    <script>
        document.addEventListener("livewire:navigated", function () {

            const tableBody = document.querySelector("table tbody");

            function customRound(value) {
                value = parseFloat(value);
                if (isNaN(value)) return "0.00";

                const decimalPart = value - Math.floor(value);

                if (decimalPart >= 0.40) {
                    return (Math.ceil(value)).toFixed(2); // Must return string with .00
                } else {
                    return (Math.floor(value)).toFixed(2);
                }
            }

            function getInput(name) {
                return document.querySelector(`[name="${name}"]`);
            }

            function calculateTotals() {
                let totalPrice = 0;
                let totalDiscount = 0;

                tableBody.querySelectorAll("tr").forEach(row => {
                    const qty = parseFloat(row.querySelector('[name*="[qty]"]')?.value) || 0;
                    const pieces = parseFloat(row.querySelector('[name*="[pieces]"]')?.value) || 0;
                    const discount = parseFloat(row.querySelector('[name*="[discount]"]')?.value) || 0;
                    const boxPerPic = parseFloat(row.querySelector('[name*="[box_per_pic]"]')?.value) || 0;

                    const unitPrice = parseFloat(
                        row.querySelector('[name*="[price]"]')?.value ||
                        row.querySelector('[name*="[unit_price]"]')?.value
                    ) || 0;

                    const calculatedQty = (qty * boxPerPic) + pieces;
                    const lineTotal = calculatedQty * unitPrice;
                    const discountAmount = lineTotal * (discount / 100);
                    const finalLineTotal = lineTotal - discountAmount;

                    totalPrice += finalLineTotal;
                    totalDiscount += discountAmount;

                    const totalInput = row.querySelector('[name*="[total]"]');
                    if (totalInput) {
                        totalInput.value = customRound(finalLineTotal);
                    }
                });

                const otherCharge = parseFloat(getInput("otherCharge")?.value) || 0;
                const paidAmount = parseFloat(getInput("paidAmount")?.value) || 0;

                const totalBeforeDiscount = totalPrice + totalDiscount;
                const grandTotal = totalBeforeDiscount + otherCharge - totalDiscount;
                const dueAmount = grandTotal - paidAmount;

                getInput("total_amount").value = customRound(totalBeforeDiscount);
                getInput("custom_discount").value = customRound(totalDiscount);
                getInput("totalMedicinePrice").value = customRound(totalPrice);
                getInput("grandTotal").value = customRound(grandTotal);
                getInput("dueAmount").value = customRound(dueAmount);
            }

            function recalculateFromUserInputs(totalPrice = null, totalDiscount = null) {
                if (totalPrice === null) {
                    totalPrice = (getInput("totalMedicinePrice")?.value) || 0;
                    totalPrice = totalPrice.replace(/,/g, '');
                    totalPrice = parseFloat(totalPrice);
                }
                if (totalDiscount === null) {
                    totalDiscount = (getInput("custom_discount")?.value) || 0;
                    totalDiscount = totalDiscount.replace(/,/g, '');
                    totalDiscount = parseFloat(totalDiscount);
                }

                const otherCharge = parseFloat(getInput("otherCharge")?.value) || 0;
                const paidAmount = parseFloat(getInput("paidAmount")?.value) || 0;

                const totalBeforeDiscount = totalPrice + totalDiscount;
                const grandTotal =customRound((totalBeforeDiscount + otherCharge) - totalDiscount);
                const dueAmount = customRound(grandTotal - paidAmount);

                getInput("grandTotal").value = (grandTotal);
                getInput("dueAmount").value = (dueAmount);
            }

            // 🔁 Observe all rows for changes
            function bindRowEvents(row) {
                row.querySelectorAll("input").forEach(input => {
                    input.addEventListener("input", calculateTotals);
                });
            }

            // 🔁 Watch for new rows added dynamically
            const observer = new MutationObserver(() => {
                document.querySelectorAll("table tbody tr").forEach(bindRowEvents);
                calculateTotals();
            });
            observer.observe(tableBody, { childList: true, subtree: true });

            // 🔁 Watch for manual change in otherCharge / paidAmount
            ["otherCharge", "paidAmount"].forEach(name => {
                const input = getInput(name);
                if (input) {
                    input.addEventListener("input", () => {
                        recalculateFromUserInputs();
                    });
                }
            });

            // Initial bind
            document.querySelectorAll("table tbody tr").forEach(bindRowEvents);
            // calculateTotals();
        });

        @if($invoiceDetails->status == PENDING_STATUS || $invoiceDetails->status == DRAFT_STATUS)
        document.addEventListener('livewire:navigated', function () {

            document.addEventListener("keydown", function (e) {
                if (e.ctrlKey && e.code === "Space" && !e.repeat) {
                    // addRow();
                }
            });

            function addRow() {
                const tbody = document.getElementById('invoice-order-item');

                const row = document.createElement('tr');

                row.innerHTML = `
                <td style="width: 280px; position: relative;">
                    <div wire:ignore>
                        <select class="js-example-basic-single3 form-select" name="rows[${currentIndex}][product_id]">
                            <option value="">Select Medicine Type</option>
                        </select>
                    </div>
                    <input value="" name="rows[${currentIndex}][companyId]" type="hidden" class="form-control">
                    <input value="" name="rows[${currentIndex}][product_id]" type="hidden" class="form-control">
                    <input value="" name="rows[${currentIndex}][productName]" type="hidden" class="form-control">
                    <input value="" name="rows[${currentIndex}][box_per_pic]" type="hidden" class="form-control">
                    <input value="" name="rows[${currentIndex}][unit_price]" type="hidden" class="form-control">
                </td>
                <td>
                    <div class="d-flex">
                        <input type="number" name="rows[${currentIndex}][qty]" class="form-control me-2" placeholder="Box" style="width: 50%;">
                        <input type="number" step="any" name="rows[${currentIndex}][pieces]" class="form-control" placeholder="Pieces" style="width: 50%;">
                    </div>
                </td>
                <td>
                    <input name="rows[${currentIndex}][price]" step="any" type="number" class="form-control" placeholder="Enter price">
                </td>
                <td>
                    <input readonly name="rows[${currentIndex}][stock]" type="text" class="form-control" placeholder="Stock" style="width: 80px">
                </td>
                @if (auth()->user()->role == ADMIN_ROLE)
                <td>
                    <input type="number" name="rows[${currentIndex}][discount]" class="form-control" placeholder="Discount" step="any" style="width: 80px">
                    </td>
                @endif
                <td>
                    <input readonly name="rows[${currentIndex}][total]" type="text" class="form-control" placeholder="Total" style="width: 90px">
                    </td>
                    <td>
                        <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-xs"><i class="fa-solid fa-delete-left"></i></button>
                    </td>`;

                tbody.appendChild(row);

                const selectElement = $(row).find('.js-example-basic-single3').select2({
                    ajax: {
                        url: '{{ route('admin.get-medicine-product') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {q: params.term};
                        },
                        processResults: function (data) {
                            return {results: data.results};
                        },
                        cache: true
                    },
                    // placeholder: 'Select Medicine Type',
                    dropdownParent: $('#invoice-order-item'),
                    width: '100%',
                    minimumInputLength: 2
                }).on('select2:select', function (e) {
                    const product = e.params.data;
                    const thisRow = $(this).closest('tr');
                    thisRow.find('input[name*="[companyId]"]').val(product.company_id);
                    thisRow.find('input[name*="[product_id]"]').val(product.id);
                    thisRow.find('input[name*="[box_per_pic]"]').val(product.box_per_pic);
                    thisRow.find('input[name*="[productName]"]').val(product.productName);
                    thisRow.find('input[name*="[unit_price]"]').val(product.unit_price);
                    thisRow.find('input[name*="[price]"]').val(product.unit_price);
                    thisRow.find('input[name*="[stock]"]').val(product.stock);
                });

                selectElement.select2('open');

                currentIndex++;
            }

            function removeRow(button) {
                const row = button.closest('tr');
                row.remove();
            }

        });
        @endif

        document.addEventListener("livewire:navigated", function () {

            $('#invoiceForm').on('submit', function (e) {

                e.preventDefault();

                $('.select2-error, .qty-pieces-error').html('');
                let isValid = true;

                $('[name^="rows["]').each(function () {
                    const row = $(this).closest('tr');
                    const select = row.find('.js-example-basic-single3');
                    const qtyInput = row.find('[name*="[qty]"]');
                    const piecesInput = row.find('[name*="[pieces]"]');
                    const select2Error = row.find('.select2-error');
                    const qtyPiecesError = row.find('.qty-pieces-error');

                    const qty = parseFloat(qtyInput.val()) || 0;
                    const pieces = parseFloat(piecesInput.val()) || 0;

                    if (!select.val()) {
                        isValid = false;
                        select2Error.html('Please select a medicine type.');
                    }

                    if(select.hasClass('pass')){
                        isValid = true;
                        select2Error.html('');
                    }

                    if (qty === 0 && pieces === 0) {
                        isValid = false;
                        qtyPiecesError.html('Enter Qty or Pieces.');
                    }
                });

                const dueAmountInput = $('[name="dueAmount"]');
                const dueAmountVal = dueAmountInput.val().replace(/,/g, '');
                const dueAmount = parseFloat(dueAmountVal) || 0;

                if (dueAmount < 0) {
                    isValid = false;
                    $('.due-amount-error').html('Due amount cannot be negative.');
                }


                if (!isValid) {
                    event.preventDefault();
                    return false;
                }

                const formData = $(this).serialize();

                $.ajax({
                    url: '{{ route('admin.update-invoice',$invoiceId) }}',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (response) {

                        if(response.status){
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
                                icon: 'success',
                                title:'Invoice created successfully!'
                            });

                            setTimeout(function (){
                                location.reload(true);
                            },1000)

                        }else {
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
                                icon: 'error',
                                title: response.message
                            });
                        }


                    },
                    error: function (xhr) {
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
                            icon: 'error',
                            title: 'Invoice created successfully!'
                        });
                    }
                });
            });
        });

        document.addEventListener('livewire:navigated', function() {
            window.addEventListener('show-pdf', function(e) {
                try {
                    let base64 = e.detail[0].replace(/-/g, '+').replace(/_/g, '/');
                    const pad = base64.length % 4;
                    if (pad) {
                        if (pad === 1) {
                            throw new Error('Invalid base64 string');
                        }
                        base64 += new Array(5 - pad).join('=');
                    }

                    const binaryString = atob(base64);
                    const bytes = new Uint8Array(binaryString.length);

                    for (let i = 0; i < binaryString.length; i++) {
                        bytes[i] = binaryString.charCodeAt(i);
                    }

                    const blob = new Blob([bytes], {
                        type: 'application/pdf'
                    });
                    const url = URL.createObjectURL(blob);

                    // Create modal elements
                    const modal = document.createElement('div');
                    modal.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 80%;
                    height: 80%;
                    background: white;
                    z-index: 1000;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 0 20px rgba(0,0,0,0.5);
                `;

                    const closeButton = document.createElement('button');
                    closeButton.textContent = '×';
                    closeButton.style.cssText = `
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    cursor: pointer;
                    background: none;
                    border: none;
                    font-size: 24px;
                    padding: 5px 10px;
                    color: #333;
                    z-index: 1001;
                `;

                    const overlay = document.createElement('div');
                    overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 999;
                `;

                    const iframe = document.createElement('iframe');
                    iframe.style.width = '100%';
                    iframe.style.height = '100%';
                    iframe.style.border = 'none';
                    iframe.src = url;

                    // Build modal structure
                    modal.appendChild(closeButton);
                    modal.appendChild(iframe);
                    document.body.appendChild(overlay);
                    document.body.appendChild(modal);

                    // Close functionality
                    function closeModal() {
                        document.body.removeChild(modal);
                        document.body.removeChild(overlay);
                        URL.revokeObjectURL(url);
                    }

                    closeButton.addEventListener('click', closeModal);
                    overlay.addEventListener('click', closeModal);

                    // Cleanup after 10 minutes as fallback
                    setTimeout(() => {
                        if (document.body.contains(modal)) {
                            closeModal();
                        }
                    }, 600000);

                } catch (error) {
                    console.error('PDF Error:', error);
                    alert('Error opening PDF: ' + error.message);
                }
            });
        })
    </script>

    <script>
        $(document).ready(function() {
            $('#pending_invoice').click(function() {

                // $('.select2-error, .qty-pieces-error').html('');
                // let isValid = true;
                //
                // $('[name^="rows["]').each(function () {
                //     const row = $(this).closest('tr');
                //     const select = row.find('.js-example-basic-single3');
                //     const qtyInput = row.find('[name*="[qty]"]');
                //     const piecesInput = row.find('[name*="[pieces]"]');
                //     const select2Error = row.find('.select2-error');
                //     const qtyPiecesError = row.find('.qty-pieces-error');
                //
                //     const qty = parseFloat(qtyInput.val()) || 0;
                //     const pieces = parseFloat(piecesInput.val()) || 0;
                //
                //     if (!select.val()) {
                //         isValid = false;
                //         select2Error.html('Please select a medicine type.');
                //     }
                //
                //     if (qty === 0 && pieces === 0) {
                //         isValid = false;
                //         qtyPiecesError.html('Enter Qty or Pieces.');
                //     }
                // });
                //
                // const dueAmountInput = $('[name="dueAmount"]');
                // const dueAmount = parseFloat(dueAmountInput.val()) || 0;
                //
                // if (dueAmount < 0) {
                //     isValid = false;
                //     $('.due-amount-error').html('Due amount cannot be negative.');
                // }
                //
                //
                // if (!isValid) {
                //     event.preventDefault();
                //     return false;
                // }

                const formData = $('#invoiceForm').serialize();


                $.ajax({
                    url: '{{ route('admin.update-pending-invoice') }}',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (response) {

                        if(response.status){
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
                                icon: 'success',
                                title:'Invoice updated successfully!'
                            });

                            setTimeout(function (){
                                window.location.href = response.routeUrl;
                            },1000)

                        }else {
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
                                icon: 'error',
                                title: response.message
                            });
                        }


                    },
                    error: function (xhr) {
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
                            icon: 'error',
                            title: 'Invoice created successfully!'
                        });
                    }
                });
            });
        });
    </script>

@endpush
