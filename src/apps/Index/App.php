<?php

namespace apps\Index;

class App
{

    function __construct($mvc)
    {

        $mvc->parse_page($mvc->router->get_request_app(), "Index", "main", "Index.json", array());
    }
}

$app = new App($this);
