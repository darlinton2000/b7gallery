<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Salva a imagem no disco
     *
     * @param $image
     * @return string
     */
    public function storeImageInDisk($image)
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
    public function storeImageInDatabase($title, $url)
    {
        return Image::create([
            'title' => $title,
            'url' => $url
        ]);
    }

    /**
     * Deleta a imagem do banco de dados
     *
     * @param $dataBaseImage
     * @return void
     */
    public function deleteDataBaseImage($dataBaseImage)
    {
        if ($dataBaseImage) {
            $dataBaseImage->delete();
        }
    }

    /**
     * Deleta a imagem do disco
     *
     * @param $imageUrl
     * @return void
     */
    public function deleteImageFromDisk($imageUrl)
    {
        $imagePath = str_replace(asset('storage/'), '', $imageUrl);

        Storage::disk('public')->delete($imagePath);
    }
}
