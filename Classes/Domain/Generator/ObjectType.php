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
            'description' => 'Access content type for Neos.Demo namespace',
            'fields' => ObjectTypeFields::createByPackage($typeResolver, $namespace)
        ]);
    }

    public static function createByPackage(TypeResolver $typeResolver, ContentNamespace $namespace): ObjectType
    {
        return new ObjectType($typeResolver, $namespace);
    }
}
