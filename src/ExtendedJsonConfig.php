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
    /** @var array User defined actions */
    private static $userDefinedActions = [];

    /**
     * @inheritdoc
     */
    public function __construct(string $json, bool $jsonIsUrl = false)
    {
        parent::__construct($json, $jsonIsUrl);

        // Do actions of variables names
        if (!empty(self::$userDefinedActions)) {
            array_walk_recursive($this->configuration, [$this, 'doActions']);
        }
    }

    /**
     * Load configuration.
     *
     * @param string      $json          JSON data
     * @param bool        $jsonIsUrl     If JSON data is URL? (default: false)
     * @param string|null $baseDirectory Base directory to get JSON file
     *
     * @return array
     * @throws \Berlioz\Config\Exception\ConfigException
     * @throws \Berlioz\Config\Exception\NotFoundException
     */
    protected function load(string $json, bool $jsonIsUrl = false, string $baseDirectory = null): array
    {
        $configuration = [];
        $localJsonLoading = [];

        if ($jsonIsUrl) {
            do {
                if (!empty($baseDirectory)) {
                    $json = realpath($path = sprintf('%s/%s', rtrim($baseDirectory, '\\/'), ltrim($json, '\\/')));
                }
                $baseDirectory = dirname($json);

                // Check recursive file call
                if (in_array($json, $this->jsonLoading)) {
                    throw new ConfigException(sprintf('Recursive configuration inclusion/extend for file "%s"', $path ?? $json));
                }

                $configuration = array_replace_recursive($this->loadUrl($json), $configuration);

                // Get @extends value
                $extends = $json = $configuration['@extends'] ?? false;
                unset($configuration['@extends']);

                // Add JSON file to currently loading
                $this->jsonLoading[] = $localJsonLoading[] = $json;

                // Do inclusions
                array_walk_recursive($configuration, [$this, 'doInclusions'], $baseDirectory);
            } while ($extends !== false);

            // Remove JSON file from currently loading
            foreach ($localJsonLoading as $json) {
                if ($jsonIsUrl && ($key = array_search($json, $this->jsonLoading)) !== false) {
                    unset($this->jsonLoading[$key]);
                }
            }
        } else {
            $configuration = $this->loadJson($json);
        }

        return $configuration;
    }

    /**
     * @inheritdoc
     */
    protected function loadJson(string $json): array
    {
        $json = preg_replace('#^\s*//.*$\v?#mx', '', $json);

        return parent::loadJson($json);
    }

    ////////////////////////////
    /// INCLUSIONS & ACTIONS ///
    ////////////////////////////

    /**
     * Do inclusions.
     *
     * @param mixed  $value
     * @param string $baseDirectory Base directory
     *
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    protected function doInclusions(&$value, $key, string $baseDirectory)
    {
        if (is_string($value)) {
            $match = [];

            if (preg_match(sprintf('/^\s*%1$s(?<action>include|extends)\:(?<var>[\w\-\.\,\s]+)%1$s\s*$/i', preg_quote(self::TAG)), $value, $match) == 1) {
                try {
                    switch ($match['action']) {
                        case 'include':
                            $value = $this->load($match['var'], true, $baseDirectory);
                            break;
                        case 'extends':
                            $files = explode(',', $match['var']);
                            $files = array_map('trim', $files);
                            $files = array_map(
                                function ($file) use ($baseDirectory) {
                                    return $this->load($file, true, $baseDirectory);
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