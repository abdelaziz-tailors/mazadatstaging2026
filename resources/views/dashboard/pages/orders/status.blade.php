@if($item->status_cart=="shipping")
    <span class="badge rounded-pill  bg-black  inv-badge"> {{TranslationHelper::translate($item->status_cart)}}</span>

@elseif($item->status_cart=="paid")
    <span class="badge rounded-pill bg-success inv-badge"> {{TranslationHelper::translate($item->status_cart)}}</span>
@else
    <span class="badge rounded-pill bg-danger inv-badge"> {{TranslationHelper::translate($item->status_cart )}}</span>

@endif

