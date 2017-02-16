<?php


namespace Kitrix\Plugins;


use Kitrix\Core;
use PHPUnit\Framework\TestCase;

class PluginsManagerTest extends TestCase
{
    /** @var PluginsManager */
    private $manager;

    public function setUp()
    {
        $this->manager = PluginsManager::getInstance();
        self::assertEquals(true, $this->manager instanceof PluginsManager);
    }

    public function testPluginGet() {

        $core = $this->manager->getPlugin('Kitrix', 'Core');
        self::assertEquals(true, $core instanceof Core);

        $this->expectException('Exception');
        $notValid = $this->manager->getPlugin('SomeRandom', 'NotExistPlugin');
        self::assertEquals(true, $notValid === null);

        $plugins = $this->manager->getRegisteredPlugins();
        self::assertArrayHasKey('Kitrix', $plugins);
        self::assertArrayHasKey('Core', $plugins['Kitrix']);
        self::assertInstanceOf('\\Kitrix\\Core', $plugins['Kitrix']['Core']);

        foreach ($this->manager->getRegisteredPluginsList() as $plugin) {
            self::assertInstanceOf('\\Kitrix\\Plugin', $plugin);
        }
    }
}
