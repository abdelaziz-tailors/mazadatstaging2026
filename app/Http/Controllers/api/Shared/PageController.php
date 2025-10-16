<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;

use App\Models\Page;
use App\Http\Resources\PageResource;

use App\Traits\ResponseTrait;

class PageController extends Controller
{
    use ResponseTrait;

    public function about() {
        $page = Page::select('name->'.app()->getLocale().' as name', 'description->'.app()->getLocale().' as description','image')
            ->where('id', 1)->first();
        $data = new PageResource($page);
        return $this->success_response(NULL, $data);
    }
    public function privacy() {
        $page = Page::select('name->'.app()->getLocale().' as name', 'description->'.app()->getLocale().' as description','image')
            ->where('id', 3)->first();
        $data = new PageResource($page);
        return $this->success_response(NULL, $data);
    }
    public function terms() {
        $page = Page::select('name->'.app()->getLocale().' as name', 'description->'.app()->getLocale().' as description','image')
            ->where('id', 2)->first();
        $data = new PageResource($page);
        return $this->success_response(NULL, $data);
    }
    public function data() {
        $page = Page::select('name->'.app()->getLocale().' as name', 'description->'.app()->getLocale().' as description','image')
            ->where('id', 2)->first();
        $data = new PageResource($page);
        return $this->success_response(NULL, $data);
    }

}
