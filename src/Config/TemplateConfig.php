<?php

namespace AgantarCom\CalendarBundle\Config;

use Twig\Markup;
use Twig\Environment;

final class TemplateConfig
{
    private $twig;
    private $day = "@Calendar/components/day.html.twig";
    private $event = "@Calendar/components/event.html.twig";
    private $template = [];

    public function __construct(
        Environment $twig,
    ) {
        $this->twig = $twig;
    }

    public function setTemplate(string $name, string $templatePath, array $context): void
    {
        $this->template[$name] = [
            'path' => $templatePath,
            'context' => $context
        ];
    }

    public function render(string $template): Markup
    {
        $path = $this->template[$template]['path'];
        $context = $this->template[$template]['context'];
        $html = $this->twig->render($path, $context);
        return new Markup($html, 'UTF-8');
    }

    public function setEvent(string $eventsTemplatePath): void
    {
        $this->event = $eventsTemplatePath;
    }

    public function getEvent(): ?string
    {
        return $this->event;
    }

    public function setDay(string $dayTemplatePath): void
    {
        $this->day = $dayTemplatePath;
    }

    public function getDay(): ?string
    {
        return $this->day;
    }
}
