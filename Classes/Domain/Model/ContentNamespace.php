<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

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
