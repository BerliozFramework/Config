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
        if ($jsonIsUrl) {
            return $this->loadUrl($json);
        }

        return $this->loadJson($json);
    }

    /**
     * Load JSON data.
     *
     * @param string $json
     *
     * @return array
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    protected function loadJson(string $json): array
    {
        $configuration = json_decode($json, true);

        if (!is_array($configuration)) {
            throw new ConfigException('Not a valid JSON data');
        }

        return $configuration;
    }

    /**
     * Load JSON file.
     *
     * @param string $jsonFile
     *
     * @return array
     * @throws \Berlioz\Config\Exception\NotFoundException
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    protected function loadUrl(string $jsonFile): array
    {
        //Get real path of file
        if (($fileName = realpath($jsonFile)) === false) {
            throw new NotFoundException(sprintf('File "%s" not found', $jsonFile));
        }

        // Read file
        if (($json = @file_get_contents($fileName)) === false) {
            throw new ConfigException(sprintf('Unable to load configuration file "%s"', $fileName));
        }

        try {
            return $this->loadJson($json);
        } catch (ConfigException $e) {
            throw new ConfigException(sprintf('Not a valid JSON data for file "%s"', $fileName));
        }
    }
}