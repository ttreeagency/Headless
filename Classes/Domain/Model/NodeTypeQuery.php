<?php

namespace Ttree\Headless\Domain\Model;

use GraphQL\Type\Definition\Type;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\ContextFactory;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Utility\Arrays;
use Ttree\Headless\Types\Node;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Ttree\Headless\Types\Scalars;
use Neos\Flow\Annotations as Flow;

final class NodeTypeQuery
{
    /**
     * @var array
     * @Flow\InjectConfiguration(path="namespaceMapping")
     */
    protected $namespaceMapping;

    /**
     * @var ContextFactory
     * @Flow\Inject
     */
    protected $contextFactory;

    /**
     * @var NodeType
     */
    protected $nodeType;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var QueryName
     */
    protected $queryName;

    public static function create(NodeType $nodeType) :NodeTypeQuery
    {
        $nodeTypeQuery = new static();
        $nodeTypeQuery->nodeType = $nodeType;
        list($nodeTypeQuery->namespace, $nodeTypeQuery->type) = Arrays::trimExplode(':', $nodeType->getName());
        $nodeTypeQuery->generateAlias();
        $nodeTypeQuery->generateQueryName();

        return $nodeTypeQuery;
    }

    public function queries(TypeResolver $typeResolver) :array {
        $queryName = $this->queryName->getName();
        $allQueryName = 'all' . (new Plural($this->queryName->getName()))->getPlural();

        return [
            $queryName => [
                'type' => $typeResolver->get(Node::class),
                'description' => \vsprintf('Get a specific content node of type "%s"', [$this->nodeType->getName()]),
                'args' => [
                    'identifier' => ['type' => $typeResolver->get(Scalars\Uuid::class)],
                    'path' => ['type' => $typeResolver->get(Scalars\AbsoluteNodePath::class)],
                ],
                'resolve' => function ($_, array $args) {
                    $defaultContext = $this->contextFactory->create();
                    if (isset($args['identifier'])) {
                        return new AccessibleObject($defaultContext->getNodeByIdentifier($args['identifier']));
                    } elseif (isset($args['path'])) {
                        return new AccessibleObject($defaultContext->getNode($args['path']));
                    }
                    throw new \InvalidArgumentException('node path or identifier have to be specified!', 1460064707);
                }
            ],
            $allQueryName => [
                'type' => Type::listOf($typeResolver->get(Node::class)),
                'description' => \vsprintf('Get all content node of type "%s"', [$this->nodeType->getName()]),
                'resolve' => function ($_, array $args) {
                    $defaultContext = $this->contextFactory->create();
                    $query = new FlowQuery([$defaultContext->getRootNode()]);
                    $query = $query->find(\vsprintf('[instanceof %s]', [$this->nodeType->getName()]));

                    return new IterableAccessibleObject($query->get());
                }
            ]
        ];
    }

    protected function generateAlias()
    {
        if ($this->namespaceMapping['*'] === $this->namespace) {
            $this->alias = $this->type;
            return;
        }

        if (isset($this->namespaceMapping[$this->namespace])) {
            $this->alias = $this->namespaceMapping[$this->namespace] . ':' . $this->type;
            return;
        }

        $this->alias = $this->namespace . ':' . $this->type;
    }

    protected function generateQueryName() {
        $this->queryName = new QueryName($this->alias);
    }
}
