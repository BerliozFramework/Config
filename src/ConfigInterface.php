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

interface ConfigInterface
{
    /**
     * Get value.
     *
     * Key given in parameter must be in format: key.key2.key3
     * Must throw exception if $throw parameter is true, else must return null value if key not found.
     *
     * @param string $key   Key
     * @param bool   $throw Throw exception if doesn't exists, else returns null
     *
     * @return mixed
     * @throws \Berlioz\Config\Exception\NotFoundException
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function get(string $key = null, bool $throw = true);

    /**
     * Key exists ?
     *
     * Key given in parameter must be in format: key.key2.key3
     * Must return boolean value if key not found.
     *
     * @param string $key Key
     *
     * @return bool
     */
    public function has(string $key = null): bool;

    /**
     * Set user defined variables.
     *
     * @param array $variables
     *
     * @return static
     */
    public function setVariables(array $variables);

    /**
     * Set user defined variable.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return static
     */
    public function setVariable(string $name, $value);

    /**
     * Get user defined variable.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getVariable($name);
}