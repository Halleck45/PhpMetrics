<?php
namespace Hal\Metric\Class_\Structural;

use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node as TreeNode;
use Hal\Metric\Information\LackOfCohesionOfMethods;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Lack of cohesion of methods
 *
 * Class ExternalsVisitor
 * @package Hal\Metric\Class_\Coupling
 */
class LcomVisitor extends NodeVisitorAbstract
{

    /**
     * @var Metrics
     */
    private $metrics;

    /**
     * ClassEnumVisitor constructor.
     * @param Metrics $metrics
     */
    public function __construct(Metrics $metrics)
    {
        $this->metrics = $metrics;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Class_) {

            // we build a graph of internal dependencies in class
            $graph = new Graph();
            $class = $this->metrics->get($node->namespacedName->toString());

            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Stmt\ClassMethod) {

                    if (!$graph->has($stmt->name . '()')) {
                        $graph->insert(new TreeNode($stmt->name . '()'));
                    }
                    $from = $graph->get($stmt->name . '()');

                    \iterate_over_node($stmt, function ($node) use ($from, &$graph) {

                        if ($node instanceof Node\Expr\PropertyFetch && isset($node->var->name) && $node->var->name == 'this') {
                            $name = getNameOfNode($node);
                            // use of attribute $this->xxx;
                            if (!$graph->has($name)) {
                                $graph->insert(new TreeNode($name));
                            }
                            $to = $graph->get($name);
                            $graph->addEdge($from, $to);
                            return;
                        }


                        if ($node instanceof Node\Expr\MethodCall) {
                            if (!$node->var instanceof Node\Expr\New_ && isset($node->var->name) && getNameOfNode($node->var) === 'this') {
                                // use of method call $this->xxx();
                                // use of attribute $this->xxx;
                                $name = getNameOfNode($node->name) . '()';
                                if (!$graph->has($name)) {
                                    $graph->insert(new TreeNode($name));
                                }
                                $to = $graph->get($name);
                                $graph->addEdge($from, $to);
                                return;
                            }
                        }

                    });
                }
            }

            // we count paths
            $paths = 0;
            foreach ($graph->all() as $node) {
                $paths += $this->traverse($node);
            }

            $class->set(LackOfCohesionOfMethods::ID, $paths);
        }
    }

    /**
     * Traverse node, and return 1 if node has not been visited yet
     *
     * @param TreeNode $node
     * @return int
     */
    private function traverse(TreeNode $node)
    {
        if ($node->visited) {
            return 0;
        }
        $node->visited = true;

        foreach ($node->getAdjacents() as $adjacent) {
            $this->traverse($adjacent);
        }

        return 1;
    }
}
