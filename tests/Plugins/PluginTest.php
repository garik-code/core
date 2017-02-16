<?php


namespace Kitrix\Plugins;


use Kitrix\Core;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /** @var Plugin */
    private $plugin;

    private $rootPath;

    public function setUp()
    {
        $this->rootPath = basename(__DIR__ . "../../");
        $this->plugin = new Core('Kitrix', 'Core', $this->rootPath);
    }

    public function testMainMeta() {
        self::assertEquals('Kitrix', $this->plugin->getNamespace());
        self::assertEquals('Core', $this->plugin->getId());
        self::assertEquals('\\Kitrix\\Core', $this->plugin->getClassPath());
        self::assertEquals($this->rootPath, $this->plugin->getPath());
        self::assertEquals("kitrix_core", $this->plugin->getUnderScoredName());

        // set meta
        $t = "Test Core Plugin";
        $this->plugin->setTitle($t);
        self::assertEquals($t, $this->plugin->getTitle());

        $t = "Test Core Desc";
        $this->plugin->setDescription($t);
        self::assertEquals($t, $this->plugin->getDescription());

        // test authors
        $t = ["One" => ["name" => "one"]];
        self::assertEquals([], $this->plugin->getAuthors());
        $this->plugin->setAuthors($t);
        self::assertEquals($t, $this->plugin->getAuthors());
    }

    public function testGetInstance() {

        $core = Core::getInstance();
        self::assertInstanceOf('\\Kitrix\\Core', $core);
        self::assertEquals(true, $this->plugin instanceof Core);
    }

    /**
     * @param $test
     * @param $expectedBool
     * @dataProvider nameSpaceProvider
     */
    public function testValidateNameSpace($test, $expectedBool) {

        self::assertEquals($expectedBool, $this->plugin->validateNameSpace($test));

    }

    public function nameSpaceProvider() {
        return [
            ['SomeNameSpace', true],
            ['_notValidNameSpace', false],
            ['notValid', false],
            ['NOT_VALID_SAME', false],
            ['ThisISVALID', true]
        ];
    }

}
