<?php

namespace App\Admin\Controllers;

class ErrorsController extends BaseController
{
    public function show404Action($message = '')
    {
        return self::output2(404, $message,'',404);
    }

    public function show500Action($message = '')
    {
        return self::output2(500, 'Server error.','',500);
    }
}
