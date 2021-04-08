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

use Berlioz\Config\Exception\ConfigException;

/**
 * Class YamlAdapter.
 */
class YamlAdapter extends AbstractFileAdapter
{
    public function __construct(
        string $str,
        bool $strIsUrl = false,
        int $priority = 0,
        protected array $yaml = [],
    ) {
        $default = ['pos' => 0, 'ndocs' => null, 'callbacks' => []];
        $this->yaml = array_replace($default, $this->yaml);
        $this->yaml = array_intersect_key($this->yaml, $default);

        parent::__construct($str, $strIsUrl, $priority);
    }

    /**
     * @inheritDoc
     */
    protected function load(string $str, bool $strIsUrl = false): array
    {
        if (false === extension_loaded('yaml')) {
            throw new ConfigException('Needs extension "ext-yaml" to use YAML adapter');
        }

        if (true === $strIsUrl) {
            return $this->assertResult(@yaml_parse_file($str, ...$this->yaml), 'Not a valid YAML file');
        }


        return $this->assertResult(@yaml_parse($str, ...$this->yaml));
    }

    /**
     * Assert result.
     *
     * @param mixed $result
     * @param string|null $message
     *
     * @return array
     * @throws ConfigException
     */
    private function assertResult(mixed $result, ?string $message = null): array
    {
        if (!is_array($result)) {
            throw new ConfigException($message ?? 'Not a valid YAML contents');
        }

        return $result;
    }
}