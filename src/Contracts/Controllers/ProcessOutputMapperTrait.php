<?php

declare(strict_types=1);

namespace Canvas\Contracts\Controllers;

use Canvas\Exception\ServerErrorHttpException;

trait ProcessOutputMapperTrait
{
    protected $dto = null;
    protected $dtoMapper = null;

    /**
    * Format Controller Result base on a Mapper.
    *
    * @param mixed $results
    * @return void
    */
    protected function processOutput($results)
    {
        $this->canUseMapper();

        //add a mapper
        $this->dtoConfig->registerMapping(get_class($this->model), $this->dto)
            ->useCustomMapper($this->dtoMapper);

        if (is_array($results) && isset($results['data'])) {
            $results['data'] = $this->mapper->mapMultiple($results['data'], $this->dto);
            return  $results;
        }

        return is_iterable($results) ?
            $this->mapper->mapMultiple($results, $this->dto)
            : $this->mapper->map($results, $this->dto);
    }

    /**
     * Can we use the mapper on this request?
     *
     * @return boolean
     */
    protected function canUseMapper(): bool
    {
        if (!is_object($this->model) || empty($this->dto)) {
            throw new ServerErrorHttpException('No Mapper configured on this controller ' . get_class($this));
        }

        return true;
    }
}
