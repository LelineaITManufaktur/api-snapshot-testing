<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting\Exception;

final class WildcardMismatch extends \Exception
{
    /**
     * @param mixed $value
     */
    public function __construct(string $wildcard, string $path, $value)
    {
        $message = 'Wildcard "%s" at path "%s" could not be performed.
                    Given value "%s" does not match the wildcards constraint.';

        parent::__construct(\sprintf($message, $wildcard, $path, \var_export($value, true)));
    }
}
