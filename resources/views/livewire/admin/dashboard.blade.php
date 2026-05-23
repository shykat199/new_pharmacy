<div>
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Welcome to Dashboard</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-12 stretch-card">
            <div class="row flex-grow-1">
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Today Total Sale Amount</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-8">
                                    <h3 class="mt-2">{{ number_format(todayTotalSale(),2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Today Total Paid Amount</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-8">
                                    <h3 class="mt-2">{{ number_format(todayPaidAmount(),2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Today Total Due Amount</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-8">
                                    <h3 class="mt-2">{{ number_format(todayDueAmount(),2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Today Total Invoice</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-8">
                                    <h3 class="mt-2">{{ number_format(todayTotalInvoice()) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-12 stretch-card">
            <div class="row flex-grow-1">
                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Admin</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-8">
                                    <h3 class="mt-2">{{ number_format($totalAdmin) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Staff</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-8">
                                    <h3 class="mt-2">{{ number_format($totalStaff) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total User</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-8">
                                    <h3 class="mt-2">{{ number_format($totalUser) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-12 stretch-card">
            <div class="row flex-grow-1">

                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Medicine</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-8">
                                    <h3 class="mt-2">{{ number_format($totalMedicine) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Out Of Stock Medicine</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-8">
                                    <h3 class="mt-2">{{ number_format($totalOutOfMedicine) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Inactive Medicine</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-5">
                                    <h3 class="mt-2">{{ number_format($totalInactiveMedicine) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-12 col-xl-12 stretch-card">
            <div class="row flex-grow-1">

{{--                <div class="col-md-4 grid-margin stretch-card">--}}
{{--                    <div class="card">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex justify-content-between align-items-baseline">--}}
{{--                                <h6 class="card-title mb-0">Total Paid Amount</h6>--}}
{{--                            </div>--}}
{{--                            <div class="row">--}}
{{--                                <div class="col-6 col-md-12 col-xl-5">--}}
{{--                                    <h3 class="mt-2">{{ number_format($totalPaidAmount, 2) }}</h3>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="col-md-4 grid-margin stretch-card">--}}
{{--                    <div class="card">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex justify-content-between align-items-baseline">--}}
{{--                                <h6 class="card-title mb-0">Total Due Amount</h6>--}}
{{--                            </div>--}}
{{--                            <div class="row">--}}
{{--                                <div class="col-6 col-md-12 col-xl-8">--}}
{{--                                    <h3 class="mt-2 amount-text" id="medicineAmount" style="filter: blur(5px); user-select: none; pointer-events: none;">--}}
{{--                                        {{ number_format($totalDueAmount, 2) }}--}}
{{--                                    </h3>--}}
{{--                                    <button class="btn btn-sm btn-primary mt-3" onclick="verifyPassword('medicine')">View Amount <i class="fa fa-eye"></i></button>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-md-4 grid-margin stretch-card">--}}
{{--                    <div class="card">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex justify-content-between align-items-baseline">--}}
{{--                                <h6 class="card-title mb-0">Total Medicine Price</h6>--}}
{{--                            </div>--}}
{{--                            <div class="row">--}}
{{--                                <div class="col-6 col-md-12 col-xl-8">--}}
{{--                                    <h3 class="mt-2 amount-text" id="dueAmount" style="filter: blur(5px); user-select: none; pointer-events: none;">--}}
{{--                                        {{ number_format(totalMedicinePrice(), 2) }}--}}
{{--                                    </h3>--}}
{{--                                    <button class="btn btn-sm btn-primary mt-3" onclick="verifyPassword('due')">View Amount <i class="fa fa-eye"></i></button>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

            </div>
        </div>
    </div> <!-- row -->

    <div class="row">
        <div class="col-lg-5 col-xl-4 grid-margin grid-margin-xl-0 stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <h6 class="card-title mb-0">User With Due Amount</h6>
                        @can('get user list')
                            <a wire:navigate href="{{ route('admin.user-order-invoice') }}"
                                class="btn btn-primary btn-xs">View
                                All</a>
                        @endcan
                    </div>
                    <div class="d-flex flex-column">

                        @foreach ($userWithDueAmount as $user)
                            @can('get user list')
                                @php
                                    $url = route('admin.invoice-details', $user->id);
                                @endphp
                            @endcan

                            <a wire:navigate href="{{ $url ?? '' }}"
                                class="d-flex align-items-center border-bottom pb-3">
                                <div class="w-100">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="text-body mb-2">
                                            {{ !empty($user->user) ? $user->user->name : 'Unknown' }}
                                            ({{ $user->invoice_id }})
                                        </h6>
                                        <h6>{{ number_format($user->due_amount, 2) }}</h6>
                                    </div>
                                    <p class="text-secondary">
                                        Phone: {{ !empty($user->user) ? $user->user->phone : 'Unknown' }}</p>
                                    <p class="text-secondary">
                                        Date: {{ !empty($user->created_at) ? \Carbon\Carbon::parse($user->created_at)->format('d-M-Y') : 'N/A' }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-7 col-xl-8 stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                        <h6 class="card-title mb-0">Out Of Stock Product</h6>
                        @can('get pre medicine stock list')
                            <a wire:navigate href="{{route('admin.medicine',['type'=>'outofstock'])}}" class="btn btn-primary btn-xs">View
                                All</a>
                        @endcan
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pt-0">#Id</th>
                                    <th class="pt-0">Medicine Name</th>
                                    <th class="pt-0">Company Name</th>
                                    <th class="pt-0">Unit Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($outOfStockProduct as $key => $product)
                                    <tr>
                                        <td>{{ ++$key }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->company->name }}</td>
                                        <td>{{ number_format($product->unit_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- row -->
</div>

{{--@push('scripts')--}}
{{--    <script>--}}
{{--        function verifyPassword(type) {--}}
{{--            const amountElement = type === 'medicine'--}}
{{--                ? document.getElementById('medicineAmount')--}}
{{--                : document.getElementById('dueAmount');--}}

{{--            const isBlurred = amountElement.style.filter !== 'none';--}}

{{--            if (isBlurred) {--}}
{{--                const password = prompt("Enter admin password to view amount:");--}}
{{--                if (!password) return;--}}

{{--                fetch("{{ route('verify.amount.password') }}", {--}}
{{--                    method: 'POST',--}}
{{--                    headers: {--}}
{{--                        'Content-Type': 'application/json',--}}
{{--                        'X-CSRF-TOKEN': '{{ csrf_token() }}'--}}
{{--                    },--}}
{{--                    body: JSON.stringify({--}}
{{--                        password: password,--}}
{{--                        type: type--}}
{{--                    })--}}
{{--                })--}}
{{--                    .then(response => response.json())--}}
{{--                    .then(data => {--}}
{{--                        if (data.success) {--}}
{{--                            amountElement.style.filter = 'none';--}}
{{--                            amountElement.style.userSelect = 'auto';--}}
{{--                            amountElement.style.pointerEvents = 'auto';--}}
{{--                        } else {--}}
{{--                            alert("Incorrect password.");--}}
{{--                        }--}}
{{--                    })--}}
{{--                    .catch(() => alert("Something went wrong."));--}}
{{--            } else {--}}
{{--                amountElement.style.filter = 'blur(5px)';--}}
{{--                amountElement.style.userSelect = 'none';--}}
{{--                amountElement.style.pointerEvents = 'none';--}}
{{--            }--}}
{{--        }--}}
{{--    </script>--}}
{{--@endpush--}}
