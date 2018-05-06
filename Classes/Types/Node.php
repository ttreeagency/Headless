<?php
declare(strict_types=1);

namespace Ttree\Headless\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Neos\ContentRepository\Domain\Model as CR;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Neos\Media\Domain\Service\ThumbnailService;
use Ttree\Headless\CustomType\CustomFieldInterface;
use Ttree\Headless\CustomType\CustomFieldTypeInterface;
use Ttree\Headless\Domain\Model as Model;
use Ttree\Headless\Types\Scalars\DateTime;
use Ttree\Headless\Types\Scalars\Uuid;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\TypeResolver;

class Node extends ObjectType
{
    /**
     * @var ThumbnailService
     * @Flow\Inject
     */
    protected $thumbnailService;

    /**
     * @var ResourceManager
     * @Flow\Inject
     */
    protected $resourceManager;

    /**
     * @param TypeResolver $typeResolver
     * @param CR\NodeType $nodeType
     * @throws Exception
     */
    public function __construct(TypeResolver $typeResolver, CR\NodeType $nodeType)
    {
        $nodeType = new Model\NodeType($nodeType);

        $fields = $this->prepareSystemPropertiesDefinition($typeResolver);
        $this->preparePropertiesDefinition($typeResolver, $nodeType, $fields);
        $this->prepareCustomPropertiesDefinition($typeResolver, $nodeType, $fields);

        $config = [
            'name' => $nodeType->getFqdnContentName(),
            // todo add support to have a node type description in YAML
            'description' => $nodeType->getName(),
            'fields' => $fields,
        ];

        parent::__construct($config);
    }

    protected function prepareSystemPropertiesDefinition(TypeResolver $typeResolver)
    {
        return [
            'id' => [
                'type' => $typeResolver->get(Uuid::class),
                'description' => 'The identifier of this node (not the technical id)',
                'resolve' => function (AccessibleObject $wrappedNode) {
                    /** @var CR\NodeInterface $node */
                    $node = $wrappedNode->getObject();
                    return $node->getIdentifier();
                }
            ],
            'createdAt' => [
                'type' => $typeResolver->get(DateTime::class),
                'description' => 'The identifier of this node (not the technical id)',
                'resolve' => function (AccessibleObject $wrappedNode) {
                    /** @var CR\NodeInterface $node */
                    $node = $wrappedNode->getObject();
                    return $node->getNodeData()->getCreationDateTime();
                }
            ],
            'updatedAt' => [
                'type' => $typeResolver->get(DateTime::class),
                'description' => 'The identifier of this node (not the technical id)',
                'resolve' => function (AccessibleObject $wrappedNode) {
                    /** @var CR\NodeInterface $node */
                    $node = $wrappedNode->getObject();
                    return $node->getNodeData()->getLastModificationDateTime();
                }
            ],
        ];
    }

    protected function preparePropertiesDefinition(TypeResolver $typeResolver, Model\NodeType $nodeType, array &$fields)
    {
        foreach ($nodeType->getProperties() as $propertyName => $propertyConfiguration) {
            if (!isset($propertyConfiguration['type']) || $propertyName[0] === '_') {
                continue;
            }
            /** @var Type $type */
            $type = (new Model\TypeMapper($propertyConfiguration['type']))->convert($typeResolver);
            if ($type === null) {
                continue;
            }
            switch ($propertyConfiguration['type']) {
                case 'string':
                case 'integer':
                case 'boolean':
                case 'array':
                case 'DateTime':
                    $fields[$propertyName] = $this->prepareSimplePropertyDefinition($type, $propertyName);
                    break;
                case 'Neos\Media\Domain\Model\ImageInterface':
                    $fields[$propertyName] = $this->prepareImagePropertyDefinition($type, $propertyName);
                    break;
                case 'Neos\Media\Domain\Model\Asset':
                    // todo implement
                    break;
                case 'array<Neos\Media\Domain\Model\Asset>':
                    // todo implement
                    break;
                case 'reference':
                    // todo implement
                    break;
                case 'references':
                    // todo implement
                    break;
                default:
                    throw new Exception('Unsupported type exception', 1510943187);
            }
        }
    }

    protected function prepareCustomPropertiesDefinition(TypeResolver $typeResolver, Model\NodeType $nodeType, array &$fields)
    {
        $customProperties = $nodeType->getConfiguration('options.Ttree:Headless.properties') ?: [];
        foreach ($customProperties as $propertyName => $propertyConfiguration) {
            $fields[$propertyName] = $this->prepareCustomPropertyDefinition($typeResolver, $nodeType, $propertyName, $propertyConfiguration);
        }
    }

    protected function prepareCustomPropertyDefinition(TypeResolver $typeResolver, Model\NodeType $nodeType, string $propertyName, array $configuration)
    {
        /** @var CustomFieldTypeInterface|CustomFieldInterface $customType */
        $customType = new $configuration['implementation'];
        return [
            'type' => $customType->type($typeResolver, $nodeType->getNodeType()),
            'description' => $customType->description($nodeType->getNodeType()),
            'resolve' => $customType->resolve($nodeType->getNodeType())
        ];
    }

    protected function prepareSimplePropertyDefinition(Type $type, string $propertyName)
    {
        return [
            'type' => $type,
            // todo add support to have a property description in YAML
            'description' => $propertyName,
            'resolve' => function (AccessibleObject $wrappedNode) use ($propertyName) {
                /** @var CR\NodeInterface $node */
                $node = $wrappedNode->getObject();
                return $node->getProperty($propertyName);
            }
        ];
    }

    protected function prepareImagePropertyDefinition(Type $type, string $propertyName)
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
                'allowCropping' => ['type' => Type::boolean(), 'description' => 'Whether the image should be cropped if the given sizes would hurt the aspect ratio'],
                'allowUpScaling' => ['type' => Type::boolean(), 'description' => 'Whether the resulting image size might exceed the size of the original image'],
            ],
            'resolve' => function (AccessibleObject $wrappedNode, array $args) use ($propertyName) {
                /** @var CR\NodeInterface $node */
                $node = $wrappedNode->getObject();
                $image = $node->getProperty($propertyName);
                if (!$image) {
                    return null;
                }
                $args = \array_filter($args);
                if ($args !== []) {
                    $configuration = new ThumbnailConfiguration($args['width'] ?? null, $args['maximumWidth'] ?? null, $args['height'] ?? null, $args['maximumHeight'] ?? null, $args['allowCropping'] ?? false, $args['allowUpScaling'] ?? false);
                    $image = $this->thumbnailService->getThumbnail($image, $configuration);
                }
                $url = $this->resourceManager->getPublicPersistentResourceUri($image->getResource());
                return new AccessibleObject(new class($image, $url) {
                    public $width;
                    public $height;
                    public $url;
                    public function __construct(ImageInterface $image, string $url)
                    {
                        $this->width = $image->getWidth();
                        $this->height = $image->getHeight();
                        $this->url = $url;
                    }
                });
            }
        ];
    }
}
