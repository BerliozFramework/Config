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

declare(strict_types=1);

namespace Berlioz\Config;

use Berlioz\Config\Exception\ConfigException;

abstract class AbstractConfig implements ConfigInterface
{
    const TAG = '%';
    /** @var array Configuration */
    protected $configuration;
    /** @var array Default variables */
    protected $defaultVariables = ['best_framework'      => 'BERLIOZ',
                                   'php_version'         => PHP_VERSION,
                                   'php_version_id'      => PHP_VERSION_ID,
                                   'php_major_version'   => PHP_MAJOR_VERSION,
                                   'php_minor_version'   => PHP_MINOR_VERSION,
                                   'php_release_version' => PHP_RELEASE_VERSION,
                                   'php_sapi'            => PHP_SAPI,
                                   'system_os'           => PHP_OS];
    /** @var array User defined variables */
    private $userDefinedVariables = [];

    /**
     * AbstractConfig constructor.
     */
    public function __construct()
    {
        if ((version_compare(PHP_VERSION, '7.2.0') >= 0)) {
            $this->defaultVariables['system_os_family'] = PHP_OS_FAMILY;
        }
    }

    /**
     * @inheritdoc
     */
    public function get(string $key = null, $default = null)
    {
        try {
            if (!is_null($key)) {
                $key = explode('.', $key);
                $value = b_array_traverse($this->configuration, $key, $exists);

                if ($exists === false) {
                    $value = $default;
                }
            } else {
                $value = $this->configuration;
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
        return $this->defaultVariables[$name] ??
               $this->userDefinedVariables[$name] ??
               $default;
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
        if (!is_string($value)) {
            return;
        }

        // Variables
        $matches = [];
        if (preg_match_all(sprintf('/%1$s(?<var>[\w\-\.\,\s]+)%1$s/i', preg_quote(self::TAG)), $value, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                // Is variable ?
                if (is_null($subValue = $this->getVariable($match['var']))) {
                    $subValue = $this->get($match['var']);
                }

                // Booleans
                if ($subValue === true) {
                    $subValue = 'true';
                }
                if ($subValue === false) {
                    $subValue = 'false';
                }

                $value = str_replace(sprintf('%2$s%1$s%2$s', $match['var'], self::TAG), $subValue, $value);
            }

            if (in_array($value, ['true', 'false'], true)) {
                $value = $value == 'true';
            } else {
                if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
                    $value = intval($value);
                } else {
                    if (filter_var($value, FILTER_VALIDATE_FLOAT) !== false) {
                        $value = floatval($value);
                    }
                }
            }

            $this->replaceVariables($value);
        }
    }
}