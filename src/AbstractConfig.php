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

abstract class AbstractConfig implements ConfigInterface
{
    /** @var array Specials variables */
    private $specialVariables = [];

    /**
     * Get root directory.
     *
     * @return string
     */
    abstract protected function getRootDirectory(): string;

    /**
     * Set special variables.
     *
     * @param array $variables
     *
     * @return static
     */
    public function setSpecialVariables(array $variables)
    {
        $this->specialVariables = $variables;

        return $this;
    }

    /**
     * Set special variable.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return static
     */
    public function setSpecialVariable(string $name, $value)
    {
        $this->specialVariables[$name] = $value;

        return $this;
    }

    /**
     * Get special variable.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getSpecialVariable($name)
    {
        switch ($name) {
            case 'directory_root':
                return $this->getRootDirectory();
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
                return $this->specialVariables[$name] ?? null;
        }
    }
}