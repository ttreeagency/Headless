<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Generator;


use GraphQL\Type\Definition\Type;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\Flow\Annotations as Flow;
use Ttree\Headless\CustomType\AllNodeCustomField;
use Ttree\Headless\CustomType\CustomFieldInterface;
use Ttree\Headless\CustomType\NodeCustomField;
use Ttree\Headless\Domain\Model\ContentNamespace;
use Ttree\Headless\Domain\Model\Plural;
use Ttree\Headless\Domain\Model\QueryableNodeTypes;
use Ttree\Headless\Types\Node;
use Wwwision\GraphQL\TypeResolver;

class ObjectTypeFields
{
    /**
     * @var QueryableNodeTypes
     * @Flow\Inject
     */
    protected $queryableNodeTypes;

    /**
     * @var ContentNamespace
     */
    protected $contentNamespace;

    /**
     * @var TypeResolver
     */
    protected $typeResolver;

    public function __construct(TypeResolver $typeResolver, ContentNamespace $namespace)
    {
        $this->contentNamespace = $namespace;
        $this->typeResolver = $typeResolver;
    }

    public function fields()
    {
        $fields = [];
        /** @var NodeType $nodeType */
        foreach ($this->queryableNodeTypes->iterate() as $nodeType) {
            list($namespace, $name) = explode(':', $nodeType->getName());
            if ($namespace !== $this->contentNamespace->getRaw()) {
                continue;
            }
            $name = str_replace('.', '', $name);
            $fields[$this->singleRecordFieldName($name)] = $this->singleFieldDefinition($this->typeResolver, $name, $nodeType);
            $fields[$this->allRecordsFieldName($name)] = $this->allRecordsFieldDefinition($this->typeResolver, $name, $nodeType);
            // todo add support for custom fields
        }
        return $fields;
    }

    protected function singleRecordFieldName(string $name)
    {
        return $name;
    }

    protected function singleFieldDefinition(TypeResolver $typeResolver, string $nodeTypeShortName, NodeType $nodeType)
    {
        $type = $typeResolver->get([Node::class, $nodeType->getName()], $nodeType);

        /** @var CustomFieldInterface $customType */
        $customType = new NodeCustomField();

        return $this->type($customType, $type, $typeResolver, $nodeTypeShortName, $nodeType);
    }

    protected function allRecordsFieldName(string $name)
    {
        return 'all' . (string)(new Plural($name));
    }

    protected function allRecordsFieldDefinition(TypeResolver $typeResolver, string $nodeTypeShortName, NodeType $nodeType)
    {
        $type = $typeResolver->get([Node::class, $nodeType->getName()], $nodeType);

        $typeClassName = $this->getTypeImplementation($nodeType, 'all');
        /** @var CustomFieldInterface $customType */
        $customType = new $typeClassName;

        return $this->type($customType, Type::listOf($type), $typeResolver, $nodeTypeShortName, $nodeType);
    }

    protected function type(CustomFieldInterface $customType, $type, TypeResolver $typeResolver, string $nodeTypeShortName, NodeType $nodeType)
    {
        return [
            'type' => $type,
            'args' => $customType->args($typeResolver),
            'description' => $customType->description($nodeType),
            'resolve' => $customType->resolve($nodeType)
        ];
    }

    protected function getTypeImplementation(NodeType $nodeType, string $presetName): ?string {
        $implementation = $nodeType->getConfiguration('options.Ttree:Headless.fields.' . $presetName . '.implementation');
        if ($implementation === null) {
            return AllNodeCustomField::class;
        }
        return $implementation;
    }
}
