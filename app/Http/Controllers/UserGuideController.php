<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserGuideController extends Controller
{
    /**
     * Display the comprehensive user guide page.
     */
    public function index()
    {
        return view('pages.user_guide.index');
    }
}
