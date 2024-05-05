<?php

namespace App\Interfaces;

use App\Models\Image;

interface ImageServiceInterface
{
    public function storeNewImage($image, $title): Image;
    public function deleteDataBaseImage($dataBaseImage): bool;
    public function deleteImageFile($imageUrl): bool;
}
