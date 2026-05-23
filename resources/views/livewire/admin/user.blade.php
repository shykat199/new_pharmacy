<div>
    @section('admin.breadcrumb')
        <li class="breadcrumb-item" aria-current="page">{{$page}}</li>
{{--        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target=".bd-example-modal-xl">Extra large modal</button>--}}
    @endsection
    <div class="row">
        <div class="col-12 col-xl-12 grid-margin stretch-card">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>{{$page}}</h3>
                        @can('add user')
                            <a href="" wire:click.prevent="addCustomer" class="btn btn-primary btn-sm btn-icon-text"><i
                                    class="fa fa-plus-circle"></i> &nbsp; Add New</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    @livewire('admin.user-table')
                </div>
            </div>
        </div>
    </div> <!-- row -->
    @livewire('admin.modal.user-modal')

        <!-- Modal -->
        <div wire:ignore.self class="modal fade" id="myExtraLargeModalLabel" tabindex="-1"
             aria-labelledby="myExtraLargeModalLabel" aria-hidden="true"
             data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $userName }} Finance Module</h5>
                        <button type="button" class="btn-close" onclick="closeFinanceModal()" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-xl-12 stretch-card">
                                <div class="row flex-grow-1">
                                    <div class="col-md-6 grid-margin stretch-card">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-baseline">
                                                    <h6 class="card-title mb-0">Total Purchase Amount</h6>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 col-md-12 col-xl-5">
                                                        <h3 class="mt-2">{{number_format($totalAmount,2)}}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
{{--                                    <div class="col-md-4 grid-margin stretch-card">--}}
{{--                                        <div class="card">--}}
{{--                                            <div class="card-body">--}}
{{--                                                <div class="d-flex justify-content-between align-items-baseline">--}}
{{--                                                    <h6 class="card-title mb-0">Total Paid</h6>--}}
{{--                                                </div>--}}
{{--                                                <div class="row">--}}
{{--                                                    <div class="col-6 col-md-12 col-xl-5">--}}
{{--                                                        <h3 class="mt-2">{{number_format($paidAmount,2)}}</h3>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                    <div class="col-md-6 grid-margin stretch-card">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-baseline">
                                                    <h6 class="card-title mb-0">Total Due Amount</h6>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 col-md-12 col-xl-5">
                                                        <h3 class="mt-2">{{number_format($dueAmount,2)}}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <div class="row">
                            <form class="forms-sample" wire:submit.prevent="submitFinance">
                                <div class="row">
                                    <!-- Left column: Amount + Radio buttons -->
                                    <div class="col-6">
                                        <!-- Amount input -->
                                        <div class="mb-3 me-4">
                                            <label for="financeAmount" class="form-label">Amount</label>
                                            <input type="number" step="any" wire:model.defer="financeAmount" required class="form-control" id="financeAmount" placeholder="Due Amount">
                                            @error('financeAmount') <span class="text-danger">{{$message}}</span>  @enderror
                                        </div>
                                        <!-- Inline Radio Buttons -->
                                        <div class="mb-4 me-3">

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" wire:model.defer="financeType" name="financeType" id="credit" value="credit">
                                                <label class="form-check-label" for="credit">Credit</label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" wire:model.defer="financeType" name="financeType" id="debit" value="debit">
                                                <label class="form-check-label" for="debit">Debit</label>
                                            </div>

                                        </div>
                                        @error('financeType') <span class="text-danger">{{$message}}</span>  @enderror

                                        <!-- Submit button -->
                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </div>
                                    <!-- Right column: Note -->
                                    <div class="col-6">
                                        <label for="note" class="mb-1">Note</label>
                                        <textarea id="note" wire:model="note" class="form-control" rows="6" placeholder="Enter your note here..."></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="row">
                            <!-- Debit -->
                            <div class="col-md-6 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Debit List</h6>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                <tr>
                                                    <th>#Id</th>
                                                    <th>Amount</th>
                                                    <th>Note</th>
                                                    <th>Paid Date</th>
                                                </tr>
                                                </thead>
                                                <tbody>

                                                @foreach($userDebitList as $list)
                                                    <tr>
                                                        <th>{{$list->id}}</th>
                                                        <td>{{number_format($list->amount,2)}}</td>
                                                        <td style="white-space: normal; word-wrap: break-word;">{{ $list->note ?? 'N/A' }}</td>
                                                        <td>{{formatDate($list->paid_date)}}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Credit -->
                            <div class="col-md-6 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Credit List</h6>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                <tr>
                                                    <th>#Id</th>
                                                    <th>Amount</th>
                                                    <th>Note</th>
                                                    <th>Paid Date</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($userCreditList as $list)
                                                    <tr>
                                                        <th>{{$list->id}}</th>
                                                        <td>{{number_format($list->amount,2)}}</td>
                                                        <td style="white-space: normal; word-wrap: break-word;">{{ $list->note ?? 'N/A' }}</td>
                                                        <td>{{formatDate($list->paid_date,'d-M-Y')}}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</div>
@push('scripts')
    <script>
        let financeModal;

        Livewire.on('openFinanceModal', () => {
            const modalEl = document.getElementById('myExtraLargeModalLabel');
            financeModal = new bootstrap.Modal(modalEl, {
                backdrop: 'static',
                keyboard: false
            });
            financeModal.show();
        });

        Livewire.on('hide-finance-modal', () => {
            if (financeModal) {
                financeModal.hide();
            }
        });

        function closeFinanceModal() {
            if (financeModal) {
                financeModal.hide();
                Livewire.dispatch('resetField')
            }
        }
    </script>
@endpush
