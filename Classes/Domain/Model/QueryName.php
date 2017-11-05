<?php

namespace Ttree\Headless\Domain\Model;

use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\Flow\Exception;
use Neos\Utility\Arrays;

final class QueryName
{
    /**
     * @var string
     */
    protected $name;

    public function __construct(string $nodeTypeAlias)
    {
        if (\mb_strtolower($nodeTypeAlias[0]) === $nodeTypeAlias[0]) {
            throw new Exception(\vsprintf('The first caracter in "%s" need to be in uppercase.', $nodeTypeAlias), 1509893786);
        }
        $this->name = \str_replace(['.', ':'], '', $nodeTypeAlias);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
