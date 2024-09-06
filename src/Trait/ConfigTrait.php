<?php

namespace AgantarCom\CalendarBundle\Trait;

use AgantarCom\CalendarBundle\Config\CalendarConfig;

trait ConfigTrait
{
    protected CalendarConfig $config;

    public function minified(): static
    {
        $this->config->setMinified();
        return $this;
    }

    public function setWeekends(bool $weekends): static
    {
        $this->config->setWeekends($weekends);
        return $this;
    }

    public function setTimeblocks(int $minutes, bool $visible = true): static
    {
        $this->config->setTimeblocks($minutes, $visible);
        return $this;
    }

    public function setStartTime(string $time): static
    {
        $this->config->setStartTime($time);
        return $this;
    }

    public function setEndTime(string $time): static
    {
        $this->config->setEndTime($time);
        return $this;
    }

    public function hideTimeblocks(): static
    {
        $this->config->hideTimeblocks();
        return $this;
    }

    public function setConfig(array $config): static
    {
        foreach ($config as $key => $value) {
            if (method_exists($this->config, 'set' . ucfirst($key))) {
                $this->config->{'set' . ucfirst($key)}($value);
            }
        }
        return $this;
    }

    private function to_array(): array
    {
        return $this->config->toArray();
    }
}
