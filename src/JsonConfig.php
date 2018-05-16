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
 * Class JsonConfig.
 *
 * Offer basic configuration class to manage JSON configuration files.
 * Access to the values with get() method, uses 'key.subkey.something' for example.
 *
 * @package Berlioz\Core
 */
class JsonConfig extends AbstractConfig
{
    /**
     * JsonConfig constructor.
     *
     * @param string $json      JSON data
     * @param bool   $jsonIsUrl If JSON data is URL? (default: false)
     *
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function __construct(string $json, bool $jsonIsUrl = false)
    {
        // Load configuration
        $this->configuration = $this->load($json, $jsonIsUrl);

        parent::__construct();
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
        try {
            $fileName = null;

            if ($jsonIsUrl) {
                $fileName = realpath($json);
                $json = @file_get_contents($fileName);
            }

            if ($json !== false) {
                $configuration = json_decode($json, true);

                if (!is_array($configuration)) {
                    if ($jsonIsUrl) {
                        throw new ConfigException(sprintf('Not a valid JSON configuration file "%s"', $fileName));
                    } else {
                        throw new ConfigException('Not a valid JSON data');
                    }
                }
            } else {
                if (file_exists($fileName)) {
                    throw new ConfigException(sprintf('Unable to load configuration file "%s"', $fileName));
                } else {
                    throw new NotFoundException(sprintf('File "%s" not found', $fileName));
                }
            }
        } catch (ConfigException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ConfigException(sprintf('Unable to load configuration file "%s"', $fileName));
        }

        return $configuration;
    }

    /**
     * @inheritdoc
     */
    public function get(string $key = null, bool $throw = true)
    {
        try {
            $key = explode('.', $key);
            $value = b_array_traverse($this->configuration, $key, $exists);

            if ($exists === false && $throw) {
                throw new NotFoundException(sprintf('Unable to find "%s" key in configuration file', implode('.', $key)));
            }
        } catch (ConfigException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ConfigException(sprintf('Unable to get "%s" key in configuration file', implode('.', $key)));
        }

        return $value;
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
}