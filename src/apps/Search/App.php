<?php

namespace apps\Search;

class App
{

    function onReady($mvc)
    {

        // define vars
        $this->mvc = $mvc;
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;

        $mvc->parse_page($mvc->router->get_request_app(), "Index", $_POST['layout'], "Search.json", array(

            'layout' => $_POST['layout']

        ));
    }
}