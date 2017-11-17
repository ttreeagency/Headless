<?php

namespace Ttree\Headless\Domain\Service;


use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\ContextFactory;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Core\ApplicationContext;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\ObjectManagement\ObjectManager;
use Ttree\Headless\Domain\Model\ContentNamespace;
use Ttree\Headless\Domain\Model\Plural;
use Ttree\Headless\Types\NamespacedNode;
use Ttree\Headless\Types\Scalars;
use Ttree\Headless\Types\Node;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;

class NamespacedObjectTypeFields
{
    /**
     * @var ContextFactory
     * @Flow\Inject
     */
    protected $contextFactory;

    /**
     * @var NodeTypeManager
     * @Flow\Inject
     */
    protected $nodeTypeManager;

    /**
     * @var NamespacedNodeFactory
     * @Flow\Inject
     */
    protected $namespacedNodeFactory;

    /**
     * @var ContentNamespace
     */
    protected $contentNamespace;

    public static function createByPackage(TypeResolver $typeResolver, ContentNamespace $namespace): array
    {
        $self = new NamespacedObjectTypeFields();
        $self->contentNamespace = $namespace;
        return $self->fields($typeResolver);
    }

    protected function singleFieldDefinition(TypeResolver $typeResolver, string $nodeTypeShortName, NodeType $nodeType)
    {
        $type = $this->namespacedNodeFactory->create($typeResolver, $nodeType);
        return [
            'type' => $type,
            'args' => [
                'identifier' => ['type' => $typeResolver->get(Scalars\Uuid::class)],
                'path' => ['type' => $typeResolver->get(Scalars\AbsoluteNodePath::class)],
            ],
            'description' => $nodeTypeShortName . ' content type',
            'resolve' => function ($_, array $args) {
                $defaultContext = $this->contextFactory->create();
                // todo enfore node type
                if (isset($args['identifier'])) {
                    return new AccessibleObject($defaultContext->getNodeByIdentifier($args['identifier']));
                } elseif (isset($args['path'])) {
                    return new AccessibleObject($defaultContext->getNode($args['path']));
                }
                throw new \InvalidArgumentException('node path or identifier have to be specified!', 1460064707);
            }
        ];
    }

    protected function fields(TypeResolver $typeResolver)
    {
        $fields = [];
        /** @var NodeType $nodeType */
        foreach ($this->nodeTypeManager->getNodeTypes(false) as $nodeType) {
            if ($nodeType->getName() === 'unstructured') {
                continue;
            }
            list ($nodeTypeNamespace, $nodeTypeShortName) = \explode(':', $nodeType->getName());
            if ($nodeTypeNamespace !== $this->contentNamespace->getRaw()) {
                continue;
            }
            $fields[(string)$nodeTypeShortName] = $this->singleFieldDefinition($typeResolver, $nodeTypeShortName, $nodeType);
            $fields[$this->allRecordsFieldName($nodeTypeShortName)] = $this->allRecordsFieldDefinition($typeResolver, $nodeTypeShortName, $nodeType);
        }
        return $fields;
    }

    protected function allRecordsFieldName(string $nodeTypeShortName)
    {
        return 'all' . (string)(new Plural($nodeTypeShortName));
    }

    protected function allRecordsFieldDefinition(TypeResolver $typeResolver, string $nodeTypeShortName, NodeType $nodeType)
    {
        $type = $this->namespacedNodeFactory->create($typeResolver, $nodeType);
        return [
            'type' => Type::listOf($type),
            'args' => [
                'parentIdentifier' => ['type' => $typeResolver->get(Scalars\Uuid::class)],
                'parentPath' => ['type' => $typeResolver->get(Scalars\AbsoluteNodePath::class)],
            ],
            'description' => 'All ' . $nodeTypeShortName . 'content types',
            'resolve' => function ($_, array $args) use ($nodeType) {
                $defaultContext = $this->contextFactory->create();
                if (isset($args['parentIdentifier'])) {
                    $parentNode = $defaultContext->getNodeByIdentifier($args['parentIdentifier']);
                } else {
                    $parentNode = isset($args['parentPath']) ? $defaultContext->getNode($args['parentPath']) : $defaultContext->getRootNode();
                }
                $query = new FlowQuery([$parentNode]);

                return new IterableAccessibleObject($query->find('[instanceof ' . $nodeType->getName() . ']')->get());
            }
        ];
    }
}
