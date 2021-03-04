<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Neos\Flow\Annotations as Flow;

/**
 * Scalar type representing node identifiers (UUID)
 * 
 * @Flow\Proxy(false)
 */
class Uuid extends ScalarType
{
    /**
     * @var string
     */
    const PATTERN_MATCH_UUID = '/^([a-f0-9]){8}-([a-f0-9]){4}-([a-f0-9]){4}-([a-f0-9]){4}-([a-f0-9]){12}$/';

    /**
     * @var string
     */
    public $name = 'UUID';

    /**
     * @var string
     */
    public $description = 'A UUID represented as string';

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
        return (is_string($value) && preg_match(self::PATTERN_MATCH_UUID, $value) === 1);
    }

}
