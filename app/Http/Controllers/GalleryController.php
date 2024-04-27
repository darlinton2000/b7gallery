<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Validation\Rule;

class GalleryController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

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
        $this->validateRequest($request);

        $title = $request->input('title');
        $image = $request->file('image');

        try {
            $dataBaseImage = $this->imageService->storeNewImage($image, $title);

        } catch (Exception $error) {
            $this->imageService->roolback();

            return redirect()->back()->withErrors([
                'error' => 'Erro ao salvar a imagem. Tente novamente.'
            ]);
        }

        return redirect()->route('index');
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

    /**
     * Faz a validação da requisição
     *
     * @param Request $request
     * @return void
     */
    private function validateRequest(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|min:6',
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:4096',
                Rule::dimensions()->maxWidth(3000)->maxHeight(3000)
            ]
        ]);
    }
}
