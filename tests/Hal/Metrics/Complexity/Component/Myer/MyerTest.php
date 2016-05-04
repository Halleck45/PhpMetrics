<?php
namespace Test\Hal\Metrics\Complexity\Text\Halstead;

use Hal\Component\Token\Tokenizer;
use Hal\Metrics\Complexity\Component\Myer\Myer;

/**
 * @group myer
 * @group metric
 */
class MyerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider provideIntervals
     */
    public function testICanGetMyerInterval($filename, $interval, $distance) {

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize(file_get_contents($filename));
        $object = new Myer();
        $class = $this->getMock('\Hal\Component\Reflected\Klass');
        $class->method('getTokens')->will($this->returnValue($tokens));
        $result = $object->calculate($class);
        $this->assertEquals($interval, $result->getInterval());
        $this->assertEquals($distance, $result->getDistance());
    }

    public function provideIntervals() {
        return array(
            array(__DIR__.'/../../../../../resources/myer/f1.php', '4:5', 1)
            , array(__DIR__.'/../../../../../resources/myer/f2.php', '9:16', 7)
        );
    }

    public function testMyerResultCanBeConvertedToArray() {

        $result = new \Hal\Metrics\Complexity\Component\Myer\Result();
        $array = $result->asArray();
        $this->assertArrayHasKey('myerInterval', $array);
        $this->assertArrayHasKey('myerDistance', $array);
    }
}
