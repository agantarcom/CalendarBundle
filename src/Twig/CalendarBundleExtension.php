<?php

namespace AgantarCom\CalendarBundle\Twig;

use DateTime;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class CalendarBundleExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('calendarWeekHeader', [$this, 'calendarWeekHeader']),
            new TwigFunction('mois', [$this, 'mois']),
            new TwigFunction('jour', [$this, 'jour']),
            new TwigFunction('calendarMonthlyHeader', [$this, 'calendarMonthlyHeader']),
        ];
    }

    public function jour(DateTime $date)
    {
        $dayNumber = $date->format('l');
        $names = [
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche',
        ];

        return $names[$dayNumber];
    }

    public function mois($date, $year = false)
    {
        $month = $date->format('F');

        $names = [
            'January' => 'Janvier',
            'February' => 'Février',
            'March' => 'Mars',
            'April' => 'Avril',
            'May' => 'Mai',
            'June' => 'Juin',
            'July' => 'Juillet',
            'August' => 'Août',
            'September' => 'Septembre',
            'October' => 'Octobre',
            'November' => 'Novembre',
            'December' => 'Décembre',
        ];
        $extra = $year ? ' ' . $date->format('Y') : '';

        return $names[$month] . $extra;
    }

    public function calendarWeekHeader($date)
    {
        $date = clone $date;
        $day = $date->format('l');
        $names = [
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche',
        ];

        return $names[$day] . ' ' . $date->format('d');
    }

    public function calendarMonthlyHeader()
    {
        return [
            'Lundi',
            'Mardi',
            'Mercredi',
            'Jeudi',
            'Vendredi',
            'Samedi',
            'Dimanche',
        ];
    }
}
