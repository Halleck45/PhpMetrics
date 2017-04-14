<?php
namespace Hal\Report\Violations\Xml;

use Hal\Application\Config\Config;
use Hal\Component\Output\Output;
use Hal\Metric\Consolided;
use Hal\Metric\Metrics;
use Hal\Violation\Violation;
use Symfony\Component\Console\Output\OutputInterface;

class Reporter
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Output
     */
    private $output;

    /**
     * Reporter constructor.
     * @param Config $config
     * @param Output $output
     */
    public function __construct(Config $config, Output $output)
    {
        $this->config = $config;
        $this->output = $output;
    }


    public function generate(Metrics $metrics)
    {

        $logFile = $this->config->get('report-violations');
        if (!$logFile) {
            return;
        }

        // map of levels
        $map = [
            Violation::CRITICAL => 4,
            Violation::ERROR => 3,
            Violation::WARNING => 1,
            Violation::INFO => 0,
        ];

        // root
        $xml = new \DOMDocument("1.0", "UTF-8");
        $xml->formatOutput = true;
        $root = $xml->createElement("pmd");
        $root->setAttribute('version', '@package_version@');
        $root->setAttribute('timestamp', date('c'));

        foreach ($metrics->all() as $metric) {
            $violations = $metric->get('violations');
            if (sizeof($violations) == 0) {
                continue;
            }

            $node = $xml->createElement('file');
            $node->setAttribute('name', $metric->get('name'));

            foreach ($violations as $violation) {
                $item = $xml->createElement('violation');
                $item->setAttribute('beginline', 1);
                $item->setAttribute('rule', $violation->getName());
                $item->setAttribute('ruleset', $violation->getName());
                $item->setAttribute('externalInfoUrl', 'http://phpmetrics.org/documentation/index.html');
                $item->setAttribute('priority', $map[$violation->getLevel()]);
                $item->nodeValue = $violation->getDescription();
                $node->appendChild($item);
            }

            $root->appendChild($node);
        }

        $xml->appendChild($root);

        // save file
        file_exists(dirname($logFile)) || mkdir(dirname($logFile), 0755, true);
        file_put_contents($logFile, $xml->saveXML());

        $this->output->writeln(sprintf('XML report generated in "%s"', $logFile));
    }
}
