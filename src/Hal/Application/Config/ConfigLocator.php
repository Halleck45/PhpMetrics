<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;
use Hal\Component\Config\Hydrator;
use Hal\Component\Config\Loader;
use Hal\Component\Config\Validator;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Config locator
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ConfigLocator
{
    /**
     * Default files to check
     * @var array
     */
    private $defaults = array();

    /**
     * Constructor
     *
     * @param array $defaults
     */
    public function __construct(array $defaults = null) {
        if(is_null($defaults)) {
            $defaults = array('.phpmetrics.yml', '.phpmetrics.yml.dist', '.phpmetrics-dist.yml');
        }
        $this->defaults = $defaults;
    }

    /**
     * Locates file. Il no file is provided, it will search for default .phpmetrics.yml file
     *
     * @param $filename
     * @return string
     */
    public function locate($filename) {

        if(null === $filename) {
            // try to use default configfile : .phpmetrics.yml or .phpmetrics.yml.dist
            foreach($this->defaults as $filenameToCheck) {
                $filenameToCheck = getcwd().DIRECTORY_SEPARATOR.$filenameToCheck;
                if (\file_exists($filenameToCheck) && \is_readable($filenameToCheck)) {
                    return $filenameToCheck;
                }
            }
        }

        if(!\file_exists($filename) ||!\is_readable($filename)) {
            throw new \RuntimeException('configuration file is not accessible');
        }

        return $filename;
    }
}