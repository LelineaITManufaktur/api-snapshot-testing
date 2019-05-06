<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Exception;

use Exception;

final class CantBeReplaced extends Exception
{
    public function __construct(string $constraint, string $path)
    {
        parent::__construct(sprintf('Replacement "%s" at path "%s" could not be performed. Given value does not match the replacement\'s constraint.', $constraint, $path));
    }
}
