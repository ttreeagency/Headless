<?php
declare(strict_types=1);

namespace Ttree\Headless\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Ttree\Headless\Types\Scalars;
use Wwwision\GraphQL\TypeResolver;

final class Image extends ObjectType implements TypeResolverBasedInterface
{

    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'Image',
            'description' => 'Representation of a binary image with thumbnail',
            'fields' => [
                'url' => ['type' => Type::nonNull($typeResolver->get(Scalars\Url::class))],
                'width' => ['type' => Type::nonNull(Type::int())],
                'height' => ['type' => Type::nonNull(Type::int())]
            ],
        ]);
    }
}
