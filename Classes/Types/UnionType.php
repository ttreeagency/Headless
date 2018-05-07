<?php
declare(strict_types=1);

namespace Ttree\Headless\Types;

use Neos\ContentRepository\Domain\Model as CR;
use Wwwision\GraphQL\TypeResolver;
use GraphQL\Type\Definition;

class UnionType extends Definition\UnionType
{
    public function __construct(TypeResolver $typeResolver, string $name, array $configuration)
    {
        parent::__construct([
            'name' => $name,
            'description' => $configuration['description'],
            'types' => array_map(function (CR\NodeType $nodeType) use ($typeResolver) {
                return $typeResolver->get([ Node::class, $nodeType->getName() ], $nodeType);
            }, $configuration['types']),
        ]);
    }
}
