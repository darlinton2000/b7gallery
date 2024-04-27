<?php

namespace App\Services;

use App\Interfaces\ImageServiceInterface;
use App\Models\Image;
use Exception;
use Error;
use Illuminate\Support\Facades\Storage;

class ImageService implements ImageServiceInterface
{
    private $rollbackQueue = null;

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

    /**
     * Deleta a imagem do banco de dados e do storage
     *
     * @return void
     */
    public function roolback()
    {
        dd($this->rollbackQueue);
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
        $url = asset('storage/' . $imageName);
        $this->addToRollbackQueue('deleteImageFromDisk', [$url]);

        return $url;
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
        $image = Image::create([
            'title' => $title,
            'url' => $url
        ]);

        $this->addToRollbackQueue('deleteDataBaseImage', [$image]);

        return $image;
    }

    private function addToRollbackQueue($method, $params = [])
    {
        $this->rollbackQueue[] = [
            'method' => $method,
            'params' => $params
        ];
    }
}
