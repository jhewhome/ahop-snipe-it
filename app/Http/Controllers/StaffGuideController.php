<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class StaffGuideController extends Controller
{
    public function index(): View
    {
        return view('staff-guide.index');
    }
}
