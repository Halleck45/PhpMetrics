<?php

namespace Test;
use Hal\Component\Parser\CodeParser;
use Hal\Component\Reflected\File;
use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Parser\Searcher;
use Hal\Component\Token\Token;
use Hal\Component\Token\Tokenizer;

/**
 * @group parser
 */
class DependenciesTest extends \PHPUnit_Framework_TestCase {

    public function testStaticCallsAreFound()
    {
        $code = <<<EOT
namespace Demo;
class A {
    public function foo() {
        B::bar();
        self::bar();
        parent::foo();
    }
}
class B { }
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(2, sizeof($classes));
        $classA = $classes[0];
        $methods = $classA->getMethods();
        $this->assertEquals(1, sizeof($methods));

        // Calls
        // -------------
        $calls = $methods['foo']->getCalls();
        $this->assertEquals(3, sizeof($calls));
        $this->assertEquals('\Demo\B', $calls[0]->getType());
        $this->assertEquals('bar', $calls[0]->getMethodName());
        $this->assertTrue($calls[0]->isStatic());
        $this->assertFalse($calls[0]->isParent());
        $this->assertFalse($calls[0]->isItself());

        $this->assertTrue($calls[1]->isStatic());
        $this->assertFalse($calls[1]->isParent());
        $this->assertTrue($calls[1]->isItself());

        $this->assertTrue($calls[2]->isStatic());
        $this->assertTrue($calls[2]->isParent());
        $this->assertFalse($calls[2]->isItself());
    }

    public function testInstanciedCallsAreFound()
    {
        $code = <<<EOT
namespace Demo;
class A {
    public function foo() {
         \$v = new B;
        \$v->baz();
        (new C)->baz();
    }
}
class B { }
EOT;
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(2, sizeof($classes));
        $classA = $classes[0];
        $methods = $classA->getMethods();
        $this->assertEquals(1, sizeof($methods));

        // Dependencies
        // -------------
        $dependencies = $methods['foo']->getCalls();
        $this->assertEquals(2, sizeof($dependencies));
        $this->assertEquals('\Demo\B', $dependencies[0]->getType());
        $this->assertEquals('\Demo\C', $dependencies[1]->getType());
    }

    public function testTypedReturnOfPhp7AreFound() {
        $code = <<<EOT
namespace My;
class Class1 {
    public function foo(): Class2 {
    }
}
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(1, sizeof($classes));
        $classA = $classes[0];
        $methods = $classA->getMethods();
        $this->assertEquals(1, sizeof($methods));

        // Returns
        // -------------
        $returns = $methods['foo']->getReturns();
        $this->assertEquals(1, sizeof($returns));
        $this->assertEquals('\\My\\Class2', $returns[0]->getType());
    }

    public function testReturnsInCodeAreFound() {
        $code = <<<EOT
namespace My;
class Class1 {
    public function foo(){
        if(true) {
            return new \B;
        } else {
            return new Class2;
        }

        return 1;
    }
}
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(1, sizeof($classes));
        $classA = $classes[0];
        $methods = $classA->getMethods();
        $this->assertEquals(1, sizeof($methods));

        // Returns
        // -------------
        $returns = $methods['foo']->getReturns();
        $this->assertEquals(3, sizeof($returns));
        $this->assertEquals('\\B', $returns[0]->getType());
        $this->assertEquals('\\My\\Class2', $returns[1]->getType());
        $this->assertEquals(Token::T_VALUE_INTEGER, $returns[2]->getType());
    }


    public function testMixedDependenciesAreFound()
    {
        $code = <<<EOT
namespace Demo;
class A {
    public function foo(\C \$c): \ReturnedValue {
         \$v = new B;
        (new C)->baz();
        \D::foo();
    }
}
EOT;
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(1, sizeof($classes));
        $classA = $classes[0];


        $expected = array(
            '\\ReturnedValue',
            '\\C',
            '\\Demo\B',
            '\\Demo\C',
            '\\D',
        );
        $this->assertEquals($expected, $classA->getDependencies());

    }

    public function testMCallsOnItselfAreFound()
    {
        $code = <<<EOT
namespace Demo;
class A {
    public function foo() {
         return \$this->bar();
    }

    public function bar() {}
}
EOT;
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(1, sizeof($classes));
        $classA = $classes[0];
        $methods =$classA->getMethods();
        $this->assertEquals(2, sizeof($methods));
        $method = $methods['foo'];

        $this->assertEquals(1, sizeof($method->getCalls()));
        $call = $method->getCalls()[0];
        $this->assertTrue($call->isItself());
        $this->assertEquals('bar', $call->getMethodName());

    }


    /**
     * @expectedException \Hal\Component\Parser\Exception\IncorrectSyntaxException
     */
    public function testIncorrectSyntaxCallsThrowException()
    {
        $code = <<<EOT
namespace Demo;
class A {
    public function foo() {
         return new;
    }
}
EOT;
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);

    }
}