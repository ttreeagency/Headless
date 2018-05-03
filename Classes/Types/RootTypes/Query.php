<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\RootTypes;

use GraphQL\Type\Definition\ObjectType;
use Ttree\Headless\Domain\Generator\QueryDefinition;
use Wwwision\GraphQL\TypeResolver;

class Query extends ObjectType
{
    public function __construct(TypeResolver $typeResolver)
    {
        $definition = new QueryDefinition();
        parent::__construct([
            'name' => 'Query',
            'description' => 'Root queries for the Neos Content Repository',
            'fields' => $definition->fields($typeResolver)
        ]);
    }
}
