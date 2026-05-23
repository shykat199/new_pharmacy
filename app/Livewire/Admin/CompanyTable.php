<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class CompanyTable extends PowerGridComponent
{
    public string $tableName = 'companies';

    public function setUp(): array
    {
        $this->showCheckBox();

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
        $company =  Company::query();

        if (!empty($searchTerm)) {
            $searchTerm = strtolower($searchTerm);
            $company->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ["{$searchTerm}%"])
                    ->orWhereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"]);
            })
                ->orderByRaw("
                CASE
                    WHEN LOWER(name) LIKE '{$searchTerm}%' THEN 1
                    ELSE 2
                END
            ")->orderBy('name');
        }

        return  $company->orderByDesc('id');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name', function ($company) {
                $url = route('admin.medicine',['type'=>'company','company'=>$company->slug]);
                return '<a href="' . $url . '" class="text-primary underline">' . e($company->name) . '</a>';
            })
            ->add('status', function ($company) {
                return $company->status == ACTIVE_STATUS
                    ? '<span class="bg-success text-white px-2 py-1 rounded-md">Active</span>'
                    : '<span class="bg-danger text-white px-2 py-1 rounded-md">Inactive</span>';
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Status', 'status')
                ->sortable()
                ->searchable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    public function actions(Company $company): array
    {
        $actions = [
            // Edit Button with permission check
            auth()->user()->can('view company') ? Button::add('edit')
                ->slot('<i class="fa fa-edit"></i>')
                ->attributes(['wire:click.prevent' => "editCompany($company->id)"])
                ->class('btn btn-primary btn-sm') : null,

            // Delete Button with permission check
            auth()->user()->can('delete company') ? Button::add('delete')
                ->slot('<i class="fa fa-trash"></i>')
                ->class('btn btn-danger btn-sm')
                ->dispatch('deleteEvent', ['id' => $company->id]) : null,
        ];

        return array_filter($actions);

    }

    public function editCompany($id)
    {
        $company = Company::find($id);
        $this->dispatch('openModal', 'Edit Company', 'updateCompany',[
            'id'      => $company->id,
            'name'    => $company->name,
            'status' => $company->status,
        ]);
    }
}
