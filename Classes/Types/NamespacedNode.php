<?php
namespace Ttree\Headless\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Ttree\Headless\Domain\Model\TypeMapper;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Ttree\Headless\Types\Scalars\AbsoluteNodePath;
use Ttree\Headless\Types\Scalars\Uuid;
use Neos\ContentRepository\Domain\Model as CR;
use Ttree\Headless\Domain\Model as Model;

class NamespacedNode extends ObjectType
{
    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    public function __construct(TypeResolver $typeResolver, CR\NodeType $nodeType)
    {
        $nodeType = new Model\NodeType($nodeType);
        $fields = [
            '_name' => [
                'type' => Type::string(),
                'description' => 'Name of this node'
            ],
            '_label' => [
                'type' => Type::string(),
                'description' => 'Full length plain text label of this node'
            ],
            '_hasProperty' => [
                'type' => Type::boolean(),
                'description' => 'If this node has a property with the given name',
                'args' => [
                    'propertyName' => ['type' => Type::nonNull(Type::string())],
                ],
                'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                    /** @var NodeInterface $node */
                    $node = $wrappedNode->getObject();
                    return $node->hasProperty($args['propertyName']);
                }
            ],
            '_isHidden' => [
                'type' => Type::boolean(),
                'description' => 'Whether this node is marked hidden'
            ],
            '_isHiddenInIndex' => [
                'type' => Type::boolean(),
                'description' => 'Whether this node should be hidden in indexes'
            ],
            '_hiddenBeforeDateTime' => [
                'type' => $typeResolver->get(Scalars\DateTime::class),
                'description' => 'The date and time before which this node will be automatically hidden'
            ],
            '_hiddenAfterDateTime' => [
                'type' => $typeResolver->get(Scalars\DateTime::class),
                'description' => 'The node and time after which this node will be hidden'
            ],
            '_isRemoved' => [
                'type' => Type::boolean(),
                'description' => 'Whether this node has been removed'
            ],
            '_isVisible' => [
                'type' => Type::boolean(),
                'description' => 'Whether this node is visible (depending on hidden flag, hiddenBeforeDateTime and hiddenAfterDateTime)'
            ],
            '_isAccessible' => [
                'type' => Type::boolean(),
                'description' => 'Whether this node may be accessed according to the current security context'
            ],
            '_path' => [
                'type' => $typeResolver->get(AbsoluteNodePath::class),
                'description' => 'The absolute path of tis node'
            ],
            '_contextPath' => [
                'type' => Type::string(),
                'description' => 'The absolute path of this node including context information'
            ],
            '_depth' => [
                'type' => Type::int(),
                'description' => 'The level at which this node is located in the tree'
            ],
            '_workspace' => [
                'type' => $typeResolver->get(Workspace::class),
                'description' => 'The workspace this node is contained in'
            ],
            '_identifier' => [
                'type' => $typeResolver->get(Uuid::class),
                'description' => 'The identifier of this node (not the technical id)'
            ],
            '_nodeType' => [
                'type' => $typeResolver->get(NodeType::class),
                'description' => 'The node type of this node'
            ]
        ];

        foreach ($nodeType->getProperties() as $propertyName => $propertyConfiguration) {
            if (!isset($propertyConfiguration['type']) || $propertyName[0] === '_') {
                continue;
            }
            $type = (new TypeMapper($propertyConfiguration['type']))->convert();
            if ($type === null) {
                continue;
            }
            $fields[$propertyName] = [
                'type' => $type,
                // todo add support to have a property description in YAML
                'description' => $propertyName,
                'resolve' => function (AccessibleObject $wrappedNode) use ($propertyName) {
                    /** @var NodeInterface $node */
                    $node = $wrappedNode->getObject();
                    return $node->getProperty($propertyName);
                }
            ];
        }
        return parent::__construct([
            'name' => $nodeType->getFqdnContentName(),
            // todo add support to have a node type description in YAML
            'description' => $nodeType->getName(),
            'fields' => $fields,
        ]);
    }

    protected function typeMapper() {

    }
}
