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
use Berlioz\Config\ExtendedJsonConfig;
use PHPUnit\Framework\TestCase;

class ExtendedJsonConfigTest extends TestCase
{
    public const TEST_CONSTANT = 'foobar';

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function test__construct()
    {
        $config = new ExtendedJsonConfig(sprintf('%s%s', __DIR__, '/files/config.json'), true);
        $this->assertInstanceOf(ExtendedJsonConfig::class, $config);
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function test__constructNoUrl()
    {
        $json = file_get_contents(sprintf('%s%s', __DIR__, '/files/config.2.json'));
        $config = new ExtendedJsonConfig($json);
        $this->assertInstanceOf(ExtendedJsonConfig::class, $config);
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function test__constructNotExistsFile()
    {
        $this->expectException(NotFoundException::class);
        new ExtendedJsonConfig(sprintf('%s%s', __DIR__, '/files/config.notexists.json'), true);
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function test__constructMalFormedFile()
    {
        $this->expectException(ConfigException::class);
        new ExtendedJsonConfig(sprintf('%s%s', __DIR__, '/files/config.malformed.json'), true);
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function testGet()
    {
        $config = new ExtendedJsonConfig(sprintf('%s%s', __DIR__, '/files/config.json'), true);

        // Test variable
        $this->assertEquals('value1-1+value2', $config->get('var2'));

        // Test extends
        $this->assertEquals(false, $config->get('var5.var1'));

        // Test include
        $this->assertEquals(true, $config->get('var6.var1'));

        // Test env
        $this->assertEquals('BAR', $config->get('envtest'));

        // Test constant
        $this->assertEquals(PHP_VERSION, $config->get('consttest1'));
        $this->assertEquals('foobar', $config->get('consttest2'));
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function testGetNotFound()
    {
        $config = new ExtendedJsonConfig(sprintf('%s%s', __DIR__, '/files/config.json'), true);
        $this->assertEquals('notfound', $config->get('var100', 'notfound'));
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function testHas()
    {
        $config = new ExtendedJsonConfig(sprintf('%s%s', __DIR__, '/files/config.json'), true);
        $this->assertFalse($config->has('var23.var1'));
        $this->assertTrue($config->has('var5.var1'));
        $this->assertTrue($config->has('var6.var1'));
        $this->assertTrue($config->has('var1.var1-3'));
    }

    /**
     * @throws \Berlioz\Config\Exception\ConfigException
     */
    public function testConfigExtended()
    {
        $config = new ExtendedJsonConfig(sprintf('%s%s', __DIR__, '/files/config.extended.json'), true);
        $this->assertFalse($config->has('var23.var1'));
        $this->assertTrue($config->has('var5.var1'));
        $this->assertEquals('warning', $config->get('log'));
        $this->assertTrue($config->get('debug'));
    }

    public function testConfigUserDefinedAction()
    {
        ExtendedJsonConfig::addAction(
            'actionTest',
            function ($value) {
                return intval($value) * 2;
            }
        );
        $config = new ExtendedJsonConfig(sprintf('%s%s', __DIR__, '/files/config.extended2.json'), true);
        $this->assertEquals(10, $config->get('action'));
    }
}
