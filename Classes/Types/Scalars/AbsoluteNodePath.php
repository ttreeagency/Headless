<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Neos\Flow\Annotations as Flow;

/**
 * Represents an absolute node path in the form "/sites/some-site/some/path" (including leading slash)
 *
 * @Flow\Proxy(false)
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
     * @param AstNode $valueNode
     * @return string
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        if (!$valueNode instanceof StringValueNode) {
            return null;
        }
        return $this->coerceNodePath($valueNode->value);
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
