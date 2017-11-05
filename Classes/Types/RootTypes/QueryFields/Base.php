<?php
namespace Ttree\Headless\Types\RootTypes\QueryFields;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Neos\Domain\Service\NodeSearchService;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Ttree\Headless\Types\Context;
use Ttree\Headless\Types\InputTypes\NodeIdentifierOrPath;
use Ttree\Headless\Types\Node;
use Ttree\Headless\Types\NodeType;
use Ttree\Headless\Types\Scalars;
use Ttree\Headless\Types\Workspace;

final class Base
{

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var NodeSearchService
     */
    protected $nodeSearchService;

    /**
     * @var array
     */
    protected $fields = [];

    public static function fields(TypeResolver $typeResolver): array
    {
        $queryFields = new static();
        $queryFields->fields = [
            'context' => [
                'type' => $typeResolver->get(Context::class),
                'args' => [
                    'workspaceName' => ['type' => Type::string(), 'description' => 'The name of the workspace'],
                    'currentDateTime' => ['type' => $typeResolver->get(Scalars\DateTime::class), 'description' => 'The date and time to simulate (ISO 8601 format)'],
                    'dimensions' => ['type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class), 'description' => 'Dimensions and their values'],
                    'targetDimensions' => ['type' => Type::string(), 'description' => 'Dimensions and their values when creating new nodes'],
                    'invisibleContentShown' => ['type' => Type::boolean(), 'description' => 'Whether nodes marked "hidden" should be shown in this context'],
                    'removedContentShown' => ['type' => Type::boolean(), 'description' => 'Whether nodes marked "hidden" should be shown in this context'],
                    'inaccessibleContentShown' => ['type' => Type::boolean(), 'description' => 'Whether nodes marked "hidden" should be shown in this context'],
                ],
                'resolve' => function ($_, $contextConfiguration) use ($queryFields) {
                    $context = $queryFields->contextFactory->create($contextConfiguration);
                    return new AccessibleObject($context);
                },
            ],

            'node' => [
                'type' => $typeResolver->get(Node::class),
                'args' => [
                    'identifier' => ['type' => $typeResolver->get(Scalars\Uuid::class)],
                    'path' => ['type' => $typeResolver->get(Scalars\AbsoluteNodePath::class)],
                ],
                'resolve' => function ($_, array $args) use ($queryFields) {
                    $defaultContext = $queryFields->contextFactory->create();
                    if (isset($args['identifier'])) {
                        return new AccessibleObject($defaultContext->getNodeByIdentifier($args['identifier']));
                    } elseif (isset($args['path'])) {
                        return new AccessibleObject($defaultContext->getNode($args['path']));
                    }
                    throw new \InvalidArgumentException('node path or identifier have to be specified!', 1460064707);
                }
            ],

            'rootNode' => [
                'type' => $typeResolver->get(Node::class),
                'resolve' => function ($_) use ($queryFields) {
                    $defaultContext = $queryFields->contextFactory->create();
                    return new AccessibleObject($defaultContext->getRootNode());
                },
            ],

            'nodesOnPath' => [
                'type' => Type::listOf($typeResolver->get(Node::class)),
                'args' => [
                    'start' => ['type' => Type::nonNull($typeResolver->get(Scalars\AbsoluteNodePath::class))],
                    'end' => ['type' => Type::nonNull($typeResolver->get(Scalars\AbsoluteNodePath::class))],
                ],
                'resolve' => function ($_, array $args) use ($queryFields) {
                    $defaultContext = $queryFields->contextFactory->create();

                    return new IterableAccessibleObject($defaultContext->getNodesOnPath($args['start'], $args['end']));
                }
            ],

            'workspace' => [
                'type' => $typeResolver->get(Workspace::class),
                'description' => 'A Content Repository workspace',
                'args' => [
                    'name' => ['type' => Type::nonNull(Type::string()), 'description' => 'Name of the workspace to retrieve'],
                ],
                'resolve' => function ($_, array $args) use ($queryFields) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $workspace = $queryFields->workspaceRepository->findOneByName($args['name']);

                    if ($workspace === null) {
                        throw new \InvalidArgumentException(sprintf('A workspace named "%s" could not be found.', $args['name']), 1461323974);
                    }

                    return new AccessibleObject($workspace);
                }
            ],

            'workspaces' => [
                'type' => Type::listOf($typeResolver->get(Workspace::class)),
                'description' => 'A list of all Content Repository workspaces',
                'resolve' => function ($_) use ($queryFields) {
                    return new IterableAccessibleObject($queryFields->workspaceRepository->findAll());
                }
            ],

            'nodeType' => [
                'type' => $typeResolver->get(NodeType::class),
                'description' => 'The specified node type (which could be abstract)',
                'args' => [
                    'nodeTypeName' => ['type' => Type::nonNull(Type::string()), 'description' => 'The node type identifier'],
                ],
                'resolve' => function ($_, array $args) use ($queryFields) {
                    return new AccessibleObject($queryFields->nodeTypeManager->getNodeType($args['nodeTypeName']));
                }
            ],

            'nodeTypes' => [
                'type' => Type::listOf($typeResolver->get(NodeType::class)),
                'description' => 'A list of all registered node types',
                'args' => [
                    'includeAbstractNodeTypes' => ['type' => Type::boolean(), 'description' => 'Whether to include abstract node types, defaults to TRUE'],
                ],
                'resolve' => function ($_, array $args) use ($queryFields) {
                    $includeAbstractNodeTypes = isset($args['includeAbstractNodeTypes']) ? $args['includeAbstractNodeTypes'] : true;
                    return new IterableAccessibleObject($queryFields->nodeTypeManager->getNodeTypes($includeAbstractNodeTypes));
                }
            ],

            'hasNodeType' => [
                'type' => Type::boolean(),
                'description' => 'Whether the specified node type is registered (including abstract node types)',
                'args' => [
                    'nodeTypeName' => ['type' => Type::nonNull(Type::string()), 'description' => 'The node type identifier'],
                ],
                'resolve' => function ($_, array $args) use ($queryFields) {
                    return $queryFields->nodeTypeManager->hasNodeType($args['nodeTypeName']);
                }
            ],

            'nodesByProperties' => [
                'type' => Type::listOf($typeResolver->get(Node::class)),
                'description' => 'Find nodes recursively in the default context, using the NodeSearchService',
                'args' => [
                    'term' => ['type' => Type::nonNull(Type::string()), 'description' => 'Arbitrary search term'],
                    'searchNodeTypes' => ['type' => Type::nonNull(Type::listOf(Type::string())), 'description' => 'Simple array of Node type names to include in the search result'],
                    'startingPoint' => ['type' => $typeResolver->get(NodeIdentifierOrPath::class), 'description' => 'Optional starting point for the search'],
                ],
                'resolve' => function ($_, array $args) use ($queryFields) {
                    $defaultContext = $queryFields->contextFactory->create();
                    $startingPoint = isset($args['startingPoint']) ? NodeIdentifierOrPath::getNodeFromContext($defaultContext, $args['startingPoint']) : null;
                    return new IterableAccessibleObject($queryFields->nodeSearchService->findByProperties($args['term'], $args['searchNodeTypes'], $defaultContext, $startingPoint));
                }
            ],
        ];

        return $queryFields->fields;
    }
}
