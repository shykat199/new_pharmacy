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
                        @can('add user')
                            <a href="" wire:click.prevent="addStaff" class="btn btn-primary btn-sm btn-icon-text"><i
                                    class="fa fa-plus-circle"></i> &nbsp; Add New</a>
                        @endcan

                    </div>
                </div>
                <div class="card-body">
                    @livewire('admin.staff-table')
                </div>
            </div>
        </div>
    </div> <!-- row -->
    @livewire('admin.modal.user-modal')
</div>
