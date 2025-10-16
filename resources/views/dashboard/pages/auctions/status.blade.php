@if($item->status=="end")
    <span class="badge rounded-pill bg-danger inv-badge"> {{TranslationHelper::translate($item->status)}}</span>

@elseif($item->status=="start")
    <span class="badge rounded-pill bg-success inv-badge"> {{TranslationHelper::translate($item->status)}}</span>
@else
    <span class="badge rounded-pill bg-black inv-badge"> {{TranslationHelper::translate('Not start')}}</span>

@endif

