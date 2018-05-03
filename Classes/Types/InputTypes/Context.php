<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\InputTypes;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Ttree\Headless\Types\Scalars;
use Wwwision\GraphQL\TypeResolver;

class Context extends InputObjectType
{

    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'ContextInput',
            'description' => 'Input type for the Content Repository Context',
            'fields' => [
                'workspaceName' => ['type' => Type::string(), 'description' => 'The workspace of this context, e.g. "live" or "user-admin"'],
                'currentDateTime' => ['type' => $typeResolver->get(Scalars\DateTime::class), 'description' => 'Simulated date & time, defaults to the current server time (ISO 8601 format)'],
                'dimensions' => ['type' => $typeResolver->get(Scalars\UnstructuredObjectScalar::class), 'description' => 'Dimensions for this context, e.g. {"language": ["en", "de"]}'],
                'targetDimensions' => ['type' => Type::string(), 'description' => 'Dimensions to be applied for new/updated nodes, e.g. {"language": "en"}'],
                'invisibleContentShown' => ['type' => Type::boolean(), 'description' => 'Whether or not to show nodes with a "hidden" flag, defaults to FALSE'],
                'removedContentShown' => ['type' => Type::boolean(), 'description' => 'Whether or not to show nodes with a "removed" flag, defaults to FALSE'],
                'inaccessibleContentShown' => ['type' => Type::boolean(), 'description' => 'Whether or not to ignore node access restrictions, defaults to FALSE'],
            ],
        ]);
    }
}
