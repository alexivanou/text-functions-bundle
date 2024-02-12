<?php

/*

    Пишет дату в прошлом словами: "менее минуты назад", "около часа назад", "5 месяцев назад"...
    Чтобы всё заработало, надо в файле переводов прописать:

# Time ago in words - Twig Extension
time_ago:
    #less_than_seconds_ago:  "{1} менее %seconds секунды назад|{2} менее %seconds секунд назад|{5} менее %seconds секунд назад"
    less_than_seconds_ago: только что
    less_than_a_minute_ago: только что
    1_minute_ago: минуту назад
    some_minutes_ago: "{1} %minutes минуту назад|{2} %minutes минуты назад|{5} %minutes минут назад"
    about_1_hour_ago: около часа назад
    about_some_hours_ago: "{1} около %hours часа назад|{2} около %hours часов назад|{5} около %hours часов назад"
    1_day_ago: вчера
    some_days_ago:  "{1} %days день назад|{2} %days дня назад|{5} %days дней назад"
    some_months_ago:  "{1} %months месяц назад|{2} %months месяца назад|{5} %months месяцев назад"
    some_years_ago:  "{1} %years год назад|{2} %years года назад|{5} %years лет назад"
    in_less_than_seconds:  "{1} менее чем через %seconds секунду|{2} менее чем через %seconds секунды|{5} менее чем через %seconds секунд"
    in_less_a_minute: менее чем через минуту
    in_1_minute: через минуту
    in_some_minute: "{1} через %minutes минуту|{2} через %minutes минуты|{5} через %minutes минут"
    in_1_hour: через час
    in_about_some_hours: "{1} через %hours час|{2} через %hours часа|{5} через %hours часов"
    in_1_day: через 1 день
    in_some_days: "{1} через %days день|{2} через %days дня|{5} через %days дней"




 */

namespace Psystems\TextFunctionsBundle\Twig\Extension;

use Symfony\Component\Translation\IdentityTranslator;
use Psystems\TextFunctionsBundle\Services\TextFunctions;

class TimeAgoExtension extends \Twig_Extension
{
    protected $translator;

    /**
     * Constructor method
     *
     * @param IdentityTranslator $translator
     */
    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('distance_of_time_in_words', array($this, 'distanceOfTimeInWordsFilter')),
            new \Twig_SimpleFilter('time_ago_in_words', array($this, 'timeAgoInWordsFilter')),
        );
    }

    /**
     * Like distance_of_time_in_words, but where to_time is fixed to timestamp()
     *
     * @param $from_time String or DateTime
     * @param bool $include_seconds
     * @param bool $include_months
     *
     * @return mixed
     */
    function timeAgoInWordsFilter($from_time, $include_seconds = false, $include_months = false)
    {
        return $this->distanceOfTimeInWordsFilter($from_time, new \DateTime('now'), $include_seconds, $include_months);
    }

    /**
     * Reports the approximate distance in time between two times given in seconds
     * or in a valid ISO string like.
     * For example, if the distance is 47 minutes, it'll return
     * "about 1 hour". See the source for the complete wording list.
     *
     * Integers are interpreted as seconds. So, by example to check the distance of time between
     * a created user an it's last login:
     * {{ user.createdAt|distance_of_time_in_words(user.lastLoginAt) }} returns "less than a minute".
     *
     * Set include_seconds to true if you want more detailed approximations if distance < 1 minute
     * Set include_months to true if you want approximations in months if days > 30
     *
     * @param $from_time String or DateTime
     * @param $to_time String or DateTime
     * @param bool $include_seconds True to return distance in seconds when it's lower than a minute.
     * @param bool $include_months
     *
     * @return mixed
     */
    public function distanceOfTimeInWordsFilter($from_time, $to_time = null, $include_seconds = false, $include_months = false)
    {
        $datetime_transformer = new \Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer(null, null, 'Y-m-d H:i:s');
        $timestamp_transformer = new \Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToTimestampTransformer();

        # Transforming to Timestamp
        if (!($from_time instanceof \DateTime) && !is_numeric($from_time)) {
            $from_time = $datetime_transformer->reverseTransform($from_time);
            $from_time = $timestamp_transformer->transform($from_time);
        } elseif($from_time instanceof \DateTime) {
            $from_time = $timestamp_transformer->transform($from_time);
        }

        $to_time = empty($to_time) ? new \DateTime('now') : $to_time;

        # Transforming to Timestamp
        if (!($to_time instanceof \DateTime) && !is_numeric($to_time)) {
            $to_time = $datetime_transformer->reverseTransform($to_time);
            $to_time = $timestamp_transformer->transform($to_time);
        } elseif($to_time instanceof \DateTime) {
            $to_time = $timestamp_transformer->transform($to_time);
        }

        $future = ($to_time < $from_time) ? true : false;

        $distance_in_minutes = round((abs($to_time - $from_time))/60);
        $distance_in_seconds = round(abs($to_time - $from_time));

        if($future){
            return $this->future($distance_in_minutes,$include_seconds,$distance_in_seconds);
        }

        if ($distance_in_minutes <= 1){
            if ($include_seconds){
                if ($distance_in_seconds < 5){
                    return $this->translator->transChoice('time_ago.less_than_seconds_ago', TextFunctions::pluralNumber(5), array('%seconds' => 5));
                }
                elseif($distance_in_seconds < 10){
                    return $this->translator->transChoice('time_ago.less_than_seconds_ago', TextFunctions::pluralNumber(10), array('%seconds' => 10));
                }
                elseif($distance_in_seconds < 20){
                    return $this->translator->transChoice('time_ago.less_than_seconds_ago', TextFunctions::pluralNumber(20), array('%seconds' => 20));
                }
                elseif($distance_in_seconds < 60){
                    return $this->translator->trans('time_ago.less_than_a_minute_ago');
                }
                else {
                    return $this->translator->trans('time_ago.1_minute_ago');
                }
            }
            return ($distance_in_minutes==0) ? $this->translator->trans('time_ago.less_than_a_minute_ago', array()) : $this->translator->trans('time_ago.1_minute_ago', array());
        }
        elseif ($distance_in_minutes <= 45){
            return $this->translator->transChoice('time_ago.some_minutes_ago', TextFunctions::pluralNumber($distance_in_minutes), array('%minutes' => $distance_in_minutes));
        }
        elseif ($distance_in_minutes <= 90){
            return $this->translator->trans('time_ago.about_1_hour_ago');
        }
        elseif ($distance_in_minutes <= 1440){
            return $this->translator->transChoice('time_ago.about_some_hours_ago', TextFunctions::pluralNumber(round($distance_in_minutes/60)), array('%hours' => round($distance_in_minutes/60)));
        }
        elseif ($distance_in_minutes <= 2880){
            return $this->translator->trans('time_ago.1_day_ago');
        }
        else{
            $distance_in_days = round($distance_in_minutes/1440);
            if (!$include_months || $distance_in_days <= 30) {
                return $this->translator->transChoice('time_ago.some_days_ago', TextFunctions::pluralNumber($distance_in_days), array('%days' => round($distance_in_days)));
            }
            elseif ($distance_in_days < 345) {
                return $this->translator->transchoice('time_ago.some_months_ago', TextFunctions::pluralNumber(round($distance_in_days/30)), array('%months' => round($distance_in_days/30)));
            }
            else {
                return $this->translator->transchoice('time_ago.some_months_ago', TextFunctions::pluralNumber(round($distance_in_days/365)), array('%years' => round($distance_in_days/365)));
            }
        }
    }


    // TODO
    private function future($distance_in_minutes,$include_seconds,$distance_in_seconds){
        if ($distance_in_minutes <= 1){
            if ($include_seconds){
                if ($distance_in_seconds < 5){
                    return $this->translator->transchoice('time_ago.in_less_than_seconds', TextFunctions::pluralNumber(5), array('%seconds' => 5));
                }
                elseif($distance_in_seconds < 10){
                    return $this->translator->transchoice('time_ago.in_less_than_seconds', TextFunctions::pluralNumber(10), array('%seconds' => 10));
                }
                elseif($distance_in_seconds < 20){
                    return $this->translator->transchoice('time_ago.in_less_than_seconds', TextFunctions::pluralNumber(20), array('%seconds' => 20));
                }
                elseif($distance_in_seconds < 60){
                    return $this->translator->trans('time_ago.in_less_a_minute');
                }
                else {
                    return $this->translator->trans('time_ago.in_1_minute');
                }
            }
            return ($distance_in_minutes===0) ? $this->translator->trans('time_ago.in_less_a_minute', array()) : $this->translator->trans('time_ago.in_1_minute', array());
        }
        elseif ($distance_in_minutes <= 45){
            return $this->translator->transchoice('time_ago.in_some_minute', TextFunctions::pluralNumber($distance_in_minutes), array('%minutes' => $distance_in_minutes));
        }
        elseif ($distance_in_minutes <= 90){
            return $this->translator->trans('time_ago.in_1_hour');
        }
        elseif ($distance_in_minutes <= 1440){
            return $this->translator->transchoice('time_ago.in_about_some_hours', TextFunctions::pluralNumber(round($distance_in_minutes/60)), array('%hours' => round($distance_in_minutes/60)));
        }
        elseif ($distance_in_minutes <= 2880){
            return $this->translator->trans('time_ago.in_1_day');
        }
        else{
            return $this->translator->transchoice('time_ago.in_some_days', TextFunctions::pluralNumber(round($distance_in_minutes/1440)), array('%days' => round($distance_in_minutes/1440)));
        }

    }


    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'time_ago_extension';
    }


}
