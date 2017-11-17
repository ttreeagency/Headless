<?php

namespace Ttree\Headless\Domain\Model;

use Neos\Flow\Exception;

final class ContentNamespace
{
    protected $raw;

    protected $namespace;

    public function __construct(string $namespace)
    {
        $this->raw = $namespace;
        $this->namespace = $this->normalize($namespace);
    }

    protected function normalize(string $value): string
    {
        return \str_replace('.', '', $value);
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
