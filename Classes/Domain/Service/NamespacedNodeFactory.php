<?php

namespace Ttree\Headless\Domain\Service;


use GraphQL\Type\Definition\ObjectType;
use Ttree\Headless\Domain\Model\ContentNamespace;
use Ttree\Headless\Types\NamespacedNode;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model as CR;

/**
 * @Flow\Scope("singleton")
 */
class NamespacedNodeFactory
{
    protected $cache = [];

    public function create(TypeResolver $typeResolver, CR\NodeType $nodeType)
    {
        $name = $nodeType->getName();
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }
        $this->cache[$name] = new NamespacedNode($typeResolver, $nodeType);
        return $this->cache[$name];
    }
}
