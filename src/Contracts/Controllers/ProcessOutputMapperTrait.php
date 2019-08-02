<?php

declare(strict_types=1);

namespace Canvas\Contracts\Controllers;

use Canvas\Exception\ServerErrorHttpException;
use AutoMapperPlus\DataType;
use StdClass;

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

        //if we have relationships we use StdClass to allow use to overwrite the array as we see fit in the Dto
        $mapperModel = !$this->request->hasQuery('relationships') ? get_class($this->model) : DataType::ARRAY;
        $this->dto = !$this->request->hasQuery('relationships') ? $this->dto : StdClass::class;

        $this->dtoConfig->registerMapping($mapperModel, $this->dto)
            ->useCustomMapper($this->dtoMapper);

        if (is_array($results) && isset($results['data'])) {
            $results['data'] = $this->mapper->mapMultiple($results['data'], $this->dto);
            return  $results;
        }

        //return $this->mapper->map($results, $this->dto, $this->getMapperOptions());
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

    /**
     * If we have relationships send them as addicontal context to the mapper.
     *
     * @return array
     */
    protected function getMapperOptions(): array
    {
        if ($this->request->hasQuery('relationships')) {
            return [
                'relationships' => explode(',', $this->request->getQuery('relationships'))
            ];
        }

        return [];
    }
}
