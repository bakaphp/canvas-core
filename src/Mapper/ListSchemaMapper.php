<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Baka\Contracts\Elasticsearch\CustomFiltersSchemaTrait;

class ListSchemaMapper extends CustomMapper
{
    use CustomFiltersSchemaTrait;

    private $elastic;

    /**
     * @param SystemModules $systemModel
     * @param \Canvas\Dto\ListSchema $listSchema
     *
     * @return ListSchema
     */
    public function mapToObject($systemModel, $listSchema, array $context = [])
    {
        $bulkActions = [
            [
                'name' => 'Export CSV',
                'action' => 'exportCsv'
            ], [
                'name' => 'Export PDF',
                'action' => 'exportPdf'
            ], [
                'name' => 'Delete',
                'action' => 'bulkDelete'
            ]
        ];

        //if the system model uses elastic then we can show custom filters
        /*         if ($systemModel->useElastic()) {
                    $this->elastic = DI::getDefault()->get('elastic');
                    $listSchema->customFilterFields = $this->getSchema($systemModel->slug);
                }
         */

        $listSchema->bulkActions = !empty($systemModel->bulk_actions) ? json_decode($systemModel->bulk_actions) : $bulkActions;
        $listSchema->tableFields = !empty($systemModel->browse_fields) ? json_decode($systemModel->browse_fields) : null;

        return $listSchema;
    }
}
