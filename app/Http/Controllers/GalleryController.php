<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Deleta a imagem
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        $image = Image::findOrFail($id);
        $url = parse_url($image->url);
        $path = ltrim($url['path'], '/storage\/');

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            $image->delete();
        }

        return redirect()->route('index');
    }
}
