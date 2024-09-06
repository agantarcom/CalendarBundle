<?php

namespace AgantarCom\CalendarBundle\Trait;

trait ViewTrait
{
    const MONTH = 'month';
    const WEEK = 'week';
    const DAY = 'day';
    const LIST = 'list';

    private $view = self::MONTH;
    private $asList = false;

    /**
     * Retrieves the view for the calendar configuration.
     *
     * @return string The view for the calendar configuration.
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * Sets the view for the calendar.
     *
     * @param string $view The view to set for the calendar.
     * @return void
     */
    public function setView(?string $view): void
    {
        if ($view === null) {
            $view = self::MONTH;
        } else if ($view === self::LIST) {
            $view = self::MONTH;
            $this->asList = true;
        } else {
            if (!in_array($view, [self::MONTH, self::WEEK, self::DAY])) {
                throw new \InvalidArgumentException('Invalid view');
            }
        }
        $this->view = $view;
    }
}
