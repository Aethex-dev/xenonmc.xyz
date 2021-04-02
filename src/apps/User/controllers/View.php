<?php

namespace apps\User;

class View {

    function onReady($mvc)
    {

        // define vars
        $this->db = $db = $mvc->db;
        $this->conn = $conn = $mvc->conn;
        $this->mvc = $mvc;

        // get profile user data
        $db->select()
            ->table($mvc->config['prefix'] . 'users')
            ->column("*")
            ->where('username = ?')
            ->param(array(

                $mvc->router->get_url()[2]

            ))
            ->types("s")
            ->execute($conn);
        
        while($profile = $db->fetch()) {

            // get reputation data
            $db->select()
                ->table($mvc->config['prefix'] . 'users_rep')
                ->column("*")
                ->where('uuid = ?')
                ->param(array(

                    $profile['uuid']

                ))
                ->types("s")
                ->execute($conn);

            while($profile['rep'] = $db->fetch()) {

                $mvc->parse_page($mvc->router->get_request_app(), "View", $_POST['layout'], "User-view.json", array(

                    'layout' => $_POST['layout'],
                    'profile' => $profile

                ));

                return true;
            }

            return true;
        }

        $mvc->parse_page($mvc->router->get_request_app(), "View", $_POST['layout'], "User-view.json", array(

            'layout' => $_POST['layout']

        ));
    }
}