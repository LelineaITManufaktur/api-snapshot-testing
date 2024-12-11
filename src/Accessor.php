<?php

declare(strict_types=1);

namespace Lelinea\ApiSnapshotTesting;

use Lelinea\ApiSnapshotTesting\Exception\InvalidMappingPath;
use Lelinea\ApiSnapshotTesting\Exception\WildcardMismatch;
use Lelinea\ApiSnapshotTesting\Wildcard\Wildcard;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class Accessor
{
    /**
     * @param string|\stdClass|object|mixed[] $data
     *
     * @throws InvalidMappingPath
     */
    public function assertFields($data, Wildcard $wildcard, string $pathPrefix = ''): void
    {
        if (\is_string($data)) {
            return;
        }

        $dataPaths = $this->buildDataPaths($wildcard, $data, $pathPrefix);

        foreach ($dataPaths as $pathData) {
            $this->assert($wildcard, $pathData, $pathPrefix);
        }
    }

    /**
     * @return mixed[]
     */
    private function buildDataPaths(Wildcard $wildcard, mixed $data, string $pathPrefix): array
    {
        $paths = \explode('[*]', $pathPrefix . $wildcard->atPath());
        $dataPaths = ['' => $data];
        foreach ($paths as $path) {
            foreach ($dataPaths as $checkPath => $pathData) {
                $elements = '' === $checkPath . $path ? $pathData : $this->getValue($data, $checkPath . $path);
                unset($dataPaths[$checkPath]);
                if (\is_array($elements)) {
                    foreach ($elements as $n => $element) {
                        $dataPaths[\sprintf('%s%s[%s]', $checkPath, $path, $n)] = $element;
                    }
                } else {
                    $finalPath = \sprintf('%s%s', $checkPath, $path);
                    $dataPaths[$finalPath] = $this->getValue($data, $finalPath);
                }
            }
        }

        return $dataPaths;
    }

    /**
     * @param string|\stdClass|object|mixed[] $data
     *
     * @throws InvalidMappingPath
     */
    public function replaceFields($data, Wildcard $wildcard, string $pathPrefix = ''): void
    {
        if (\is_string($data)) {
            return;
        }

        $dataPaths = $this->buildDataPaths($wildcard, $data, $pathPrefix);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($dataPaths as $path => $pathData) {
            $propertyAccessor->setValue($data, $path, Wildcard::REPLACEMENT);
        }
    }

    /**
     * @param object|mixed[] $data
     */
    private function getValue(mixed $data, string $path): mixed
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        try {
            return $propertyAccessor->getValue($data, $path);
        } catch (NoSuchPropertyException $exception) {
            throw new InvalidMappingPath($path);
        }
    }

    private function assert(Wildcard $wildcard, mixed $value, string $pathPrefix): void
    {
        if (!$wildcard->match($value)) {
            throw new WildcardMismatch(\get_class($wildcard), $pathPrefix . $wildcard->atPath(), $value);
        }
    }
}
