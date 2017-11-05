<?php
namespace Ttree\Headless\Types\RootTypes;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Neos\Domain\Service\NodeSearchService;
use Ttree\Headless\Types\RootTypes\QueryFields\Base;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Ttree\Headless\Types\Context;
use Ttree\Headless\Types\InputTypes\NodeIdentifierOrPath;
use Ttree\Headless\Types\Node;
use Ttree\Headless\Types\NodeType;
use Ttree\Headless\Types\Scalars;
use Ttree\Headless\Types\Workspace;

/**
 * A GraphQL root definition for all queries on the root level
 */
class Query extends ObjectType
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
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        /** @noinspection PhpUnusedParameterInspection */
        return parent::__construct([
            'name' => 'Query',
            'description' => 'Root queries for the Neos Content Repository',
            'fields' => \array_merge(
                Base::fields($typeResolver)
            )
        ]);
    }
}
