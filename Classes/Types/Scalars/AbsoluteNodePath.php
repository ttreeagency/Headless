<?php
namespace Ttree\Headless\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use Neos\Flow\Annotations as Flow;

/**
 * Represents an absolute node path in the form "/sites/some-site/some/path" (including leading slash)
 */
class AbsoluteNodePath extends ScalarType
{

    /**
     * @var string
     */
    public $name = 'AbsoluteNodePathScalar';

    /**
     * @var string
     */
    public $description = 'An absolute node path in the form "/sites/some-site/some/path" (including leading slash)';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
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
        return (is_string($value) && substr($value, 0, 1) === '/');
    }
}
