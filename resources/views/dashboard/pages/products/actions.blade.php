{{--@if (Auth::guard('admin')->user()->can('edit video'))--}}

<a class="btn btn-sm bg-primary-light"  href="{{ route('admin.products.edit', $item->id) }}">
    <i class="fe fe-edit"></i> {{ TranslationHelper::translate('edit') }}
</a>

{{--@endif--}}
{{--@if (Auth::guard('admin')->user()->can('delete video'))--}}
<a class="btn btn-sm bg-warning-light" href="{{ route('admin.products.comments', $item->id) }}">
    <i class="fas fa-comments"></i> {{ TranslationHelper::translate('View Comments') }}
</a>
<a class="btn btn-sm bg-danger-light" href="#deleteCountryModal-{{ $item->id }}" data-bs-toggle="modal">
    <i class="fas fa-trash"></i> {{ TranslationHelper::translate('delete') }}
</a>


{{--@endif--}}

{{--@if (Auth::guard('admin')->user()->can('delete video'))--}}
    <!-- Modal -->
    <div class="modal fade" id="deleteCountryModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">{{ TranslationHelper::translate('Delete Product') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form role="form" action="{{ url('/admin/products/'.$item->id) }}" class="" method="POST">
                        <input name="_method" type="hidden" value="DELETE">
                        {{ csrf_field() }}
                        <p>{{ TranslationHelper::translate('are_you_sure') }}</p>
                        <button type="submit" class="btn btn-danger" name='delete_modal'><i class="fa fa-trash" aria-hidden="true"></i> {{ TranslationHelper::translate('delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
{{--@endif--}}
