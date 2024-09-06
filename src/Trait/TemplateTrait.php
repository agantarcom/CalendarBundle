<?php

namespace AgantarCom\CalendarBundle\Trait;

trait TemplateTrait
{
    private $templates;

    public function setEventsTemplatePath(string $eventsTemplatePath, array $context = []): self
    {
        $this->templates->setTemplate('event', $eventsTemplatePath, $context);
        return $this;
    }

    public function setDayTemplatePath(string $dayTemplatePath, array $context = []): self
    {
        $this->templates->setTemplate('day', $dayTemplatePath, $context);
        return $this;
    }

    public function setContentTemplatePath(string $contentTemplatePath, array $context = []): self
    {
        $this->templates->setTemplate('content', $contentTemplatePath, $context);
        return $this;
    }

    public function setLegendTemplatePath(string $legendTemplatePath, array $context = []): self
    {
        $this->templates->setTemplate('legend', $legendTemplatePath, $context);
        return $this;
    }
}
