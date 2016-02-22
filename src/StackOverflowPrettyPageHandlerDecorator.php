<?php

namespace SoWhoops;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Masterminds\HTML5;
use Whoops\Exception\Inspector;
use Whoops\Handler\Handler;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

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
     * StackOverflowPrettyPageHandlerDecorator constructor.
     * @param PrettyPageHandler $prettyPageHandler
     */
    public function __construct(PrettyPageHandler $prettyPageHandler)
    {
        $this->prettyPageHandler = $prettyPageHandler;
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

        if ($handlerStatus === Handler::QUIT) {
            $answers = [];

            $exceptionMessage = $this->getException()->getMessage();

            $answers = array_merge($answers, $this->getMostLikelyAnswers($exceptionMessage));
            $answers = array_merge($answers, $this->getLessLikelyAnswers($exceptionMessage));

            $generatedContent = $this->generateHTMLForAnswers($answers);

            $html5 = new HTML5();
            $dom = $html5->loadHTML($output);
            $w = qp($dom, '.details-container')->firstChild()->after("<div>{$generatedContent}</div>");
            $w->writeHTML5();
        }

        return $handlerStatus;
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
     * @return mixed
     */
    private function getStackOverflowResponse($title)
    {
        $client = new Client();

        $params = [
            'order' => 'desc',
            'sort' => 'activity',
            'tagged' => 'php',
            'intitle' => $title,
            'site' => 'stackoverflow'
        ];

        $url = 'https://api.stackexchange.com/2.2/search?' . http_build_query($params);

        $request = new Request('GET', $url);

        $response = $client->send($request);

        $decodedResponse = json_decode((string)$response->getBody());

        return $decodedResponse;
    }

    /**
     * @param $exceptionMessage
     * @return array
     */
    private function getMostLikelyAnswers($exceptionMessage)
    {
        $decodedResponse = $this->getStackOverflowResponse($exceptionMessage);

        $answers = [];

        foreach ((array) $decodedResponse->items as $item) {
            $answers[] = (object)[
                'title' => $item->title,
                'link' => $item->link,
                'tags' => $item->tags,
                'is_answered' => $item->is_answered
            ];
        }

        return $answers;
    }

    /**
     * @param $exceptionMessage
     * @return array
     */
    private function getLessLikelyAnswers($exceptionMessage)
    {
        // No items, do more generic search.
        $exceptionParts = explode(':', (string)$exceptionMessage);

        $decodedResponse = $this->getStackOverflowResponse($exceptionParts[0]);

        $answers = [];

        foreach ((array) $decodedResponse->items as $item) {
            $answers[] = (object)[
                'title' => $item->title,
                'link' => $item->link,
                'tags' => $item->tags,
                'is_answered' => $item->is_answered
            ];
        }

        return $answers;
    }

    /**
     * @param $answers
     * @return string
     */
    private function generateHTMLForAnswers($answers)
    {
        $generatedContent = '';

        $limitedAnswers = array_slice($answers, 0, 5);

        foreach($limitedAnswers as $answer) {
            $generatedContent .= "<a href='{$answer->link}'>{$answer->title}</a>";
            $generatedContent .= " <span>(" . implode(', ', $answer->tags) . ")</span>";
            $generatedContent .= "<br />";
        }

        return $generatedContent;
    }
}