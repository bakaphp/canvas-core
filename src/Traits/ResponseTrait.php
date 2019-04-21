<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Http\Response;
use Phalcon\Mvc\Micro;
use function Canvas\Core\isSwooleServer;

/**
 * Trait ResponseTrait
 *
 * @package Canvas\Traits
 */
trait ResponseTrait
{
    /**
     * Halt execution after setting the message in the response
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
