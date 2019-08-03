<?php

declare(strict_types=1);

namespace Canvas\Contracts\Mapper;

trait RelationshipTrait
{
    /**
    *  Attach relationship to DTO.
    */
    private function getRelationships(object $object, object $objectDto, array $relationships): void
    {
        if (array_key_exists('relationships', $relationships)) {
            foreach ($relationships['relationships'] as $relationship) {
                $objectDto->$relationship = $object->$relationship;
            }
        }
    }
}
