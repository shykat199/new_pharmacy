<div>
    @push('style')
        <link rel="stylesheet" href="{{asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css')}}">
    @endpush
    @section('admin.breadcrumb')
        <li class="breadcrumb-item" aria-current="page">{{$page}}</li>
    @endsection
    @include('sweetalert::alert')

    <div class="row">
        <div class="col-6 col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <h6 class="card-title mb-0">MP</h6>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-12 col-xl-8">
                            <h3 class="mt-2 amount-text" id="medicineAmount" style="filter: blur(5px); user-select: none; pointer-events: none;">
                                <span id="totalMedicineAmount">0.00</span>
                            </h3>
                            <button class="btn btn-sm btn-primary mt-3" onclick="verifyPassword('medicine')">View <i class="fa fa-eye"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <h6 class="card-title mb-0">DA</h6>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-12 col-xl-8">
                            <h3 class="mt-2 amount-text" id="dueAmount" style="filter: blur(5px); user-select: none; pointer-events: none;">
                                <span id="totalDueAmount">0.00</span>
                            </h3>
                            <button class="btn btn-sm btn-primary mt-3" onclick="verifyPassword('due')">View <i class="fa fa-eye"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-12 grid-margin stretch-card">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>{{$page}}</h3>
                        <button type="button" class="btn btn-primary btn-sm btn-icon-text" data-bs-toggle="modal" data-bs-target="#exampleModal"><i
                                class="fa fa-plus-circle"></i> &nbsp; Add New</button>
                    </div>
                </div>
                <div class="card-body">
                    @livewire('admin.acl-table')
                </div>
            </div>
        </div>
    </div> <!-- row -->

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{route('add-role')}}">
                        @csrf
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label">Role Name</label>
                            <input type="text" name="name" class="form-control" id="exampleInputEmail1"
                                   aria-describedby="emailHelp">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editRoleModalLabel">Update Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('admin.update-role') }}">
                            @csrf
                            <input type="hidden" name="id" wire:model="role_id" id="role_id" value="">
                            <div class="mb-3">
                                <label for="role_name" class="form-label">Role Name</label>
                                <input wire:model="role_name" type="text" name="name" class="form-control" id="role_name">
                            </div>

                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

</div>

@push('scripts')
    <script src="{{asset('assets/vendors/datatables.net/dataTables.js')}}"></script>
    <script src="{{asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js')}}"></script>
    <script src="{{asset('assets/js/data-table.js')}}"></script>

    <script>
        function verifyPassword(type) {
            const amountElement = type === 'medicine'
                ? document.getElementById('medicineAmount')
                : document.getElementById('dueAmount');

            const isBlurred = amountElement.style.filter !== 'none';

            if (isBlurred) {
                const password = prompt("Enter admin password to view amount:");
                if (!password) return;

                fetch("{{ route('verify.amount.password') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        password: password,
                        type: type
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            amountElement.style.filter = 'none';
                            amountElement.style.userSelect = 'auto';
                            amountElement.style.pointerEvents = 'auto';
                            if (data.type == 'medicine'){
                                document.getElementById('totalMedicineAmount').innerHTML = data.amount
                            }else {
                                document.getElementById('totalDueAmount').innerHTML = data.amount
                            }
                        } else {
                            alert("Incorrect password.");
                        }
                    })
                    .catch(() => alert("Something went wrong."));
            } else {
                amountElement.style.filter = 'blur(5px)';
                amountElement.style.userSelect = 'none';
                amountElement.style.pointerEvents = 'none';
            }
        }
    </script>

@endpush
