<?php
declare(strict_types=1);

namespace Ttree\Headless\Types;

use GraphQL\Type\Definition\Type;
use Neos\ContentRepository\Domain\Model as CR;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Ttree\Headless\CustomType\CustomFieldInterface;
use Ttree\Headless\CustomType\CustomFieldTypeInterface;
use Ttree\Headless\Domain\Model as Model;
use Ttree\Headless\Types\Scalars\DateTime;
use Ttree\Headless\Types\Scalars\Uuid;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\TypeResolver;

trait NodeTrait
{
    /**
     * @var \Neos\Media\Domain\Service\ThumbnailService
     * @Flow\Inject
     */
    protected $thumbnailService;

    /**
     * @var \Neos\Flow\ResourceManagement\ResourceManager
     * @Flow\Inject
     */
    protected $resourceManager;

    protected function fields(TypeResolver $typeResolver, Model\NodeTypeWrapper $nodeTypeWrapper): array
    {
        $fields = $this->prepareSystemPropertiesDefinition($typeResolver);
        $fields = $this->preparePropertiesDefinition($typeResolver, $nodeTypeWrapper, $fields);
        $fields = $this->prepareCustomPropertiesDefinition($typeResolver, $nodeTypeWrapper, $fields);
        return $fields;
    }

    protected function prepareSystemPropertiesDefinition(TypeResolver $typeResolver): array
    {
        return [
            'id' => [
                'type' => Type::nonNull($typeResolver->get(Uuid::class)),
                'description' => 'The identifier of this node',
                'resolve' => function (AccessibleObject $wrappedNode) {
                    /** @var CR\NodeInterface $node */
                    $node = $wrappedNode->getObject();
                    return $node->getIdentifier();
                }
            ],
            'createdAt' => [
                'type' => Type::nonNull($typeResolver->get(DateTime::class)),
                'description' => 'The creation date of the current node',
                'resolve' => function (AccessibleObject $wrappedNode) {
                    /** @var CR\NodeInterface $node */
                    $node = $wrappedNode->getObject();
                    return $node->getNodeData()->getCreationDateTime();
                }
            ],
            'updatedAt' => [
                'type' => Type::nonNull($typeResolver->get(DateTime::class)),
                'description' => 'The last modification date of the current node',
                'resolve' => function (AccessibleObject $wrappedNode) {
                    /** @var CR\NodeInterface $node */
                    $node = $wrappedNode->getObject();
                    return $node->getNodeData()->getLastModificationDateTime();
                }
            ],
        ];
    }

    protected function isPropertyRequired(Model\NodeTypeWrapper $nodeTypeWrapper, string $propertyName)
    {
        $validations = $nodeTypeWrapper->getNodeType()->getConfiguration('properties.' . $propertyName . '.validation') ?: [];
        return isset($validations['Neos.Neos/Validation/NotEmptyValidator']);
    }

    protected function preparePropertiesDefinition(TypeResolver $typeResolver, Model\NodeTypeWrapper $nodeTypeWrapper, array $fields): array
    {
        foreach ($nodeTypeWrapper->getProperties() as $propertyName => $propertyConfiguration) {
            if (!isset($propertyConfiguration['type']) || $propertyName[0] === '_') {
                continue;
            }
            /** @var Type $type */
            $type = (new Model\TypeMapper($propertyConfiguration['type']))->convert($typeResolver);
            if ($type === null) {
                continue;
            }
            if ($this->isPropertyRequired($nodeTypeWrapper, $propertyName)) {
                $type = Type::nonNull($type);
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
        return $fields;
    }

    protected function prepareCustomPropertiesDefinition(TypeResolver $typeResolver, Model\NodeTypeWrapper $nodeTypeWrapper, array $fields): array
    {
        $customProperties = $nodeTypeWrapper->getConfiguration('options.Ttree:Headless.properties') ?: [];
        foreach ($customProperties as $propertyName => $propertyConfiguration) {
            $fields[$propertyName] = $this->prepareCustomPropertyDefinition($typeResolver, $nodeTypeWrapper, $propertyName, $propertyConfiguration);
        }
        return $fields;
    }

    protected function prepareCustomPropertyDefinition(TypeResolver $typeResolver, Model\NodeTypeWrapper $nodeTypeWrapper, string $propertyName, array $configuration): array
    {
        $options = $configuration['options'] ?? [];
        /** @var CustomFieldTypeInterface|CustomFieldInterface $className */
        $className = new $configuration['class']($options);

        return [
            'type' => $className->type($typeResolver, $nodeTypeWrapper->getNodeType()),
            'args' => $className->args($typeResolver),
            'description' => $className->description($nodeTypeWrapper->getNodeType()),
            'resolve' => $className->resolve($nodeTypeWrapper->getNodeType())
        ];
    }

    protected function prepareSimplePropertyDefinition(Type $type, string $propertyName): array
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
