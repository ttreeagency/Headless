<?php
namespace Ttree\Headless\Types\InputTypes;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Wwwision\GraphQL\TypeResolver;
use Ttree\Headless\Types\Scalars;

class Package extends InputObjectType
{

    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'Package',
            'description' => 'Input type for the Package context',
            'fields' => [
                'packageKey' => ['type' => Type::string(), 'description' => 'Unique key of this package. Example for the Flow package: "Neos.Flow"']
            ],
        ]);
    }
}
