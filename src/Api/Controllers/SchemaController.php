<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Http\Api\BaseController as BakaBaseController;
use Baka\Http\Exception\InternalServerErrorException;
use Canvas\Dto\ListSchema;
use Canvas\Mapper\ListSchemaMapper;
use Canvas\Models\SystemModules;
use Phalcon\Db\Column;
use Phalcon\Http\Response;

class SchemaController extends BakaBaseController
{
    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new SystemModules();
    }

    /**
     * Overwrite inde schema.
     *
     * @return Response
     */
    public function index() : Response
    {
        return $this->response(['We cant list this apps schema']);
    }

    /**
     * Given the slug get the schema.
     *
     * @param string $slug
     *
     * @return Response
     */
    public function getBySlug(string $slug) : Response
    {
        $schema = SystemModules::getBySlug($slug);

        //add a mapper
        $this->dtoConfig->registerMapping(SystemModules::class, ListSchema::class)
            ->useCustomMapper(new ListSchemaMapper());

        return $this->response($this->mapper->map($schema, ListSchema::class));
    }

    /**
     * Give the slug we give you the DB schema
     * this route is only meant to be run by an admin.
     *
     * @param string $slug
     *
     * @return Response
     */
    public function getModelDescription(string $slug) : Response
    {
        //none admin users can only edit themselves
        if (!$this->userData->hasRole('Default.Admins')) {
            throw new InternalServerErrorException('No route found');
        }

        $schema = SystemModules::getBySlug($slug);
        $model = new $schema->model_name;

        $columns = $this->db->describeColumns($model->getSource());
        $structure = [];
        $type = null;

        foreach ($columns as $column) {
            switch ($column->getType()) {
                case Column::TYPE_INTEGER:
                    $structure[$column->getName()] = 'integer';
                    break;
                case Column::TYPE_BIGINTEGER:
                    $structure[$column->getName()] = 'long';
                    break;
                case Column::TYPE_TEXT:
                case Column::TYPE_VARCHAR:
                case Column::TYPE_CHAR:
                    $structure[$column->getName()] = 'text';
                    break;
                case Column::TYPE_DATE:
                    // We define a format for date structure.
                    $structure[$column->getName()] = ['date', 'yyyy-MM-dd'];
                    break;
                case Column::TYPE_DATETIME:
                    // We define a format for datetime structure.
                    $structure[$column->getName()] = ['date', 'yyyy-MM-dd HH:mm:ss'];
                    break;
                case Column::TYPE_DECIMAL:
                    $structure[$column->getName()] = 'float';
                    break;
            }
        }

        if ($model->hasCustomFields()) {
            $structure = array_merge($structure, $model->getCustomFields());
        }

        return $this->response($structure);
    }
}
