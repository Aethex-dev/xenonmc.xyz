<?php

namespace apps\User;

class getData {

    function onReady($mvc)
    {
        
        $db = $mvc->db;
        $conn = $mvc->conn;

        $db->select()
            ->table($mvc->config['prefix'] . 'users')
            ->column("*")
            ->where('uuid = ?')
            ->param(array(

                $mvc->router->get_url()[2]

            ))
            ->types("s")
            ->execute($conn);

        while($data = $db->fetch()) {

            foreach($data as $key => $value) {

                if(str_starts_with($key, 'password') || str_starts_with($key, 'auth') || str_starts_with($key, 'end') || str_starts_with($key, 'ip') || str_starts_with($key, 'email')) {

                    unset($data[$key]);
                }
            }

            echo json_encode($data);
            return true;
        }

        http_response_code(404);
    }
}