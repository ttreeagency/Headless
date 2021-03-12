<?php
declare(strict_types=1);

namespace Ttree\Headless\Types;

use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\Type;
use Neos\ContentRepository\Domain\Model as CR;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Ttree\Headless\CustomType\CustomFieldInterface;
use Ttree\Headless\CustomType\CustomFieldTypeInterface;
use Ttree\Headless\Domain\Model as Model;
use Ttree\Headless\Domain\Model\SimplePropertyDefinition;
use Ttree\Headless\Types\Scalars\DateTime;
use Ttree\Headless\Types\Scalars\Uuid;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use function array_filter;

trait NodeTrait
{
    /**
     * @var \Neos\Media\Domain\Service\ThumbnailService
     * @Flow\Inject(lazy=false)
     */
    protected $thumbnailService;

    /**
     * @var \Neos\Flow\ResourceManagement\ResourceManager
     * @Flow\Inject(lazy=false)
     */
    protected $resourceManager;

    protected function fields(TypeResolver $typeResolver, Model\NodeTypeWrapper $nodeTypeWrapper): array
    {
        $fields = $this->prepareSystemPropertiesDefinition($typeResolver, $nodeTypeWrapper);
        $fields = $this->preparePropertiesDefinition($typeResolver, $nodeTypeWrapper, $fields);
        $fields = $this->prepareCustomPropertiesDefinition($typeResolver, $nodeTypeWrapper, $fields);
        return $this->removeExcludedProperties($nodeTypeWrapper, $fields);
    }

    protected function prepareSystemPropertiesDefinition(TypeResolver $typeResolver, Model\NodeTypeWrapper $nodeTypeWrapper): array
    {
        if ($nodeTypeWrapper->getConfiguration('options.Ttree:Headless.disableSystemProperties') === true) return [];

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
            if (!isset($propertyConfiguration['type']) || $propertyName[0] === '_') continue;
            /** @var Type|NullableType $type */
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
                    $fields[$propertyName] = SimplePropertyDefinition::create($type, $propertyName, $propertyName)->get();
                    break;
                case 'Neos\Media\Domain\Model\ImageInterface':
                    $fields[$propertyName] = Model\ImagePropertyDefinition::create($type, $propertyName, $propertyName, $this->thumbnailService, $this->resourceManager);
                    break;
                case 'Neos\Media\Domain\Model\Asset':
                    // @todo implement support for Asset
                    break;
                case 'array<Neos\Media\Domain\Model\Asset>':
                    // @todo implement support for array of Assets
                    break;
                case 'reference':
                    // @todo implement reference support
                    break;
                case 'references':
                    // @todo implement references support
                    break;
                default:
                    throw new Exception('Unsupported type exception', 1510943187);
            }
        }
        return $fields;
    }

    protected function removeExcludedProperties(Model\NodeTypeWrapper $nodeTypeWrapper, array $fields): array
    {
        $excludedProperties = $nodeTypeWrapper->getConfiguration('options.Ttree:Headless.excludedProperties') ?: [];
        foreach ($excludedProperties as $propertyName) {
            unset($fields[$propertyName]);
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
}
