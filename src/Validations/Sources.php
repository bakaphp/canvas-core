<?php
declare(strict_types=1);

namespace Canvas\Validations;

use Phalcon\Di;
use Canvas\Models\Sources as SourcesModel;
use Canvas\Contracts\Auth\SocialProviderInterface;
use Exception;

class Sources
{
    
    /**
     * validation
     *
     * @param  SourcesModel $source
     * @param  string $email
     * @param  string $token
     * @return array
     */
    public static function validation(SourcesModel $source, string $email, string $token) : array
    {
        $validationClass = $source->getValidationClass();
        $validation = new $validationClass();
        $interfaces = class_implements($validation);
        if (!in_array(SocialProviderInterface::class, $interfaces)) {
            throw new Exception("The validation class isn't a implementation of SocialInterface");
        }

        return $validation->getInfo($token);
    }
}