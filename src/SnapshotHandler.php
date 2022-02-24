<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting;

use function preg_replace;

final class SnapshotHandler
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function snapshotExists(Snapshot $snapshot): bool
    {
        return $this->filesystem->has($this->getFilename($snapshot->getId(), $snapshot->getDriver()));
    }

    public function writeToFilesystem(Snapshot $snapshot): void
    {
        $this->filesystem->put($this->getFilename($snapshot->getId(), $snapshot->getDriver()), $snapshot->getContent());
    }

    public function getFilename(string $snapshotId, Driver $driver): string
    {
        $file = $snapshotId . '.' . $driver->extension();
        // Remove anything which isn't a word, whitespace, number
        // or any of the following characters -_~,;[]().
        $file = (string) preg_replace('([^\w\s\d\-_~,;\[\]\(\).])', '', $file);
        // Remove any runs of periods
        $file = (string) preg_replace('([\.]{2,})', '', $file);

        return $file;
    }
}
