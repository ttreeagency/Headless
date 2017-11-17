<?php
namespace Ttree\Headless\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Ttree\Headless\Domain\Model\TypeMapper;
use Ttree\Headless\Types\Scalars\DateTime;
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

        $fields = $this->prepareSystemPropertiesDefinition($typeResolver);
        $this->preparePropertiesDefinition($nodeType, $fields);

        return parent::__construct([
            'name' => $nodeType->getFqdnContentName(),
            // todo add support to have a node type description in YAML
            'description' => $nodeType->getName(),
            'fields' => $fields,
        ]);
    }

    protected function prepareSystemPropertiesDefinition(TypeResolver $typeResolver )
    {
        return [
            'id' => [
                'type' => $typeResolver->get(Uuid::class),
                'description' => 'The identifier of this node (not the technical id)',
                'resolve' => function (AccessibleObject $wrappedNode) {
                    /** @var NodeInterface $node */
                    $node = $wrappedNode->getObject();
                    return $node->getIdentifier();
                }
            ],
            'createdAt' => [
                'type' => $typeResolver->get(DateTime::class),
                'description' => 'The identifier of this node (not the technical id)',
                'resolve' => function (AccessibleObject $wrappedNode) {
                    /** @var NodeInterface $node */
                    $node = $wrappedNode->getObject();
                    return $node->getNodeData()->getCreationDateTime();
                }
            ],
            'updatedAt' => [
                'type' => $typeResolver->get(DateTime::class),
                'description' => 'The identifier of this node (not the technical id)',
                'resolve' => function (AccessibleObject $wrappedNode) {
                    /** @var NodeInterface $node */
                    $node = $wrappedNode->getObject();
                    return $node->getNodeData()->getLastModificationDateTime();
                }
            ],
        ];
    }

    protected function preparePropertiesDefinition(Model\NodeType $nodeType, array &$fields)
    {
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
    }
}
