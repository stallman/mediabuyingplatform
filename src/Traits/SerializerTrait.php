<?php

namespace App\Traits;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

trait SerializerTrait
{
    public function serializer(Array $ignoredAttributes = [])
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $defaultContext = [
            AbstractObjectNormalizer::MAX_DEPTH_HANDLER => function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
                return $innerObject->id;
            },
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
            AbstractNormalizer::IGNORED_ATTRIBUTES => $ignoredAttributes,
        ];
        $normalizers = [
            new ObjectNormalizer(
                null,
                null,
                null,
                null,
                null,
                null,
                $defaultContext
            ),
        ];

        return new Serializer($normalizers, $encoders);
    }

}