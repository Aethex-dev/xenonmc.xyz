<?php

namespace apps\Index;

class App
{

    function onReady($mvc)
    {

        $mvc->parse_page($mvc->router->get_request_app(), "Index", "main", "Index.json", array());
    }
}