<?php

namespace App\Livewire;

use App\Models\Invoices;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class PendingInvoiceList extends PowerGridComponent
{
    public string $tableName = 'invoices';
    public string $selectedCustomer = '';

    public array $filters = [];
    public $date = '';

    protected $listeners = ['delete','companySelected','dateSelected'];

    public $authUser ;

    public function setUp(): array
    {
//        $this->showCheckBox();

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

        $searchTerm = $this->search;

        $invoice = Invoices::query()
            ->with(['user', 'createdBy'])
            ->whereIn('status', [PENDING_STATUS, DRAFT_STATUS])
            ->when($this->selectedCustomer, function ($q) {
                $q->whereHas('user', function ($q) {
                    $q->where('id', $this->selectedCustomer);
                });
            });

        if ($this->authUser->role == ADMIN_ROLE) {
            // Only users with ADMIN or STAFF role
            $invoice->whereHas('createdBy', function ($q) {
                $q->whereIn('role', [ADMIN_ROLE, STAFF_ROLE]);
            });
        } else {
            // Only invoices created by current user
            $invoice->where('created_by', $this->authUser->id);
        }

        if (!empty($searchTerm)) {
            $searchTerm = strtolower($searchTerm);

            $invoice->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($subQuery) use ($searchTerm) {
                    $subQuery->whereRaw('LOWER(name) LIKE ?', ["{$searchTerm}%"])
                        ->orWhereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"]);
                });
            })->orderByRaw("
        CASE
            WHEN EXISTS (
                SELECT 1 FROM users
                WHERE users.id = invoices.user_id
                AND LOWER(users.name) LIKE '{$searchTerm}%'
            ) THEN 1
            ELSE 2
        END
    ");
        }

        if (!empty($this->filters['date']['created_at']['formatted'])) {
            $date = $this->filters['date']['created_at']['formatted'];
            $invoice->whereDate('created_at', '=', $date);
        }

        return $invoice->orderByDesc('id');

    }

    public function relationSearch(): array
    {
        return [
            'user' => ['name'],
            'createdBy' => ['name']
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('invoice_id')
            ->add('created_by', function ($item) {
                return !empty($item->createdBy)
                    ? $item->createdBy->name . ' (' . ($item->createdBy->role == ADMIN_ROLE ? 'AD' : 'ST') . ')'
                    : 'Unknown';
            })
            ->add('user_id', function ($item) {
                return !empty($item->user) ? $item->user->name : 'Unknown';
            })
            ->add('total_amount', function ($item) {
                return number_format($item->final_total, 2);
            })
            ->add('custom_total_amount', function ($item) {
                return number_format($item->total_amount, 2);
            })
            ->add('custom_discount', function ($item) {
                return number_format($item->custom_discount, 2);
            })
            ->add('other_charges', function ($item) {
                return number_format($item->other_charges, 2);
            })
            ->add('final_total')
            ->add('paid_amount', function ($item) {
                return number_format($item->paid_amount, 2);
            })
            ->add('due_amount')
            ->add('modify_created_at', function ($item) {
                return \Carbon\Carbon::parse($item->created_at)->format('Y-m-d');
            })
            ->add('status', function ($product) {
                return $product->status == PENDING_STATUS
                    ? '<span class="bg-warning text-white px-2 py-1 rounded-md">Pending</span>' :
                    ($product->status == DELETE_STATUS ? '<span class="bg-danger text-white px-2 py-1 rounded-md">Delete</span>' :
                        ($product->status == DRAFT_STATUS ?'<span class="bg-warning text-white px-2 py-1 rounded-md">Draft</span>':
                            '<span class="bg-success text-white px-2 py-1 rounded-md">Paid/Approved</span>'));
            });
    }

    public function columns(): array
    {
        $column =  [
            Column::make('Id', 'id')->hidden(),
            Column::make('Invoice id', 'invoice_id')->searchable(),
            Column::make('Created by', 'created_by')->searchable(),
            Column::make('User Name', 'user_id')->searchable(),
//            Column::make('Total Amount', 'total_amount')
//                ->sortable()
//                ->searchable(),

//            Column::make('Custom discount', 'custom_discount')
//                ->sortable()
//                ->searchable(),

//            Column::make('Other charges', 'other_charges')
//                ->sortable()
//                ->searchable(),

            Column::make('Grand total', 'final_total')
                ->sortable()
                ->searchable(),

            Column::make('Paid amount', 'paid_amount')
                ->sortable()
                ->searchable(),

            Column::make('Due amount', 'due_amount')
                ->sortable()
                ->searchable(),

//            Column::make('Status', 'status'),

            Column::make('Created at', 'modify_created_at', 'created_at')
                ->sortable(),

            Column::action('Action')
        ];

        if (!empty($this->authUser) && $this->authUser->role == USER_ROLE){
            $column =  [
                Column::make('Id', 'id'),
                Column::make('Invoice id', 'invoice_id')->searchable(),
                Column::make('Created by', 'created_by')->searchable(),

                Column::make('Total Amount', 'total_amount')
                    ->sortable()
                    ->searchable(),

                Column::make('Paid amount', 'paid_amount')
                    ->sortable()
                    ->searchable(),

                Column::make('Due amount', 'due_amount')
                    ->sortable()
                    ->searchable(),

                Column::make('Status', 'status'),

                Column::make('Created at', 'modify_created_at', 'created_at')
                    ->sortable(),

                Column::action('Action')
            ];
        }

        return $column;
    }

    public function filters(): array
    {
        return [
            Filter::datepicker('modify_created_at', 'created_at')
                ->params([
                    'timezone' => 'Asia/Dhaka',
                    'dateFormat' => 'Y-m-d',
                    'mode' => 'single',
                ])
        ];
    }

//    public function dateSelected ($data)
//    {
//       $this->date = $data;
//    }

    public function actions(Invoices $item): array
    {

        $attribute = ['wire:click' => "redirectToInvoice($item->id)"];

        if(auth()->user()->role == USER_ROLE){
            $attribute = ['wire:click' => "redirectToInvoice($item->id)"];
        }

        $actions = [
            // Edit Button with permission check
            auth()->user()->can('view invoice') ? Button::add('edit')
                ->slot('<i class="fa fa-edit"></i>')
                ->attributes($attribute)
                ->class('btn btn-primary btn-sm') : null,

            // Delete Button with permission check
            auth()->user()->can('delete invoice') ? Button::add('delete')
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

    public function delete(\App\Models\Invoices $invoice): void
    {

        OrderItem::where('invoice_id',$invoice->id)->delete();

        $dltInvoice = $invoice->delete();

        if ($dltInvoice) {
            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('refresh-browser');
            $this->dispatch('toast', type: 'success', message: 'Invoice deleted successfully');
        } else {
            $this->dispatch('toast', type: 'error', message: 'Invoice not deleted');
        }
    }

    public function companySelected($id)
    {
        $this->selectedCustomer = $id;
    }

    public function redirectToInvoice($id)
    {
        if (auth()->user()->role == USER_ROLE) {
            return redirect()->route('user.invoice-details', $id);
        }

        return redirect()->route('admin.invoice-details', $id);
    }
}
