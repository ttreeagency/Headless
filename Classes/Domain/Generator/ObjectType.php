<?php

namespace Ttree\Headless\Domain\Generator;


use Ttree\Headless\Domain\Model\ContentNamespace;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;

class ObjectType extends \GraphQL\Type\Definition\ObjectType
{
    public function __construct(TypeResolver $typeResolver, ContentNamespace $namespace)
    {
        return parent::__construct([
            'name' => $namespace->getNamespace() . 'Types',
            'description' => sprintf('Access content type for %s namespace', $namespace->getRaw()),
            'fields' => ObjectTypeFields::createByNamespace($typeResolver, $namespace)
        ]);
    }

    public static function createByNamespace(TypeResolver $typeResolver, ContentNamespace $namespace): ObjectType
    {
        return new ObjectType($typeResolver, $namespace);
    }
}
