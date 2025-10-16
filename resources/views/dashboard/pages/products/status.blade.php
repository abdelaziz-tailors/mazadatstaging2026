@if($item->status=="pending")
    <span class="badge rounded-pill  bg-black  inv-badge"> {{TranslationHelper::translate($item->status)}}</span>

@elseif($item->status=="working")
    <span class="badge rounded-pill bg-success inv-badge"> {{TranslationHelper::translate($item->status)}}</span>
@else
    <span class="badge rounded-pill bg-danger inv-badge"> {{TranslationHelper::translate($item->status)}}</span>

@endif

