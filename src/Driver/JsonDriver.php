<?php

declare(strict_types=1);

namespace KigaRoo\SnapshotTesting\Driver;

use KigaRoo\SnapshotTesting\Accessor;
use KigaRoo\SnapshotTesting\Driver;
use KigaRoo\SnapshotTesting\Exception\CantBeSerialized;
use KigaRoo\SnapshotTesting\Replacement\Replacement;
use PHPUnit\Framework\Assert;
use stdClass;
use const JSON_PRETTY_PRINT;
use const PHP_EOL;
use function is_string;
use function json_decode;
use function json_encode;

final class JsonDriver implements Driver
{
    /**
     * @param Replacement[] $replacements
     */
    public function serialize(string $json, array $replacements = []) : string
    {
        $data = $this->decode($json);

        $data = $this->replaceFieldsWithConstraintExpression($data, $replacements);

        return json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
    }

    public function extension() : string
    {
        return 'json';
    }

    /**
     * @param Replacement[] $replacements
     */
    public function match(string $expected, string $actual, array $replacements = []) : void
    {
        $actualArray = $this->decode($actual);
        $actualArray = $this->replaceFieldsWithConstraintExpression($actualArray, $replacements);
        $actual      = json_encode($actualArray);

        Assert::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @param string|stdClass|Replacement[] $actualStringOrObjectOrArray
     * @param Replacement[]                 $replacements
     *
     * @return string|stdClass|mixed[]
     */
    private function replaceFieldsWithConstraintExpression($actualStringOrObjectOrArray, array $replacements)
    {
        if (is_string($actualStringOrObjectOrArray)) {
            return $actualStringOrObjectOrArray;
        }

        foreach ($replacements as $replacement) {
            (new Accessor())->replace($actualStringOrObjectOrArray, $replacement);
        }

        return $actualStringOrObjectOrArray;
    }

    /**
     * @return string|object|mixed[]
     *
     * @throws CantBeSerialized
     */
    private function decode(string $data)
    {
        $data = json_decode($data);

        if ($data === false) {
            throw new CantBeSerialized('Given string does not contain valid json.');
        }

        return $data;
    }
}
