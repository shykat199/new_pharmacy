<?php

namespace App\Livewire\Admin;

use App\Models\Invoices;
use App\Models\OrderItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class UserInvoiceDetails extends Component
{
    public $invoiceId;
    public $invoiceDetails;
    public $orderItems;
    public $totalMedicinePrice;
    public function getInvoiceDetails($id)
    {
        $this->invoiceId = $id;
        $this->invoiceDetails = Invoices::with(['user'])->find($this->invoiceId);
        $this->orderItems = OrderItem::with(['product.company'])->where('invoice_id', $this->invoiceId)->get();
        $this->totalMedicinePrice = OrderItem::where('invoice_id', $this->invoiceId)->sum('final_total');

        $data['userName'] = !empty($this->invoiceDetails->user) ? $this->invoiceDetails->user->name : 'Unknown';
        $data['userId'] = !empty($this->invoiceDetails->user) ? $this->invoiceDetails->user->id : null;
        $data['phone'] = !empty($this->invoiceDetails->user) ? $this->invoiceDetails->user->phone : 'Unknown';
        $data['address'] = !empty($this->invoiceDetails->user) ? $this->invoiceDetails->user->address : 'Unknown';
        $data['invoiceStatus'] = $this->invoiceDetails->status == ACTIVE_STATUS ? ACTIVE_STATUS : ($this->invoiceDetails->status == PENDING_STATUS ? PENDING_STATUS : DRAFT_STATUS);

        $data['grandTotal'] = number_format($this->invoiceDetails->final_total, 2) ?? 0;
        $data['paidAmount'] = !empty($this->invoiceDetails) ? number_format($this->invoiceDetails->paid_amount, 2) : 0;
        $data['otherCharge'] = !empty($this->invoiceDetails) ? number_format($this->invoiceDetails->other_charges, 2) : 0;
        $data['dueAmount'] = number_format($this->invoiceDetails->due_amount, 2);
        $data['totalMedicinePrice'] = number_format($this->totalMedicinePrice, 2);
        $data['note'] = $this->invoiceDetails->note ?? '';
        $data['total_amount_without_discount'] = number_format($this->invoiceDetails->total_amount, 2);
        $data['custom_total_discount'] = number_format($this->invoiceDetails->custom_total_discount, 2);
        $data['orderItems'] = $this->orderItems;
        $data['invoiceId'] = $this->invoiceId;
        $data['invoiceDetailsId'] = $this->invoiceDetails->invoice_id;
        $data['invoiceDetails'] = $this->invoiceDetails;
        $data['page'] = 'Invoice Details';
        $data['page1'] = 'Customer Details';
        $data['title'] = 'Invoice Details';


        return view('livewire.admin.user-invoice-details', $data);
    }

    #[Layout('layout.app')]
    #[Title('Customers Invoice Details')]
    public function render()
    {
        return view('livewire.admin.user-invoice-details',[
            'page'=>'User Invoice Details'
        ]);
    }
}
