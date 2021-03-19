<?php
declare(strict_types=1);

namespace Ttree\Headless\Service;

use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\Flow\Annotations as Flow;
use Psr\Log\LoggerInterface;
use Ttree\Headless\Domain\Model\FieldType;
use Ttree\Headless\Types\NodeInterface;
use Wwwision\GraphQL\TypeResolver;

/**
 * @Flow\Scope("singleton")
 */
final class InterfaceRegistry
{
    protected static array $interfaces = [];

    /**
     * @var LoggerInterface
     * @Flow\Inject(name="Neos.Flow:SystemLogger")
     */
    protected $logger;

    public function get(TypeResolver $typeResolver, NodeType $nodeType): NodeInterface
    {
        $name = FieldType::createFromNodeType($nodeType)->getName();
        if (!isset(self::$interfaces[$name])) {
            $this->logger->info(vsprintf('Generate interface for %s (%s)', [$name, $nodeType->getName()]));
            self::$interfaces[$name] = new NodeInterface($typeResolver, $nodeType);
        }
        return self::$interfaces[$name];
    }
}
