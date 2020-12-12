<?php

declare(strict_types=1);

namespace Canvas\Contracts;

use function Baka\isSwooleServer;
use Canvas\Http\Response;
use Phalcon\Mvc\Micro;

trait ResponseTrait
{
    /**
     * Halt execution after setting the message in the response.
     *
     * @param Micro  $api
     * @param int    $status
     * @param string $message
     *
     * @return mixed
     */
    protected function halt(Micro $api, int $status, string $message)
    {
        $apiResponse = !isSwooleServer() ? new Response() : $this->response;

        $apiResponse
            ->setPayloadError($message)
            ->setStatusCode($status)
            ->send();

        $api->stop();
    }
}
