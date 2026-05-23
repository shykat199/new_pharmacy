<?php

namespace App\Livewire\Admin;

use App\Models\Invoices;
use App\Models\OrderItem;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class UserInvoiceListTable extends PowerGridComponent
{
    public string $tableName = 'invoices';
    public $date = '';

    protected $listeners = ['delete','dateSelected'];
    public array $filters = [];

    public $userId;

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
//            PowerGrid::header()
//                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function header(): array
    {

        return [

            Button::add('delete')
                ->slot('<i class="fa fa-trash"></i> Bulk Delete')
                ->class('btn btn-danger btn-sm')
                ->dispatch('bulkDelete.' . $this->tableName, [])
                ->attributes([
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'top',
                    'title' => 'Bulk Delete',
                ]),
        ];
    }

    public function datasource(): Builder
    {

        $invoices =  Invoices::query()
            ->where('invoices.user_id',$this->userId)
            ->with(['user', 'createdBy']);

        if (!empty($this->filters['date']['created_at']['formatted'])) {
            $date = $this->filters['date']['created_at']['formatted'];
            $invoices->whereDate('created_at', '=', $date);
        }

        return  $invoices->orderByDesc('id');
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
                return number_format($item->total_amount, 2);
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
            Column::make('Final total', 'final_total')
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

        return $column;
    }

    public function filters(): array
    {
        return [
            Filter::datepicker('modify_created_at', 'created_at')
                ->params([
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

            Transaction::whereIn('invoice_id',$this->checkboxValues)->delete();

            OrderItem::whereIn('invoice_id',$this->checkboxValues)->delete();

            \App\Models\Invoices::whereIn('id', $this->checkboxValues)->delete();

            $this->checkboxValues = [];

            $this->dispatch('toast', type: 'success', message: 'Selected invoice deleted successfully!');
            $this->dispatch('pg:eventRefresh-users');

        } else {
            $this->dispatch('toast', type: 'error', message: 'Please select invoice to perform delete action!');
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

        return redirect()->route('admin.user-invoice-details', $id);
    }

    private function generateInvoicePdf($itemId)
    {
        try {

            $invoiceId = $itemId;

            $invoice = Invoices::find($invoiceId);


            $rows = OrderItem::where('invoice_id',$invoiceId)->get()->toArray();

            $invoiceDetails = $invoice;
            $grandTotal = $invoice->final_total;
            $totalMedicinePrice = $invoice->final_total;
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
                    ->setPaper([0, 0, 500, 1300]);
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
            $totalMedicinePrice = $invoice->final_total;
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
