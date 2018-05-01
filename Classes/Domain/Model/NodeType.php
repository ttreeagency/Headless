<?php

namespace Ttree\Headless\Domain\Model;

use Neos\Flow\Exception;
use Neos\ContentRepository\Domain\Model as CR;

final class NodeType
{
    protected $nodeType;
    protected $namespace;
    protected $shortName;
    protected $contentNamespace;

    public function __construct(CR\NodeType $nodeType)
    {
        $this->nodeType = $nodeType;
        if ($nodeType->getName() === 'unstructured') {
            throw new Exception('Unstructured Node Type can not be used in the GraphQL API', 1510922748);
        }

        list($this->namespace, $shortName) = \explode(':', $nodeType->getName());
        $this->shortName = str_replace('.', '', $shortName);
        $this->contentNamespace = new ContentNamespace($this->namespace);
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

    public function getFqdnContentName(): string
    {
        return $this->contentNamespace->getNamespace() . $this->shortName;
    }
}
