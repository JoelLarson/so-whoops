<?php

namespace SoWhoops\SearchAlgorithm;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use SoWhoops\SearchAlgorithm;

class PrefixAnswers implements SearchAlgorithm
{
    /**
     * @param $answers
     * @return bool
     */
    public function isValid($answers)
    {
        return count($answers) <= 0;
    }

    /**
     * @return array
     */
    public function getAnswers(\Exception $e)
    {
        // No items, do more generic search.
        $exceptionParts = explode(':', (string) $e->getMessage());

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
     * @param $title
     * @return string
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

        return json_decode((string)$response->getBody());
    }
}