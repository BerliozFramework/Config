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

namespace Berlioz\Config\Tests;

use Berlioz\Config\Exception\ConfigException;
use Berlioz\Config\Exception\NotFoundException;
use Berlioz\Config\JsonConfig;
use PHPUnit\Framework\TestCase;

class JsonConfigTest extends TestCase
{
    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function test__constructJson()
    {
        $config = new JsonConfig('{"key": "value"}');
        $this->assertInstanceOf(JsonConfig::class, $config);
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function test__constructFile()
    {
        $config = new JsonConfig(sprintf('%s%s', __DIR__, '/files/config.json'), true);
        $this->assertInstanceOf(JsonConfig::class, $config);
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function test__constructNotExistsFile()
    {
        $this->expectException(NotFoundException::class);
        new JsonConfig(sprintf('%s%s', __DIR__, '/files/config.notexists.json'), true);
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function test__constructMalFormedFile()
    {
        $this->expectException(ConfigException::class);
        new JsonConfig(sprintf('%s%s', __DIR__, '/files/config.malformed.json'), true);
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function test__constructEmptyFile()
    {
        $config = new JsonConfig(sprintf('%s%s', __DIR__, '/files/config.empty.json'), true);
        $this->assertInstanceOf(JsonConfig::class, $config);
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function testMerge()
    {
        $config1 = new JsonConfig(sprintf('%s%s', __DIR__, '/files/config.1.json'), true);
        $config2 = new JsonConfig(sprintf('%s%s', __DIR__, '/files/config.2.json'), true);
        $config1->merge($config2);
        $this->assertEquals(['var1' => false,
                             'var2' => '%directory_root%'],
                            $config1->original());
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function testOriginal()
    {
        $config = new JsonConfig(sprintf('%s%s', __DIR__, '/files/config.json'), true);
        $this->assertEquals(['directory_root' => 'test',
                             'debug'          => false,
                             'log'            => 'warning',
                             'envtest'        => '%env:BERLIOZ_FOO%',
                             'var1'           => ['var1-1' => 'value1-1',
                                                  'var1-2' => 'value1-2',
                                                  'var1-3' => 'value1-3'],
                             'var2'           => '%var1.var1-1%+value2',
                             'var3'           => '%directory_root%',
                             'var4'           => '%best_framework%',
                             'var5'           => '%extends:config.1.json, config.2.json%',
                             'var6'           => '%include:config.1.json%',
                             'var7'           => '%debug%',
                             'var8'           => '%userdefined%'],
                            $config->original());
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function testGet()
    {
        $config = new JsonConfig(sprintf('%s%s', __DIR__, '/files/config.json'), true);

        // Test variable
        $this->assertEquals('value1-1', $config->get('var1.var1-1'));
        $this->assertEquals('value1-1+value2', $config->get('var2'));
        $this->assertEquals('%extends:config.1.json, config.2.json%', $config->get('var5'));
        $this->assertEquals('%include:config.1.json%', $config->get('var6'));

        $this->assertEquals(['directory_root' => 'test',
                             'debug'          => false,
                             'log'            => 'warning',
                             'envtest'        => '%env:BERLIOZ_FOO%',
                             'var1'           => ['var1-1' => 'value1-1',
                                                  'var1-2' => 'value1-2',
                                                  'var1-3' => 'value1-3'],
                             'var2'           => 'value1-1+value2',
                             'var3'           => 'test',
                             'var4'           => 'BERLIOZ',
                             'var5'           => '%extends:config.1.json, config.2.json%',
                             'var6'           => '%include:config.1.json%',
                             'var7'           => false,
                             'var8'           => ''],
                            $config->get());
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function testGetNotFound()
    {
        $config = new JsonConfig(sprintf('%s%s', __DIR__, '/files/config.json'), true);
        $this->assertEquals('notfound', $config->get('var100', 'notfound'));
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function testHas()
    {
        $config = new JsonConfig(sprintf('%s%s', __DIR__, '/files/config.json'), true);
        $this->assertFalse($config->has('var23.var1'));
        $this->assertTrue($config->has('var5'));
        $this->assertFalse($config->has('var5.var1'));
        $this->assertTrue($config->has('var6'));
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function testBooleanConversion()
    {
        $config = new JsonConfig(sprintf('%s%s', __DIR__, '/files/config.json'), true);
        $this->assertEquals($config->get('debug'), $config->get('var7'));
        $this->assertFalse($config->get('debug'));
        $this->assertFalse($config->get('var7'));
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function testVariables()
    {
        $config = new JsonConfig(sprintf('%s%s', __DIR__, '/files/config.json'), true);
        $this->assertEquals($config, $config->setVariable('userdefined', 'Berlioz'));
        $this->assertEquals('Berlioz', $config->getVariable('userdefined'));
        $this->assertEquals(['userdefined' => 'Berlioz'],
                            $config->getVariables());
        $this->assertEquals($config, $config->setVariables(['userdefined2' => 'Berlioz2',
                                                            'userdefined3' => 'Berlioz3']));
        $this->assertEquals(['userdefined2' => 'Berlioz2',
                             'userdefined3' => 'Berlioz3'],
                            $config->getVariables());
    }
}
