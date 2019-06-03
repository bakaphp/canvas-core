<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Baka\Elasticsearch\Contracts\CustomFiltersSchemaTrait;
use Phalcon\DI;

// You can either extend the CustomMapper, or just implement the MapperInterface
// directly.
class ListSchemaMapper extends CustomMapper
{
    use CustomFiltersSchemaTrait;

    private $elastic;

    /**
     * @param SystemModules $systeModel
     * @param \Canvas\Dto\ListSchema $listSchema
     * @return ListSchema
     */
    public function mapToObject($systeModel, $listSchema)
    {
        $listSchema->bulkActions = [
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
        if ($systeModel->useElastic()) {
            $this->elastic = DI::getDefault()->get('elastic');
            $listSchema->customFilterFields = $this->getSchema($systeModel->slug);
        }

        /**
         * get the schema
         * @todo in PHP 7.3 change to use exceptions
         */
        $listSchema->tableFields = !empty($systeModel->browse_fields) ? json_decode($systeModel->browse_fields) : null;

        return $listSchema;
    }
}
