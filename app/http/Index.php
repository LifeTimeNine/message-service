<?php

namespace app\http;

use main\Http;
use model\User;

class Index extends Http
{
    public function index()
    {
        $this->createResponse()
            ->end('<h3>Welcome to the message push service!</h3>');
    }
}