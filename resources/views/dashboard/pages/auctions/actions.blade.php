{{-- Icon-style row actions, scoped to the Auctions table only (per explicit
request — the shared "videos.actions" partial used by /admin/videos keeps
its existing text-button style untouched). Font Awesome only — feathericon's
CSS isn't linked in the dashboard layout, so "fe fe-*" icons (still used by
the older videos.actions partial) silently render nothing.

The kebab dropdown (Add Product / view Product) was removed per explicit
request: "view Product" was redundant with the standalone view icon above.
"Add Product" moved out of this column entirely into its own "القطع" column
(see AuctionController::get_data()'s "pieces_action" column) so this actions
column only holds view/edit and can stay the last column in the table. --}}
<div class="d-flex align-items-center gap-2">
    <a class="md-icon-btn" href="{{ route('admin.auctions.show', $item->id) }}" title="{{ TranslationHelper::translate('view') }}">
        <i class="fas fa-eye"></i>
    </a>

    <a class="md-icon-btn" href="{{ route('admin.videos.edit', $item->id) }}" title="{{ TranslationHelper::translate('edit') }}">
        <i class="fas fa-pen"></i>
    </a>
</div>
