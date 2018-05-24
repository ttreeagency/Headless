<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class Slug extends ScalarType
{

    /**
     * @var string
     */
    public $name = 'Slug';

    /**
     * @var string
     */
    public $description = 'A unique slug that can be use as URL segment';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $value
     * @return string
     */
    public function serialize($value)
    {
        return $this->coerceNodePath($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public function parseValue($value)
    {
        return $this->coerceNodePath($value);
    }

    /**
     * @param AstNode $valueAST
     * @return string
     */
    public function parseLiteral($valueAST)
    {
        if (!$valueAST instanceof StringValue) {
            return null;
        }
        return $this->coerceNodePath($valueAST->value);
    }

    /**
     * @param string $value
     * @return string
     */
    private function coerceNodePath($value)
    {
        if (!self::isValid($value)) {
            return null;
        }
        return $value;
    }

    /**
     * @param string $value
     * @return bool
     */
    static public function isValid($value)
    {
        return (is_string($value));
    }
}
