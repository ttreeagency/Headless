<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\Scalars;

use DateTimeImmutable;
use DateTimeInterface;
use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Neos\Flow\Annotations as Flow;

/**
 * Scalar type wrapper for \DateTimeInterface values
 *
 * @Flow\Proxy(false)
 */
class DateTime extends ScalarType
{
    const DEFAULT_FORMAT = DATE_ISO8601;

    /**
     * @var string
     */
    public $name = 'DateTimeScalar';

    /**
     * @var string
     */
    public $description = 'A Date and time, represented as ISO 8601 conform string';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param DateTimeInterface $value
     * @return string
     */
    public function serialize($value)
    {
        if (!$value instanceof DateTimeInterface) {
            return null;
        }
        return $value->format(self::DEFAULT_FORMAT);
    }

    /**
     * @param string $value
     * @return DateTimeImmutable
     */
    public function parseValue($value)
    {
        if (!is_string($value)) {
            return null;
        }
        $dateTime = DateTimeImmutable::createFromFormat(self::DEFAULT_FORMAT, $value);
        if ($dateTime === false) {
            return null;
        }
        return $dateTime;
    }

    /**
     * @param AstNode $valueNode
     * @param array|null $variables
     * @return DateTimeImmutable
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        if (!$valueNode instanceof StringValueNode) {
            return null;
        }
        return $this->parseValue($valueNode->value);
    }
}
