<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class GalleryController extends Controller
{
    /**
     * Retorna a view index
     *
     * @return View
     */
    public function index(): View
    {
        return view('index');
    }

    public function upload(Request $request)
    {
        dd($request);
    }

    public function delete()
    {

    }
}
