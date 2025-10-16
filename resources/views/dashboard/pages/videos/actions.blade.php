{{--@if (Auth::guard('admin')->user()->can('add video'))--}}

    <a class="btn btn-sm bg-primary-light"  href="{{ route('admin.products.create', $item->id) }}">
        <i class="fe fe-plus"></i> {{ TranslationHelper::translate('Add Product') }}
    </a>
{{--@endif--}}

{{--@if (Auth::guard('admin')->user()->can('edit video'))--}}

        <a class="btn btn-sm bg-primary-light"  href="{{ route('admin.products.index', $item->id) }}">
            <i class="fe fe-plus"></i> {{ TranslationHelper::translate('view Product') }}
        </a>
{{--@endif--}}

{{--@if (Auth::guard('admin')->user()->can('edit video'))--}}

<a class="btn btn-sm bg-success-light"  href="{{ route('admin.videos.edit', $item->id) }}">
    <i class="fe fe-edit"></i> {{ TranslationHelper::translate('edit') }}
</a>

{{--@endif--}}
{{--@if (Auth::guard('admin')->user()->can('edit video'))--}}

{{--<a class="btn btn-sm bg-danger-light"  href="{{ route('admin.videos.show', $item->id) }}">--}}
{{--    <i class="fe fe-lock"></i> {{ TranslationHelper::translate('disable') }}--}}
{{--</a>--}}
{{--@endif--}}

