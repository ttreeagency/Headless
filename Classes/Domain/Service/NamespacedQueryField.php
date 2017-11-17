<?php

namespace Ttree\Headless\Domain\Service;

use GraphQL\Type\Definition\ObjectType;
use Ttree\Headless\Domain\Model\ContentNamespace;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;

class NamespacedQueryField
{
    protected $fields = [];

    public function __construct(ContentNamespace $namespace, TypeResolver $typeResolver, ObjectType $type)
    {
        $this->fields = [
            $namespace->getNamespace() . 'Namespace' => [
                'type' => $type,
                'resolve' => function () use ($namespace) {
                    return $namespace->getRaw();
                },
            ],
        ];
    }

    public static function create(ContentNamespace $namespace, TypeResolver $typeResolver, ObjectType $type)
    {
        return new NamespacedQueryField($namespace, $typeResolver, $type);
    }

    public function fields(): array
    {
        return $this->fields;
    }
}
