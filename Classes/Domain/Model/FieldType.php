<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

use Neos\Flow\Exception;
use Neos\ContentRepository\Domain\Model as CR;

final class FieldType
{
    const NAMESPACE_SEPARATOR = ':';

    /**
     * @var string
     */
    protected $name;

    protected function __construct(string $name)
    {
        $this->name = \str_replace(['.', self::NAMESPACE_SEPARATOR], '', $name);
    }

    public static function createFromNodeType(CR\NodeType $nodeType)
    {
        list($namespace, $name) = explode(self::NAMESPACE_SEPARATOR, $nodeType->getName());

        $override = function (CR\NodeType $nodeType, string $current, string $path) {
            $override = $nodeType->getConfiguration($path);
            if ($override !== null) {
                $current = $override;
            }
            return $current;
        };

        $name = $override($nodeType, $name, 'options.Ttree:Headless.name');
        $namespace = $override($nodeType, $namespace, 'options.Ttree:Headless.namespace');

        $nodeType = trim(trim($namespace) . self::NAMESPACE_SEPARATOR . trim($name), self::NAMESPACE_SEPARATOR);

        return new static($nodeType);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
