<div class="md-empty-state" role="status">
    <span class="md-empty-state-icon" aria-hidden="true"><i class="{{ $icon ?? 'fa-solid fa-inbox' }}"></i></span>
    <p class="mb-0">{{ $message ?? TranslationHelper::translate('nothing_found') }}</p>
</div>
