<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Exception;
use PDOException;
use Phalcon\Http\Response;
use Phalcon\Queue\Beanstalk\Exception as BeanstalkException;
use Canvas\Exception\ServerErrorHttpException;
use RedisException;

/**
 * Class IndexController
 *
 * @package Canvas\Api\Controllers
 *
 * @property Redis $redis
 * @property Beanstalk $queue
 * @property Mysql $db
 * @property \Monolog\Logger $log
 */
class IndexController extends BaseController
{
    /**
     * Index
     *
     * @method GET
     * @url /
     *
     * @return Response
     */
    public function index($id = null) : Response
    {
        return $this->response(['Woot Baka']);
    }

    /**
     * Show the status of the diferent services
     *
     * @method GET
     * @url /status
     *
     * @return Response
     */
    public function status() : Response
    {
        $response = [];

        //Try to connect to Redis
        try {
            $this->redis->hSet('htest', 'a', 'x');
            $this->redis->hSet('htest', 'b', 'y');
            $this->redis->hSet('htest', 'c', 'z');
            $this->redis->hSet('htest', 'd', 't');
            $this->redis->hGetAll('htest');

            //$this->redis->ping();
        } catch (RedisException $e) {
            $this->log->error($e->getMessage(), $e->getTrace());
            $response['errors']['redis'] = $e->getMessage();
        } catch (Exception $e) {
            $this->log->error("Redis isn't working. {$e->getMessage()}", $e->getTrace());
            $response['errors']['redis'] = "Redis isn't working.";
        }

        //Try to connect to Beanstalk
        try {
            $this->queue->connect();
        } catch (BeanstalkException $e) {
            $this->log->error($e->getMessage(), $e->getTrace());
            $response['errors']['beanstalk'] = $e->getMessage();
        } catch (Exception $e) {
            $this->log->error("Beanstalk isn't working. {$e->getMessage()}", $e->getTrace());
            $response['errors']['beanstalk'] = "Beanstalk isn't working.";
        } finally {
            $this->queue->disconnect();
        }

        //Try to connect to db
        try {
            $this->db->connect();
        } catch (PDOException $e) {
            $this->log->error($e->getMessage(), $e->getTrace());
            $response['errors']['db'] = $e->getMessage();
        } catch (Exception $e) {
            $this->log->error("The database isn't working. {$e->getMessage()}", $e->getTrace());
            $response['errors']['db'] = "The database isn't working.";
        }

        if (!count($response)) {
            return $this->response(['OK']);
        }

        throw new ServerErrorHttpException(json_encode($response));
    }
}
