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

interface ConfigAwareInterface
{
    /**
     * Get config.
     *
     * @return \Berlioz\Config\ConfigInterface|null
     */
    public function getConfig(): ?ConfigInterface;

    /**
     * Set config.
     *
     * @param \Berlioz\Config\ConfigInterface $config
     *
     *
     * @return static
     */
    public function setConfig(ConfigInterface $config);

    /**
     * Has config?
     *
     * @return bool
     */
    public function hasConfig(): bool;
}