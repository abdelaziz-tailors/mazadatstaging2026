@php
    $live = $order->liveVideo;

    // User ID of the auction owner / partner.
    // OrderItem.seller_id uses users.id, so this is the correct comparison.
    $organizerUserId = (int) ($live?->user_id ?? 0);

    $commissionPayer = $live?->commission_payer ?? 'buyer';
    $commissionPct = (float) ($live?->commission_amount ?? 0);

    $partnerTotal = 0.0;
    $externalSellersTotal = 0.0;

    foreach ($sellerInvoices as $invoice) {
        $isOwnPiece = (int) $invoice['seller_id'] === $organizerUserId;

        if ($isOwnPiece) {
            // If the auction owner owns the piece,
            // the full sale price belongs to him.
            $partnerTotal += (float) $invoice['gross'];
        } else {
            // External seller keeps his net.
            $externalSellersTotal += (float) $invoice['net'];

            // Commission + service fee + piece services belong to partner.
            $partnerTotal +=
                (float) $invoice['commission']
                + (float) $invoice['service_fee']
                + (float) $invoice['piece_services'];
        }
    }

    $partnerTotal = round($partnerTotal, 2);
    $externalSellersTotal = round($externalSellersTotal, 2);
@endphp


<div class="mb-0">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">
            {{ app()->getLocale() === 'ar' ? 'ملخص مستحقات الطلب' : 'Order Entitlements Summary' }}
        </h6>
    </div>


    @foreach ($sellerInvoices as $invoice)

        @php
            $isOwnPiece = (int) $invoice['seller_id'] === $organizerUserId;

            $partnerEarnings = $isOwnPiece
                ? (float) $invoice['gross']
                : (
                    (float) $invoice['commission']
                    + (float) $invoice['service_fee']
                    + (float) $invoice['piece_services']
                );
        @endphp


        <div class="border rounded p-3 bg-light mb-3">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <strong>
                    @if ($isOwnPiece)
                        {{ app()->getLocale() === 'ar' ? 'صاحب المزاد' : 'Auction Owner' }}:
                    @else
                        {{ app()->getLocale() === 'ar' ? 'البائع الخارجي' : 'External Seller' }}:
                    @endif

                    {{ $invoice['seller_name'] }}
                </strong>


                @if ($isOwnPiece)

                    <span class="badge bg-success">
                        {{ app()->getLocale() === 'ar' ? 'قطعة المشترك' : 'Owner Piece' }}
                    </span>

                @else

                    <span class="badge bg-secondary">
                        {{ app()->getLocale() === 'ar' ? 'قطعة بائع خارجي' : 'External Seller Piece' }}
                    </span>

                @endif

            </div>


            <div class="table-responsive">

                <table class="table table-sm table-bordered mb-0">

                    <thead>

                        @if ($isOwnPiece)

                            <tr>
                                <th>
                                    {{ app()->getLocale() === 'ar' ? 'اسم القطعة' : 'Item' }}
                                </th>

                                <th>
                                    {{ app()->getLocale() === 'ar' ? 'سعر الانتهاء' : 'Final Price' }}
                                </th>

                                <th>
                                    {{ app()->getLocale() === 'ar' ? 'مستحق المشترك' : 'Partner Entitlement' }}
                                </th>
                            </tr>

                        @else

                            <tr>
                                <th>
                                    {{ app()->getLocale() === 'ar' ? 'اسم القطعة' : 'Item' }}
                                </th>

                                <th>
                                    {{ app()->getLocale() === 'ar' ? 'سعر الانتهاء' : 'Final Price' }}
                                </th>

                                @if ($commissionPayer === 'seller')
                                    <th>
                                        {{ app()->getLocale() === 'ar' ? 'العمولة' : 'Commission' }}
                                    </th>
                                @endif

                                <th>
                                    {{ app()->getLocale() === 'ar' ? 'رسوم الخدمة' : 'Service Fee' }}
                                </th>

                                <th>
                                    {{ app()->getLocale() === 'ar' ? 'خدمات القطعة' : 'Piece Services' }}
                                </th>

                                <th>
                                    {{ app()->getLocale() === 'ar' ? 'صافي البائع' : 'Seller Net' }}
                                </th>

                                <th>
                                    {{ app()->getLocale() === 'ar' ? 'مستحق المشترك' : 'Partner Entitlement' }}
                                </th>
                            </tr>

                        @endif

                    </thead>


                    <tbody>

                        @foreach ($invoice['lines'] as $line)

                            @php
                                $linePartnerEarnings = $isOwnPiece
                                    ? (float) $line['price']
                                    : (
                                        (float) $line['commission']
                                        + (float) $line['service_fee']
                                        + (float) $line['piece_services']
                                    );
                            @endphp


                            @if ($isOwnPiece)

                                <tr>
                                    <td>{{ $line['title'] }}</td>

                                    <td>
                                        {{ number_format($line['price'], 2) }}
                                    </td>

                                    <td>
                                        <strong class="text-success">
                                            {{ number_format($linePartnerEarnings, 2) }}
                                        </strong>
                                    </td>
                                </tr>

                            @else

                                <tr>
                                    <td>{{ $line['title'] }}</td>

                                    <td>
                                        {{ number_format($line['price'], 2) }}
                                    </td>

                                    @if ($commissionPayer === 'seller')
                                        <td>
                                            {{ number_format($line['commission'], 2) }}
                                        </td>
                                    @endif

                                    <td>
                                        {{ number_format($line['service_fee'], 2) }}
                                    </td>

                                    <td>
                                        {{ number_format($line['piece_services'], 2) }}
                                    </td>

                                    <td>
                                        <strong>
                                            {{ number_format($line['net'], 2) }}
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="text-success">
                                            {{ number_format($linePartnerEarnings, 2) }}
                                        </strong>
                                    </td>
                                </tr>

                            @endif

                        @endforeach

                    </tbody>


                    <tfoot>

                        @if ($isOwnPiece)

                            <tr class="table-secondary">

                                <th>
                                    {{ app()->getLocale() === 'ar' ? 'الإجمالي' : 'Total' }}
                                </th>

                                <th>
                                    {{ number_format($invoice['gross'], 2) }}
                                </th>

                                <th>
                                    <strong class="text-success">
                                        {{ number_format($partnerEarnings, 2) }}
                                    </strong>
                                </th>

                            </tr>

                        @else

                            <tr class="table-secondary">

                                <th>
                                    {{ app()->getLocale() === 'ar' ? 'الإجمالي' : 'Total' }}
                                </th>

                                <th>
                                    {{ number_format($invoice['gross'], 2) }}
                                </th>

                                @if ($commissionPayer === 'seller')
                                    <th>
                                        {{ number_format($invoice['commission'], 2) }}
                                    </th>
                                @endif

                                <th>
                                    {{ number_format($invoice['service_fee'], 2) }}
                                </th>

                                <th>
                                    {{ number_format($invoice['piece_services'], 2) }}
                                </th>

                                <th>
                                    <strong>
                                        {{ number_format($invoice['net'], 2) }}
                                    </strong>
                                </th>

                                <th>
                                    <strong class="text-success">
                                        {{ number_format($partnerEarnings, 2) }}
                                    </strong>
                                </th>

                            </tr>

                        @endif

                    </tfoot>

                </table>

            </div>

        </div>

    @endforeach


    {{-- Final order financial summary from the partner's point of view --}}
    <div class="border rounded p-3 mt-4">

        <h6 class="mb-3">
            {{ app()->getLocale() === 'ar' ? 'ملخص الطلب للمشترك' : 'Partner Order Summary' }}
        </h6>


        <div class="row">

            <div class="col-md-4 mb-2">

                <small class="text-muted d-block">
                    {{ app()->getLocale() === 'ar' ? 'قيمة القطع في الطلب' : 'Order Items Value' }}
                </small>

                <strong>
                    {{ number_format($order->subtotal, 2) }}
                </strong>

            </div>


            <div class="col-md-4 mb-2">

                <small class="text-muted d-block">
                    {{ app()->getLocale() === 'ar' ? 'مستحق البائعين الخارجيين' : 'External Sellers Entitlement' }}
                </small>

                <strong>
                    {{ number_format($externalSellersTotal, 2) }}
                </strong>

            </div>


            <div class="col-md-4 mb-2">

                <small class="text-muted d-block">
                    {{ app()->getLocale() === 'ar' ? 'مستحق المشترك' : 'Partner Entitlement' }}
                </small>

                <strong class="text-success">
                    {{ number_format($partnerTotal, 2) }}
                </strong>

            </div>

        </div>

    </div>

</div>
