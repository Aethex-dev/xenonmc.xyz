<?php

namespace apps\Account;

class Menu
{

    function __construct($mvc)
    {

        // define vars
        $this->mvc = $mvc;
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;

        // get reputation info
        $this->db->select()
            ->table($this->mvc->config['prefix'] . 'users_rep')
            ->column("*")
            ->where('uuid = ? LIMIT 1')
            ->param(array(

                $mvc->global['user_uuid']

            ))
            ->types("s")
            ->execute($this->conn);

        $user_rep = $this->db->fetch();

        // render page
        $mvc->parse_page($mvc->router->get_request_app(), "Menu", $_POST['layout'], "Account-menu.json", array(

            'layout' => $_POST['layout'],
            'user_rep' => $user_rep

        ));
    }
}

$app = new Menu($this);
