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

/**
 * Describes a config-aware instance.
 */
trait ConfigAwareTrait
{
    /** @var \Berlioz\Config\ConfigInterface Config */
    protected $config;

    /**
     * Get config.
     *
     * @return \Berlioz\Config\ConfigInterface|null
     */
    public function getConfig(): ?ConfigInterface
    {
        return $this->config;
    }

    /**
     * Set config.
     *
     * @param \Berlioz\Config\ConfigInterface $config
     *
     * @return static
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Has config?
     *
     * @return bool
     */
    public function hasConfig(): bool
    {
        return null !== $this->config;
    }
}