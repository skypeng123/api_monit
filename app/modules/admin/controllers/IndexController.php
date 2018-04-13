<?php

namespace App\Admin\Controllers;

class IndexController extends BaseController
{
    public function indexAction()
    {

        $this->view->pick('index/index');

    }


}
