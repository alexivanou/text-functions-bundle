<?php

/**
 * Сборник различных функций и фильтров по работе с текстом. Фактически, обёртка над
 */

namespace Psystems\TextFunctionsBundle\Twig\Extension;

use Psystems\TextFunctionsBundle\Services\TextFunctions;

class TextExtension extends \Twig_Extension
{
    private $text_functions;

    public function __construct(TextFunctions $text_functions)
    {
        $this->text_functions = $text_functions;
    }

    public function getName()
    {
        return 'text';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('plural', array($this, 'plural')),
            new \Twig_SimpleFunction('print_r', array($this, 'print_r')),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('price', array($this, 'priceFilter'), array('is_safe'=>array('html'))),
            new \Twig_SimpleFilter('num2str', array($this, 'num2strFilter')),
        );
    }

    /**
     *
     * @param  integer $count
     * @param  string  $str   1 пользователь 1 user
     * @param  string  $str2  2 пользователя 2 users
     * @param  string  $str5  5 пользователей 10 users
     * @return string
     */
    public function plural($count, $str1, $str2, $str5)
    {
        return $this->text_functions->plural($count, $str1, $str2, $str5);
    }

    public function print_r($value)
    {
        return print_r($value, true);
    }

    public function priceFilter($price, $precision = 0)
    {
        return $this->text_functions->asPrice($price, $precision);
    }


    /**
     * Возвращает сумму прописью
     * @author runcore http://habrahabr.ru/post/53210/
     * @uses plural(...)
     *
     *
     */
    public function num2strFilter($num) {
        return $this->text_functions->num2str($num);
    }

}
