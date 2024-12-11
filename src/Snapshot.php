<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting;

use Lelinea\ApiSnapshotTesting\Wildcard\Wildcard;

final class Snapshot
{
    private string $id;

    private string $content;

    private Driver $driver;

    private function __construct(
        string $id,
        string $content,
        Driver $driver,
    ) {
        $this->id      = $id;
        $this->driver  = $driver;
        $this->content = $content;
    }

    public static function forTestCase(
        string $id,
        string $content,
        Driver $driver,
    ): self {
        return new self($id, $content, $driver);
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param Wildcard[] $wildcards
     */
    public function assertMatches(string $actual, array $wildcards = []): void
    {
        $this->driver->match($this->content, $actual, $wildcards);
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function update(string $actual, string $requestUrl): void
    {
        $this->content = $this->driver->serialize($actual, $requestUrl);
    }
}
