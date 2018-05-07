<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\RootTypes;

use GraphQL\Type\Definition\ObjectType;
use Neos\ContentRepository\Domain\Model\NodeType;
use Ttree\Headless\Domain\Generator\ObjectTypeFields;
use Ttree\Headless\Domain\Model\ContentNamespace;
use Ttree\Headless\Domain\Model\QueryableNodeTypes;
use Wwwision\GraphQL\TypeResolver;

class Query extends ObjectType
{
    public function __construct(TypeResolver $typeResolver)
    {
        $queryableNodeTypes = new QueryableNodeTypes();

        $fields = [];
        /** @var NodeType $nodeType */
        foreach ($queryableNodeTypes->iterate() as $nodeType) {
            $fields = array_merge((new ObjectTypeFields($typeResolver, ContentNamespace::createFromNodeType($nodeType)))->definition(), $fields);
        }

        parent::__construct([
            'name' => 'Query',
            'description' => 'Root queries for the Neos Content Repository',
            'fields' => $fields
        ]);
    }
}
