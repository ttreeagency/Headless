<?php
declare(strict_types=1);

namespace Ttree\Headless\Service;

use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\Flow\Annotations as Flow;
use Ttree\Headless\Types\NodeInterface;
use Ttree\Headless\Types\UnionType;
use Wwwision\GraphQL\TypeResolver;

/**
 * @Flow\Scope("singleton")
 */
final class UnionTypeRegistry
{
    protected $unionTypes = [];

    public function get(TypeResolver $typeResolver, string $name, array $configuration): NodeInterface
    {
        $name = trim($name);
        if (!isset($this->unionTypes[$name])) {
            $this->unionTypes[$name] = new UnionType($typeResolver, $name, $configuration);
        }
        return $this->unionTypes[$name];
    }
}
