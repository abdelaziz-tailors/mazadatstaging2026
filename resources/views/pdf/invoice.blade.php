<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $invoiceNumber }}</title>
    <style>
        @font-face {
            font-family: 'Cairo';
            font-weight: normal;
            src: url('{{ $regularFontPath }}');
        }
        @font-face {
            font-family: 'Cairo';
            font-weight: bold;
            src: url('{{ $boldFontPath }}');
        }
        {{-- The Arabic-subset Cairo above has no digit/Latin glyphs (dompdf does
             not fall back mid-run), so numbers/order codes use this subset. --}}
        @font-face {
            font-family: 'CairoLatin';
            font-weight: normal;
            src: url('{{ $regularLatinFontPath }}');
        }
        @font-face {
            font-family: 'CairoLatin';
            font-weight: bold;
            src: url('{{ $boldLatinFontPath }}');
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            text-align: right;
            color: #232838;
            font-size: 13px;
            margin: 0;
            padding: 30px;
        }
        .header {
            width: 100%;
            margin-bottom: 18px;
        }
        .header .brand {
            float: right;
            font-size: 20px;
            font-weight: bold;
            color: #7a5a1e;
        }
        .header .meta {
            float: left;
            text-align: left;
        }
        .header .clear {
            clear: both;
        }
        .invoice-number {
            font-size: 16px;
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 4px;
        }
        .status-paid {
            background-color: #e3f6ec;
            color: #1e8a53;
        }
        .status-unpaid {
            background-color: #fdeaea;
            color: #c0392b;
        }
        .section-title {
            font-size: 13px;
            color: #6b7385;
            margin: 4px 0;
        }
        .auction-title {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 14px;
        }
        table.breakdown {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.breakdown td {
            padding: 9px 4px;
            border-bottom: 1px solid #e6e6e6;
            font-size: 13px;
        }
        table.breakdown td.label {
            color: #6b7385;
        }
        table.breakdown td.value {
            text-align: left;
            font-weight: bold;
        }
        .total-row td {
            border-bottom: none;
            border-top: 2px solid #232838;
            font-size: 16px;
            font-weight: bold;
            padding-top: 14px;
        }
        .total-row td.value {
            color: #1e8a53;
        }
        .footer {
            margin-top: 30px;
            font-size: 11px;
            color: #9aa2b1;
            text-align: center;
        }
        .num {
            font-family: 'CairoLatin', sans-serif;
            unicode-bidi: bidi-override;
            direction: ltr;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">مزادات عالم أنعام</div>
        <div class="meta">
            <div class="invoice-number"><span class="num">{{ $invoiceNumber }}</span></div>
            <span class="status-badge {{ $paymentStatus === 'paid' ? 'status-paid' : 'status-unpaid' }}">
                {{ $paymentStatus === 'paid' ? 'مدفوعة' : 'مستحقة' }}
            </span>
        </div>
        <div class="clear"></div>
    </div>

    <div class="section-title">{{ $auctionTitle }}</div>
    <div class="auction-title">{{ $itemTitle }}</div>

    <table class="breakdown">
        <tr>
            <td class="label">قيمة المزايدة</td>
            <td class="value"><span class="num">{{ number_format($bidValue, 2) }}</span> ر.س</td>
        </tr>
        <tr>
            <td class="label">عمولة المشتري</td>
            <td class="value"><span class="num">{{ number_format($buyerCommission, 2) }}</span> ر.س</td>
        </tr>
        <tr>
            <td class="label">عمولة البائع</td>
            <td class="value"><span class="num">{{ number_format($sellerCommission, 2) }}</span> ر.س</td>
        </tr>
        <tr>
            <td class="label">حق الرعاية</td>
            <td class="value"><span class="num">{{ number_format($sponsorshipFee, 2) }}</span> ر.س</td>
        </tr>
        <tr>
            <td class="label">ضريبة القيمة المضافة</td>
            <td class="value"><span class="num">{{ number_format($vat, 2) }}</span> ر.س</td>
        </tr>
        <tr class="total-row">
            <td class="label">الإجمالي</td>
            <td class="value"><span class="num">{{ number_format($total, 2) }}</span> ر.س</td>
        </tr>
    </table>

    <div class="footer">
        رقم الطلب: <span class="num">{{ $orderNumber }}</span> &nbsp;|&nbsp; تاريخ الإصدار: <span class="num">{{ $issuedAt }}</span>
    </div>
</body>
</html>
