<?php

declare(strict_types=1);

namespace Tests;

use function json_encode;
use Lelinea\ApiSnapshotTesting\Exception\InvalidMappingPath;
use Lelinea\ApiSnapshotTesting\MatchesSnapshots;
use Lelinea\ApiSnapshotTesting\Wildcard\BooleanWildcard;
use Lelinea\ApiSnapshotTesting\Wildcard\DateTimeOrNullWildcard;
use Lelinea\ApiSnapshotTesting\Wildcard\DateTimeWildcard;
use Lelinea\ApiSnapshotTesting\Wildcard\IntegerWildcard;
use Lelinea\ApiSnapshotTesting\Wildcard\UuidOrNullWildcard;
use Lelinea\ApiSnapshotTesting\Wildcard\UuidWildcard;
use Lelinea\ApiSnapshotTesting\Wildcard\Wildcard;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    use MatchesSnapshots;

    public function testJson(): void
    {
        $data = json_encode(
            [
                'tests' => [
                 'id1'     => 'b84c9b7f-1ebb-49b6-9d18-4305932b2dd1',
                 'id2'     => null,
                 'id3'     => 'b84c9b7f-1ebb-49b6-9d18-4305932b2dd1',
                 'nested1' => [
                     0 => [
                         'nested2' => [
                             0 => ['int' => 2],
                             1 => ['int' => 3],
                         ],
                     ],
                     1 => [
                         'nested2' => [
                             0 => ['int' => 4],
                             1 => ['int' => 5],
                         ],
                     ],
                 ],
                 'multiple' => [
                     0 => 'a',
                     1 => 6665,
                     2 => 'c',
                 ],
                 'integers' => [
                     123,
                     1234,
                     12345,
                     123456,
                 ],
                 'booleans' => [
                     true,
                     true,
                     false,
                 ],
                 'objects' => [
                     ['id' => 123],
                     ['id' => 234],
                     ['id' => 345],
                 ],
                 'arrays' => [
                     [1, 2, 3],
                     [3, 4, 5],
                 ],
                 'dateTimes' => [
                     [
                         '2019-07-10T12:50:20+00:00',
                         '2019-02-28T13:50:24+02:00',
                     ],
                     [
                         '02.02.2012 12:12',
                         '01.01.2011 11:11',
                     ],
                 ],
                 'dateTimeOrNull' => [
                     '2019-07-10T12:50:20+00:00',
                     null,
                 ],
             ],
            ]
        );

        $wildcards = [
            new IntegerWildcard('tests.nested1[*].nested2[*].int'),
            new UuidWildcard('tests.id1'),
            new UuidOrNullWildcard('tests.id2'),
            new UuidOrNullWildcard('tests.id3'),
            new IntegerWildcard('tests.multiple[1]'),
            new IntegerWildcard('tests.integers[*]'),
            new BooleanWildcard('tests.booleans[*]'),
            new IntegerWildcard('tests.objects[*].id'),
            new IntegerWildcard('tests.arrays[*][2]'),
            new DateTimeWildcard('tests.dateTimes[0][*]'),
            new DateTimeWildcard('tests.dateTimes[1][*]', 'd.m.Y H:i'),
            new DateTimeOrNullWildcard('tests.dateTimeOrNull[*]'),
        ];

        $this->assertMatchesJsonSnapshot($data, 'localhost/json-test-route-a', $wildcards);
    }

    /**
     * @dataProvider provideFail
     */
    public function testFailOnInvalidMapping(Wildcard $wildcard): void
    {
        $data = json_encode(
            [
                'tests' => [
                 'id'      => 'b84c9b7f-1ebb-49b6-9d18-4305932b2dd1',
                 'nested1' => [
                     0 => [
                         'nested2' => [
                             0 => ['int' => 2],
                             1 => ['int' => 3],
                         ],
                     ],
                     1 => [
                         'nested2' => [
                             0 => ['int' => 4],
                             1 => ['int' => 5],
                         ],
                     ],
                 ],
                 'multiple' => [
                     0 => 'a',
                     1 => 6665,
                     2 => 'c',
                 ],
                 'integers' => [
                     123,
                     1234,
                     12345,
                     123456,
                 ],
                 'objects' => [
                     ['id' => 123],
                     ['id' => 234],
                     ['id' => 345],
                 ],
                 'arrays' => [
                     [1, 2, 3],
                     [3, 4, 5],
                 ],
                 'dateTimes' => [
                     [
                         '2019-07-10T12:50:20+00:00',
                         '2019-02-28T13:50:24+02:00',
                     ],
                     [
                         '02.02.2012 12:12',
                         '01.01.2011 11:11',
                     ],
                 ],
             ],
            ]
        );
        $this->expectException(InvalidMappingPath::class);
        $this->assertMatchesJsonSnapshot($data, 'localhost/json-test-route-b', [$wildcard]);
    }

    /**
     * @return Wildcard[][]
     */
    public function provideFail(): array
    {
        return [
            [new IntegerWildcard('tests.nested0[*].nested2[*].int')],
            [new IntegerWildcard('tests.nested1[*].nested0[*].int')],
            [new IntegerWildcard('tests.nested1[*].nested2[*].foo')],
            [new UuidWildcard('tests.foo.id')],
        ];
    }
}
