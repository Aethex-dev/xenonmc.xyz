<?php

namespace apps\Search;

class Query
{

    function __construct($mvc)
    {

        // define vars
        $this->db = $mvc->db;
        $this->mvc = $mvc;
        $this->conn = $mvc->conn;

        if (ajaxID == 'search') {

            // requested from form
            $mvc->router->header("location", "/search/query/" . urlencode($_POST['query']));

            return true;
        }

        // search logic
        echo urldecode($mvc->router->get_url()[2]);

        return false;
    }
}

$app = new Query($this);


