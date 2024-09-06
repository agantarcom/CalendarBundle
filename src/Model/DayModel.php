<?php

namespace AgantarCom\CalendarBundle\Model;

use App\Entity\Event;

class DayModel
{
    private $date;
    private $dayNumberInWeek;
    private $isPast;
    private $isPresent;
    private $isFuture;
    private $lastMonth;
    private $nextMonth;
    private $events;
    private $timeblock;

    public function __construct()
    {
        $this->events = [];
    }

    public function setDate($date): void
    {
        $this->date = $date;
    }

    public function getdate()
    {
        return $this->date;
    }

    public function setDayNumberInWeek($dayNumberInWeek): void
    {
        $this->dayNumberInWeek = $dayNumberInWeek;
    }

    public function getDayNumberInWeek(): int
    {
        return $this->dayNumberInWeek;
    }

    public function setPast($isPast): void
    {
        $this->isPast = $isPast;
    }

    public function isPast(): bool
    {
        return $this->isPast;
    }

    public function setPresent($isPresent): void
    {
        $this->isPresent = $isPresent;
    }

    public function isPresent(): bool
    {
        return $this->isPresent;
    }

    public function setFuture($isFuture): void
    {
        $this->isFuture = $isFuture;
    }

    public function isFuture(): bool
    {
        return $this->isFuture;
    }

    public function setLastMonth($lastMonth): void
    {
        $this->lastMonth = $lastMonth;
    }

    public function isLastMonth(): bool
    {
        return $this->lastMonth;
    }

    public function setNextMonth($nextMonth): void
    {
        $this->nextMonth = $nextMonth;
    }

    public function isNextMonth(): bool
    {
        return $this->nextMonth;
    }

    public function setEvents($events): void
    {
        $this->events = $events;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function getEventAt(string $time): ?CalendarEvent
    {
        $targetTime = new \DateTime($this->date . ' ' . $time);
        foreach ($this->events as $event) {
            if ($event->getStart() <= $targetTime && $event->getEnd() > $targetTime) {
                return $event;
            }
        }
        return null;
    }

    public function getEventInRange(string $start, string $end): ?CalendarEvent
    {
        $startTime = new \DateTime($this->date . ' ' . $start);
        $endTime = new \DateTime($this->date . ' ' . $end);
        foreach ($this->events as $event) {
            if ($startTime <= $event->getStart() && $endTime >= $event->getEnd()) {
                return $event;
            }
        }
        return null;
    }

    public function setTimeBlock($timeblock): void
    {
        $this->timeblock = $timeblock;
    }
}
