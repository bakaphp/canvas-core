<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers\Users;

use Baka\Database\Exception\ModelNotFoundException;
use Canvas\Api\Controllers\BaseController;
use Canvas\Models\UserLinkedSources;
use Phalcon\Http\Response;

class SocialController extends BaseController
{
    /**
     * Request account deletion.
     *
     * @param string $id
     *
     * @return Response
     */
    public function disconnectFromSite(int $id, string $site) : Response
    {
        try {
            $socialConnection = UserLinkedSources::getConnectionBySite($this->userData, $site);
            $socialConnection->softDelete();
        } catch (ModelNotFoundException $e) {
        }

        return $this->response(
            'Disconnected from Social Site.',
        );
    }
}
