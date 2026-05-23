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
                    <div class="d-flex align-items-center justify-content-between">
                        <!-- Left: page title -->
                        <h3 class="mb-0">{{ $page ?? 'Invoices' }}</h3>

                        <!-- Center: date picker -->
{{--                        <div class="mx-auto" style="width: 200px;">--}}
{{--                            <input id="invoiceDatePicker" type="date" class="form-control form-control-sm" wire:model="selectedDate"/>--}}
{{--                        </div>--}}

                        <!-- Right: button -->
                        @can('add invoice')
                            <a href="{{ route('admin.create-invoice') }}" class="btn btn-primary btn-sm btn-icon-text">
                                <i class="fa fa-plus-circle"></i> &nbsp; Create Invoice
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    @livewire('admin.invoice-table',['authUser'=>Auth::user(),'segment'=>$segment])
                </div>
            </div>
        </div>
    </div> <!-- row -->

    <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-labelledby="pdfPreviewModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pdfPreviewModalLabel">Invoice Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe id="pdfIframe" src="" width="100%" height="700px"
                                style="border: none;"></iframe>
                    </div>
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
    <script>
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
@endpush
