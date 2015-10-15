<?php

namespace Zodyac\Behat\PerceptualDiffExtension\Formatter;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zodyac\Behat\ExtensibleHtmlFormatter\Event\FormatterEvent;
use Zodyac\Behat\ExtensibleHtmlFormatter\Event\FormatterStepEvent;
use Zodyac\Behat\PerceptualDiffExtension\Comparator\ScreenshotComparator;
use Zodyac\Behat\PerceptualDiffExtension\Exception\PerceptualDiffException;

class HtmlFormatterListener implements EventSubscriberInterface
{
    protected $screenshotComparator;

    public function __construct(ScreenshotComparator $screenshotComparator)
    {
        $this->screenshotComparator = $screenshotComparator;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'formatter.html.step' => 'printPdiff',
            'formatter.html.head' => 'printStyles'
        );
    }

    public function printPdiff(FormatterStepEvent $event)
    {
        // Get the diff filename
        $filename = $this->screenshotComparator->getDiff($event->getStep());
        if ($filename !== null) {
            // Output the pdiff section if there was a diff
            $baselinePath = $this->screenshotComparator->getBaselinePath(false) . $filename;
            $diffPath = $this->screenshotComparator->getDiffPath(false) . $filename;
            $screenshotPath = $this->screenshotComparator->getScreenshotPath(false) . $filename;

            $html = <<<TEMPLATE
            <div class="pdiff">
                <a href="./$baselinePath" target="new"><img alt="Baseline" src="./$baselinePath" /></a>
                <a href="./$screenshotPath" target="new"><img alt="Current" src="./$screenshotPath" /></a>
                <a href="./$diffPath" target="new"><img alt="Diff" src="./$diffPath" /></a>
            </div>
TEMPLATE;

            $event->writeln($html);
        }
    }

    /**
     * Outputs additional CSS for the pdiff section
     *
     * @param FormatterEvent $event
     */
    public function printStyles(FormatterEvent $event)
    {
        $styles = <<<TEMPLATE
        <style type="text/css">
        #behat .pdiff img {
            width:300px;
            margin:5px;
            border:2px solid #aaa;
        }
        </style>
TEMPLATE;

        $event->writeln($styles);
    }
}
