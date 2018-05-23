<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Config;

use Berlioz\Config\Exception\ConfigException;

abstract class AbstractConfig implements ConfigInterface
{
    const TAG = '%';
    /** @var array Configuration */
    protected $configuration;
    /** @var array User defined variables */
    private $userDefinedVariables = [];

    /**
     * @inheritdoc
     */
    public function get(string $key = null, $default = null)
    {
        try {
            $key = explode('.', $key);
            $value = b_array_traverse($this->configuration, $key, $exists);

            if ($exists === false) {
                $value = $default;
            }

            // Do replacement of variables names
            if (is_array($value)) {
                array_walk_recursive($value, [$this, 'replaceVariables']);
            } else {
                $this->replaceVariables($value);
            }

            return $value;
        } catch (\Exception $e) {
            throw new ConfigException(sprintf('Unable to get "%s" key in configuration file', implode('.', $key)));
        }
    }

    /**
     * @inheritdoc
     */
    public function has(string $key = null): bool
    {
        try {
            $key = explode('.', $key);
            b_array_traverse($this->configuration, $key, $exists);
        } catch (\Exception $e) {
            $exists = false;
        }

        return $exists;
    }

    /**
     * @inheritdoc
     */
    public function setVariables(array $variables)
    {
        $this->userDefinedVariables = $variables;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setVariable(string $name, $value)
    {
        $this->userDefinedVariables[$name] = $value;

        return $this;
    }

    /**
     * @inheritdoc
     *
     * Some variables are already defined:
     *   - php_version
     *   - php_version_id
     *   - php_major_version
     *   - php_minor_version
     *   - php_release_version
     *   - php_sapi
     *   - system_os
     *   - system_os_family
     */
    public function getVariable(string $name, $default = null)
    {
        switch ($name) {
            case 'best_framework':
                return 'BERLIOZ';
            case 'php_version':
                return PHP_VERSION;
            case 'php_version_id':
                return PHP_VERSION_ID;
            case 'php_major_version':
                return PHP_MAJOR_VERSION;
            case 'php_minor_version':
                return PHP_MINOR_VERSION;
            case 'php_release_version':
                return PHP_RELEASE_VERSION;
            case 'php_sapi':
                return PHP_SAPI;
            case 'system_os':
                return PHP_OS;
            case 'system_os_family':
                return PHP_OS_FAMILY;
            default:
                return $this->userDefinedVariables[$name] ?? $default;
        }
    }

    /**
     * Replace variables.
     *
     * @param mixed $value
     *
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    protected function replaceVariables(&$value)
    {
        if (is_string($value)) {
            // Variables
            $matches = [];
            if (preg_match_all(sprintf('/%1$s(?<var>[\w\-\.\,\s]+)%1$s/i', preg_quote(self::TAG)), $value, $matches, PREG_SET_ORDER) > 0) {
                foreach ($matches as $match) {
                    // Is variable ?
                    if (is_null($subValue = $this->getVariable($match['var']))) {
                        $subValue = $this->get($match['var']);
                    }

                    $value = str_replace(sprintf('%2$s%1$s%2$s', $match['var'], self::TAG), $subValue, $value);
                }

                $this->replaceVariables($value);
            }
        }
    }
}