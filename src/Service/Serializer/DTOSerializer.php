<?php

namespace App\Service\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DTOSerializer implements SerializerInterface
{
    private SerializerInterface $serializer;

    public function __construct(private ValidatorInterface $validator)
    {
        $this->serializer = new Serializer(
            [new ObjectNormalizer(
                classMetadataFactory: new ClassMetadataFactory(new AttributeLoader()),
                nameConverter: new CamelCaseToSnakeCaseNameConverter()
            )],
            [new JsonEncoder()]
        );
    }

    public function serialize(mixed $data, string $format, array $context = []): string
    {
        return $this->serializer->serialize($data, $format, $context);
    }

    public function deserialize(mixed $data, string $type, string $format, array $context = []): mixed
    {
        $dto = $this->serializer->deserialize($data, $type, $format, $context);

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            throw new ValidationFailedException(null, $errors);
        }

        return $dto;
    }
}