<?php

namespace SoWhoops;

use Masterminds\HTML5;
use Whoops\Exception\Inspector;
use Whoops\Handler\Handler;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Util\TemplateHelper;

/**
 * Class StackOverflowPrettyPageHandlerDecorator
 * @package SoWhoops
 */
class StackOverflowPrettyPageHandlerDecorator extends Handler implements HandlerInterface
{
    /**
     * @var PrettyPageHandler
     */
    protected $prettyPageHandler;

    /**
     * @var SearchAlgorithm[]
     */
    protected $searchAlgorithms;

    /**
     * StackOverflowPrettyPageHandlerDecorator constructor.
     * @param PrettyPageHandler $prettyPageHandler
     * @param SearchAlgorithm[] $searchAlgorithms
     */
    public function __construct(PrettyPageHandler $prettyPageHandler, $searchAlgorithms = [])
    {
        $this->prettyPageHandler = $prettyPageHandler;

        $this->setSearchAlgorithms($searchAlgorithms);
    }

    /**
     * @return int|null
     * @throws \Exception
     */
    public function handle()
    {
        ob_start();
        $handlerStatus = $this->prettyPageHandler->handle();
        $output = ob_get_clean();

        if ($handlerStatus !== Handler::QUIT) {
            return $handlerStatus;
        }

        $answers = $this->getExceptionAnswers($this->getException());

        $templateHelper = new TemplateHelper();

        $soFile = __DIR__ . '/Resources/views/stackoverflow.html.php';

        $templateHelper->setVariables([
            'answers' => array_slice($answers, 0, 5)
        ]);

        ob_start();
        $templateHelper->render($soFile);
        $soOutput = ob_get_clean();

        $html5 = new HTML5();
        $dom = $html5->loadHTML($output);
        $w = qp($dom, '.details-container')->firstChild()->after($soOutput)
            ->top('html')->find('style')->append(file_get_contents(__DIR__ . '/Resources/css/stackoverflow.css'));

        $w->writeHTML5();

        return Handler::QUIT;
    }

    /**
     * @param  Run $run
     * @return void
     */
    public function setRun(Run $run)
    {
        parent::setRun($run);

        $this->prettyPageHandler->setRun($run);
    }

    /**
     * @param  \Throwable $exception
     * @return void
     */
    public function setException($exception)
    {
        parent::setException($exception);

        $this->prettyPageHandler->setException($exception);
    }

    /**
     * @param  Inspector $inspector
     * @return void
     */
    public function setInspector(Inspector $inspector)
    {
        parent::setInspector($inspector);

        $this->prettyPageHandler->setInspector($inspector);
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->prettyPageHandler, $name) === false) {
            throw new \BadMethodCallException("PrettyPageHandler does not implement '{$name}'");
        }

        return call_user_func_array([$this->prettyPageHandler, $name], $arguments);
    }

    /**
     * @param $e
     * @return array
     */
    private function getExceptionAnswers($e)
    {
        $answers = [];

        /** @var SearchAlgorithm[] $searchAlgorithms */
        $searchAlgorithms = $this->getSearchAlgorithms();

        foreach ($searchAlgorithms as $algorithm) {
            if (!$algorithm->isValid($answers)) {
                continue;
            }

            $answers = array_merge($answers, $algorithm->getAnswers($e));
        }

        return $answers;
    }

    /**
     * @return SearchAlgorithm[]
     */
    private function getSearchAlgorithms()
    {
        return $this->searchAlgorithms;
    }

    /**
     * @param $searchAlgorithms
     */
    private function setSearchAlgorithms($searchAlgorithms)
    {
        $this->searchAlgorithms = [];

        foreach($searchAlgorithms as $algorithm) {
            $this->addSearchAlgorithm($algorithm);
        }
    }

    /**
     * @param $algorithm
     */
    private function addSearchAlgorithm(SearchAlgorithm $algorithm)
    {
        $this->searchAlgorithms[] = $algorithm;
    }
}