<?php

namespace SoWhoops;

/**
 * Interface SearchAlgorithm
 * @package SoWhoops
 */
interface SearchAlgorithm
{
    /**
     * @param $answers
     * @return bool
     */
    public function isValid($answers);

    /**
     * @param \Exception $e
     * @return array
     */
    public function getAnswers(\Exception $e);
}