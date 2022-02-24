<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting\Wildcard;

final class DateTimeOrNullWildcard extends BaseDateTimeWildcard
{
    /**
     * @param mixed $mixed
     */
    public function match($mixed): bool
    {
        if (null === $mixed) {
            return true;
        }

        return parent::match($mixed);
    }
}
