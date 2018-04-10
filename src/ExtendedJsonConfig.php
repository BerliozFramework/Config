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
    /**
     * @inheritdoc
     */
    public function __construct(string $rootDir, string $fileName)
    {
        parent::__construct($rootDir, $fileName);

        // Do replacement of variables names
        array_walk_recursive($this->configuration, [$this, 'replaceVariables']);
    }

    /**
     * Load configuration.
     *
     * @param string $file File name
     *
     * @return array
     * @throws \Berlioz\Config\Exception\ConfigException If unable to load configuration file
     */
    protected function load(string $file): array
    {
        $configuration = parent::load($file);

        // Do inclusions
        array_walk_recursive($configuration, [$this, 'doInclusions']);

        return $configuration;
    }

    /**
     * Do inclusions.
     *
     * @param mixed $value
     *
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function doInclusions(&$value)
    {
        if (is_string($value)) {
            $match = [];

            if (preg_match('/^\s*~~(?<action>include|extends)\:(?<var>[\w\-\.\,\s]+)~~\s*$/i', $value, $match) == 1) {
                try {
                    switch ($match['action']) {
                        case 'include':
                            $value = $this->load($match['var']);
                            break;
                        case 'extends':
                            $files = explode(',', $match['var']);
                            $files = array_map('trim', $files);
                            $files = array_map([$this, 'load'], $files);

                            $value = call_user_func_array('array_replace_recursive', $files);
                            break;
                    }
                } catch (\Exception $e) {
                    throw new ConfigException(sprintf('Unable to do inclusion of config line "%s"', $value), 0, $e);
                }
            }
        }
    }

    /**
     * Replace variables.
     *
     * @param mixed $value
     *
     * @throws \Berlioz\Config\Exception\ConfigException
     * @throws \Berlioz\Config\Exception\NotFoundException
     */
    public function replaceVariables(&$value)
    {
        if (is_string($value)) {
            // Variables
            $matches = [];
            if (preg_match_all('/~~(?:(?<action>\w+)\:)?(?<var>[\w\-\.\,\s]+)~~/i', $value, $matches, PREG_SET_ORDER) > 0) {
                foreach ($matches as $match) {
                    if (empty($match['action']) || $match['action'] == 'var') {
                        // Is special variable ?
                        if (is_null($subValue = $this->getSpecialVariable($match['var']))) {
                            $subValue = $this->get($match['var']);
                        }

                        $value = str_replace(sprintf('~~%s~~', $match['var']), $subValue, $value);
                    } else {
                        switch ($match['action']) {
                            case 'include':
                            case 'extends':
                                throw new ConfigException(sprintf('Action "%s" not allowed here', $match['action']));
                                break;
                            case 'special':
                                if (is_null($value = $this->getSpecialVariable($match['var']))) {
                                    throw new NotFoundException(sprintf('Unknown "%s" special variable', $match['var']));
                                }
                                break;
                            default:
                                throw new ConfigException(sprintf('Unknown action "%s" in config file "%s"', $match['action']));
                        }
                    }
                }

                $this->replaceVariables($value);
            }
        }
    }
}