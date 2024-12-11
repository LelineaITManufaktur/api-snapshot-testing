<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting\Wildcard;

final class BooleanWildcard implements Wildcard
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function atPath(): string
    {
        return $this->path;
    }

    public function match(mixed $mixed): bool
    {
        return \is_bool($mixed);
    }
}
