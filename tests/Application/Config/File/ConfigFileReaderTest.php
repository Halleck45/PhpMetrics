<?php

use Hal\Application\Config\File\ConfigFileReaderInterface;

use PHPUnit\Framework\TestCase;

class ConfigFileReaderTest extends TestCase
{
    public function testJsonConfigFile()
    {
        $configs = [
            __DIR__.'/examples/config.json',
            __DIR__.'/examples/config.ini',
        ];

        foreach ($configs as $filename) {
            $config = new \Hal\Application\Config\Config();

            /** @var ConfigFileReaderInterface $reader */
            $reader = \Hal\Application\Config\File\ConfigFileReaderFactory::createFromFileName($filename);
            $reader->read($config);

            $this->assertEquals($this->getExpectedData(), $config->all());
        }
    }

    /**
     *
     */
    private function getExpectedData()
    {
        return [
            'exclude'     => 'test1',
            'report-html' => 'test2',
        ];
    }
}
