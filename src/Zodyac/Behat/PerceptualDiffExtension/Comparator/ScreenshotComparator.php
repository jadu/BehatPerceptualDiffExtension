<?php

namespace Zodyac\Behat\PerceptualDiffExtension\Comparator;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Gherkin\Node\StepNode;

class ScreenshotComparator implements EventSubscriberInterface
{
    /**
     * Base path
     *
     * @var string
     */
    protected $path;

    /**
     * Amount of time in seconds to sleep before taking a screenshot.
     *
     * @var int
     */
    protected $sleep;

    /**
     * Options passed to the `compare` call.
     *
     * @var array
     */
    protected $compareOptions;

    /**
     * When the suite was started.
     *
     * @var \DateTime
     */
    protected $started;

    /**
     * The scenario currently being tested.
     *
     * @var ScenarioNode
     */
    protected $currentScenario;

    /**
     * Current step (reset for each scenario)
     *
     * @var int
     */
    protected $stepNumber;

    /**
     * Diff results
     *
     * @var array
     */
    protected $diffs = array();

    public function __construct($path, $sleep, array $compareOptions)
    {
        $this->path = rtrim($path, '/') . '/';
        $this->sleep = (int) $sleep;
        $this->compareOptions = $compareOptions;
        $this->started = new \DateTime();
    }

    public static function getSubscribedEvents()
    {
        return array(
            'beforeSuite' => 'clearScreenshotDiffs',
            'beforeScenario' => 'resetStepCounter'
        );
    }

    /**
     * Returns the filename of the diff screenshot or null if
     * there were no differecnes.
     *
     * @param StepNode $step
     * @return string
     */
    public function getDiff(StepNode $step)
    {
        $hash = spl_object_hash($step);

        if (isset($this->diffs[$hash])) {
            return $this->diffs[$hash];
        }
    }

    /**
     * Returns all of the diffs
     *
     * @return array
     */
    public function getDiffs()
    {
        return $this->diffs;
    }

    /**
     * Returns the screenshot path
     *
     * @return string
     */
    public function getScreenshotPath()
    {
        return $this->path . $this->started->format('YmdHis') . '/';
    }

    /**
     * Returns the baseline screenshot path
     *
     * @return string
     */
    public function getBaselinePath()
    {
        return $this->path . 'baseline/';
    }

    /**
     * Returns the diff path
     *
     * @return string
     */
    public function getDiffPath()
    {
        return $this->path . 'diff/';
    }

    /**
     * Remove all the previous diffs
     */
    public function clearScreenshotDiffs()
    {
        $diffPath = $this->getDiffPath();
        if (is_dir($diffPath)) {
            $this->removeDirectory($diffPath);
        }
    }

    /**
     * Keep track of the current scenario and step number for use in the file name
     *
     * @param ScenarioEvent $event
     */
    public function resetStepCounter(ScenarioEvent $event)
    {
        $this->currentScenario = $event->getScenario();
        $this->stepNumber = 0;
    }

    /**
     * Takes a screenshot if the step passes and compares it to the baseline
     *
     * @param ContextInterface $context
     * @param StepNode $step
     */
    public function takeScreenshot(ContextInterface $context, StepNode $step)
    {
        // Increment the step number
        $this->stepNumber++;

        if ($this->sleep > 0) {
            // Convert seconds to microseconds
            usleep($this->sleep * 1000000);
        }

        $screenshotPath = $this->getScreenshotPath();
        $screenshotFile = $screenshotPath . $this->getFilepath($step);
        $this->ensureDirectoryExists($screenshotFile);

        // Save the screenshot
        file_put_contents($screenshotFile, $context->getSession()->getScreenshot());

        // Comparison
        $baselinePath = $this->getBaselinePath();
        $diffPath = $this->getDiffPath();

        $baselineFile = str_replace($screenshotPath, $baselinePath, $screenshotFile);
        if (!is_file($baselineFile)) {
            $this->ensureDirectoryExists($baselineFile);

            // New step, move into the baseline but return as there is no need for a comparison
            copy($screenshotFile, $baselineFile);
            return;
        }

        // Output the comparison to a temp file
        $tempFile = $this->path . '/temp.png';

        // Run the comparison
        $output = array();
        exec($this->getCompareCommand($baselineFile, $screenshotFile, $tempFile), $output, $return);

        if ($return === 0 || $return === 1) {
            // Check that there are some differences
            if ($return === 1) {
                $diffFile = str_replace($screenshotPath, $diffPath, $screenshotFile);
                $this->ensureDirectoryExists($diffFile);

                // Store the diff
                rename($tempFile, $diffFile);

                // Record the diff for output
                $this->diffs[spl_object_hash($step)] = $this->getFilepath($step);
            } elseif (is_file($tempFile)) {
                // Clean up the temp file
                unlink($tempFile);
            }

            return $output[0];
        }

        return false;
    }

    /**
     * Ensure the directory where the file will be saved exists.
     *
     * @param string $file
     * @return boolean Returns true if the directory exists and false if it could not be created
     */
    protected function ensureDirectoryExists($file)
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            return mkdir($dir, 0777, true);
        }

        return true;
    }

    /**
     * Recursively removes a directory and it's contents.
     *
     * @param string $path
     * @return boolean
     */
    protected function removeDirectory($path)
    {
        $dir = new \DirectoryIterator($path);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isFile() || $fileinfo->isLink()) {
                unlink($fileinfo->getPathName());
            } elseif (!$fileinfo->isDot() && $fileinfo->isDir()) {
                $this->removeDirectory($fileinfo->getPathName());
            }
        }

        return rmdir($path);
    }

    /**
     * Returns the ImageMagick compare command with the correct arguments.
     *
     * @param string $baselineFile The baseline screenshot
     * @param string $screenshotFile The screenshot to compare with
     * @param string $tempFile The temp file to store the diff in (this will be deleted if there are no differences)
     * @return string
     */
    protected function getCompareCommand($baselineFile, $screenshotFile, $tempFile)
    {
        return sprintf(
            'compare -fuzz %d%% -metric %s -highlight-color %s %s %s %s 2>&1',
            $this->compareOptions['fuzz'],
            escapeshellarg($this->compareOptions['metric']),
            escapeshellarg($this->compareOptions['highlight_color']),
            escapeshellarg($baselineFile),
            escapeshellarg($screenshotFile),
            escapeshellarg($tempFile)
        );
    }

    /**
     * Returns the relative file path for the given step
     *
     * @param StepNode $step
     * @return string
     */
    protected function getFilepath($step)
    {
        return sprintf('%s/%s/%d-%s.png',
            $this->formatString($this->currentScenario->getFeature()->getTitle()),
            $this->formatString($this->currentScenario->getTitle()),
            $this->stepNumber,
            $this->formatString($step->getText())
        );
    }

    /**
     * Formats a title string into a filename friendly string
     *
     * @param string $string
     * @return string
     */
    protected function formatString($string)
    {
        $string = preg_replace('/[^\w\s\-]/', '', $string);
        $string = preg_replace('/[\s\-]+/', '-', $string);

        return $string;
    }
}
