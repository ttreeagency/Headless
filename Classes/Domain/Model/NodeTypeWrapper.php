<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

use Neos\ContentRepository\Domain\Model as CR;
use Neos\Flow\Exception;

final class NodeTypeWrapper
{
    const UNSTRUCTURED_NODETYPE = 'unstructured';

    protected CR\NodeType $nodeType;
    protected string $namespace;
    protected string $shortName;
    protected ContentNamespace $contentNamespace;

    public function __construct(CR\NodeType $nodeType)
    {
        $this->nodeType = $nodeType;
        if ($nodeType->getName() === self::UNSTRUCTURED_NODETYPE) {
            throw new Exception('Unstructured Node Type can not be used in the GraphQL API', 1510922748);
        }

        list($this->namespace, $shortName) = \explode(':', $nodeType->getName());
        $this->shortName = str_replace('.', '', $shortName);
        $this->contentNamespace = ContentNamespace::createFromNodeType($nodeType);
    }

    public function getName(): string
    {
        return $this->nodeType->getName();
    }

    public function getProperties(): array
    {
        return $this->nodeType->getProperties();
    }

    public function getConfiguration(string $path)
    {
        return $this->nodeType->getConfiguration($path);
    }

    public function getNodeType(): CR\NodeType
    {
        return $this->nodeType;
    }

    public function getContentNamespace(): string
    {
        return $this->contentNamespace->getNamespace();
    }

    public function getTypeName(): string
    {
        return FieldType::createFromNodeType($this->nodeType)->getName();
    }
}
