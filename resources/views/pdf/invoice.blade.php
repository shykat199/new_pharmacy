<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>
    <title></title>
</head>
<body>

<div style="text-align: center; font-family: Arial, sans-serif; font-size: 12px;">
    <h1 style="letter-spacing: 2px;">{{ $title }}</h1>
</div>

<div style="margin-top: 20px; margin-bottom: 15px; width: 100%; text-align: center; font-family: Arial, sans-serif; font-size: 12px;">
    <!-- Invoice ID at the top and bold -->
    <div style="margin-bottom: 10px;">
        <h4 style="margin: 0; letter-spacing: 2px;"><strong>InvoiceId: {{ !empty($invoiceDetails) ? $invoiceDetails->invoice_id : 'N/A' }}</strong></h4>
    </div>

    <!-- User Information -->
    <div style="margin-bottom: 10px;">
        <h4 style="margin: 0; font-weight: bold; letter-spacing: 2px;">Name: {{ !empty($invoiceDetails->user) ? $invoiceDetails->user->name : 'N/A' }}</h4>
    </div>
    <div style="margin-bottom: 10px;">
        <h4 style="margin: 0; font-weight: bold; letter-spacing: 2px;">Email: {{ !empty($invoiceDetails->user) ? $invoiceDetails->user->email : 'N/A' }}</h4>
    </div>
    <div style="margin-bottom: 10px;">
        <h4 style="margin: 0; font-weight: bold; letter-spacing: 2px;">Phone: {{ !empty($invoiceDetails->user) ? $invoiceDetails->user->phone : 'N/A' }}</h4>
    </div>
    <div style="margin-bottom: 10px;">
        <h4 style="margin: 0; font-weight: bold; letter-spacing: 2px;">Address: {{ !empty($invoiceDetails->user) ? $invoiceDetails->user->address : 'N/A' }}</h4>
    </div>
    <div style="margin-bottom: 10px;">
        <h4 style="margin: 0; font-weight: bold; letter-spacing: 2px;">Date: {{ formatDate($invoiceDetails->created_at) }}</h4>
    </div>
</div>


<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
    <thead>
    <tr style="background-color: #343a40; color: #ffffff; text-align: left;">
        <th style="border: 1px solid #000; padding: 8px;">S.</th>
        <th style="border: 1px solid #000; padding: 8px;">Product</th>
        <th style="border: 1px solid #000; padding: 8px;">Mrp</th>
        <th style="border: 1px solid #000; padding: 8px;">Qty</th>
        <th style="border: 1px solid #000; padding: 8px;">Discount(%)</th>
        <th style="border: 1px solid #000; padding: 8px;">Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orderItems as $key => $order)
        @php
            $getProductDetails = \App\Models\Product::find($order['product_id']);
            $box_per_pic = $getProductDetails->box_per_pic ?? 1;
            $totalBoxQty = 0;
            $totalQty = $order['pieces'];
            if (!empty($order['qty'])) {
                $totalBoxQty = (int)$order['qty'] * (int)$box_per_pic;
            }
            $finalQty = (int)$totalQty + (int)$totalBoxQty ;
        @endphp
        <tr>
            <td style="border: 1px solid #000; padding: 8px;">{{ ++$key }}</td>
            <td style="border: 1px solid #000; padding: 8px;">{{ $order['product']->name ?? $getProductDetails->name }}</td>
            <td style="border: 1px solid #000; padding: 8px;">{{ number_format($order['price'],2) ?? '0' }}</td>
            <td style="border: 1px solid #000; padding: 8px;">{{ $finalQty ?? '0' }}</td>
            <td style="border: 1px solid #000; padding: 8px;">{{ !empty($order['discount']) ?  number_format($order['discount'],2) : '0' }}</td>
            <td style="border: 1px solid #000; padding: 8px;">{{ number_format($order['total'],2) ?? 'N/A' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<!-- Footer Content Outside Table -->
<div style="margin-top: 20px; width: 100%; text-align: right; font-family: Arial, sans-serif; font-size: 12px;">
    <!-- Grand Total -->

    <div style="display: flex; align-items: center; justify-content: flex-end; margin-bottom: 8px;">
        <label style="font-weight: bold; margin-right: 8px;">Total:</label>
        <input value="{{ number_format($total, 2) }}" type="number" id="grandTotal" disabled
               style="max-width: 200px; text-align: right; padding: 5px; border: 1px solid #ccc;"
               placeholder="0.00">
    </div>

    <!-- Other Charge -->
    <div style="display: flex; align-items: center; justify-content: flex-end; margin-bottom: 8px;">
        <label style="font-weight: bold; margin-right: 8px;">Other Charge:</label>
        <input value="{{ $otherCharge }}" type="number" step="0.01" id="otherCharge" disabled
               style="max-width: 200px; text-align: right; padding: 5px; border: 1px solid #ccc;"
               placeholder="Other charge">
    </div>

    <div style="display: flex; align-items: center; justify-content: flex-end; margin-bottom: 8px;">
        <label style="font-weight: bold; margin-right: 8px;">Grand Total:</label>
        <input value="{{ number_format($grandTotal, 2) }}" type="number" id="grandTotal" disabled
               style="max-width: 200px; text-align: right; padding: 5px; border: 1px solid #ccc;"
               placeholder="0.00">
    </div>

    <!-- Paid Amount -->
    <div style="display: flex; align-items: center; justify-content: flex-end; margin-bottom: 8px;">
        <label style="font-weight: bold; margin-right: 8px;">Paid Amount:</label>
        <input value="{{ $paidAmt }}" type="number" id="paidAmount" disabled
               step="0.01"
               style="max-width: 200px; text-align: right; padding: 5px; border: 1px solid #ccc;"
               placeholder="Paid amount">
    </div>

    <!-- Due Amount -->
    <div style="display: flex; align-items: center; justify-content: flex-end;">
        <label style="font-weight: bold; margin-right: 8px;">Due Amount:</label>
        <input value="{{ $dueAmt }}" type="number" disabled
               style="max-width: 200px; text-align: right; padding: 5px; border: 1px solid #ccc;"
               placeholder="0.00">
    </div>
</div>

</body>
</html>
