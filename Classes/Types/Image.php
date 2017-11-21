<?php

namespace Ttree\Headless\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Ttree\Headless\Types\Scalars;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\TypeResolver;

final class Image extends ObjectType
{

    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'Image',
            'description' => 'Representation of a binary image with thumbnail',
            'fields' => [
                'url' => ['type' => $typeResolver->get(Scalars\Url::class)],
                'width' => ['type' => Type::int()],
                'height' => ['type' => Type::int()],
                'orientation' => ['type' => Type::string()]
            ],
        ]);
    }
}
