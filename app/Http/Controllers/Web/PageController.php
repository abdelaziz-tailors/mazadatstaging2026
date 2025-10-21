<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display the About page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function about()
    {
        $page = Page::select('name->'.app()->getLocale().' as name', 'description->'.app()->getLocale().' as description','image')
            ->where('id', 1)->first();
        
        return view('front.pages.about', compact('page'));
    }

    /**
     * Display the Privacy Policy page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function privacy()
    {
        $page = Page::select('name->'.app()->getLocale().' as name', 'description->'.app()->getLocale().' as description','image')
            ->where('id', 3)->first();
        
        return view('front.pages.privacy', compact('page'));
    }

    /**
     * Display the Terms and Conditions page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function terms()
    {
        $page = Page::select('name->'.app()->getLocale().' as name', 'description->'.app()->getLocale().' as description','image')
            ->where('id', 2)->first();
        
        return view('front.pages.terms', compact('page'));
    }
}

