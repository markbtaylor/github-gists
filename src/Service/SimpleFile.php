<?php

namespace App\Service;

class SimpleFile
{
    public function write(string $filePath, string $contents): bool|int
    {
        return file_put_contents($filePath, $contents);
    }

    public function read(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        return file_get_contents($filePath) ?: null;
    }

    public function delete(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        return unlink($filePath);
    }
}