<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\PaymentMethodsCreds;
use Exception;
use Phalcon\Http\Response;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class PaymentMethodsCredsController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new PaymentMethodsCreds();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['users_id', ':', $this->userData->getId()],
            ['companies_id', ':', '0|' . $this->userData->currentCompanyId()],
            ['apps_id', ':', $this->app->getId()]
        ];
    }

    /**
     * Get current payment methods creds.
     *
     * @return Response
     */
    public function getCurrentPaymentMethodsCreds() : Response
    {
        $paymentMethod = [];

        try {
            $paymentMethod = $this->model->getCurrentPaymentMethodCreds();
        } catch (Exception $e) {
        }
        return $this->response($paymentMethod);
    }
}
