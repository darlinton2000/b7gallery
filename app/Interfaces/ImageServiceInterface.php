<?php

namespace App\Interfaces;

use App\Models\Image;

interface ImageServiceInterface
{
    public function storeImageInDisk($image): string;
    public function storeImageInDatabase($title, $url): Image;
    public function deleteDataBaseImage($dataBaseImage): bool;
    public function deleteImageFromDisk($imageUrl): bool;
}
