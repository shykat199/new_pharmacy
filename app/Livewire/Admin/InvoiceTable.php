<?php

namespace App\Livewire\Admin;

use App\Models\Invoices;
use App\Models\OrderItem;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Livewire\Attributes\On;

final class InvoiceTable extends PowerGridComponent
{
    public string $tableName = 'invoices';
    public $date = '';

    protected $listeners = ['delete','dateSelected'];

    public $authUser ;
    public $segment ;
    public array $filters = [];
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
        $status = request()->get('status');
        $role = request()->get('role');
        $searchTerm = $this->search;

        $invoices =  Invoices::query()->with(['user', 'createdBy']);

        if (!empty($this->authUser) && $this->authUser->role == USER_ROLE){
            $invoices = $invoices->where('user_id',$this->authUser->id);
            if (!empty($status) && $status == 'pending') {
                $invoices = $invoices->whereIn('invoices.status', [PENDING_STATUS, DRAFT_STATUS]);
            } else {
                $invoices = $invoices->where('invoices.status', ACTIVE_STATUS);
            }
        }

        if (!empty($this->authUser) && $this->authUser->role == STAFF_ROLE) {

            $invoices = $invoices->where('created_by', $this->authUser->id);

            if (!empty($status) && $status == 'pending') {
                $invoices = $invoices->whereIn('invoices.status', [PENDING_STATUS, DRAFT_STATUS]);
            } else {
                $invoices = $invoices->where('invoices.status', ACTIVE_STATUS);
            }
        }

        if (!empty($this->authUser) && $this->authUser->role == ADMIN_ROLE) {

            if (!empty($status) && $status == 'pending' && !empty($role) && $role == 'staff') {
                $invoices = $invoices->whereIn('invoices.status', [PENDING_STATUS, DRAFT_STATUS])
                    ->whereHas('createdBy', function ($query) {
                        $query->where('role', STAFF_ROLE);
                    });
            } elseif (!empty($status) && $status == 'pending' && !empty($role) && $role == 'customer') {
                $invoices = $invoices->whereIn('invoices.status', [PENDING_STATUS, DRAFT_STATUS])
                    ->whereHas('createdBy', function ($query) {
                        $query->where('role', USER_ROLE);
                    });
            } elseif (!empty($status) && $status == 'pending') {
                $invoices = $invoices->whereIn('invoices.status', [PENDING_STATUS, DRAFT_STATUS]);
            } else {
                $invoices = $invoices->where('invoices.status', ACTIVE_STATUS);
            }
        }

        if (!empty($searchTerm)) {
            $searchTerm = strtolower($searchTerm);
            $invoices->where(function ($q) use ($searchTerm) {
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

        if ($this->segment == 'draft-invoices'){

            $invoices =  Invoices::query()->with(['user', 'createdBy']);
            $invoices = $invoices->where('status',DRAFT_STATUS);
        }

        if (!empty($this->filters['date']['created_at']['formatted'])) {
            $date = $this->filters['date']['created_at']['formatted'];
            $invoices->whereDate('created_at', '=', $date);
        }

//        if (!empty($this->date)){
//            $invoices->whereDate('created_at',$this->date);
//        }

        return  $invoices->orderByDesc('updated_at');
    }
    
   public function header(): array
    {
        $button = [];

        if (\Auth::user()->role == ADMIN_ROLE){
            $button[]= Button::add('delete')
                ->slot('<i class="fa fa-trash"></i> Bulk Delete')
                ->class('btn btn-danger btn-sm')
                ->dispatch('bulkDelete.' . $this->tableName, [])
                ->attributes([
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'top',
                    'title' => 'Bulk Delete',
                ]);
        }

        return $button;
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
                return !empty($item->createdBy) ? $item->createdBy->name : 'Unknown';
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

            Column::make('Grand total', 'total_amount')
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

                Column::make('Grand Amount', 'total_amount')
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
        $actions = [];

        if(auth()->user()->role == USER_ROLE){
            $attribute = ['wire:click' => "redirectToInvoice($item->id)"];
        }

        $pdfAttributeWithDiscount = ['wire:click' => "generatePdf($item->id,'withDiscount')"];
        $pdfAttributeWithOutDiscount = ['wire:click' => "generatePdf($item->id,'withOutDiscount')"];

        if (auth()->user()->can('view invoice')) {
            $actions[] = Button::add('view')
                ->slot('<i class="fa fa-edit"></i>')
                ->attributes($attribute)
                ->class('btn btn-primary btn-sm');
        }

        if (auth()->user()->can('delete invoice')) {
            $actions[] = Button::add('delete')
                ->slot('<i class="fa fa-trash"></i>')
                ->class('btn btn-danger btn-sm')
                ->dispatch('deleteEvent', ['id' => $item->id]);
        }

        if (auth()->user()->role === ADMIN_ROLE) {
            $actions[] = Button::add('print_with_discount')
                ->slot('<i class="fa fa-print"></i> With Discount')
                ->attributes($pdfAttributeWithDiscount)
                ->class('btn btn-success btn-sm');

            $actions[] = Button::add('print_without_discount')
                ->slot('<i class="fa fa-print"></i> Without Discount')
                ->attributes($pdfAttributeWithOutDiscount)
                ->class('btn btn-warning btn-sm');
        }

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
    
    #[On('bulkDelete.invoices')]
    public function bulkDelete(): void
    {

        if (count($this->checkboxValues) > 0) {

            \App\Models\Invoices::whereIn('id', $this->checkboxValues)->update(['status' => DELETE_STATUS]);

            $this->checkboxValues = [];

            $this->dispatch('toast', type: 'success', message: 'Selected invoice deleted successfully!');
            $this->dispatch('pg:eventRefresh-invoices');

        } else {
            $this->dispatch('toast', type: 'error', message: 'Please select row to perform delete action!');
        }
    }

    public function generatePdf($itemId,$type)
    {

        $discount = $type;

        if (!empty($discount) && $discount == 'withDiscount') {

            $pdfContent = $this->generateInvoicePdfWithDiscount($itemId);

        } else {

            $pdfContent = $this->generateInvoicePdf($itemId);

        }

        // URL-safe base64 encoding
        $base64 = base64_encode($pdfContent);
        $base64 = rtrim($base64, '=');
        $base64 = strtr($base64, '+/', '-_');

        $this->dispatch('show-pdf', $base64);

    }

    public function redirectToInvoice($id)
    {
        // Add any pre-processing logic here if needed
        if (auth()->user()->role == USER_ROLE) {
            return redirect()->route('user.invoice-details', $id);
        }



        return redirect()->route('admin.invoice-details', $id);
    }

    private function generateInvoicePdf($itemId)
    {
        try {

            $invoiceId = $itemId;

            $invoice = Invoices::find($invoiceId);


            $rows = OrderItem::where('invoice_id',$invoiceId)->get()->toArray();

            $invoiceDetails = $invoice;
            $grandTotal = $invoice->final_total;
            $totalMedicinePrice = $invoice->total_amount;
            $paidAmount = $invoice->paid_amount;
            $otherCharge = $invoice->other_charges;
            $dueAmount = $invoice->due_amount;

            if (!empty($invoiceDetails)) {
                $shopName = getSettingsData('shopName');

                $data = [
                    'title' => mb_convert_encoding($shopName, 'UTF-8', 'auto'),
                    'date' => date('d-M-Y'),
                    'orderItems' => $rows,
                    'invoiceDetails' => $invoice,
                    'grandTotal' => $grandTotal,
                    'total' => $totalMedicinePrice,
                    'paidAmt' => $paidAmount,
                    'otherCharge' => $otherCharge,
                    'dueAmt' => $dueAmount,
                ];

                $pdf = PDF::loadView('pdf.copy-invoice-withOutDiscount', $data)
                    ->setOptions(['defaultFont' => 'Arial'])
                    ->setPaper([0, 0, 600, 1300]);
//
//            $fileName = 'invoice-'.str_replace('#','',$this->invoiceDetails->invoice_id).'_' . time() . '.pdf';

                return $pdf->output();
            }

        } catch (\Exception $e) {
            $this->dispatch('pdf-error', ['message' => $e->getMessage()]);
            dd($e->getMessage());
            return;
        }
    }

    private function generateInvoicePdfWithDiscount($itemId)
    {
        try {

            $invoiceId = $itemId;

            $invoice = Invoices::find($invoiceId);

            $rows = OrderItem::where('invoice_id',$invoiceId)->get()->toArray();

            $invoiceDetails = $invoice;
            $grandTotal = $invoice->final_total;
            $totalMedicinePrice = $invoice->total_amount;
            $paidAmount = $invoice->paid_amount;
            $otherCharge = $invoice->other_charges;
            $dueAmount = $invoice->due_amount;


            if (!empty($invoiceDetails)) {
                $shopName = getSettingsData('shopName');

                $data = [
                    'title' => mb_convert_encoding($shopName, 'UTF-8', 'auto'),
                    'date' => date('d-M-Y'),
                    'orderItems' => $rows,
                    'invoiceDetails' => $invoice,
                    'grandTotal' => $grandTotal,
                    'total' => $totalMedicinePrice,
                    'paidAmt' => $paidAmount,
                    'otherCharge' => $otherCharge,
                    'dueAmt' => $dueAmount,
                    'discount' => 'withDiscount'
                ];

                $pdf = PDF::loadView('pdf.copy-invoice', $data)
                    ->setOptions(['defaultFont' => 'Arial'])
                    ->setPaper([0, 0, 600, 1300]);
//
//            $fileName = 'invoice-'.str_replace('#','',$this->invoiceDetails->invoice_id).'_' . time() . '.pdf';

                return $pdf->output();
            }

        } catch (\Exception $e) {

            dd($e->getMessage());
            return;
        }
    }

}
