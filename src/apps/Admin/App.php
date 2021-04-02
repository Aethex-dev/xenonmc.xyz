<?php

namespace apps\Admin;

class App {

    function onReady($mvc) {

        // define vars
        $this->mvc = $mvc;
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;

        $this->config = json_decode(json_encode($mvc->router->get_app_config($mvc->router->get_request_app())), true);

        // check if user is logged in
        if($mvc->global['loggedIn'] == true) {

            // check if user has permission
            if($mvc->global['user_rank'] >= $this->config['admin-rank-id']) {

                // render page
                $mvc->parse_page($mvc->router->get_request_app(), "Index", $_POST['layout'], "Admin.json", array(

                    'layout' => $_POST['layout']

                ));

                return true;
            }

            // render page
            $mvc->parse_page($mvc->router->get_request_app(), "NotLoggedIn", $_POST['layout'], "Admin-notadminorhigher.json", array(

                'layout' => $_POST['layout']

            ));

            return true;
        }

        // render page
        $mvc->parse_page($mvc->router->get_request_app(), "NotLoggedIn", $_POST['layout'], "Admin-notloggedin.json", array(

            'layout' => $_POST['layout']

        ));
    }
}