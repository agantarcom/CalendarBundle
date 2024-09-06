<?php

namespace AgantarCom\CalendarBundle\Trait;

trait DataTrait
{
    private $data = [];

    /**
     * Sets the data for the given date.
     *
     * @param \DateTimeImmutable $date the date to set the data for
     */
    private function setData(\DateTimeImmutable $date): void
    {
        $data = [];
        $data['date'] = $date;
        $data['weekdays'] = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        if ($this->view === 'week') {
            // If it's monday, keep the date, otherwise set it to the last monday
            $d = $date->format('N') == 1 ? $date  : $date->modify('last monday');
            $previousDate = $d->modify('-1 week');
            $nextDate = $d->modify('+1 week');
        } else {
            $d = $date->modify('first day of this month');
            $previousDate = $d->modify('-1 month');
            $nextDate = $d->modify('+1 month');
        }

        $data['navigation'] = [
            'today' => $this->createUri(self::QUERYARG_DATE),
            'previous' => $this->createUri(self::QUERYARG_DATE, $previousDate->format(('Y-m-d'))),
            'next' => $this->createUri(self::QUERYARG_DATE, $nextDate->format(('Y-m-d'))),
            'monthView' => $this->createUri(self::QUERYARG_VIEW),
            'weekView' => $this->createUri(self::QUERYARG_VIEW, 'week'),
        ];

        // Remove any date query parameter
        // $data['requestUri'] = preg_replace('/&?date=[0-9]{4}-[0-9]{2}-[0-9]{2}/', '', $this->requestStack->getCurrentRequest()->getRequestUri());
        $this->data = $data;
    }

    private function setNavigation(): void {}

    /**
     * Creates a URI with the specified query argument and value.
     *
     * @param string $qArg The query argument.
     * @param string|null $value The value of the query argument. Defaults to null.
     * @return string The created URI.
     */
    private function createUri(string $qArg, string $value = null): string
    {
        // Get the current request URI
        $currentUri = $this->requestStack->getCurrentRequest()->getRequestUri();

        $parsedUri = parse_url($currentUri);
        $path = $parsedUri['path'] ?? '';
        $query = $parsedUri['query'] ?? '';

        // Parse the query string into an array
        parse_str($query, $queryParams);

        // Remove the query argument from the query parameters
        unset($queryParams[$qArg]);

        // Add the query argument to the query parameters
        if ($value !== null) {
            $queryParams[$qArg] = $value;
        }

        // Rebuild the query string
        $newQuery = http_build_query($queryParams);

        return $path . ($newQuery ? '?' . $newQuery : '');
    }
}




    // /**
    //  * Creates a URI with the specified date query argument.
    //  *
    //  * @param string|null $date The date to set the query argument to. Defaults to null.
    //  * @return string The created URI.
    //  */
    // private function createDateUri(string $date = null): string
    // {
    //     // Get the current request URI
    //     $defaultUri = $this->requestStack->getCurrentRequest()->getRequestUri();

    //     // Clean up any existing date query parameter
    //     $defaultUri = preg_replace('/[&\?]' . self::QUERYARG_DATE . '=[\d-]{0,10}/', '', $defaultUri);

    //     // Create the query arg
    //     if ($date) {
    //         // Add Query arg as new or append to existing query args
    //         $calendarQueryArg = strpos($defaultUri, '?') ? '&' : '?';
    //         $calendarQueryArg .= self::QUERYARG_DATE . "=" . $date;
    //     }
    //     return $defaultUri . ($calendarQueryArg ?? '');
    // }