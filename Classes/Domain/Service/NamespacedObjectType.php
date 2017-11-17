<?php

namespace Ttree\Headless\Domain\Service;


use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\ContextFactory;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Core\ApplicationContext;
use Neos\Flow\ObjectManagement\ObjectManager;
use Ttree\Headless\Domain\Model\ContentNamespace;
use Ttree\Headless\Domain\Model\Plural;
use Ttree\Headless\Types\Scalars;
use Ttree\Headless\Types\Node;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;

class NamespacedObjectType extends ObjectType
{
    public function __construct(TypeResolver $typeResolver, ContentNamespace $namespace)
    {
        return parent::__construct([
            'name' => $namespace->getNamespace() . 'Types',
            'description' => 'Access content type for Neos.Demo namespace',
            'fields' => NamespacedObjectTypeFields::createByPackage($typeResolver, $namespace)
        ]);
    }

    public static function createByPackage(TypeResolver $typeResolver, ContentNamespace $namespace): ObjectType
    {
        return new NamespacedObjectType($typeResolver, $namespace);
    }
}
