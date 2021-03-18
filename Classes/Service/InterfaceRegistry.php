<?php
declare(strict_types=1);

namespace Ttree\Headless\Service;

use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\Flow\Annotations as Flow;
use Ttree\Headless\Domain\Model\FieldType;
use Ttree\Headless\Types\NodeInterface;
use Wwwision\GraphQL\TypeResolver;

/**
 * @Flow\Scope("singleton")
 */
final class InterfaceRegistry
{
    protected static array $interfaces = [];

    public function get(TypeResolver $typeResolver, NodeType $nodeType): NodeInterface
    {
        $name = FieldType::createFromNodeType($nodeType)->getName();
        if (!isset(self::$interfaces[$name])) {
            self::$interfaces[$name] = new NodeInterface($typeResolver, $nodeType);
        }
        return self::$interfaces[$name];
    }
}
