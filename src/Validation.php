<?php

declare(strict_types=1);

namespace Canvas;

use Phalcon\Validation as PhalconValidation;
use Canvas\Exception\UnprocessableRequestException;

/**
 * Class Validation.
 *
 * @package Canvas
 */
class Validation extends PhalconValidation
{
    /**
     *
     * Overwrite to throw the exception and avoid all the overloaded code
     * Validate a set of data according to a set of rules.
     *
     * @param array|object data
     * @param object entity
     * @return \Phalcon\Validation\Message\Group
     */
    public function validate($data = null, $entity = null)
    {
        $validate = parent::validate($data, $entity);

        if (count($validate)) {
            throw new UnprocessableRequestException((string) current($validate));
        }
    }
}
