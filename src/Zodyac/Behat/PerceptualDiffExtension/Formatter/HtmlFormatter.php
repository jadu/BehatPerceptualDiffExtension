<?php

namespace Zodyac\Behat\PerceptualDiffExtension\Formatter;

use Behat\Behat\DataCollector\LoggerDataCollector;
use Behat\Behat\Definition\DefinitionInterface;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Formatter\HtmlFormatter as BaseHtmlFormatter;
use Behat\Gherkin\Node\StepNode;
use Zodyac\Behat\PerceptualDiffExtension\Listener\ScreenshotListener;

class HtmlFormatter extends BaseHtmlFormatter
{
    protected $listener;

    public function setListener(ScreenshotListener $listener)
    {
        $this->listener = $listener;
    }

    protected function printStepBlock(StepNode $step, DefinitionInterface $definition = null, $color)
    {
        $this->writeln('<div class="step">');

        $this->printStepName($step, $definition, $color);
        if (null !== $definition) {
            $this->printStepDefinitionPath($step, $definition);
        }

        $diff = $this->listener->getStepDiff($step);
        if ($diff) {
            $this->writeln('<div class="pdiff">');
            $this->writeln('<a href="file://' . $this->listener->getBaselinePath() . $diff . '" target="new"><img alt="Baseline screenshot" src="file://' . $this->listener->getBaselinePath() . $diff . '" /></a>');
            $this->writeln('<a href="file://' . $this->listener->getDiffPath() . $diff . '" target="new"><img alt="Diff" src="file://' . $this->listener->getDiffPath() . $diff . '" /></a>');
            $this->writeln('<a href="file://' . $this->listener->getScreenshotPath() . $diff . '" target="new"><img alt="Screenshot from this test run" src="file://' . $this->listener->getScreenshotPath() . $diff . '" /></a>');
            $this->writeln('</div>');
        }

        $this->writeln('</div>');
    }

    protected function printSummary(LoggerDataCollector $logger)
    {
        $results = $logger->getScenariosStatuses();
        $result = $results['failed'] > 0 ? 'failed' : 'passed';
        $this->writeln('<div class="summary '.$result.'">');

        $this->writeln('<div class="counters">');

        $this->printScenariosSummary($logger);
        $this->printStepsSummary($logger);

        // Output pdiff summary
        $diffs = $this->listener->getDiffs();
        $diffCount = count($diffs);

        $this->writeln('<p class="pdiffs">' . $diffCount . ' failed pdiffs</p>');

        if ($this->parameters->get('time')) {
            $this->printTimeSummary($logger);
        }

        $this->writeln('</div>');

        $this->writeln(<<<'HTML'
<div class="switchers">
    <a href="javascript:void(0)" id="behat_show_all">[+] all</a>
    <a href="javascript:void(0)" id="behat_hide_all">[-] all</a>
</div>
HTML
);

        $this->writeln('</div>');
    }

    /**
     * Get HTML template style.
     *
     * @return string
     */
    protected function getHtmlTemplateStyle()
    {
        return <<<'HTMLTPL'
        body {
            margin:0px;
            padding:0px;
            position:relative;
            padding-top:90px;
        }
        #behat {
            float:left;
            font-family: Georgia, serif;
            font-size:18px;
            line-height:26px;
            width:100%;
        }
        #behat .statistics {
            float:left;
            width:100%;
            margin-bottom:15px;
        }
        #behat .statistics p {
            text-align:right;
            padding:5px 15px;
            margin:0px;
            border-right:10px solid #000;
        }
        #behat .statistics.failed p {
            border-color:#C20000;
        }
        #behat .statistics.passed p {
            border-color:#3D7700;
        }
        #behat .feature {
            margin:15px;
        }
        #behat h2, #behat h3, #behat h4 {
            margin:0px 0px 5px 0px;
            padding:0px;
            font-family:Georgia;
        }
        #behat h2 .title, #behat h3 .title, #behat h4 .title {
            font-weight:normal;
        }
        #behat .path {
            font-size:10px;
            font-weight:normal;
            font-family: 'Bitstream Vera Sans Mono', 'DejaVu Sans Mono', Monaco, Courier, monospace !important;
            color:#999;
            padding:0px 5px;
            float:right;
        }
        #behat .path a:link,
        #behat .path a:visited {
            color:#999;
        }
        #behat .path a:hover,
        #behat .path a:active {
            background-color:#000;
            color:#fff;
        }
        #behat h3 .path {
            margin-right:4%;
        }
        #behat ul.tags {
            font-size:14px;
            font-weight:bold;
            color:#246AC1;
            list-style:none;
            margin:0px;
            padding:0px;
        }
        #behat ul.tags li {
            display:inline;
        }
        #behat ul.tags li:after {
            content:' ';
        }
        #behat ul.tags li:last-child:after {
            content:'';
        }
        #behat .feature > p {
            margin-top:0px;
            margin-left:20px;
        }
        #behat .scenario {
            margin-left:20px;
            margin-bottom:20px;
        }
        #behat .scenario > ol,
        #behat .scenario .examples > ol {
            margin:0px;
            list-style:none;
            padding:0px;
        }
        #behat .scenario > ol {
            margin-left:20px;
        }
        #behat .scenario > ol:after,
        #behat .scenario .examples > ol:after {
            content:'';
            display:block;
            clear:both;
        }
        #behat .scenario > ol li,
        #behat .scenario .examples > ol li {
            float:left;
            width:95%;
            padding-left:5px;
            border-left:5px solid;
            margin-bottom:4px;
        }
        #behat .scenario > ol li .argument,
        #behat .scenario .examples > ol li .argument {
            margin:10px 20px;
            font-size:16px;
            overflow:hidden;
        }
        #behat .scenario > ol li table.argument,
        #behat .scenario .examples > ol li table.argument {
            border:1px solid #d2d2d2;
        }
        #behat .scenario > ol li table.argument thead td,
        #behat .scenario .examples > ol li table.argument thead td {
            font-weight: bold;
        }
        #behat .scenario > ol li table.argument td,
        #behat .scenario .examples > ol li table.argument td {
            padding:5px 10px;
            background:#f3f3f3;
        }
        #behat .scenario > ol li .keyword,
        #behat .scenario .examples > ol li .keyword {
            font-weight:bold;
        }
        #behat .scenario > ol li .path,
        #behat .scenario .examples > ol li .path {
            float:right;
        }
        #behat .scenario .examples {
            margin-top:20px;
            margin-left:40px;
        }
        #behat .scenario .examples h4 span {
            font-weight:normal;
            background:#f3f3f3;
            color:#999;
            padding:0 5px;
            margin-left:10px;
        }
        #behat .scenario .examples table {
            margin-left:20px;
        }
        #behat .scenario .examples table thead td {
            font-weight:bold;
            text-align:center;
        }
        #behat .scenario .examples table td {
            padding:2px 10px;
            font-size:16px;
        }
        #behat .scenario .examples table .failed.exception td {
            border-left:5px solid #000;
            border-color:#C20000 !important;
            padding-left:0px;
        }
        pre {
            font-family:monospace;
        }
        .snippet {
            font-size:14px;
            color:#000;
            margin-left:20px;
        }
        .backtrace {
            font-size:12px;
            line-height:18px;
            color:#000;
            overflow:hidden;
            margin-left:20px;
            padding:15px;
            border-left:2px solid #C20000;
            background: #fff;
            margin-right:15px;
        }
        #behat .passed {
            background:#DBFFB4;
            border-color:#65C400 !important;
            color:#3D7700;
        }
        #behat .failed {
            background:#FFFBD3;
            border-color:#C20000 !important;
            color:#C20000;
        }
        #behat .undefined, #behat .pending {
            border-color:#FAF834 !important;
            background:#FCFB98;
            color:#000;
        }
        #behat .skipped {
            background:lightCyan;
            border-color:cyan !important;
            color:#000;
        }
        #behat .summary {
            position: absolute;
            top: 0px;
            left: 0px;
            width:100%;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 18px;
        }
        #behat .summary .counters {
            padding: 10px;
            border-top: 0px;
            border-bottom: 0px;
            border-right: 0px;
            border-left: 5px;
            border-style: solid;
            height: 65px;
            overflow: hidden;
        }
        #behat .summary .switchers {
            position: absolute;
            right: 15px;
            top: 25px;
        }
        #behat .summary .switcher {
            text-decoration: underline;
            cursor: pointer;
        }
        #behat .summary .switchers a {
            margin-left: 10px;
            color: #000;
        }
        #behat .summary .switchers a:hover {
            text-decoration:none;
        }
        #behat .summary p {
            margin:0px;
        }
        #behat .jq-toggle > .scenario,
        #behat .jq-toggle > ol,
        #behat .jq-toggle > .examples {
            display:none;
        }
        #behat .jq-toggle-opened > .scenario,
        #behat .jq-toggle-opened > ol,
        #behat .jq-toggle-opened > .examples {
            display:block;
        }
        #behat .jq-toggle > h2,
        #behat .jq-toggle > h3 {
            cursor:pointer;
        }
        #behat .jq-toggle > h2:after,
        #behat .jq-toggle > h3:after {
            content:' |+';
            font-weight:bold;
        }
        #behat .jq-toggle-opened > h2:after,
        #behat .jq-toggle-opened > h3:after {
            content:' |-';
            font-weight:bold;
        }

        #behat .pdiff img {
            width:300px;
            margin:5px;
            border:2px solid #aaa;
        }
HTMLTPL;
    }

    protected function getHtmlTemplateScript()
    {
        $script = <<<'SCRIPT'
        $('#behat .summary .counters .pdiffs')
            .addClass('switcher')
            .click(function(){
                var $scenario = $('.feature .scenario:has(.pdiff)');
                var $feature  = $scenario.parent();

                $('#behat_hide_all').click();

                $scenario.addClass('jq-toggle-opened');
                $feature.addClass('jq-toggle-opened');
            });
SCRIPT;

        return parent::getHtmlTemplateScript() . $script;
    }
}
