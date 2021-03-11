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

namespace Berlioz\Config\Tests\Adapter;

use Berlioz\Config\Adapter\JsonAdapter;
use Berlioz\Config\Exception\ConfigException;
use PHPUnit\Framework\TestCase;

class JsonAdapterTest extends TestCase
{
    public function testLoadString()
    {
        $ini = <<<EOF
{
  "qux": "value1",
  "section": {
    "foo": "value",
    "qux": "value2"
  },
  "section2": {
    // Comment
    "bar": "value3"
  },
}
EOF;

        $adapter = new JsonAdapter($ini);

        $this->assertEquals('value1', $adapter->get('qux'));
        $this->assertEquals('value', $adapter->get('section.foo'));
        $this->assertEquals('value2', $adapter->get('section.qux'));
        $this->assertEquals(['bar' => 'value3'], $adapter->get('section2'));

        $this->assertFalse($adapter->has('baz'));
        $this->assertTrue($adapter->has('section.foo'));
    }

    public function testLoadStringFailed()
    {
        $this->expectException(ConfigException::class);

        $ini = <<<EOF
{
EOF;
        new JsonAdapter($ini);
    }

    public function testLoadFile()
    {
        $adapter = new JsonAdapter(__DIR__ . '/config.json5', true);

        $this->assertEquals('value1', $adapter->get('qux'));
        $this->assertEquals('value', $adapter->get('section.foo'));
        $this->assertEquals('value2', $adapter->get('section.qux'));
        $this->assertEquals(['bar' => 'value3'], $adapter->get('section2'));
    }

    public function testLoadFileFailed()
    {
        $this->expectException(ConfigException::class);

        new JsonAdapter(__DIR__ . '/config-failed.json5', true);
    }
}