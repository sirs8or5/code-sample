<?php

interface SourceClientInterface
{
    /**
     * @return array
     */
    public function fetchAll() : array;
}