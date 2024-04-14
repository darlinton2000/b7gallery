<?php

namespace App\Services;

use App\Interfaces\ImageServiceInterface;
use App\Models\Image;
use Exception;
use Error;
use Illuminate\Support\Facades\Storage;

class ImageService implements ImageServiceInterface
{
    /**
     * Salva a imagem no disco e no banco de dados
     *
     * @param $image
     * @param $title
     * @return Image
     */
    public function storeNewImage($image, $title): Image
    {
        try {
            $url = $this->storeImageInDisk($image);
            return $this->storeImageInDatabase($title, $url);
        } catch (Exception $e) {
            throw new Error('Erro ao salvar a imagem, tente novamente.');
        }
    }

    /**
     * Salva a imagem no disco
     *
     * @param $image
     * @return string
     */
    private function storeImageInDisk($image): string
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
    private function storeImageInDatabase($title, $url): Image
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
     * @return bool
     */
    public function deleteDataBaseImage($dataBaseImage): bool
    {
        if ($dataBaseImage) {
            $dataBaseImage->delete();

            return true;
        }

        return false;
    }

    /**
     * Deleta a imagem do disco
     *
     * @param $imageUrl
     * @return void
     */
    public function deleteImageFromDisk($imageUrl): bool
    {
        if ($imageUrl) {
            $imagePath = str_replace(asset('storage/'), '', $imageUrl);
            Storage::disk('public')->delete($imagePath);

            return true;
        }

        return false;
    }
}
