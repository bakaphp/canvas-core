<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Phalcon\Mvc\Model;
use Canvas\Models\SubscriptionsHistory;

/**
 * Trait HistoricalRecordsTrait.
 *
 * @package Canvas\Traits
 *
 * @property \Phalcon\Di $di
 *
 */
trait HistoricalRecordsTrait
{
    

    /**
     * Add new history record
     *
     * @param Model $subscription
     *
     * @return bool
     */
    public static function addRecord(Model $record): bool
    {
        $recordFieldsArray = [];
        $newHistoricalRecord = new self();
        $whiteList = [];

        foreach ($record as $key => $value) {
            if ($key == 'id') {
                $recordFieldsArray["record_id"] = (int)$value;
                $whiteList[] = "record_id";
            } else {
                $recordFieldsArray[$key] = $value;
                $whiteList[] = $key;
            }
        }

        $newHistoricalRecord = new self();
        $newHistoricalRecord->assign($recordFieldsArray);
        $newHistoricalRecord->saveOrFail();
        reset($recordFieldsArray);

        return true;
    }
}
