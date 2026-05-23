<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

final class ProductTable extends PowerGridComponent
{
    public string $tableName = 'products';
    public string $selectedCompany = '';
    public $requestType;

    protected $listeners = ['companySelected'];

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput(),

            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function datasource(): Builder
    {
        $searchTerm = $this->search;

        $query = Product::query()
            ->whereHas('company', function ($q) {
                $q->where('status', ACTIVE_STATUS);
            })
            ->with(['company'])
            ->when($this->requestType === 'outofstock', function ($q) {
                $q->where('stock', 0)->where('status', ACTIVE_STATUS);
            })
            ->when($this->requestType === 'lowstock', function ($q) {
                $q->whereRaw('stock < (low_stock * box_per_pic)')
                    ->where('status', ACTIVE_STATUS);
            })
            ->when($this->requestType === 'company', function ($q) {
                $company = request()->get('company');
                $q->whereHas('company', function ($query) use ($company) {
                    $query->where('slug', $company);
                });
            })
            ->when($this->selectedCompany, function ($q) {
                $q->whereHas('company', function ($q) {
                    $q->where('id', $this->selectedCompany);
                });

                $q->when($this->requestType === 'outofstock', function ($q) {
                    $q->where('stock', 0)->where('status', ACTIVE_STATUS);
                });

                $q->when($this->requestType === 'lowstock', function ($q) {
                    $q->whereColumn('stock', '<', 'low_stock')->where('status', ACTIVE_STATUS);
                });

                $q->when($this->requestType === 'company', function ($q) {
                    $company = request()->get('company');
                    $q->whereHas('company', function ($query) use ($company) {
                        $query->where('slug', $company);
                    });
                });
            });

        if (!empty($searchTerm)) {
            $searchTerm = strtolower($searchTerm);
            $query->where(function ($q) use ($searchTerm) {
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

        return $query->orderByDesc('id');
    }



    public function relationSearch(): array
    {
        return [
            'company' => [
                'name',
            ]
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name',function ($product){
                return $product->name.' '.$product->strength.' '.Str::limit($product->type,'3',' - ').Str::limit($product->company->name,'3','');
            })
            ->add('unit_price')
            ->add('box_per_pic')
            ->add('stock')
            ->add('low_stock', fn($prduct)=>$prduct->low_stock ?? 0)
            ->add('status', function ($product) {
                return $product->status == ACTIVE_STATUS
                    ? '<span class="bg-success text-white px-2 py-1 rounded-md">Active</span>'
                    : '<span class="bg-danger text-white px-2 py-1 rounded-md">Inactive</span>';
            });
//            ->add('modify_created_at',function ($product){
//                return \Carbon\Carbon::parse($product->created_at)->format('d-M-Y');
//            });
    }

    public function columns(): array
    {
        return [
//            Column::make('Id', 'id'),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Unit price', 'unit_price')
                ->sortable()
                ->searchable(),

            Column::make('Box per pic', 'box_per_pic')
                ->sortable()
                ->searchable()
                ->headerAttribute('class', 'custom-header-class') // For header
                ->bodyAttribute('class', 'custom-body-class'),    // For body cells

            Column::make('Stock', 'stock')
                ->sortable()
                ->searchable(),

            Column::make('Low Stock Alert', 'low_stock'),

            Column::make('Status', 'status')
                ->sortable()
                ->searchable(),

//            Column::add()
//                ->title('Created at')
//                ->field('modify_created_at', 'modify_created_at'),

            Column::action('Action')
        ];
    }

//    public function filters(): array
//    {
//        return [
//            // Select filter by company_id
//            Filter::select('company', 'products.company_id')
//                ->dataSource(\App\Models\Company::all())
//                ->optionLabel('name')
//                ->optionValue('id'),
//
//            // Text input filter by company name (search)
//            Filter::inputText('company_name')
//                ->placeholder('Search Company Name')
//                ->operators(['contains']),
//        ];
//    }

    public function actions(Product $item): array
    {
        $actions = [
            // Add Stock Button with permission check
            auth()->user()->can('update medicine') ? Button::add('add-stock')
                ->slot('<i class="fa fa-plus-circle text-white"></i>')
                ->class('btn btn-warning btn-sm')
                ->dispatch('openModal', [
                    'modalId' => 'addStock',
                    'modalType' => 'addStock',
                    'id' => $item->id
                ]) : null,

            // Edit Button with permission check
            auth()->user()->can('view medicine') ? Button::add('edit')
                ->slot('<i class="fa fa-edit"></i>')
                ->attributes(['wire:click.prevent' => "editMedicine($item->id)"])
                ->class('btn btn-primary btn-sm') : null,

            // Delete Button with permission check
            auth()->user()->can('delete medicine') ? Button::add('delete')
                ->slot('<i class="fa fa-trash"></i>')
                ->class('btn btn-danger btn-sm')
                ->dispatch('deleteEvent', ['id' => $item->id]) : null,
        ];

        // Remove null values from the array
        return array_filter($actions, fn($action) => $action !== null);
    }

    public function editMedicine($id)
    {
        $this->dispatch('openModal',[
            'modalId' => 'createModal',
            'edit_modal'=> true,
            'productId'=>$id
        ]);
    }

    public function companySelected($id)
    {
        $this->selectedCompany = $id;
    }

    public function globalSearch(Builder $builder): Builder
    {
        $search = $this->search;

        return $builder->where(function ($query) use ($search) {
            $query->where('name', 'LIKE', $search . '%') // prioritize names starting with search term
            ->orWhere('name', 'LIKE', '%' . $search . '%'); // fallback to anywhere
        });
    }

}
