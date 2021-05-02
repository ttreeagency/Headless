<?php
declare(strict_types=1);

namespace Ttree\Headless\CustomType;

use GraphQL\Type\Definition\Type;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Ttree\Headless\Types\Scalars;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;

class AllNodeCustomField implements CustomFieldInterface
{
    /**
     * @var ContextFactoryInterface
     * @Flow\Inject
     */
    protected $contextFactory;

    public function args(TypeResolver $typeResolver): array
    {
        return [
            'from' => ['type' => $typeResolver->get(Scalars\Uuid::class)],
            'depth' => ['type' => Type::int(), 'defaultValue' => 1],
            'limit' => [
                'type' => Type::int(),
                'description' => 'The maximum number of records returned',
                'defaultValue' => 10
            ]
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
            $parentNode = $context->getNodeByIdentifier($args['from']);
            $query = new FlowQuery([$parentNode]);

            return new IterableAccessibleObject($query->find('[instanceof ' . $nodeType->getName() . ']')->get());
        };
    }
}
