<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting\Exception;

final class InvalidMappingPath extends \Exception
{
    public function __construct(string $path)
    {
        parent::__construct(\sprintf('Path to "%s" could not be mapped.', $path));
    }
}
