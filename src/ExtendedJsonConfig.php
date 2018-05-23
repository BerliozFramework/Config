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
use Berlioz\Config\Exception\NotFoundException;

/**
 * Class ExtendedJsonConfig.
 *
 * Offer basic configuration class to manage JSON configuration files.
 * Access to the values with get() method, uses 'key.subkey.something' for example.
 *
 * @package Berlioz\Core
 */
class ExtendedJsonConfig extends JsonConfig
{
    /** @var string[] JSON files currently loading */
    private $jsonLoading = [];
    /** @var null|string Directory files */
    private $directory = null;
    /** @var array User defined actions */
    private static $userDefinedActions = [];

    /**
     * @inheritdoc
     */
    public function __construct(string $json, bool $jsonIsUrl = false)
    {
        if ($jsonIsUrl) {
            $this->directory = dirname($json);
            $json = basename($json);
        }

        parent::__construct($json, $jsonIsUrl);

        // Do actions of variables names
        if (!empty(self::$userDefinedActions)) {
            array_walk_recursive($this->configuration, [$this, 'doActions']);
        }
    }

    /**
     * Load configuration.
     *
     * @param string $json      JSON data
     * @param bool   $jsonIsUrl If JSON data is URL? (default: false)
     *
     * @return array
     * @throws \Berlioz\Config\Exception\ConfigException If unable to load configuration file
     */
    protected function load(string $json, bool $jsonIsUrl = false): array
    {
        $configuration = [];
        $localJsonLoading = [];

        do {
            if ($jsonIsUrl) {
                if (empty($this->directory)) {
                    throw new ConfigException('Unable to load JSON as URL dynamically without extends this class');
                }

                if (($json = realpath($path = sprintf('%s/%s', ltrim($this->directory, '\\/'), ltrim($json, '\\/')))) === false) {
                    throw new NotFoundException(sprintf('File "" does not exist', $path));
                }

                if (in_array($json, $this->jsonLoading)) {
                    throw new ConfigException(sprintf('Recursive configuration inclusion/extend for file "%s"', $json));
                }

                // Add JSON file to currently loading
                $this->jsonLoading[] = $localJsonLoading[] = $json;
            }

            $configuration = array_replace_recursive(parent::load($json, $jsonIsUrl), $configuration);

            $extends = $json = $configuration['@extends'] ?? false;
            unset($configuration['@extends']);
        } while ($extends !== false);

        // Do actions
        array_walk_recursive($configuration, [$this, 'doInclusions']);

        // Remove JSON file from currently loading
        foreach ($localJsonLoading as $json) {
            if ($jsonIsUrl && ($key = array_search($json, $this->jsonLoading)) !== false) {
                unset($this->jsonLoading[$key]);
            }
        }

        return $configuration;
    }

    /**
     * Do inclusions.
     *
     * @param mixed $value
     *
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    protected function doInclusions(&$value)
    {
        if (is_string($value)) {
            $match = [];

            if (preg_match(sprintf('/^\s*%1$s(?<action>include|extends)\:(?<var>[\w\-\.\,\s]+)%1$s\s*$/i', preg_quote(self::TAG)), $value, $match) == 1) {
                try {
                    switch ($match['action']) {
                        case 'include':
                            $value = $this->load($match['var'], true);
                            break;
                        case 'extends':
                            $files = explode(',', $match['var']);
                            $files = array_map('trim', $files);
                            $files = array_map(
                                function ($file) {
                                    return $this->load($file, true);
                                },
                                $files);

                            $value = call_user_func_array('array_replace_recursive', $files);
                            break;
                    }
                } catch (\Exception $e) {
                    throw new ConfigException(sprintf('Unable to do action of config line "%s"', $value), 0, $e);
                }
            }
        }
    }

    /**
     * Do actions.
     *
     * @param mixed $value
     *
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function doActions(&$value)
    {
        if (is_string($value)) {
            $matches = [];

            if (preg_match(sprintf('/^\s*%1$s(?<action>[\w\-\.]+)\:(?<var>[\w\-\.\,\s]+)%1$s\s*$/i', preg_quote(self::TAG)), $value, $matches) == 1) {
                try {
                    if (!isset(self::$userDefinedActions[$matches['action']])) {
                        throw new ConfigException(sprintf('Unknown action "%s" in config file', $matches['action']));
                    }

                    $value = call_user_func_array(self::$userDefinedActions[$matches['action']],
                                                  [$matches['var'],
                                                   $this]);
                } catch (ConfigException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    throw new ConfigException(sprintf('Unable to do action of config line "%s"', $value), 0, $e);
                }
            }
        }
    }

    /**
     * Add action.
     *
     * @param string   $name     Action name
     * @param callable $callback Callback
     */
    public static function addAction(string $name, callable $callback)
    {
        self::$userDefinedActions[$name] = $callback;
    }
}