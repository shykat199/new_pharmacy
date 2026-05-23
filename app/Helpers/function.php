<?php

function formatDate($date, string $format = 'd-M-Y H:i:s')
{

    return \Illuminate\Support\Carbon::parse($date)->format($format);
}

function generateUniqueInvoiceId(): int
{
    do {
        $invoiceId = mt_rand(100000, 999999);
    } while (\App\Models\Invoices::where('invoice_id', $invoiceId)->exists());

    return $invoiceId;
}

function createTransaction($data, $type=''): void
{
    $transactionData =[
      'user_id'=>$data['user_id'],
      'amount'=>$data['amount'],
      'type'=>$data['type'] ?? 1,
      'note'=>$data['note'] ?? '',
      'paid_date'=>$data['paid_date'],
      'invoice_id'=>$data['invoice_id'],
    ];

    if (!empty($transactionData) && $type == 'update'){
        \App\Models\Transaction::where('id',$data['invoice_id'])->update([
            $transactionData
        ]);
    }else{
        \App\Models\Transaction::create($transactionData);
    }
}

function getSettingsData($input = null)
{
    if (empty($input)) {
        $data = \App\Models\SiteSetting::get()
            ->pluck('value', 'key')
            ->toArray();
    } elseif (is_array($input)) {
        $data = \App\Models\SiteSetting::whereIn('key', $input)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    } else {
        $item = \App\Models\SiteSetting::where('key', $input)->first();

        $data = empty($item) ? '' : $item->value;
    }

    return $data;
}

function getQuotient($stock, $boxPerPic): int
{
    $stock = (int)$stock;
    $boxPerPic = (int)$boxPerPic;

    if ($boxPerPic === 0 || $stock === 0) {
        return 0;
    }

    return intdiv($stock, $boxPerPic);
}

function getRemainder($stock, $boxPerPic): int
{
    $stock = (int)$stock;
    $boxPerPic = (int)$boxPerPic;

    if ($boxPerPic === 0) {
        return 0;
    }

    return $stock % $boxPerPic;
}

function customRoundNumber($value) {

    $decimalPart = $value - floor($value);

    if ($decimalPart > 0.40) {
        return ceil($value);
    } else {
        return floor($value);
    }
}

function todayTotalSale()
{
    return \App\Models\Invoices::whereDate('created_at',now())->where('status',ACTIVE_STATUS)->sum('total_amount');
}

function todayDueAmount()
{
    return \App\Models\Invoices::whereDate('created_at',now())->where('status',ACTIVE_STATUS)->sum('due_amount');
}

function todayPaidAmount()
{
    return \App\Models\Invoices::whereDate('created_at',now())->where('status',ACTIVE_STATUS)->sum('paid_amount');
}

function todayTotalInvoice()
{
    return \App\Models\Invoices::whereDate('created_at',now())->where('status',ACTIVE_STATUS)->count();
}

function totalMedicinePrice()
{
    return \App\Models\Product::where('stock', '>', 0)
        ->selectRaw('SUM(unit_price * stock) as total_price')
        ->value('total_price');
}
