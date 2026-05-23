<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}" />
    {{--    <link rel="stylesheet" href="{{asset('assets/style.css')}}" />--}}
    <title></title>
    <style>
        @page {
            margin: 10px;
        }
        body {
            margin: 10px;
            padding: 0;
        }

        @media print {
            body {
                font-size: 20px; /* Increase as needed */
            }

            .title {
                font-size: 20px;
                font-weight: bold;
            }

            h3 {
                font-size: 22px;
            }

            h4 {
                font-size: 18px;
            }

            .table-responsive .table-style thead tr th {
                font-size: 18px;
                height: 30px;
                padding: 6px 10px;
                text-align: center;
            }

            .table-responsive .table-style tbody tr td,
            .table-responsive .table-style tbody tr th,
            .table-responsive .table-style tfoot tr td,
            .table-responsive .table-style tfoot tr th {
                padding: 5px 10px;
                font-size: 16px;
                text-align: center;
            }

        }

        .table-wrap {
            max-width: 100%;
            margin: 0 auto;
            padding: 0;
            box-shadow: none;
        }
        .table-style {
            width: 100%;
            border-collapse: collapse;
        }
        .table-style th,
        .table-style td {
            font-size: 15px;
            padding: 5px 8px;
            text-align: center;
            border: 1px solid #ccc;
            word-wrap: break-word;
            white-space: normal;
        }
    </style>
</head>
<body style="width: 100%; padding: 0; margin: 0;">

<div class="table-wrap">
    <div style="font-size: 14px; text-align: center; font-family: Arial, sans-serif; margin-bottom: 2px;">
        <h2 style="letter-spacing: 2px; font-size: 16px; margin: 0;">{{ $title }}</h2>
    </div>

    <table width="100%" style="font-size: 14px; margin-top: 5px; margin-bottom: 10px;">
        <tr>
            <td style="text-align: left; font-weight: bold;">
                Name:
                <span style="font-weight: normal;">
                {{ $invoiceDetails->user->name ?? 'N/A' }}
            </span>
            </td>
            <td style="text-align: center; font-weight: bold;">
                InvoiceId:
                <span style="font-weight: normal;">
                {{ $invoiceDetails->invoice_id ?? 'N/A' }}
            </span>
            </td>
            <td style="text-align: right; font-weight: bold;">
                Date:
                <span style="font-weight: normal;">
                {{ formatDate($invoiceDetails->created_at) }}
            </span>
            </td>
        </tr>
    </table>


    <div class="table-responsive" style="margin-top: 10px;">
        <table class="table-style" style="font-size: 10px;border-collapse: collapse; width: 100%;">
            <thead style="display: table-header-group;">
            <tr class="order-item-row">
                <th style="width: 8%;">S.</th>
                <th style="width: 36%;">Product</th>
                <th style="width: 14%;">MRP</th>
                <th style="width: 12%;">BOX</th>
                <th style="width: 12%;">PIC</th>
                <th style="width: 16%;">Total</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orderItems as $key => $order): ?>
                <?php
                empty($order['qty']) ? $order['qty'] = (empty($order['box_qty']) ? 0 : $order['box_qty']) : $order['qty'];
                empty($order['pieces']) ? $order['pieces'] = (empty($order['quantity']) ? 0 : $order['quantity']) : $order['pieces'];
                $getProductDetails = \App\Models\Product::with('company')->find($order['product_id']);
                $box_per_pic = $getProductDetails->box_per_pic ?? 1;
                $totalBoxQty = !empty($order['qty']) ? (int)$order['qty'] * (int)$box_per_pic : 0;
                $finalQty = (int)($order['pieces']) + $totalBoxQty;
                ?>
            <tr>
                <td><?= $key + 1 ?></td>
                <td>{{ $order['product']->name ?? $getProductDetails->name.' '.\Illuminate\Support\Str::limit($getProductDetails->strength,3,'').' '.\Illuminate\Support\Str::limit($getProductDetails->type,3,'') .' '. \Illuminate\Support\Str::limit($getProductDetails->company->name,3,'')}}</td>
                <td>{{ number_format($order['price'], 2) }}</td>
                <td>{{ getQuotient($finalQty, (int) $box_per_pic) }}</td>
                <td>{{ getRemainder($finalQty, (int) $box_per_pic) }}</td>
{{--                <td>{{ !empty($order['discount']) ? number_format($order['discount'], 2) : '0' }}</td>--}}
                <td>{{ !empty($order['total']) ? number_format($order['total'], 2) : $order['final_total'] }}</td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <th colspan="5" style="text-align: right;">Total (TK)</th>
                <th>{{ number_format($total, 2) }}</th>
                <!--<th>{{ number_format($grandTotal, 2) }}</th>-->
            </tr>

            <tr>
                <th colspan="5" style="text-align: right;">Other Charge (TK)</th>
                <th>{{ number_format($otherCharge, 2) }}</th>
            </tr>
            <tr>
                <th colspan="5" style="text-align: right;">Grand Total (TK)</th>
                <th>{{ number_format($grandTotal, 2) }}</th>
            </tr>
            <tr>
                <th colspan="5" style="text-align: right;">Paid Amount (TK)</th>
                <th>{{ number_format($paidAmt, 2) }}</th>
            </tr>
            <tr>
                <th colspan="5" style="text-align: right;">Due Amount (TK)</th>
                <th>{{ number_format($dueAmt, 2) }}</th>
            </tr>

            </tbody>
        </table>
    </div>
</div>


</body>
</html>
