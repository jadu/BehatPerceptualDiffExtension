<?php

namespace Zodyac\Behat\PerceptualDiffExtension\Formatter;

use Behat\Behat\Formatter\FormatterDispatcher;
use Zodyac\Behat\PerceptualDiffExtension\Listener\ScreenshotListener;

class HtmlFormatterDispatcher extends FormatterDispatcher
{
    protected $listener;

    public function setListener(ScreenshotListener $listener)
    {
        $this->listener = $listener;
    }

    /**
     * Initializes formatter instance.
     *
     * @return FormatterInterface
     */
    public function createFormatter()
    {
        $formatter = parent::createFormatter();
        $formatter->setListener($this->listener);

        return $formatter;
    }
}
