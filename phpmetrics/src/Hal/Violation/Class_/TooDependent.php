<?php
namespace Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\ShouldNotHappenException;
use Hal\Violation\Violation;

class TooDependent implements Violation
{

    /** @var Metric|null */
    private $metric;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Too dependent';
    }

    /**
     * @inheritdoc
     */
    public function apply(Metric $metric)
    {
        if (!$metric instanceof ClassMetric) {
            return;
        }

        $this->metric = $metric;

        if ($this->metric->get('efferentCoupling') >= 20) {
            $this->metric->get('violations')->add($this);
            return;
        }
    }

    /**
     * @inheritdoc
     */
    public function getLevel()
    {
        return Violation::INFO;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        if ($this->metric === null) {
            throw new ShouldNotHappenException('Metric property is null');
        }

        return <<<EOT
This class looks use really high number of components.

* Efferent coupling is {$this->metric->get('efferentCoupling')}, so this class uses {$this->metric->get('efferentCoupling')} different external components.

Maybe you should check why this class has lot of dependencies.
EOT;
    }
}
