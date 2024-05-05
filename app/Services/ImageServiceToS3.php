<?php

namespace App\Services;

use App\Interfaces\ImageServiceInterface;
use App\Models\Image;
use Exception;
use Error;
use Illuminate\Support\Facades\Storage;

class ImageServiceToS3 implements ImageServiceInterface
{
    private $rollbackStack = [];

    /**
     * Salva a imagem no s3 e no banco de dados
     *
     * @param $image
     * @param $title
     * @return Image
     */
    public function storeNewImage($image, $title): Image
    {
        try {
            $url = $this->storeImageInS3($image);
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
     * Deleta a imagem do s3
     *
     * @param $imageUrl
     * @return void
     */
    public function deleteImageFile($imageUrl): bool
    {
        if ($imageUrl) {
            $imagePath = str_replace('https://b7gallery.s3.sa-east-1.amazonaws.com/', '', $imageUrl);
            Storage::disk('s3')->delete($imagePath);

            return true;
        }

        return false;
    }

    /**
     * Verifica a fila do métodos que foram executados e executa os seus respectivos rollback
     *
     * @return void
     */
    public function rollback()
    {
        while(!empty($this->rollbackStack)) {
            $rollbackAction = array_pop($this->rollbackStack);

            $method = $rollbackAction['method'];
            $params = $rollbackAction['params'];

            if (method_exists($this, $method)) {
                call_user_func_array([$this, $method], $params);
            }
        }
    }

    /**
     * Salva a imagem na AWS S3
     *
     * @param $image
     * @return string
     */
    private function storeImageInS3($image): string
    {
        $imageName = $image->storePublicly('public', 's3');
        $url = 'https://b7gallery.s3.sa-east-1.amazonaws.com/' . $imageName;
        $this->addToRollbackQueue('deleteImageFromDisk', [$imageName]);

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
        $this->rollbackStack[] = [
            'method' => $method,
            'params' => $params
        ];
    }
}
