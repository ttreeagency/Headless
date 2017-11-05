<?php

namespace Ttree\Headless\Domain\Service;

use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Annotations as Flow;
use Ttree\Headless\Domain\Model\NodeTypeQuery;
use Wwwision\GraphQL\TypeResolver;

/**
 * @Flow\Scope("singleton")
 */
class QueryCompiler
{
    /**
     * @var NodeTypeManager
     * @Flow\Inject
     */
    protected $nodeTypeManager;

    public function build(TypeResolver $typeResolver): array
    {
        $queries = [];
        \array_filter(\array_map(function (NodeType $nodeType) use ($typeResolver, &$queries) {
            if ($nodeType->getName() === 'unstructured') {
                return null;
            }
            $queries = \array_merge($queries, NodeTypeQuery::create($nodeType)->queries($typeResolver));
        }, $this->nodeTypeManager->getNodeTypes(false)));
        return $queries;
    }
}
