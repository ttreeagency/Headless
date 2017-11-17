<?php

namespace Ttree\Headless\Domain\Model;

use GraphQL\Type\Definition\Type;

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
        ];
        return $mapping[$this->type] ?? null;
    }
}
