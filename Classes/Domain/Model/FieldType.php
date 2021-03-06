<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

use Neos\ContentRepository\Domain\Model as CR;

final class FieldType
{
    const NAMESPACE_SEPARATOR = ':';
    const CUSTOM_NAME_CONFIGURATION_PATH = 'options.Ttree:Headless.name';
    const CUSTOM_NAMESPACE_CONFIGURATION_PATH = 'options.Ttree:Headless.namespace';

    protected static array $cache = [];
    /**
     * @var string
     */
    protected $name;

    protected function __construct(string $name)
    {
        $this->name = str_replace(['.', self::NAMESPACE_SEPARATOR], '', $name);
    }

    public static function createFromNodeType(CR\NodeType $nodeType)
    {
        if (isset(self::$cache[$nodeType->getName()])) return self::$cache[$nodeType->getName()];

        list($namespace, $name) = explode(self::NAMESPACE_SEPARATOR, $nodeType->getName());

        $override = function (CR\NodeType $nodeType, string $current, string $path) {
            $override = $nodeType->getConfiguration($path);
            if ($override !== null) {
                $current = $override;
            }
            return $current;
        };

        $name = $override($nodeType, $name, self::CUSTOM_NAME_CONFIGURATION_PATH);
        $namespace = $override($nodeType, $namespace, self::CUSTOM_NAMESPACE_CONFIGURATION_PATH);

        self::$cache[$nodeType->getName()] = new static(trim(trim($namespace) . self::NAMESPACE_SEPARATOR . trim($name), self::NAMESPACE_SEPARATOR));

        return self::$cache[$nodeType->getName()];
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
