<div>
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Welcome to Dashboard</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-12 stretch-card">
            <div class="row flex-grow-1">

                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Invoice</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-5">
                                    <h3 class="mt-2">{{$totalInvoice}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Due Amount</h6>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-12 col-xl-5">
                                    <h3 class="mt-2">{{number_format(@$totalDueAmount,2)}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

{{--                <div class="col-md-4 grid-margin stretch-card">--}}
{{--                    <div class="card">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex justify-content-between align-items-baseline">--}}
{{--                                <h6 class="card-title mb-0">Total Paid Amount</h6>--}}
{{--                            </div>--}}
{{--                            <div class="row">--}}
{{--                                <div class="col-6 col-md-12 col-xl-5">--}}
{{--                                    <h3 class="mt-2">{{number_format(@$totalPaidAmount,2)}}</h3>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

            </div>
        </div>
    </div> <!-- row -->

    <div class="row">

        <div class="col-lg-6 col-xl-6 stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <h6 class="card-title mb-0">Debit List</h6>
                    </div>
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
        <div class="col-lg-6 col-xl-6 stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <h6 class="card-title mb-0">Credit List</h6>
                    </div>
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
                                    <td>{{formatDate($list->paid_date)}}</td>
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
