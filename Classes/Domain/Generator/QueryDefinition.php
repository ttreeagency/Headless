<?php

namespace Ttree\Headless\Domain\Generator;

use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Ttree\Headless\Domain\Model\ContentNamespace;
use Ttree\Headless\Domain\Model\QueryableNodeTypes;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;

class QueryDefinition
{
    /**
     * @var QueryableNodeTypes
     * @Flow\Inject
     */
    protected $queryableNodeTypes;

    /**
     * @var array
     */
    protected $fields = [];

    public function fields(TypeResolver $typeResolver)
    {
        if ($this->fields !== []) {
            return $this->fields;
        }

        $namespaces = [];
        /** @var NodeType $nodeType */
        foreach ($this->queryableNodeTypes->iterate() as $nodeType) {
            list($namespace) = explode(':', $nodeType->getName());
            if (isset($namespaces[$namespace])) {
                continue;
            }
            $namespaces[$namespace] = new ContentNamespace($namespace);
        }


        $fields = [];
        foreach (array_values($namespaces) as $namespace) {
            $fields = \array_merge(QueryField::create(
                $namespace,
                $typeResolver,
                ObjectType::createByNamespace($typeResolver, $namespace)
            )->fields(), $fields);
        }
        $this->fields = $fields;

        return $this->fields;
    }
}
