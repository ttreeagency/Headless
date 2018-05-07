<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

use Neos\ContentRepository\Domain\Model as CR;

final class ContentNamespace
{
    protected $raw;

    protected $namespace;

    protected function __construct(string $namespace)
    {
        $this->raw = $namespace;
        $this->namespace = $this->normalize($namespace);
    }

    public static function createFromNodeType(CR\NodeType $nodeType)
    {
        list($namespace) = explode(':', $nodeType->getName());
        return new static($namespace);
    }

    protected function normalize(string $value): string
    {
        return \str_replace(['.', ':'], ['', '__'], $value);
    }

    public function getRaw(): string
    {
        return $this->raw;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
