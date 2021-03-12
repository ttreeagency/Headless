<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;


final class PresetClassConfigurationPath
{
    const PATTERN = 'options.Ttree:Headless.fields.{presetName}.class';

    private string $presetName;

    protected function __construct(string $presetName)
    {
        $this->presetName = $presetName;
    }

    public static function fromPresetName(string $presetName): PresetClassConfigurationPath
    {
        return new static($presetName);
    }

    public function get(): string
    {
        return str_replace('{presetName}', $this->presetName, self::PATTERN);
    }

    public function __toString(): string
    {
        return $this->get();
    }
}
