<?php

namespace Psystems\TextFunctionsBundle\Services;

class TextFunctions
{
    /**
     * Возвращает 1, 2 или 5
     * @return int
     */
    public static function pluralNumber($count) : int
    {
        $count = abs($count) % 100;
        if ( $count > 10 && $count < 20 ) return 5;
        $count %= 10;
        if ( $count > 1 && $count < 5 ) return 2;
        if ( $count == 1 ) return 1;
        return 5;
    }

    /**
     *
     * @param  integer $count
     * @param  string  $str   1 пользователь 1 user
     * @param  string  $str2  2 пользователя 2 users
     * @param  string  $str5  5 пользователей 10 users
     * @return string
     */
    public static function plural($count, string $str1, string $str2, string $str5) : string
    {
        $str = [1 => $str1, 2 => $str2, 5 => $str5];
        return $str[self::pluralNumber($count)];
    }

    /**
     * @param     $price
     * @param int $precision
     * @return null|string
     */
    public function asPrice($price, $precision = 0)
    {
        if (null === $price) {
            return null;
        }

        return number_format($price, $precision, '.', '&nbsp;') . '&nbsp;pyб.';
    }

    /**
     * Возвращает сумму прописью
     * @author runcore http://habrahabr.ru/post/53210/
     * @uses plural(...)
     *
     *
     */
    public function num2str($num) : string
    {
        $nul='ноль';
        $ten=array(
            array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
            array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
        );
        $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
        $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
        $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
        $unit=array( // Units
            array('копейка' ,'копейки' ,'копеек',  1),
            array('рубль'   ,'рубля'   ,'рублей'    ,0),
            array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
            array('миллион' ,'миллиона','миллионов' ,0),
            array('миллиард','милиарда','миллиардов',0),
        );
        //
        list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub)>0) {
            foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
                if (!intval($v)) continue;
                $uk = sizeof($unit)-$uk-1; // unit key
                $gender = $unit[$uk][3];
                list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
                else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                // units without rub & kop
                if ($uk>1) $out[]= $this->plural($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
            } //foreach
        }
        else $out[] = $nul;
        $out[] = $this->plural(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
        $out[] = $kop.' '.$this->plural($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
    }
}