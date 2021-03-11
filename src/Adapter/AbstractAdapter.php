<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2021 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Config\Adapter;

use Berlioz\Config\ConfigInterface;

/**
 * Class AbstractAdapter.
 */
abstract class AbstractAdapter implements ConfigInterface
{
    protected array $configuration;

    public function __construct(protected int $priority = 0)
    {
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key = null, mixed $default = null): mixed
    {
        return b_array_traverse_get($this->configuration, $key);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return b_array_traverse_exists($this->configuration, $key);
    }
}