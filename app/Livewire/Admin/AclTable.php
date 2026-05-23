<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class AclTable extends PowerGridComponent
{
    public string $tableName = 'roles';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Role::query();
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
            ->add('created_at',function ($item){
                return formatDate($item->created_at);
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchable(),

            Column::action('Action')
        ];
    }

    public function actions(Role $row): array
    {
        return [
            Button::add('edit')
                ->slot('<i class="fa fa-edit"></i>')
                ->attributes(['wire:click.prevent' => "editRole($row->id)"])
                ->class('btn btn-primary btn-sm'),

            Button::add('permission')
                ->slot('<i class="fa fa-info-circle"></i>')
                ->class('btn btn-info me-1 btn-sm')
                ->attributes(['wire:click.prevent' => "addPermission($row->id)"])
        ];
    }

    public function addPermission($id)
    {
        $this->redirectRoute('admin.add-permission',$id);
    }

    public function editRole($id)
    {
        $this->dispatch('open-edit-role-modal',$id);
    }

}
