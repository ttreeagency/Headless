<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class Url extends ScalarType
{
    /**
     * @var string
     */
    public $name = 'Url';

    /**
     * @var string
     */
    public $description = 'A Url represented as string';

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
        return self::isValid($value) ? $value : null;
    }

    /**
     * @param string $value
     * @return string
     */
    public function parseValue($value)
    {
        return self::isValid($value) ? $value : null;
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
        return $this->parseValue($valueNode->value);
    }

    /**
     * @param string $value
     * @return boolean
     */
    static public function isValid($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    }

}
