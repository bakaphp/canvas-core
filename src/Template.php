<?php

declare(strict_types=1);

namespace Canvas;

use Canvas\Models\EmailTemplates;
use Phalcon\Di;

/**
 * Class Validation.
 *
 * @package Canvas
 */
class Template
{
    /**
     * Given the email template name and its params
     *  - create the files
     *  - render it with the variables
     *  - return the content string for use to use anywhere.
     *
     * @param string $name
     * @param array $params
     *
     * @return string
     */
    public static function generate(string $name, array $params) : string
    {
        $di = Di::getDefault();
        $view = $di->get('view');
        $filesystem = $di->get('filesystem', ['local']);

        //get the template
        $template = EmailTemplates::getByName($name);
        $file = $template->name . '.volt';

        //write file
        $filesystem->put('/view/' . $file, $template->template);

        //render and return content
        return $view->render($template->name, $params);
    }
}
