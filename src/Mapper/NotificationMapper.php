<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Canvas\Models\SystemModules;
use ReflectionClass;
use Exception;

// You can either extend the CustomMapper, or just implement the MapperInterface
// directly.
class NotificationMapper extends CustomMapper
{
    /**
     * @param Canvas\Models\Notification $notification
     * @param Canvas\Dto\notificationDto $notificationDto
     * @return Files
     */
    public function mapToObject($notification, $notificationDto, array $context = [])
    {
        $notificationDto->id = $notification->getId();
        $notificationDto->type = $notification->type->name;
        /**
         * @todo change this for a proper title
         */
        $notificationDto->title = 'Notification Title';
        $notificationDto->icon = $notification->type->icon_url;
        $notificationDto->users_id = $notification->users_id;
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
        $notificationDto->from = [
            'user_id' => $notification->from->getId(),
            'displayname' => $notification->from->displayname,
            'avatar' => $notification->from->photo ? $notification->from->photo->url : null,
        ];

        try {
            $systemModule = SystemModules::getById($notification->system_modules_id);
            $systemModuleEntity = new $systemModule->model_name;
            $entity = [];
            if ($entity = $systemModuleEntity::findFirst($notification->entity_id)) {
                $notificationDto->entity = $entity->toArray();
            }
            $reflect = new ReflectionClass($systemModuleEntity);
            $notificationDto->entity['type'] = $reflect->getShortName();
        } catch (Exception $e) {
            $notificationDto->entity['type'] = null;
        }

        return $notificationDto;
    }
}
