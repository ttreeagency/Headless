<?php

namespace Ttree\Headless\Types\RootTypes;

use GraphQL\Type\Definition\ObjectType;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Ttree\Headless\Domain\Model\ContentNamespace;
use Ttree\Headless\Domain\Service\NamespacedObjectType;
use Ttree\Headless\Domain\Service\NamespacedQueryDefinition;
use Ttree\Headless\Domain\Service\NamespacedQueryField;
use Wwwision\GraphQL\TypeResolver;
use Ttree\Headless\Types;
use Neos\Flow\Annotations as Flow;

class Query extends ObjectType
{
    public function __construct(TypeResolver $typeResolver)
    {
        $definition = new NamespacedQueryDefinition($typeResolver);
        parent::__construct([
            'name' => 'Query',
            'description' => 'Root queries for the Neos Content Repository',
            'fields' => $definition->fields()
        ]);
    }
}
