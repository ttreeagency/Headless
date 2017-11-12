<?php

namespace Ttree\Headless\Types\RootTypes\Simple;

use GraphQL\Type\Definition\ObjectType;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Ttree\Headless\Domain\Model\NodeTypeQuery;
use Ttree\Headless\Domain\Service\FieldCompiler;
use Ttree\Headless\Types\NodeType;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;

class Query extends ObjectType
{
    /**
     * @var NodeTypeManager
     * @Flow\Inject
     */
    protected $nodeTypeManager;

    public function __construct(TypeResolver $typeResolver)
    {
        $builder = new FieldCompiler();
        parent::__construct([
            'name' => 'Query',
            'description' => 'Root queries for the Neos Content Repository',
            'fields' => \array_merge(
                $builder->build($typeResolver)
            )
        ]);
    }
}
