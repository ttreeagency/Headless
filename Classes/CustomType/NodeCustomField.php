<?php
declare(strict_types=1);

namespace Ttree\Headless\CustomType;

use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\ContextFactory;
use Ttree\Headless\CustomType\CustomFieldInterface;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Ttree\Headless\Types\Scalars;
use Neos\Flow\Annotations as Flow;

class NodeCustomField implements CustomFieldInterface
{
    /**
     * @var ContextFactory
     * @Flow\Inject
     */
    protected $contextFactory;

    public function args(TypeResolver $typeResolver): array
    {
        return [
            'identifier' => ['type' => $typeResolver->get(Scalars\Uuid::class)],
            'path' => ['type' => $typeResolver->get(Scalars\AbsoluteNodePath::class)],
        ];
    }

    public function description(NodeType $nodeType): string
    {
        return sprintf('Get one node of type %s', $nodeType->getName());
    }

    public function resolve(NodeType $nodeType): \Closure
    {
        return function ($_, array $args) {
            $context = $this->contextFactory->create();
            //  @todo enfore node type
            if (isset($args['identifier'])) {
                $node = $context->getNodeByIdentifier($args['identifier']);
                if ($node === null) {
                    throw new \InvalidArgumentException('Unable to find a node with the given identifier');
                }
                return new AccessibleObject($node);
            } elseif (isset($args['path'])) {
                $node = $context->getNode($args['path']);
                if ($node === null) {
                    throw new \InvalidArgumentException('Unable to find a node with the given path');
                }
                return new AccessibleObject($node);
            }
            throw new \InvalidArgumentException('node path or identifier have to be specified!', 1460064707);
        };
    }
}
