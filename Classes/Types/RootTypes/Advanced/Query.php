<?php

namespace Ttree\Headless\Types\RootTypes\Advanced;

use GraphQL\Type\Definition\ObjectType;
use Ttree\Headless\Types\RootTypes\Advanced\QueryFields\Base;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;

class Query extends ObjectType
{
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
