<?php

namespace AgantarCom\CalendarBundle\Service;

use Twig\Markup;
use Twig\Environment;

use AgantarCom\CalendarBundle\Model\DayModel;
use AgantarCom\CalendarBundle\Trait\DataTrait;
use AgantarCom\CalendarBundle\Trait\ViewTrait;
use AgantarCom\CalendarBundle\Trait\EventTrait;
use AgantarCom\CalendarBundle\Trait\ConfigTrait;

use AgantarCom\CalendarBundle\Trait\TemplateTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use AgantarCom\CalendarBundle\Config\CalendarConfig;
use AgantarCom\CalendarBundle\Config\TemplateConfig;

/**
 * Class CalendarService.
 *
 * This class provides functionality related to calendar operations.
 *
 *  EVENTS FORMAT
 * [
 *      date => DateTime
 *      start => DateTime
 *      end => DateTime
 *      type => type of event
 *      metadata => [ ... any custom data ]
 * ]
 */
class CalendarBundleService
{
    use ViewTrait;
    use DataTrait;
    use EventTrait;
    use ConfigTrait;
    use TemplateTrait;

    const QUERYARG_DATE = 'CalendarDate';
    const QUERYARG_VIEW = 'CalendarView';

    private $twig;

    private $date;
    private $month;

    public function __construct(
        Environment $twig,
        private RequestStack $requestStack,
    ) {
        $this->twig = $twig;
        $this->config = new CalendarConfig();
        $this->templates = new TemplateConfig($twig);
    }

    /**
     * Creates a new instance of the CalendarBundleService class.
     *
     * @param \DateTime $date The date to use for creating the instance. Defaults to the current date and time.
     *
     * @return static The newly created instance of the CalendarBundleService class.
     */
    public function create(\DateTime $date = new \DateTime()): static
    {
        $queryDate = $this->requestStack->getCurrentRequest()->query->get(self::QUERYARG_DATE);
        $queryView = $this->requestStack->getCurrentRequest()->query->get(self::QUERYARG_VIEW);
        $this->setView($queryView);

        $date = $queryDate ? new \DateTime($queryDate) : new \DateTime();
        $this->setDate(new \DateTimeImmutable($date->format('Y-m-d')));

        return $this;
    }

    /**
     * Sets the date for the CalendarService.
     *
     * @param \DateTimeImmutable $date the date to set
     */
    private function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
        $this->month = $date->format('m');
        $this->setData($date);
    }

    /**
     * Sets the day for the calendar service.
     *
     * @param string $d the day to be set
     *
     * @return array the Days data
     */
    private function setDay(string $d): DayModel
    {
        $now = (new \DateTime())->setTime(0, 0, 0);
        $date = new \DateTime($d);

        $day = new DayModel();
        $day->setDate($d);
        $day->setDayNumberInWeek($date->format('N'));
        $day->setPast($date < $now);
        $day->setPresent($date->format('d-m-Y') == $now->format('d-m-Y'));
        $day->setFuture($date > $now);
        $day->setLastMonth($date->format('m') < $this->month);
        $day->setNextMonth($date->format('m') > $this->month);
        $day->setEvents($this->getEvents($date));
        $day->setTimeblock($this->config->getTimeblock());

        // $day->$day = [
        //         'date' => $date,
        //         'dayNumberInWeek' => $date->format('N'),
        //         'isPast' => $date < $now,
        //         'isPresent' => $date->format('d-m-Y') == $now->format('d-m-Y'),
        //         'isFuture' => $date > $now,
        //         'lastMonth' => $date->format('m') < $this->month,
        //         'nextMonth' => $date->format('m') > $this->month,
        //         'events' => $this->getEvents($date),
        //     ];

        return $day;
    }


    /**
     * Retrieves the days in the month for the given date.
     *
     * @return array an array of days in the month
     */
    private function getDaysInMonth(): array
    {
        $date = $this->date;
        // Premier jour du mois
        $month = $date->format('n');
        $firstDayOfMonth = strtotime($date->format('Y-m-01'));

        // Jour de la semaine du premier jour du mois (1 pour lundi, 7 pour dimanche)
        $firstDayOfWeek = date('N', $firstDayOfMonth);

        // Calculer les jours à afficher du mois précédent
        $daysFromPrevMonth = $firstDayOfWeek - 1;

        // Premier jour à afficher dans le calendrier
        $startDate = strtotime("-$daysFromPrevMonth days", $firstDayOfMonth);

        $days = [];
        $dayCounter = 0;

        // Ajouter des jours jusqu'à ce qu'on ait au moins 28 jours (4 semaines)
        while (true) {
            $currentDate = strtotime("+$dayCounter days", $startDate);
            $days[] = date('Y-m-d', $currentDate);
            ++$dayCounter;

            // Vérifier si nous avons complété le nombre de semaines nécessaires
            if (count($days) >= 28) {
                $lastDate = strtotime($days[count($days) - 1]);
                if (date('n', $lastDate) != $month && 0 == date('w', $lastDate)) {
                    break;
                }
            }
        }

        return $days;
    }

    /**
     * Retrieves the days in the week for the given date.
     *
     * @return array an array of days in the week
     */
    private function getDaysInWeek(): array
    {
        $date = $this->date;
        // Trouver le lundi de cette semaine si on n'est âs déjà Lundi !!!
        $startOfWeek = 1 == $date->format('N') ? $date->getTimestamp() : strtotime('last monday', $date->getTimestamp());

        $weekDays = [];
        for ($i = 0; $i < 7; ++$i) {
            $weekDays[] = date('Y-m-d', strtotime("+$i days", $startOfWeek));
        }

        return $weekDays;
    }

    /**
     * Retrieves the first and last date for the view range (month, week).
     *
     * @return array an array of days in the month
     */
    public function getRange(): array
    {
        switch ($this->view) {
            case 'week':
                $r = $this->getDaysInWeek();

                return [
                    'start' => $r[0],
                    'end' => $r[count($r) - 1],
                ];
            default:
                $r = $this->getDaysInMonth();

                return [
                    'start' => $r[0],
                    'end' => $r[count($r) - 1],
                ];
        }
    }

    /**
     * Renders the calendar.
     *
     * @return array the rendered calendar data
     */
    public function get(): array
    {
        // Get days for the view
        $days = call_user_func([$this, 'getDaysIn' . ucfirst($this->view)]);

        $calendar = [];
        foreach ($days as $day) {
            $calendar[] = $this->setDay($day);
        }

        return [
            ...$this->data,
            'calendar' => $calendar,
        ];
    }

    /**
     * Renders the calendar.
     *
     * @return Markup the rendered calendar
     */
    public function render(): Markup
    {
        $this->debug();
        $html = $this->twig->render("@Calendar/base.html.twig", [
            'calendar' => $this->get(),
            'view' => $this->view,
            'config' => $this->config,
            'templates' => $this->templates,
        ]);
        return new Markup($html, 'UTF-8');
    }

    public function debug(): void
    {
        dump($this);
    }
}
