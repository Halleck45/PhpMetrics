<?php

namespace Test;

use Hal\Component\Tree\Edge;
use Hal\Component\Tree\Node;
use PHPUnit\Framework\TestCase;

/**
 * @group tree
 */
class NodeTest extends TestCase {

    public function testICanWorkWithNode() {

        $node = new Node('A');
        $to = new Node('B');
        $edge = new Edge($node, $to);
        $node->addEdge($edge);
        $node->setData('value1');

        $this->assertEquals('value1', $node->getData());
        $this->assertEquals(array('B' => $to), $node->getAdjacents());
        $this->assertEquals('A', $node->getKey());

    }

}
