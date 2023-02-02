<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting\Wildcard;

use Webmozart\Assert\Assert;

final class StringWildcard implements Wildcard
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

    /**
     * @param mixed $mixed
     */
    public function match($mixed): bool
    {
        try {
            Assert::string($mixed);

            return true;
        } catch (\InvalidArgumentException $exception) {
            return false;
        }
    }
}
