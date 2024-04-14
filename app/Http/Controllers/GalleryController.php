<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Validation\Rule;

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
        $this->validateRequest($request);

        $title = $request->input('title');
        $image = $request->file('image');

        try {
            $url = $this->storeImageInDisk($image);
            $dataBaseImage = $this->storeImageInDatabase($title, $url);
        } catch (Exception $error) {
            $this->deleteDataBaseImage($dataBaseImage);
            $this->deleteImageFromDisk($url);

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
                'max:2048',
                Rule::dimensions()->maxWidth(2000)->maxHeight(2000)
            ]
        ]);
    }

    /**
     * Salva a imagem no disco
     *
     * @param $image
     * @return string
     */
    private function storeImageInDisk($image)
    {
        $imageName = $image->storePubliclyAs('uploads', $image->hashName(), 'public');

        return asset('storage/' . $imageName);
    }

    /**
     * Salva a imagem no banco de dados
     *
     * @param $title
     * @param $url
     * @return Image
     */
    private function storeImageInDatabase($title, $url)
    {
        return Image::create([
            'title' => $title,
            'url' => $url
        ]);
    }

    /**
     * Deleta a imagem do disco
     *
     * @param $imageUrl
     * @return void
     */
    private function deleteImageFromDisk($imageUrl)
    {
        $imagePath = str_replace(asset('storage/'), '', $imageUrl);

        Storage::disk('public')->delete($imagePath);
    }

    /**
     * Deleta a imagem do banco de dados
     *
     * @param $dataBaseImage
     * @return void
     */
    private function deleteDataBaseImage($dataBaseImage)
    {
        if ($dataBaseImage) {
            $dataBaseImage->delete();
        }
    }
}
