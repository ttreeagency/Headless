<?php

namespace Ttree\Headless\Types\RootTypes;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Ttree\Headless\Domain\Generator\QueryDefinition;
use Wwwision\GraphQL\TypeResolver;

class PingQuery extends ObjectType
{
    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'PingQuery',
            'fields' => [
                'ping' => [
                    'type' => Type::string(),
                    'resolve' => function () {
                        return 'pong';
                    },
                ],
            ],
        ]);
    }
}
