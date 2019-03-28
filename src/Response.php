<?php

class Response
{
    /**
     * @param array $data
     */
    public static function json(array $data)
    {
        echo json_encode($data);
    }
}