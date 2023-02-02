<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting;

final class Filesystem
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function path(string $filename): string
    {
        return $this->basePath . \DIRECTORY_SEPARATOR . $filename;
    }

    public function has(string $filename): bool
    {
        return \file_exists($this->path($filename));
    }

    public function read(string $filename): string
    {
        return (string) \file_get_contents($this->path($filename));
    }

    public function put(string $filename, string $contents): void
    {
        if (!\file_exists($this->basePath)) {
            \mkdir($this->basePath, 0777, true);
        }

        \file_put_contents($this->path($filename), $contents);
    }
}
