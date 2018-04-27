<?php

namespace Ttree\Headless\Domain\Generator;


use GraphQL\Type\Definition\Type;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\ContextFactory;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Eel\FlowQuery\FlowQuery;
use Ttree\Headless\Domain\Factory\NodeFactory;
use Ttree\Headless\Domain\Model\ContentNamespace;
use Ttree\Headless\Domain\Model\Plural;
use Ttree\Headless\Domain\Model\QueryableNodeTypes;
use Ttree\Headless\Types\Scalars;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;

class ObjectTypeFields
{
    /**
     * @var ContextFactory
     * @Flow\Inject
     */
    protected $contextFactory;

    /**
     * @var QueryableNodeTypes
     * @Flow\Inject
     */
    protected $queryableNodeTypes;

    /**
     * @var NodeFactory
     * @Flow\Inject
     */
    protected $nodeFactory;

    /**
     * @var ContentNamespace
     */
    protected $contentNamespace;

    public static function createByNamespace(TypeResolver $typeResolver, ContentNamespace $namespace): array
    {
        $self = new ObjectTypeFields();
        $self->contentNamespace = $namespace;
        return $self->fields($typeResolver);
    }

    protected function singleFieldDefinition(TypeResolver $typeResolver, string $nodeTypeShortName, NodeType $nodeType)
    {
        $type = $this->nodeFactory->create($typeResolver, $nodeType);
        return [
            'type' => $type,
            'args' => [
                'identifier' => ['type' => $typeResolver->get(Scalars\Uuid::class)],
                'path' => ['type' => $typeResolver->get(Scalars\AbsoluteNodePath::class)],
            ],
            'description' => $nodeTypeShortName . ' content type',
            'resolve' => function ($_, array $args) {
                $context = $this->contextFactory->create();
                //  @todo enfore node type
                if (isset($args['identifier'])) {
                    return new AccessibleObject($context->getNodeByIdentifier($args['identifier']));
                } elseif (isset($args['path'])) {
                    return new AccessibleObject($context->getNode($args['path']));
                }
                throw new \InvalidArgumentException('node path or identifier have to be specified!', 1460064707);
            }
        ];
    }

    protected function fields(TypeResolver $typeResolver)
    {
        $fields = [];
        /** @var NodeType $nodeType */
        foreach ($this->queryableNodeTypes->iterate() as $nodeType) {
            list($namespace, $name) = explode(':', $nodeType->getName());
            if ($namespace !== $this->contentNamespace->getRaw()) {
                continue;
            }
            $name = str_replace('.', '', $name);
            $fields[(string)$name] = $this->singleFieldDefinition($typeResolver, $name, $nodeType);
            $fields[$this->allRecordsFieldName($name)] = $this->allRecordsFieldDefinition($typeResolver, $name, $nodeType);
        }
        return $fields;
    }

    protected function allRecordsFieldName(string $name)
    {
        return 'all' . (string)(new Plural($name));
    }

    protected function allRecordsFieldDefinition(TypeResolver $typeResolver, string $nodeTypeShortName, NodeType $nodeType)
    {
        $type = $this->nodeFactory->create($typeResolver, $nodeType);
        return [
            'type' => Type::listOf($type),
            'args' => [
                'parentIdentifier' => ['type' => $typeResolver->get(Scalars\Uuid::class)],
                'parentPath' => ['type' => $typeResolver->get(Scalars\AbsoluteNodePath::class)],
            ],
            'description' => 'All ' . $nodeTypeShortName . 'content types',
            'resolve' => function ($_, array $args) use ($nodeType) {
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
            }
        ];
    }
}
