<?php

namespace Ttree\Headless\Domain\Model;

use GraphQL\Type\Definition\Type;
use Ttree\Headless\Types\Scalars\DateTime;

final class TypeMapper
{
    protected $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function convert(): ?Type
    {
        $mapping = [
            'string' => Type::string(),
            'integer' => Type::int(),
            'boolean' => Type::boolean(),
            'array' => Type::listOf(Type::string()),
            'DateTime' => new DateTime(),
            'Neos\Media\Domain\Model\ImageInterface' => null,
            'Neos\Media\Domain\Model\Asset' => null,
            'array<Neos\Media\Domain\Model\Asset>' => null,
            'reference' => null,
            'references' => null,
        ];
        return $mapping[$this->type] ?? null;
    }
}
