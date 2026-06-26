@php $proofPath = $order->payment_proof ?? null; @endphp
@if ($proofPath && file_exists(public_path($proofPath)))
    @php $ext = strtolower(pathinfo($proofPath, PATHINFO_EXTENSION)); @endphp
    @if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true))
        <a href="{{ asset($proofPath) }}" target="_blank" rel="noopener" title="{{ TranslationHelper::translate('view_payment_proof') }}">
            <img src="{{ asset($proofPath) }}" alt="" style="max-height: 48px; max-width: 72px; object-fit: cover; border-radius: 4px;">
        </a>
    @else
        <a href="{{ asset($proofPath) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0">
            <i class="fa fa-file-pdf"></i> PDF
        </a>
    @endif
@else
    <span class="text-muted">—</span>
@endif
