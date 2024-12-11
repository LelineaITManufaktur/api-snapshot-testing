<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting\Driver;

use Lelinea\ApiSnapshotTesting\Accessor;
use Lelinea\ApiSnapshotTesting\Driver;
use Lelinea\ApiSnapshotTesting\Exception\CantBeSerialized;
use Lelinea\ApiSnapshotTesting\Wildcard\Wildcard;
use PHPUnit\Framework\Assert;

final class JsonDriver implements Driver
{
    public function serialize(string $json, string $requestUrl): string
    {
        $data = [
            'requestUrl'   => $requestUrl,
            'responseData' => $this->decode($json),
        ];

        return \json_encode($data, \JSON_PRETTY_PRINT) . \PHP_EOL;
    }

    public function extension(): string
    {
        return 'json';
    }

    /**
     * @param Wildcard[] $wildcards
     */
    public function match(string $expected, string $actual, array $wildcards = []): void
    {
        $actualArray = $this->decode($actual);
        $this->assertFields($actualArray, $wildcards);

        $actualArray = $this->replaceFields($actualArray, $wildcards);
        $actual = (string) \json_encode($actualArray);

        $decodedExpected = $this->decode($expected);
        $expectedArray = property_exists($decodedExpected, 'responseData') ? $decodedExpected->responseData : [];
        $expectedArray = $this->replaceFields($expectedArray, $wildcards);
        $expected = (string) \json_encode($expectedArray);

        Assert::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @param string|\stdClass|Wildcard[] $data
     * @param Wildcard[]                  $wildcards
     */
    private function assertFields($data, array $wildcards): void
    {
        if (\is_string($data)) {
            return;
        }

        foreach ($wildcards as $wildcard) {
            (new Accessor())->assertFields($data, $wildcard);
        }
    }

    /**
     * @param string|\stdClass|Wildcard[] $data
     * @param Wildcard[]                  $wildcards
     *
     * @return string|\stdClass|mixed[]
     */
    private function replaceFields($data, array $wildcards)
    {
        if (\is_string($data)) {
            return $data;
        }

        foreach ($wildcards as $wildcard) {
            (new Accessor())->replaceFields($data, $wildcard);
        }

        return $data;
    }

    /**
     * @throws CantBeSerialized
     */
    private function decode(string $data): mixed
    {
        $data = \json_decode($data);

        if (false === $data) {
            throw new CantBeSerialized('Given string does not contain valid json.');
        }

        return $data;
    }
}
