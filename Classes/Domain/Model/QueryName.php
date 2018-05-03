<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

use Neos\Flow\Exception;

final class QueryName
{
    /**
     * @var string
     */
    protected $name;

    public function __construct(string $nodeTypeShortName)
    {
        if (\mb_strtolower($nodeTypeShortName[0]) === $nodeTypeShortName[0]) {
            throw new Exception(\vsprintf('The first caracter in "%s" need to be in uppercase.', $nodeTypeShortName), 1509893786);
        }
        $this->name = \str_replace(['.', ':'], '', $nodeTypeShortName);
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
