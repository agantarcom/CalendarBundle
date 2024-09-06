<?php

namespace AgantarCom\CalendarBundle\Trait;

trait EventTrait
{
    private $events = [];

    public function setEvents(array $events): static
    {
        $this->events = $events;
        return $this;
    }

    public function addEvent($event): static
    {
        $event->setTimeblock($this->config->getTimeblock());
        $this->events[] = $event;
        return $this;
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
}
