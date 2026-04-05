@if ($item->payment_proof && file_exists(public_path($item->payment_proof)))
    @php
        $ext = strtolower(pathinfo($item->payment_proof, PATHINFO_EXTENSION));
    @endphp
    @if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true))
        <a href="{{ asset($item->payment_proof) }}" target="_blank" rel="noopener" title="{{ TranslationHelper::translate('view_payment_proof') }}">
            <img src="{{ asset($item->payment_proof) }}" alt="" style="max-height: 48px; max-width: 72px; object-fit: cover; border-radius: 4px;">
        </a>
    @else
        <a href="{{ asset($item->payment_proof) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0">
            <i class="fa fa-file-pdf"></i> PDF
        </a>
    @endif
@else
    <span class="text-muted">—</span>
@endif
