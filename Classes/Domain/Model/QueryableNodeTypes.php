<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

use Generator;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
final class QueryableNodeTypes
{
    /**
     * @var NodeTypeManager
     * @Flow\Inject
     */
    protected $nodeTypeManager;

    public function iterate(): Generator
    {
        /** @var NodeType $nodeType */
        foreach ($this->nodeTypeManager->getSubNodeTypes('Ttree.Headless:Queryable') as $nodeType) {
            yield $nodeType;
        }
    }
}
