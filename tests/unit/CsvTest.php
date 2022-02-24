<?php

declare(strict_types=1);

namespace Tests;

use Lelinea\ApiSnapshotTesting\MatchesSnapshots;
use Lelinea\ApiSnapshotTesting\Wildcard\DateTimeWildcard;
use Lelinea\ApiSnapshotTesting\Wildcard\StringWildcard;
use Lelinea\ApiSnapshotTesting\Wildcard\UuidWildcard;
use PHPUnit\Framework\TestCase;

final class CsvTest extends TestCase
{
    use MatchesSnapshots;

    public function testCsv(): void
    {
        $data = <<<CSV
"Column 1";"Column 2";"Column 3";"Column 4";"Column 5";"Column 6";"Column 7";"Column 8"
"b84c9b7f-1ebb-49b6-9d18-4305932b2dd1";D;Wahr;50;"Homer,\n Simpson";"id card 123";20991231;General
"b84c9b7f-1ebb-49b6-9d18-4305932b2dd1";M;Wahr;50;"Homer, Simpson";"id card 123";20991231;General
"b84c9b7f-1ebb-49b6-9d18-4305932b2dd1";A;Wahr;50;"Vorname, Nachname";"id card 123";20991231;General
CSV;

        $wildcards = [
            new StringWildcard('[*][3]'),
            new DateTimeWildcard('[*][6]', 'Ymd'),
            new UuidWildcard('[*][0]'),
        ];

        $this->assertMatchesCsvSnapshot($data, 'localhost/csv-test-route-a', $wildcards);
    }

    public function testCsvWithNonDefaultConfig(): void
    {
        $data = <<<CSV
'b84c9b7f-1ebb-49b6-9d18-4305932b2dd1',D,Wahr,50,'Homer, Simpson','id card 123',20991231,General
'b84c9b7f-1ebb-49b6-9d18-4305932b2dd1',M,Wahr,50,'Homer, Simpson','id card 123',20991231,General
'b84c9b7f-1ebb-49b6-9d18-4305932b2dd1',A,Wahr,50,'Vorname, Nachname','id card 123',20991231,General
CSV;

        $wildcards = [
            new StringWildcard('[*][3]'),
            new DateTimeWildcard('[*][6]', 'Ymd'),
            new UuidWildcard('[*][0]'),
        ];

        $this->assertMatchesCsvSnapshot($data, 'localhost/csv-test-route-b', $wildcards, false, ',', "'");
    }
}
