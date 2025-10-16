<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    // Get Area Cities
    public function index () {
         return view('front.home');
    }

}
