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
    /** @var string Root directory */
    private $rootDirectory;
    /** @var string Configuration directory */
    private $configDirectory;
    /** @var array Configuration */
    protected $configuration;

    /**
     * @inheritdoc
     */
    public function __construct(string $rootDir, string $fileName)
    {
        if (($this->rootDirectory = realpath($rootDir)) !== false) {
            // Define configuration file
            $this->setConfigDirectory(dirname($fileName));

            // Load configuration
            $this->configuration = $this->load(basename($fileName));
        } else {
            throw new \InvalidArgumentException(sprintf('Directory "%s" does not exists', $rootDir));
        }
    }

    /**
     * Set configuration directory.
     *
     * @param string $dirName Path of directory
     *
     * @return void
     * @throws \InvalidArgumentException If directory doesn't exists
     */
    protected function setConfigDirectory(string $dirName): void
    {
        $dirName = realpath($this->getRootDirectory() . $dirName);

        if (is_dir($dirName)) {
            $this->configDirectory = $dirName;
        } else {
            if ($dirName !== false) {
                throw new \InvalidArgumentException(sprintf('Directory "%s" does not exists', $dirName));
            } else {
                throw new \InvalidArgumentException(sprintf('JsonConfig directory does not exists', $dirName));
            }
        }
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
        $file = basename($file);
        $fileName = realpath($this->configDirectory . '/' . $file);

        try {
            $json = @file_get_contents($fileName);

            if ($json !== false) {
                $configuration = json_decode($json, true);

                if (empty($configuration)) {
                    throw new ConfigException(sprintf('Not a valid JSON configuration file "%s"', $file));
                }
            } else {
                if (file_exists($fileName)) {
                    throw new ConfigException(sprintf('Unable to load configuration file "%s"', $file));
                } else {
                    throw new NotFoundException(sprintf('File "%s" not found', $file));
                }
            }
        } catch (ConfigException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ConfigException(sprintf('Unable to load configuration file "%s"', $file));
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

    /**
     * Get root directory.
     *
     * @return string
     */
    protected function getRootDirectory(): string
    {
        return $this->rootDirectory;
    }
}