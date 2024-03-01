<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\RedirectResponse;
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
        $images = Image::all();

        return view('index', compact('images'));
    }

    /**
     * Faz o upload da imagem
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function upload(Request $request): RedirectResponse
    {
        if ($request->hasFile('image')) {
            $title = $request->input('title');
            $image = $request->file('image');
            $name = $image->hashName();

            $return = $image->storePubliclyAs('uploads', $name, 'public');
            $url = asset('storage/' . $return);

            Image::create([
                'title' => $title,
                'url' => $url
            ]);

            return redirect()->route('index');
        }
    }

    public function delete()
    {

    }
}
