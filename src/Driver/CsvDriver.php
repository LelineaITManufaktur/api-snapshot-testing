<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting\Driver;

use Lelinea\ApiSnapshotTesting\Accessor;
use Lelinea\ApiSnapshotTesting\Driver;
use Lelinea\ApiSnapshotTesting\Wildcard\Wildcard;
use PHPUnit\Framework\Assert;

final class CsvDriver implements Driver
{
    private string $fieldSeparator;

    private string $fieldEnclosure;

    private bool $skipHeaders;

    public function __construct(string $fieldSeparator, string $fieldEnclosure, bool $skipHeaders)
    {
        $this->fieldSeparator = $fieldSeparator;
        $this->fieldEnclosure = $fieldEnclosure;
        $this->skipHeaders = $skipHeaders;
    }

    public function serialize(string $csv, string $requestUrl): string
    {
        $decodedCsv = $this->decode($csv, false);
        $data = property_exists($decodedCsv, 'data') ? $decodedCsv->data : [];
        $handle = fopen('php://temp', 'r+');
        assert(is_resource($handle));
        foreach ($data as $line) {
            fputcsv($handle, $line, $this->fieldSeparator, $this->fieldEnclosure);
        }
        rewind($handle);
        $csvString = stream_get_contents($handle);
        assert(is_string($csvString));

        return $csvString;
    }

    public function extension(): string
    {
        return 'csv';
    }

    /**
     * @param Wildcard[] $wildcards
     */
    public function match(string $expected, string $actual, array $wildcards = []): void
    {
        $actualArray = $this->decode($actual, $this->skipHeaders);
        $this->assertFields($actualArray, $wildcards);

        $actualArray = $this->replaceFields($actualArray, $wildcards);
        $actual = (string) json_encode($actualArray);

        $expectedArray = $this->decode($expected, $this->skipHeaders);
        $expectedArray = $this->replaceFields($expectedArray, $wildcards);
        $expected = (string) json_encode($expectedArray);

        Assert::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @param Wildcard[] $wildcards
     */
    private function assertFields(object $data, array $wildcards): void
    {
        foreach ($wildcards as $wildcard) {
            (new Accessor())->assertFields($data, $wildcard, 'data');
        }
    }

    /**
     * @param Wildcard[] $wildcards
     */
    private function replaceFields(object $data, array $wildcards): object
    {
        foreach ($wildcards as $wildcard) {
            (new Accessor())->replaceFields($data, $wildcard, 'data');
        }

        return $data;
    }

    private function decode(string $data, bool $skipHeaders): object
    {
        $fp = tmpfile();
        assert(is_resource($fp));

        fwrite($fp, $data);
        rewind($fp);

        $rows = [];
        while (($row = fgetcsv($fp, 0, $this->fieldSeparator, $this->fieldEnclosure)) !== false) {
            $rows[] = array_map(static function ($rowData) {
                return '' === $rowData ? null : $rowData;
            }, $row ?? []);
        }

        if (count($rows) > 0 && $skipHeaders) {
            array_shift($rows);
        }

        return (object) ['data' => $rows];
    }
}
