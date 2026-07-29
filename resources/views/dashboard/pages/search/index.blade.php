@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('search_results') }} @endsection

@section('content')
@include('dashboard.partials.page-header', [
    'title' => $q !== '' ? TranslationHelper::translate('search_results_for') . ' "' . $q . '"' : TranslationHelper::translate('search_results'),
    'icon' => 'fa-solid fa-magnifying-glass',
])

<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ TranslationHelper::translate('Auctions') }} ({{ $auctions->count() }})</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ TranslationHelper::translate('Image') }}</th>
                        <th>{{ TranslationHelper::translate('Auctions Title') }}</th>
                        <th>{{ TranslationHelper::translate('created at') }}</th>
                        <th>{{ TranslationHelper::translate('Status') }}</th>
                        <th>{{ TranslationHelper::translate('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($auctions as $auction)
                        <tr>
                            <td>{{ $auction->id }}</td>
                            <td>
                                @include('dashboard.partials.avatar', ['path' => $auction->image, 'name' => $auction->title_ar, 'size' => 40])
                            </td>
                            <td>{{ $auction->title_ar ?? $auction->title ?? '-' }}</td>
                            <td>{{ optional($auction->created_at)->format('Y-m-d') ?? '-' }}</td>
                            <td>@include('dashboard.pages.videos.status', ['item' => $auction])</td>
                            <td>
                                <a class="md-icon-btn" href="{{ route('admin.auctions.show', $auction->id) }}" title="{{ TranslationHelper::translate('view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">{{ TranslationHelper::translate('no_results_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
