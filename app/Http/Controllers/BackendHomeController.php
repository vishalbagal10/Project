<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackendHomeController extends Controller
{
    public function dashboard() {
        return view('backend.views.dashboard');
    }
}
