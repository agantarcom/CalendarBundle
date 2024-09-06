<?php

namespace AgantarCom\CalendarBundle\Model;

class CalendarEvent
{
    private $id;
    private $title;
    private $date;
    private $start;
    private $end;
    private $allDay;
    private $extendedProps;
    private $timeblock;

    private $folded;
    private $cssClass;
    private $style;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    public function setStart(\DateTime $start): self
    {
        $this->start = $start;
        $d = clone $start;
        $date = $d->setTime(0, 0, 0);
        $this->date = $date;
        return $this;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    public function setEnd(\DateTime $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getAllDay(): ?bool
    {
        return $this->allDay;
    }

    public function setAllDay(bool $allDay): self
    {
        $this->allDay = $allDay;

        return $this;
    }

    public function getExtendedProps(): ?array
    {
        return $this->extendedProps;
    }

    public function setExtendedProps(array $extendedProps): self
    {
        $this->extendedProps = $extendedProps;

        return $this;
    }

    public function setCssClass(string $cssClass): self
    {
        $this->cssClass = $cssClass;
        return $this;
    }

    public function getCssClass(): ?string
    {
        return $this->cssClass;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start->format('Y-m-d H:i:s'),
            'end' => $this->end->format('Y-m-d H:i:s'),
            'allDay' => $this->allDay,
            'cssClass' => $this->cssClass,
            'extendedProps' => $this->extendedProps,
            'folded' => $this->folded,
            'duration' => $this->getDuration(),
            'style' => $this->getStyle(),
        ];
    }

    public function setStyle(?string $style): self
    {
        $this->style = $style;
        return $this;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function setFolded(bool $folded): self
    {
        $this->folded = $folded;
        return $this;
    }

    public function isFolded(): bool
    {
        return $this->folded;
    }

    public function setTimeblock(int $timeblock): self
    {
        $this->timeblock = $timeblock;
        return $this;
    }

    public function getDuration(): int
    {
        $seconds = $this->end->getTimestamp() - $this->start->getTimestamp();
        $minutes = $seconds / 60;
        return $minutes;
    }

    public function getTimeblockSize(): int
    {
        return $this->getDuration() / $this->timeblock;
    }
}
