<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting\Driver;

use DOMDocument;
use function is_string;
use function json_decode;
use function json_encode;
use Lelinea\ApiSnapshotTesting\Accessor;
use Lelinea\ApiSnapshotTesting\Driver;
use Lelinea\ApiSnapshotTesting\Exception\CantBeSerialized;
use Lelinea\ApiSnapshotTesting\Wildcard\Wildcard;
use const PHP_EOL;
use PHPUnit\Framework\Assert;
use function simplexml_load_string;
use stdClass;

final class XmlDriver implements Driver
{
    public function serialize(string $xml, string $requestUrl): string
    {
        $doc                     = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput       = true;

        $doc->loadXML($xml);

        return $doc->saveXML() . PHP_EOL;
    }

    public function extension(): string
    {
        return 'xml';
    }

    /**
     * @param Wildcard[] $wildcards
     */
    public function match(string $expected, string $actual, array $wildcards = []): void
    {
        $actualArray = $this->decode($actual);
        $this->assertFields($actualArray, $wildcards);

        $actualArray = $this->replaceFields($actualArray, $wildcards);
        $actual      = (string) json_encode($actualArray);

        $expectedArray = $this->decode($expected);
        $expectedArray = $this->replaceFields($expectedArray, $wildcards);
        $expected      = (string) json_encode($expectedArray);

        Assert::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @param string|stdClass|Wildcard[] $data
     * @param Wildcard[]                 $wildcards
     */
    private function assertFields($data, array $wildcards): void
    {
        if (is_string($data)) {
            return;
        }

        foreach ($wildcards as $wildcard) {
            (new Accessor())->assertFields($data, $wildcard);
        }
    }

    /**
     * @param string|stdClass|Wildcard[] $data
     * @param Wildcard[]                 $wildcards
     *
     * @return string|stdClass|mixed[]
     */
    private function replaceFields($data, array $wildcards)
    {
        if (is_string($data)) {
            return $data;
        }

        foreach ($wildcards as $wildcard) {
            (new Accessor())->replaceFields($data, $wildcard);
        }

        return $data;
    }

    /**
     * @throws CantBeSerialized
     *
     * @return mixed
     */
    private function decode(string $data)
    {
        $data = simplexml_load_string($data);

        if (false === $data) {
            throw new CantBeSerialized('Given string does not contain valid xml.');
        }
        $data = (string) json_encode($data);

        return json_decode($data);
    }
}
