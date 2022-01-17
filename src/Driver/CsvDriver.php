<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting\Driver;

use Lelinea\ApiSnapshotTesting\Accessor;
use Lelinea\ApiSnapshotTesting\Driver;
use Lelinea\ApiSnapshotTesting\Exception\CantBeSerialized;
use Lelinea\ApiSnapshotTesting\Wildcard\Wildcard;
use PHPUnit\Framework\Assert;
use stdClass;
use const PHP_EOL;
use function array_filter;
use function array_map;
use function array_values;
use function assert;
use function fopen;
use function fputcsv;
use function is_resource;
use function is_string;
use function json_encode;
use function preg_split;
use function rewind;
use function str_getcsv;
use function stream_get_contents;
use function trim;

final class CsvDriver implements Driver
{
    /** @var string */
    private $fieldSeparator;

    /** @var string */
    private $fieldEnclosure;

    public function __construct(string $fieldSeparator, string $fieldEnclosure)
    {
        $this->fieldSeparator = $fieldSeparator;
        $this->fieldEnclosure = $fieldEnclosure;
    }

    public function serialize(string $csv, string $requestUrl) : string
    {
        $data   = $this->decode($csv);
        $handle = fopen('php://temp', 'r+');
        assert(is_resource($handle));
        foreach ($data as $line) {
            fputcsv($handle, $line, $this->fieldSeparator, $this->fieldEnclosure);
        }
        rewind($handle);
        $csvString = stream_get_contents($handle);
        assert(is_string($csvString));

        return $csvString . PHP_EOL;
    }

    public function extension() : string
    {
        return 'csv';
    }

    /**
     * @param Wildcard[] $wildcards
     */
    public function match(string $expected, string $actual, array $wildcards = []) : void
    {
        $actualArray = $this->decode($actual);
        $this->assertFields($actualArray, $wildcards);

        $actualArray = $this->replaceFields($actualArray, $wildcards);
        $actualArray = array_values($actualArray);
        $actual      = json_encode($actualArray);

        $expectedArray = $this->decode($expected);
        $expectedArray = $this->replaceFields($expectedArray, $wildcards);
        $expectedArray = array_values($expectedArray);
        $expected      = json_encode($expectedArray);

        Assert::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @param string[][] $data
     * @param Wildcard[] $wildcards
     */
    private function assertFields(array $data, array $wildcards) : void
    {
        if (is_string($data)) {
            return;
        }

        foreach ($wildcards as $wildcard) {
            (new Accessor())->assertFields($data, $wildcard);
        }
    }

    /**
     * @param string[][] $data
     * @param Wildcard[] $wildcards
     *
     * @return string|stdClass|mixed[]
     */
    private function replaceFields(array $data, array $wildcards) : array
    {
        foreach ($wildcards as $wildcard) {
            (new Accessor())->replaceFields($data, $wildcard);
        }

        return $data;
    }

    /**
     * @return mixed[]
     *
     * @throws CantBeSerialized
     */
    private function decode(string $data) : array
    {
        $lines = preg_split('/\r\n|\r|\n/', $data);
        if ($lines === false) {
            throw new CantBeSerialized('Given string does not contain valid csv.');
        }
        $lines = array_filter(
            $lines,
            static function (string $line) : bool {
                return trim($line) !== '';
            }
        );

        return array_map(
            function (string $line) : array {
                return str_getcsv($line, $this->fieldSeparator, $this->fieldEnclosure);
            },
            $lines
        );
    }
}
