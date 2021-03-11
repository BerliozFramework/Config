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

use Berlioz\Config\Adapter\ArrayAdapter;
use PHPUnit\Framework\TestCase;

class ArrayAdapterTest extends TestCase
{
    public function test()
    {
        $adapter = new ArrayAdapter(
            [
                "qux" => "value1",
                "section" => [
                    "foo" => "value",
                    "qux" => "value2"
                ],
                "section2" => [
                    "bar" => "value3"
                ],
            ]
        );

        $this->assertEquals('value1', $adapter->get('qux'));
        $this->assertEquals('value', $adapter->get('section.foo'));
        $this->assertEquals('value2', $adapter->get('section.qux'));
        $this->assertEquals(['bar' => 'value3'], $adapter->get('section2'));

        $this->assertFalse($adapter->has('baz'));
        $this->assertTrue($adapter->has('section.foo'));
    }
}
