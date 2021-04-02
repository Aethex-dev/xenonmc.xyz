<?php

namespace apps\Admin;

class App {

    function __construct($mvc) {

        // define vars
        $this->mvc = $mvc;
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;

        // render page
        $mvc->parse_page($mvc->router->get_request_app(), "Index", $_POST['layout'], "Admin.json", array(

            'layout' => $_POST['layout']

        ));
    }
}

$app = new App($this);