<?php

namespace Zodyac\Behat\PerceptualDiffExtension\Tester;

use Behat\Behat\Definition\DefinitionInterface;
use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Tester\StepTester as BaseStepTester;
use Behat\Gherkin\Node\StepNode;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zodyac\Behat\PerceptualDiffExtension\Exception\PerceptualDiffException;
use Zodyac\Behat\PerceptualDiffExtension\Comparator\ScreenshotComparator;

class StepTester extends BaseStepTester
{
    /**
     * The screenshot comparator
     *
     * @var ScreenshotComparator
     */
    protected $screenshotComparator;

    /**
     * Whether to fail the step if there are perceptual differences.
     *
     * @var boolean
     */
    protected $failOnDiff;

    /**
     * The run context.
     *
     * Redefined as protected to work around the private variable
     * in the base class.
     *
     * @var ContextInterface
     */
    protected $context;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->screenshotComparator = $container->get('behat.perceptual_diff_extension.comparator.screenshot');
        $this->failOnDiff = $container->getParameter('behat.perceptual_diff_extension.fail_on_diff');
    }

    /**
     * Sets run context.
     *
     * @param ContextInterface $context
     */
    public function setContext(ContextInterface $context)
    {
        // Must set the parent context too
        parent::setContext($context);
        $this->context = $context;
    }

    /**
     * Executes provided step definition.
     *
     * If the result is not a failure then take a screenshot and compare for differences.
     *
     * @param StepNode $step
     * @param DefinitionInterface $definition
     * @throws PerceptualDiffException If there are differences compared to the baseline
     */
    protected function executeStepDefinition(StepNode $step, DefinitionInterface $definition)
    {
        try {
            parent::executeStepDefinition($step, $definition);
        } catch (\Exception $e) {
            // Ok, step failed, but let's quickly capture screenshot for html report.
        }

        $diff = $this->screenshotComparator->takeScreenshot($this->context, $step, $error);

        if (isset($e)) {
            throw $e;
        }

        if ($diff > 0 && $this->failOnDiff) {
            // There were differences between the two screenshots
            throw new PerceptualDiffException($error);
        }
    }
}
