<?php
declare(strict_types=1);

namespace Ttree\Headless\Service;

use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\Flow\Annotations as Flow;
use Ttree\Headless\Types\NodeInterface;
use Wwwision\GraphQL\TypeResolver;

/**
 * @Flow\Scope("singleton")
 */
final class InterfaceRegistry
{
    protected $interfaces = [];

    public function get(TypeResolver $typeResolver, NodeType $nodeType): NodeInterface
    {
        $name = $nodeType->getName();
        if (!isset($this->interfaces[$name])) {
            $this->interfaces[$name] = new NodeInterface($typeResolver, $nodeType);
        }
        return $this->interfaces[$name];
    }
}
