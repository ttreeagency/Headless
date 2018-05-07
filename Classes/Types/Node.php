<?php
declare(strict_types=1);

namespace Ttree\Headless\Types;

use GraphQL\Type\Definition\ObjectType;
use Neos\ContentRepository\Domain\Model as CR;
use Neos\Flow\Exception;
use Ttree\Headless\Domain\Model as Model;
use Ttree\Headless\Service\NodeInterfaceService;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;

class Node extends ObjectType
{
    use NodeTrait;

    /**
     * @var NodeInterfaceService
     * @Flow\Inject
     */
    protected $interfaceService;

    /**
     * @param TypeResolver $typeResolver
     * @param CR\NodeType $nodeTypeWrapper
     * @throws Exception
     */
    public function __construct(TypeResolver $typeResolver, CR\NodeType $nodeTypeWrapper)
    {
        $nodeTypeWrapper = new Model\NodeTypeWrapper($nodeTypeWrapper);

        parent::__construct([
            'name' => $nodeTypeWrapper->getTypeName(),
            // todo add support to have a node type description in YAML
            'description' => $nodeTypeWrapper->getName(),
            'fields' => $this->fields($typeResolver, $nodeTypeWrapper),
            'interfaces' => function () use ($typeResolver, $nodeTypeWrapper) {
                return $this->interfaces($typeResolver, $nodeTypeWrapper);
            },
        ]);
    }

    protected function interfaces(TypeResolver $typeResolver, Model\NodeTypeWrapper $nodeTypeWrapper): array
    {
        $interfaces = [];
        /** @var CR\NodeType $nodeType */
        foreach ($nodeTypeWrapper->getNodeType()->getDeclaredSuperTypes() as $nodeType) {
            $name = $nodeType->getName();
            if ($nodeType->isAbstract() === true && $nodeType->isOfType('Ttree.Headless:Interface') && !isset($interfaces[$name])) {
                $interfaces[$name] = $this->interfaceService->get($typeResolver, $nodeType);
            }
        }
        return $interfaces;
    }
}
