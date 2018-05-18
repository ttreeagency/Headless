<?php
declare(strict_types=1);

namespace Ttree\Headless\Types;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;
use Neos\ContentRepository\Domain\Model as CR;
use Neos\Flow\Exception;
use Ttree\Headless\CustomType\CustomFieldInterface;
use Ttree\Headless\CustomType\CustomFieldTypeInterface;
use Ttree\Headless\Domain\Model as Model;
use Ttree\Headless\Types\Scalars\DateTime;
use Ttree\Headless\Types\Scalars\Uuid;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\TypeResolver;

class NodeInterface extends InterfaceType
{
    use NodeTrait;

    /**
     * @param TypeResolver $typeResolver
     * @param CR\NodeType $nodeType
     * @throws Exception
     */
    public function __construct(TypeResolver $typeResolver, CR\NodeType $nodeType)
    {
        $nodeTypeWrapper = new Model\NodeTypeWrapper($nodeType);

        $fields = $this->fields($typeResolver, $nodeTypeWrapper);

        $config = [
            'name' => $nodeTypeWrapper->getTypeName(),
            // todo add support to have a node type description in YAML
            'description' => $nodeTypeWrapper->getName(),
            'fields' => $fields,
            'resolveType' => function (AccessibleObject $wrappedObject) use ($typeResolver) {
                $node = $wrappedObject->getObject();
                return $typeResolver->get([Node::class, $node->getNodeType()->getName()], $node->getNodeType());
            }
        ];

        parent::__construct($config);
    }

    protected function prepareSystemPropertiesDefinition(TypeResolver $typeResolver): array
    {
        return [
            'id' => [
                'type' => Type::nonNull($typeResolver->get(Uuid::class)),
                'description' => 'The identifier of this node (not the technical id)'
            ],
            'createdAt' => [
                'type' => Type::nonNull($typeResolver->get(DateTime::class)),
                'description' => 'The identifier of this node (not the technical id)'
            ],
            'updatedAt' => [
                'type' => Type::nonNull($typeResolver->get(DateTime::class)),
                'description' => 'The identifier of this node (not the technical id)'
            ],
        ];
    }

    protected function prepareCustomPropertyDefinition(TypeResolver $typeResolver, Model\NodeTypeWrapper $nodeTypeWrapper, string $propertyName, array $configuration): array
    {
        /** @var CustomFieldTypeInterface|CustomFieldInterface $className */
        $className = new $configuration['class'];
        return [
            'type' => $className->type($typeResolver, $nodeTypeWrapper->getNodeType()),
            'args' => $className->args($typeResolver),
            'description' => $className->description($nodeTypeWrapper->getNodeType())
        ];
    }

    protected function prepareSimplePropertyDefinition(Type $type, string $propertyName): array
    {
        return [
            'type' => $type,
            // todo add support to have a property description in YAML
            'description' => $propertyName
        ];
    }

    protected function prepareImagePropertyDefinition(Type $type, string $propertyName): array
    {
        return [
            'type' => $type,
            // todo add support to have a property description in YAML
            'description' => $propertyName,
            'args' => [
                'width' => ['type' => Type::int(), 'description' => 'Desired width of the image'],
                'maximumWidth' => ['type' => Type::int(), 'description' => 'Desired maximum width of the image'],
                'height' => ['type' => Type::int(), 'description' => 'Desired height of the image'],
                'maximumHeight' => ['type' => Type::int(), 'description' => 'Desired maximum height of the image'],
                'allowCropping' => [
                    'type' => Type::boolean(),
                    'defaultValue' => false,
                    'description' => 'Whether the image should be cropped if the given sizes would hurt the aspect ratio'
                ],
                'allowUpScaling' => [
                    'type' => Type::boolean(),
                    'defaultValue' => false,
                    'description' => 'Whether the resulting image size might exceed the size of the original image'
                ],
            ]
        ];
    }
}
