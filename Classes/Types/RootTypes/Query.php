<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\RootTypes;

use GraphQL\Type\Definition\ObjectType;
use Neos\ContentRepository\Domain\Model\NodeType;
use Ttree\Headless\Domain\Generator\ObjectTypeFields;
use Ttree\Headless\Domain\Generator\QueryDefinition;
use Ttree\Headless\Domain\Model\ContentNamespace;
use Ttree\Headless\Domain\Model\QueryableNodeTypes;
use Wwwision\GraphQL\TypeResolver;

class Query extends ObjectType
{
    public function __construct(TypeResolver $typeResolver)
    {
        $queryableNodeTypes = new QueryableNodeTypes();

        $namespaces = [];
        /** @var NodeType $nodeType */
        foreach ($queryableNodeTypes->iterate() as $nodeType) {
            list($namespace) = explode(':', $nodeType->getName());
            if (isset($namespaces[$namespace])) {
                continue;
            }
            $namespaces[$namespace] = new ContentNamespace($namespace);
        }

        $fields = [];
        foreach (array_values($namespaces) as $namespace) {
            $fields = array_merge((new ObjectTypeFields($typeResolver, $namespace))->definition(), $fields);
        }

        parent::__construct([
            'name' => 'Query',
            'description' => 'Root queries for the Neos Content Repository',
            'fields' => $fields
        ]);
    }
}
