<?php

namespace Ttree\Headless\Domain\Generator;


use Neos\ContentRepository\Domain\Model as CR;
use Ttree\Headless\Types\Node;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class NodeFactory
{
    protected $cache = [];

    public function create(TypeResolver $typeResolver, CR\NodeType $nodeType)
    {
        $name = $nodeType->getName();
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }
        $this->cache[$name] = new Node($typeResolver, $nodeType);
        return $this->cache[$name];
    }
}
