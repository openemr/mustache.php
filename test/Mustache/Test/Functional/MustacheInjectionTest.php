<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2017 Justin Hileman
 * (c) 2022 Discover and Change, Inc.
 * 
 * @author Justin Hileman
 * @author Stephen Nielson <snielson@discoverandchange.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @group mustache_injection
 * @group functional
 */
class Mustache_Test_Functional_MustacheInjectionTest extends PHPUnit_Framework_TestCase
{
    private $mustache;

    public function setUp()
    {
        $this->mustache = new Mustache_Engine();
    }

    /**
     * @dataProvider injectionData
     */
    public function testInjection($tpl, $data, $partials, $expect)
    {
        $this->mustache->setPartials($partials);
        $this->assertEquals($expect, $this->mustache->render($tpl, $data));
    }

    public function testObjectInjection()
    {
        $contextObj = new Mustache_Test_Functional_Omega();
        // should lookup the name method that returns 'Foo' and attempt to concatenate the context variable 'b'
        // which will lookup and return 'd' as the value.
        $this->assertEquals('Food', $this->mustache->render('{{name}}', $contextObj));
    }

    public function injectionData()
    {
        $interpolationData = array(
            'a' => '{{ b }}',
            'b' => 'FAIL',
        );

        $sectionData = array(
            'a' => true,
            'b' => '{{ c }}',
            'c' => 'FAIL',
        );

        $lambdaInterpolationData = array(
            'a' => array($this, 'lambdaInterpolationCallback'),
            'b' => '{{ c }}',
            'c' => 'FAIL',
        );

        $lambdaSectionData = array(
            'a' => array($this, 'lambdaSectionCallback'),
            'b' => '{{ c }}',
            'c' => 'FAIL',
        );

        $lambdaInterpolationContextData = array(
            'a' => array($this, 'lambdaInterpolationCallbackWithContext'),
            'b' => '{{ c }}',
            'c' => '{{ d }}',
            'd' => 'FAIL',
        );

        return array(
            array('{{ a }}',   $interpolationData, array(), '{{ b }}'),
            array('{{{ a }}}', $interpolationData, array(), '{{ b }}'),

            array('{{# a }}{{ b }}{{/ a }}',   $sectionData, array(), '{{ c }}'),
            array('{{# a }}{{{ b }}}{{/ a }}', $sectionData, array(), '{{ c }}'),

            array('{{> partial }}', $interpolationData, array('partial' => '{{ a }}'),   '{{ b }}'),
            array('{{> partial }}', $interpolationData, array('partial' => '{{{ a }}}'), '{{ b }}'),

            array('{{ a }}',           $lambdaInterpolationData, array(), '{{ c }}'),
            array('{{# a }}b{{/ a }}', $lambdaSectionData,       array(), '{{ c }}'),
            array('{{ a }}', $lambdaInterpolationContextData, array(), '{{ d }}'),
        );
    }

    public static function lambdaInterpolationCallback()
    {
        return '{{ b }}';
    }

    public static function lambdaSectionCallback($text)
    {
        return '{{ ' . $text . ' }}';
    }

    public static function lambdaInterpolationCallbackWithContext($context)
    {
        return $context->find('b');
    }
}

class Mustache_Test_Functional_Omega
{
    protected $_name = 'Foo';
    public $b = 'd';

    public function name(Mustache_Context $context)
    {
        $b = $context->find('b');
        return $this->_name . $b;
    }
}