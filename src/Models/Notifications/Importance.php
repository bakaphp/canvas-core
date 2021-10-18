<?php
declare(strict_types=1);

namespace Canvas\Models\Notifications;

use Canvas\Models\AbstractModel;
use Canvas\Models\Notifications;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class Importance extends AbstractModel
{
    public int $apps_id;
    public string $name;
    public string $validation_expression;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('notifications_importance');

        $this->hasMany(
            'id',
            UserEntityImportance::class,
            'importance_id',
            [
                'alias' => 'userImportance'
            ]
        );
    }

    /**
     * Given the current importance run its validation against the given notification.
     *
     * @param Notifications $notification
     *
     * @return bool
     */
    public function validateExpression(Notifications $notification) : bool
    {
        $expressionLanguage = new ExpressionLanguage();

        //validate the expression and values with symfony expression language
        return (bool) $expressionLanguage->evaluate(
            $this->validation_expression,
            [
                'notification' => $notification
            ]
        );
    }
}
