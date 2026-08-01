<?php

use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\File;

if (! function_exists('checkImageExists')) {
    function checkImageExists($url)
    {
    	if ( Storage::disk('public')->exists($url) )
    		return Storage::disk('public')->url($url);

    	return @asset('').'assets/img/logo.png';
	}   
}

if (! function_exists('checkFullImageExists')) {
    function checkFullImageExists($url)
    {
        $url = str_replace(url('/'),'',$url);
        if ( File::exists(public_path($url)) ){
            // $url
    		return Storage::disk('public')->url(str_replace('/storage','',$url));
        }  
    	return @asset('').'assets/img/logo.png';
	}
}

if (! function_exists('getCategories')) {
    function getCategories()
    {
      return Category::select('id', 'name->'.app()->getLocale().' as name', 'is_active')->where('is_active',1)->whereNull('category_id')->get();
	}
}

