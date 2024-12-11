<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting\Wildcard;

abstract class BaseDateTimeWildcard implements Wildcard
{
    private string $path;

    private string $format;

    public function __construct(string $path, string $format = \DateTime::ATOM)
    {
        $this->path   = $path;
        $this->format = $format;
    }

    public function atPath(): string
    {
        return $this->path;
    }

    public function match(mixed $mixed): bool
    {
        try {
            $dateTime = \DateTime::createFromFormat($this->format, $mixed);

            return false !== $dateTime;
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
