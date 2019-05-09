<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Apps;
use Canvas\Mapper\DTO\DTOAppsSettings;
use Phalcon\Http\Response;
use Baka\Http\QueryParserCustomFields;
use Phalcon\Mvc\Model\Resultset\Simple as SimpleRecords;
use Canvas\Exception\ModelException;

/**
 * Class LanguagesController
 *
 * @package Canvas\Api\Controllers
 *
 */
class AppsController extends BaseController
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
     * set objects
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Apps();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
        ];
    }

    /**
     * List of data. Can also return one item if key parameter is declared
     *
     * @method GET
     * url /v1/data
     *
     * @param int $id
     * @return \Phalcon\Http\Response
     */
    public function index($id = null): Response
    {
        if ($id != null) {
            return $this->getById($id);
        }

        if($this->request->hasQuery('key')){

            $key = $this->request->getQuery("key", "string");

            $app = $this->model::getByKey($key);

            if(!is_object($app)){
                throw new ModelException('App not found');
            }

            return $this->response($app);
        }

        //parse the rquest
        $parse = new QueryParserCustomFields($this->request->getQuery(), $this->model);
        $parse->appendParams($this->additionalSearchFields);
        $parse->appendCustomParams($this->additionalCustomSearchFields);
        $parse->appendRelationParams($this->additionalRelationSearchFields);
        $params = $parse->request();

        $results = (new SimpleRecords(null, $this->model, $this->model->getReadConnection()->query($params['sql'], $params['bind'])));
        $count = $this->model->getReadConnection()->query($params['countSql'], $params['bind'])->fetch(\PDO::FETCH_OBJ)->total;

        // Relationships, but we have to change it to sparo full implementation
        if ($this->request->hasQuery('relationships')) {
            $relationships = $this->request->getQuery('relationships', 'string');

            $results = QueryParser::parseRelationShips($relationships, $results);
        }



        $newResult = $this->mapper->mapMultiple(iterator_to_array($results), DTOAppsSettings::class);

        //this means the want the response in a vuejs format
        if ($this->request->hasQuery('format')) {
            $limit = (int) $this->request->getQuery('limit', 'int', 25);

            $newResult = [
                'data' => $results,
                'limit' => $limit,
                'page' => $this->request->getQuery('page', 'int', 1),
                'total_pages' => ceil($count / $limit),
                'total_rows' => $count
            ];
        }

        return $this->response($newResult);
    }

    /**
     * get item
     *
     * @param mixed $id
     *
     * @method GET
     * @url /v1/data/{id}
     *
     * @return \Phalcon\Http\Response
     */
    public function getById($id): Response
    {
        //find the info
        $objectInfo = $this->model->findFirst([
            'id = ?0 AND is_deleted = 0',
            'bind' => [$id],
        ]);

        //get relationship
        if ($this->request->hasQuery('relationships')) {
            $relationships = $this->request->getQuery('relationships', 'string');

            $objectInfo = QueryParser::parseRelationShips($relationships, $objectInfo);
        }

        $objectInfo = $this->mapper->map($objectInfo, DTOAppsSettings::class);

        if ($objectInfo) {
            return $this->response($objectInfo);
        } else {
            throw new Exception('Record not found');
        }
    }
}
