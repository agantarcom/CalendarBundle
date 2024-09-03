<?php

namespace AgantarCom\CalendarBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Markup;

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
    const QUERYARG_DATE = 'CalendarDate';
    const QUERYARG_VIEW = 'CalendarView';

    const MONTH = 'month';
    const WEEK = 'week';
    const MINI = "mini";

    private $viewsList = ['month', 'week'];

    private $twig;

    private $view;
    private $date;
    private $month;
    private $data;
    private $events;

    public function __construct(Environment $twig, private RequestStack $requestStack)
    {
        $this->twig = $twig;
    }

    public function create(\DateTime $date = new \DateTime()): static
    {
        $queryDate = $this->requestStack->getCurrentRequest()->query->get(self::QUERYARG_DATE);
        $queryView = $this->requestStack->getCurrentRequest()->query->get(self::QUERYARG_VIEW);

        $date = $queryDate ? new \DateTime($queryDate) : new \DateTime();
        $this->setDate($date);
        // Default view
        $this->view = $queryView ? $queryView : 'month';
        return $this;
    }

    /**
     * Sets the date for the CalendarService.
     *
     * @param \DateTime $date the date to set
     */
    private function setDate(\DateTime $date): void
    {
        $this->date = $date;
        $this->month = $date->format('m');
        $this->setData($date);
    }

    /**
     * Sets the data for the given date.
     *
     * @param \DateTime $date the date to set the data for
     */
    private function setData(\DateTime $date): void
    {
        $data = [];
        $d = clone $date;
        $data['weekDays'] = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        $week = [
            'current' => $d->format('W'),
            'last' => $d->modify('-1 week')->format('Y-m-d'),
            'next' => $d->modify('+2 week')->format('Y-m-d'),
        ];

        $d->modify('first day of this month');
        $month = [
            'current' => $d->format('Y-m-d'),
            'last' => $d->modify('-1 month')->format('Y-m-d'),
            'next' => $d->modify('+2 month')->format('Y-m-d'),
        ];

        $data['dates'] = [
            'dateTime' => $date,
            'now' => $date->format('Y-m-d'),
            'weeks' => $week,
            'months' => $month,
        ];

        // Views
        foreach ($this->viewsList as $view) {
            $data['views'][$view] = $this->createUri(self::QUERYARG_VIEW, $view !== 'month' ? $view : null);
        }

        $data['navigation'] = [
            'today' => $this->createUri(self::QUERYARG_DATE),
            'previewPage' => $this->createUri(self::QUERYARG_DATE, $month['last']),
            'nextPage' => $this->createUri(self::QUERYARG_DATE, $month['next']),
            'views' => $data['views'],
        ];



        // Remove any date query parameter
        $data['requestUri'] = preg_replace('/&?date=[0-9]{4}-[0-9]{2}-[0-9]{2}/', '', $this->requestStack->getCurrentRequest()->getRequestUri());
        $this->data = $data;
    }


    public function setView(string $viewType): static
    {
        if (!in_array($viewType, $this->viewsList)) {
            throw new \InvalidArgumentException('Invalid view');
        }

        // Retrieve queried view
        $queryView = $this->requestStack->getCurrentRequest()->query->get(self::QUERYARG_VIEW);

        $this->view = $queryView ?? $viewType;

        if ($viewType === 'week') {
            $this->data['navigation']['previewPage'] = $this->createUri(self::QUERYARG_DATE, $this->data['dates']['weeks']['last']);
            $this->data['navigation']['nextPage'] = $this->createUri(self::QUERYARG_DATE, $this->data['dates']['weeks']['next']);
        }

        return $this;
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

    private function createDateUri(string $date = null): string
    {
        // Get the current request URI
        $defaultUri = $this->requestStack->getCurrentRequest()->getRequestUri();

        // Clean up any existing date query parameter
        $defaultUri = preg_replace('/[&\?]' . self::QUERYARG_DATE . '=[\d-]{0,10}/', '', $defaultUri);

        // Create the query arg
        if ($date) {
            // Add Query arg as new or append to existing query args
            $calendarQueryArg = strpos($defaultUri, '?') ? '&' : '?';
            $calendarQueryArg .= self::QUERYARG_DATE . "=" . $date;
        }
        return $defaultUri . ($calendarQueryArg ?? '');
    }

    private function createUri(string $qArg, string $value = null): string
    {
        // Get the current request URI
        $defaultUri = $this->requestStack->getCurrentRequest()->getRequestUri();

        // Clean up any existing date query parameter
        // $defaultUri = preg_replace('/[&\?]' . self::QUERYARG_DATE . '=[\d-]{0,10}/', '', $defaultUri);
        $defaultUri = preg_replace('/[&\?]' . $qArg . '=[^&]*/', '', $defaultUri);

        // Create the query arg
        if ($value) {
            // Add Query arg as new or append to existing query args
            $calendarQueryArg = strpos($defaultUri, '?') ? '&' : '?';
            $calendarQueryArg .= $qArg . "=" . $value;
        }
        return $defaultUri . ($calendarQueryArg ?? '');
    }


    /**
     * Sets the events for the calendar service.
     *
     * @param array $events an array of events to be set
     *
     * @return CalendarService the updated CalendarService instance
     */
    public function setEvents(array $events): CalendarService
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Sets the day for the calendar service.
     *
     * @param string $d the day to be set
     *
     * @return array the Days data
     */
    private function setDay(string $d): array
    {
        $now = (new \DateTime())->setTime(0, 0, 0);
        $date = new \DateTime($d);
        $day = [
            'date' => $date,
            'dayNumberInWeek' => $date->format('N'),
            'isPast' => $date < $now,
            'isPresent' => $date->format('d-m-Y') == $now->format('d-m-Y'),
            'isFuture' => $date > $now,
            'lastMonth' => $date->format('m') < $this->month,
            'nextMonth' => $date->format('m') > $this->month,
            'events' => $this->getEvents($date),
        ];

        return $day;
    }

    /**
     * Retrieves events for a given date.
     *
     * @param \DateTime $date the date for which to retrieve events
     *
     * @return array an array of events for the given date
     */
    private function getEvents(\DateTime $date): array
    {
        if (!$this->events || 0 == count($this->events)) {
            return [];
        }
        $events = [];
        foreach ($this->events as $event) {
            if ($event->getDate() == $date) {
                $events[] = $event;
            }
        }

        return $events;
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

    public function render(): Markup
    {
        $template = "@Calendar/views/" . $this->view . ".html.twig";
        $this->data['template'] = $template;
        $html = $this->twig->render($template, [
            'calendar' => $this->get(),
            'view' => $this->view,
        ]);
        return new Markup($html, 'UTF-8');
    }
}
