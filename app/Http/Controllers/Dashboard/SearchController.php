<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LiveVideo;
use App\Support\PartnerDashboardScope;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global admin-header search: auctions only, matched by their exact
     * number (LiveVideo.id) — not title, and not users/orders, and not a
     * partial/fuzzy match (a "%1%"-style LIKE would also match 351/341/331
     * etc, which isn't what "look up this specific auction number" means).
     * A non-numeric query never matches anything. Results are scoped to
     * the current partner admin's own auctions the same way the Auctions
     * list page already is (PartnerDashboardScope).
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $auctions = collect();

        if ($q !== '' && ctype_digit($q)) {
            $auctionsQuery = LiveVideo::query()->where('id', (int) $q);
            PartnerDashboardScope::scopeLiveVideos($auctionsQuery);
            $auctions = $auctionsQuery->limit(10)->get();
        }

        return view('dashboard.pages.search.index', compact('q', 'auctions'));
    }
}
