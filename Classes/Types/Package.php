<?php
namespace Ttree\Headless\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Neos\ContentRepository\Domain\Service\Context as ContentRepositoryContext;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Service\NodeSearchService;
use Wwwision\GraphQL\AccessibleObject;
use Wwwision\GraphQL\IterableAccessibleObject;
use Wwwision\GraphQL\TypeResolver;
use Ttree\Headless\Types\InputTypes\NodeIdentifierOrPath;

/**
 * A GraphQL type definition describing a Package Namespace
 */
class Package extends ObjectType
{
    /**
     * @param TypeResolver $typeResolver
     */
    public function __construct(TypeResolver $typeResolver)
    {
        return parent::__construct([
            'name' => 'PackageNamespace',
            'description' => 'The package namespace',
            'fields' => [
                'package' => [
                    'type' => $typeResolver->get(Package::class),
                    'description' => 'Package Key',
                    'resolve' => function (AccessibleObject $wrappedNode) {
                        /** @var ContentRepositoryContext $context */
                        $context = $wrappedNode->getObject();
                        return new AccessibleObject($context);
                    }
                ]
            ],
        ]);
    }
}
