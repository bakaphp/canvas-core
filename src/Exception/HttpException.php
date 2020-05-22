<?php

declare(strict_types=1);

namespace Canvas\Exception;

use Canvas\Http\Response;

class HttpException extends Exception
{
    protected $httpCode = Response::BAD_REQUEST;
    protected $httpMessage = 'Bad Request';
    protected $data;

    /**
     * Get the http status code of the exception.
     *
     * @return string
     */
    public function getHttpCode() : int
    {
        return $this->httpCode;
    }

    /**
     * Get the message string from the exception.
     *
     * @return string
     */
    public function getHttpMessage() : string
    {
        return $this->httpMessage;
    }

    /**
     * Get the message DATA from the exception.
     *
     * @return string|null
     */
    public function getData() : ?array
    {
        return $this->data;
    }
}
