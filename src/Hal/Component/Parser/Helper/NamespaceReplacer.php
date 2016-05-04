<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Parser\Helper;


use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Token\Token;

class NamespaceReplacer
{

    /**
     * @var NamespaceResolver
     */
    private $namespaceResolver;

    /**
     * NamespaceReplacer constructor.
     * @param NamespaceResolver $namespaceResolver
     */
    public function __construct(NamespaceResolver $namespaceResolver)
    {
        $this->namespaceResolver = $namespaceResolver;
    }

    /**
     * Replace namespaces and aliases in tokens
     *
     * @param $tokens
     * @return mixed
     */
    public function replace($tokens)
    {
        $len = sizeof($tokens);
        $aliases = $this->namespaceResolver->all();
        $replaced = array();

        for ($i = 0; $i < $len; $i++) {

            // remove uses
            if (Token::T_USE == $tokens[$i]) {
                $i++;
                if (isset($tokens[$i + 1]) && Token::T_AS == $tokens[$i + 1]) {
                    $i = $i + 2;
                }
                continue;
            }

            // replace aliases
            foreach ($aliases as $alias => $name) {
                if ($alias === $tokens[$i]) {
                    $tokens[$i] = $name;
                }
            }

            array_push($replaced, $tokens[$i]);
        }
        return $replaced;
    }

}