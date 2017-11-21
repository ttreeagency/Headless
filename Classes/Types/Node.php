<?php
namespace Ttree\Headless\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Exception;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Neos\Media\Domain\Service\ThumbnailService;
use Ttree\Headless\Domain\Model\TypeMapper;
use Ttree\Headless\Types\Scalars\DateTime;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Ttree\Headless\Types\Scalars\AbsoluteNodePath;
use Ttree\Headless\Types\Scalars\Uuid;
use Neos\ContentRepository\Domain\Model as CR;
use Ttree\Headless\Domain\Model as Model;

class Node extends ObjectType
{
    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

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

    public function __construct(TypeResolver $typeResolver, CR\NodeType $nodeType)
    {
        $nodeType = new Model\NodeType($nodeType);

        $fields = $this->prepareSystemPropertiesDefinition($typeResolver);
        $this->preparePropertiesDefinition($typeResolver, $nodeType, $fields);

        return parent::__construct([
            'name' => $nodeType->getFqdnContentName(),
            // todo add support to have a node type description in YAML
            'description' => $nodeType->getName(),
            'fields' => $fields,
        ]);
    }

    protected function prepareSystemPropertiesDefinition(TypeResolver $typeResolver)
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

    protected function preparePropertiesDefinition(TypeResolver $typeResolver, Model\NodeType $nodeType, array &$fields)
    {
        foreach ($nodeType->getProperties() as $propertyName => $propertyConfiguration) {
            if (!isset($propertyConfiguration['type']) || $propertyName[0] === '_') {
                continue;
            }
            $type = (new TypeMapper($propertyConfiguration['type']))->convert($typeResolver);
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
                    $fields[$propertyName] = $this->prepareAssetPropertyDefinition($type, $propertyName);
                    break;
                case 'array<Neos\Media\Domain\Model\Asset>':
                    $fields[$propertyName] = $this->prepareAssetListPropertyDefinition($type, $propertyName);
                    break;
                case 'reference':
                    $fields[$propertyName] = $this->prepareReferencePropertyDefinition($type, $propertyName);
                    break;
                case 'references':
                    $fields[$propertyName] = $this->prepareReferenceListPropertyDefinition($type, $propertyName);
                    break;
                default:
                    throw new Exception('Unsupported type exception', 1510943187);
            }
        }
    }

    protected function prepareSimplePropertyDefinition(Type $type, string $propertyName)
    {
        return [
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
                /** @var NodeInterface $node */
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

    protected function prepareAssetPropertyDefinition(Type $type, string $propertyName)
    {
        throw new Exception('Todo');
    }

    protected function prepareAssetListPropertyDefinition(Type $type, string $propertyName)
    {
        throw new Exception('Todo');
    }

    protected function prepareReferencePropertyDefinition(Type $type, string $propertyName)
    {
        throw new Exception('Todo');
    }

    protected function prepareReferenceListPropertyDefinition(Type $type, string $propertyName)
    {
        throw new Exception('Todo');
    }
}
