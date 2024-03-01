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
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = $image->hashName();
            $return = $image->storePublicly('uploads', 'public', $name);
            dd($return);
        }
    }

    public function delete()
    {

    }
}
