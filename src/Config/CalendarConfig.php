<?php

namespace AgantarCom\CalendarBundle\Config;

final class CalendarConfig
{
    private $timeblock = 60;
    private $timeblocks = [];
    private $timeVisible = true;
    private $startTime = '00:00';
    private $endTime = '23:59';
    private $weekends = true;
    private $minified = false;

    public function __construct()
    {
        $this->setTimeblocks($this->timeblock, true);
    }

    /**
     * Retrieves whether weekends should be included in the calendar.
     *
     * @return bool Whether weekends should be included in the calendar.
     */
    public function getWeekends(): bool
    {
        return $this->weekends;
    }

    /**
     * Sets whether weekends should be included in the calendar.
     *
     * @param bool $weekends Whether weekends should be included.
     * @return self
     */
    public function setWeekends(bool $weekends): self
    {
        $this->weekends = $weekends;

        return $this;
    }

    /**
     * Retrieves the number of timeblocks in a day.
     *
     * @return int The number of timeblocks in a day.
     */
    public function getTimeblocks(): ?array
    {
        return $this->timeblocks;
    }

    public function getTimeblock(): int
    {
        return $this->timeblock;
    }

    /**
     * Sets the number of timeblocks in a day.
     *
     * @param int $minutes the timeblock size in minutes.
     * @return void
     */
    public function setTimeblocks(mixed $minutes, bool $visibility = true): void
    {
        $this->timeblock = $minutes;
        $this->timeVisible = $visibility;
        if ($minutes === false || !is_int($minutes)) {
            $this->timeblocks = null;
            return;
        }
        if ($minutes < 1 || $minutes > 24 * 60) {
            throw new \InvalidArgumentException('Timeblocks must fit in a day range (1 - 1440).');
        }

        $timeBlocks = [];
        $startTime = new \DateTime($this->startTime);
        $endTime = new \DateTime($this->endTime);

        while ($startTime <= $endTime) {
            // Add the current time block to the array
            $timeBlocks[] = $startTime->format('H:i');
            // Modify the time by adding the block duration
            $startTime->modify("+{$minutes} minutes");
        }

        $this->timeblocks = $timeBlocks;
    }

    public function getTimeVisible(): bool
    {
        return $this->timeVisible;
    }

    /**
     * Sets the calendar to be minified.
     *
     * @return void
     */
    public function setMinified(): void
    {
        $this->minified = true;
    }

    public function setStartTime(string $time): void
    {
        $this->startTime = $time;
    }

    public function setEndTime(string $time): void
    {
        if ($time === '00:00') {
            $time = '23:59';
        }
        $endTime = new \DateTime($time);
        $startTime = new \DateTime($this->startTime);

        if ($endTime < $startTime) {
            throw new \InvalidArgumentException('End time must be greater than start time.');
        }
        $this->endTime = $time;
    }

    /**
     * Retrieves whether the calendar is minified.
     *
     * @return bool Whether the calendar is minified.
     */
    public function minified(): bool
    {
        return $this->minified;
    }

    public function toArray(): array
    {
        return [
            'weekends' => $this->weekends,
            'timeblocks' => $this->timeblocks,
            'timeVisible' => $this->timeVisible,
            'minified' => $this->minified,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
        ];
    }

    public function hideTimeblocks(): void
    {
        $this->timeVisible = false;
    }
}
