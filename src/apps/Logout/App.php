<?php

namespace apps\Logout;

class App
{

    function __construct($mvc)
    {

        $mvc->parse_page($mvc->router->get_request_app(), "Failed", $_POST['layout'], 'Logout-failed.json', array(

            'layout' => $_POST['layout']

        ));
    }
}

$app = new App($this);
