<?php

namespace app\models;

class Render
{
    public static function phpFile($templatePath, array $params)
    {
        ob_start();
        ob_implicit_flush(false);
        extract($params, EXTR_OVERWRITE);
        require($templatePath);
        return ob_get_clean();
    }
}
 