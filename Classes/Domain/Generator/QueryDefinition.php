<?php

namespace Ttree\Headless\Domain\Generator;

use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Ttree\Headless\Domain\Model\ContentNamespace;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;

class QueryDefinition
{
    /**
     * @var NodeTypeManager
     * @Flow\Inject
     */
    protected $nodeTypeManager;

    /**
     * @var TypeResolver
     */
    protected $typeResolver;

    protected $fields = [];

    public function __construct(TypeResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
    }

    public function fields()
    {
        if ($this->fields !== []) {
            return $this->fields;
        }

        $namespaces = [];
        /** @var NodeType $nodeType */
        foreach ($this->nodeTypeManager->getNodeTypes(false) as $nodeType) {
            if ($nodeType->getName() === 'unstructured') {
                continue;
            }
            list ($nodeTypeNamespace) = \explode(':', $nodeType->getName());
            if (\in_array($nodeTypeNamespace, $namespaces)) {
                continue;
            }
            $namespaces[] = $nodeTypeNamespace;
        }

        $fields = [];
        foreach ($namespaces as $namespace) {
            $fields = \array_merge(QueryField::create(
                new ContentNamespace($namespace),
                $this->typeResolver,
                ObjectType::createByPackage($this->typeResolver, new ContentNamespace($namespace))
            )->fields(), $fields);
        }
        $this->fields = $fields;

        return $this->fields;
    }
}
