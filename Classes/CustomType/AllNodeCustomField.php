<?php
declare(strict_types=1);

namespace Ttree\Headless\CustomType;

use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\ContextFactory;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Ttree\Headless\Types\Scalars;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;

class AllNodeCustomField implements CustomFieldInterface
{
    /**
     * @var ContextFactory
     * @Flow\Inject
     */
    protected $contextFactory;

    public function args(TypeResolver $typeResolver): array
    {
        return [
            'parentIdentifier' => ['type' => $typeResolver->get(Scalars\Uuid::class)],
            'parentPath' => ['type' => $typeResolver->get(Scalars\AbsoluteNodePath::class)],
        ];
    }

    public function description(NodeType $nodeType): string
    {
        return sprintf('Find all nodes of type %s', $nodeType->getName());
    }

    public function resolve(NodeType $nodeType): \Closure
    {
        return function ($_, array $args) use ($nodeType) {
            $context = $this->contextFactory->create();
            if (isset($args['parentIdentifier'])) {
                $parentNode = $context->getNodeByIdentifier($args['parentIdentifier']);
            } elseif (isset($args['parentPath'])) {
                $parentNode = $context->getNode($args['parentPath']);
            } else {
                $parentNode = $context->getRootNode();
            }
            $query = new FlowQuery([$parentNode]);

            return new IterableAccessibleObject($query->find('[instanceof ' . $nodeType->getName() . ']')->get());
        };
    }
}
