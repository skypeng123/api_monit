<?php

namespace App\Admin\Controllers;

class ErrorsController extends BaseController
{


    public function show401Action($message = '')
    {
        if($this->request->isAjax())
            return self::output(401, $message,'',401);
        else{
            header('HTTP/1.1 401 Unauthorized.', TRUE, 401);
            echo $message;
            exit(1);
        }
    }

    public function show403Action($message = '')
    {
        if($this->request->isAjax())
            return self::output(403, $message,'',403);
        else{
            header('HTTP/1.1 403 Forbidden.', TRUE, 403);
            echo $message;
            exit(1);
        }
    }

    public function show404Action($message = '')
    {
        if($this->request->isAjax())
            return self::output(404, $message,'',404);
        else{
            header('HTTP/1.1 404 not found.', TRUE, 404);
            echo $message;
            exit(1);
        }
    }

    public function show500Action($message = '')
    {
        if($this->request->isAjax())
            return self::output(500, $message,'',500);
        else{
            header('HTTP/1.1 500 bad request.', TRUE, 500);
            echo $message;
            exit(1);
        }

    }
}
