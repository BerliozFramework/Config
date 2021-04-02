<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2021 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Config;

use ArrayObject;
use Berlioz\Config\Adapter\AdapterInterface;
use Berlioz\Config\ConfigFunction;

/**
 * Class Config.
 */
class Config implements ConfigInterface
{
    protected const ENCAPSULATION_START = '{';
    protected const ENCAPSULATION_END = '}';

    protected array $configs = [];
    protected ArrayObject $variables;
    protected ConfigFunction\ConfigFunctionSet $functions;

    /**
     * Config constructor.
     *
     * @param AdapterInterface[] $configs
     * @param array $variables
     */
    public function __construct(
        array $configs = [],
        array $variables = [],
    ) {
        $this->addConfig(...$configs);
        $this->variables = new ArrayObject($variables);

        $this->functions = new ConfigFunction\ConfigFunctionSet(
            [
                new ConfigFunction\ConfigFunction($this),
                new ConfigFunction\ConstantFunction(),
                new ConfigFunction\EnvFunction(),
                new ConfigFunction\VarFunction($this),
            ]
        );
    }

    /**
     * Get variables.
     *
     * @return ArrayObject
     */
    public function getVariables(): ArrayObject
    {
        return $this->variables;
    }

    /**
     * Add functions.
     *
     * @param ConfigFunction\ConfigFunctionInterface ...$function
     */
    public function addFunction(ConfigFunction\ConfigFunctionInterface ...$function): void
    {
        $this->functions->add(...$function);
    }

    /**
     * Get all configurations.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->configs;
    }

    /**
     * Add config.
     *
     * @param AdapterInterface ...$config
     */
    public function addConfig(AdapterInterface ...$config): void
    {
        array_unshift($this->configs, ...$config);
        usort($this->configs, fn($config1, $config2) => $config2->getPriority() <=> $config1->getPriority());
    }

    /**
     * @inheritDoc
     */
    public function get(string $key = null, mixed $default = null): mixed
    {
        $arrayValue = null;
        $found = false;

        foreach ($this->configs as $config) {
            if (!$config->has($key)) {
                continue;
            }

            // Get value
            $value = $config->get($key);
            $found = true;

            // Not an array, so not necessary to merge or continue
            if (!is_array($value)) {
                // If back value is an array, so can't merge values
                if (null !== $arrayValue) {
                    $this->treatValue($arrayValue);

                    return $arrayValue;
                }

                $this->treatValue($value);

                return $value;
            }

            $arrayValue = array_merge($arrayValue ?? [], $value);
        }

        if (false === $found) {
            return $default;
        }

        $value = $arrayValue;
        $this->treatValue($value);

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        foreach ($this->configs as $config) {
            if ($config->has($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getArrayCopy(): array
    {
        $configArrays = array_map(fn(ConfigInterface $config) => $config->getArrayCopy(), $this->configs);
        rsort($configArrays);
        $configArray = b_array_merge_recursive(...$configArrays);
        unset($configArrays);
        $this->treatValue($configArray);

        return $configArray;
    }

    /**
     * Treat value.
     *
     * @param mixed $value
     *
     * @throws Exception\ConfigException
     */
    protected function treatValue(mixed &$value): void
    {
        // Not an array or string
        if (!is_array($value) && !is_string($value)) {
            return;
        }

        // Treat recursive values
        if (is_array($value)) {
            array_walk_recursive($value, [$this, 'treatValue']);
            return;
        }

        // Not to treat
        if (!str_starts_with($value, static::ENCAPSULATION_START) &&
            !str_ends_with($value, static::ENCAPSULATION_END)) {
            return;
        }

        // If it's an asked value {= varName}
        if (str_starts_with($value, static::ENCAPSULATION_START . '=')) {
            $value = $this->functions->execute('var', trim(substr($value, 2, -1)));
            return;
        }

        $tmpValue = substr($value, 1, -1);
        $tmpValue = explode(':', $tmpValue, 2);
        $tmpValue = array_map('trim', $tmpValue);

        // Not to treat
        if (2 !== count($tmpValue)) {
            return;
        }

        $value = $this->functions->execute($tmpValue[0], $tmpValue[1]);
    }
}