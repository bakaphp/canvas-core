<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Baka\Contracts\Auth\UserInterface;
use Baka\Contracts\Database\ModelInterface;
use Baka\Contracts\Mapper\RelationshipTrait;
use function Baka\getShortClassName;
use Canvas\Models\Notifications;
use Canvas\Models\SystemModules;
use Exception;

// You can either extend the CustomMapper, or just implement the MapperInterface
// directly.
class NotificationMapper extends CustomMapper
{
    use RelationshipTrait;

    /**
     * @param Canvas\Models\Notification $notification
     * @param Canvas\Dto\notificationDto $notificationDto
     *
     * @return Files
     */
    public function mapToObject($notification, $notificationDto, array $context = [])
    {
        if (is_array($notification)) {
            $notification = Notifications::getByIdOrFail($notification['id']);
        }

        $notificationDto->id = $notification->getId();
        $notificationDto->type = $notification->type->name;
        /**
         * @todo change this for a proper title
         */
        $notificationDto->title = 'Notification Title';
        $notificationDto->icon = $notification->type->icon_url;
        $notificationDto->users_id = $notification->users_id;
        $notificationDto->users_avatar = $notification->user->getPhoto() ? $notification->user->getPhoto()->url : null;
        $notificationDto->from_users_id = $notification->from_users_id;
        $notificationDto->companies_id = $notification->companies_id;
        $notificationDto->apps_id = $notification->apps_id;
        $notificationDto->system_modules_id = $notification->system_modules_id;
        $notificationDto->notification_type_id = $notification->notification_type_id;
        $notificationDto->entity_id = $notification->entity_id;
        $notificationDto->content = $notification->content;
        $notificationDto->read = $notification->read;
        $notificationDto->created_at = $notification->created_at;
        $notificationDto->updated_at = $notification->updated_at;
        $notificationDto->from = $this->fromFormatting($notification->from);

        try {
            $systemModule = SystemModules::getById($notification->system_modules_id);
            $systemModuleEntity = new $systemModule->model_name;
            $entity = $systemModuleEntity::findFirstOrFail($notification->entity_id);
            $notificationDto->entity = $this->cleanUpEntity($entity);
            $notificationDto->entity['type'] = getShortClassName($entity);
        } catch (Exception $e) {
            $notificationDto->entity['type'] = null;
        }

        $this->getRelationships($notification, $notificationDto, $context);

        return $notificationDto;
    }

    /**
     * Given entity , cleanup any properties that will affect json formatting.
     *
     * @param ModelInterface $entity
     *
     * @return array
     */
    protected function cleanUpEntity(ModelInterface $entity) : array
    {
        return $entity->toArray();
    }

    /**
     * Allow user to update from properties.
     *
     * @param UserInterface $user
     *
     * @return array
     */
    protected function fromFormatting(UserInterface $user) : array
    {
        return [
            'user_id' => $user->getId(),
            'displayname' => $user->displayname,
            'avatar' => $user->getPhoto() ? $user->getPhoto()->url : null,
        ];
    }
}
