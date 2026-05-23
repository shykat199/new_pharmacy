<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class StaffTable extends PowerGridComponent
{

    public string $tableName = 'users';
    public string $role;

    public function setUp(): array
    {
//        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(perPage: 10, perPageValues: [10, 50, 100, 500])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $searchTerm = $this->search;
        $user =  User::query()->where('role', STAFF_ROLE);
        if (!empty($searchTerm)) {
            $searchTerm = strtolower($searchTerm);
            $user->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ["{$searchTerm}%"])
                    ->orWhereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhere('phone', 'LIKE', "{$searchTerm}%")
                    ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
            })
                ->orderByRaw("
                    CASE
                        WHEN LOWER(name) LIKE '{$searchTerm}%' THEN 1
                        WHEN phone LIKE '{$searchTerm}%' THEN 2
                        ELSE 3
                    END
                ")->orderBy('name');
        }

        return $user->orderBy('id','DESC');

    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
//            ->add('email')
            ->add('phone')
//            ->add('role', fn($user) => $user->role ? 'Customer' : '')
            ->add('balance', function ($user){
                return !empty($user->totalUnpaidAmount) ? number_format($user->totalUnpaidAmount, 2) :'0.00';
            })
//            ->add('status', function ($user) {
//                return $user->status
//                    ? '<span class="bg-success text-white px-2 py-1 rounded-md">Active</span>'
//                    : '<span class="bg-danger text-white px-2 py-1 rounded-md">Inactive</span>';
//            })
            ->add('address')
            ->add('modify_created_at',function ($user){
               return Carbon::parse($user->created_at)->format('d-M-Y');
            });

    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id')->hidden(),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Phone', 'phone')
                ->sortable()
                ->searchable(),

//            Column::make('Role', 'role')
//                ->sortable()
//                ->searchable(),

            Column::make('Due Amount', 'balance')
                ->sortable()
                ->searchable()->hidden(),

//            Column::make('Status', 'status')
//                ->sortable()
//                ->searchable(),

            Column::make('Address', 'address')
                ->sortable()
                ->searchable(),

            Column::add()
                ->title('Created at')
                ->field('modify_created_at', 'modify_created_at'),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    public function actions(User $user): array
    {
        $actions = [];

        // Edit Button with permission check
        if (auth()->user()->can('view user')) {
            $actions[] = Button::add('edit')
                ->slot('<i class="fa fa-edit"></i>')
                ->attributes(['wire:click.prevent' => "editStaff($user->id)"])
                ->class('btn btn-primary btn-sm');
        }

        // Delete Button with permission check
        if (auth()->user()->can('delete user')) {
            $actions[] = Button::add('delete')
                ->slot('<i class="fa fa-trash"></i>')
                ->class('btn btn-danger btn-sm')
                ->dispatch('deleteEvent', ['id' => $user->id]);
        }

        return $actions;
    }


    public function editStaff($id)
    {
        $user = User::find($id);
        $this->dispatch('openModal', 'Edit Staff', 'updateStaff',[
            'id'      => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'phone'   => $user->phone,
            'address' => $user->address,
            'status' => $user->status,
        ]);
    }

}
