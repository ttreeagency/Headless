<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

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

    public function iterate(): \Generator
    {
        /** @var \Neos\ContentRepository\Domain\Model\NodeType $nodeType */
        foreach ($this->nodeTypeManager->getNodeTypes(false) as $nodeType) {
            if ($nodeType->isOfType('Ttree.Headless:Queryable') === false) {
               continue;
            }
            yield $nodeType;
        }
    }
}
