<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Generator;


use Ttree\Headless\Domain\Model\ContentNamespace;
use Wwwision\GraphQL\TypeResolver;

class ObjectType extends \GraphQL\Type\Definition\ObjectType
{
    public function __construct(TypeResolver $typeResolver, ContentNamespace $namespace)
    {
        return parent::__construct([
            'name' => $namespace->getNamespace() . 'Types',
            'description' => sprintf('Access content type for %s namespace', $namespace->getRaw()),
            'fields' => (new ObjectTypeFields($typeResolver, $namespace))->fields()
        ]);
    }

    public static function createByNamespace(TypeResolver $typeResolver, ContentNamespace $namespace): ObjectType
    {
        return new ObjectType($typeResolver, $namespace);
    }
}
