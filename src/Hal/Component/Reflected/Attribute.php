<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Reflected;


use Hal\Component\Token\Token;

class Attribute
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $visibility;

    /**
     * @var boolean
     */
    private $isStatic;

    /**
     * Attribute constructor.
     * @param string $name
     * @param string $visibility
     * @param bool $isStatic
     */
    public function __construct($name, $visibility = Token::T_VISIBILITY_PUBLIC, $isStatic = false)
    {
        $this->name = $name;
        $this->visibility = $visibility;
        $this->isStatic = $isStatic;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return Token::T_VISIBILITY_PUBLIC === $this->getVisibility();
    }

    /**
     * @return boolean
     */
    public function isStatic()
    {
        return $this->isStatic;
    }
}