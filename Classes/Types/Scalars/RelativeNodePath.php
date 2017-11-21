<?php
namespace Ttree\Headless\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Type\Definition\ScalarType;
use Neos\Flow\Annotations as Flow;

/**
 * Represents an absolute node path in the form "some/relative/path" (no leading slash)
 */
class RelativeNodePath extends ScalarType
{

    /**
     * @var string
     */
    public $name = 'RelativeNodePathScalar';

    /**
     * @var string
     */
    public $description = 'A relative node path in the form "some/relative/path" (no leading slash)';

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
        if (!is_string($value) || $value[0] === '/') {
            return null;
        }
        return $value;
    }
}
