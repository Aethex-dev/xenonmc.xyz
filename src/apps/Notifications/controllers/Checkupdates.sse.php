<?php

namespace apps\Notifications;

session_start();
session_write_close();

// set output headers
header('Content-Type: text/event-stream;charset=UTF-8');
header('Cache-Control: no-cache');
ignore_user_abort(true);

class Checkupdates_sse
{

    function onReady($mvc)
    {

        if ($mvc->global['loggedIn'] == true) {

            echo "retry: 2000\n";

            // define vars
            $this->mvc = $mvc;
            $this->db = $mvc->db;
            $this->conn = $mvc->conn;

            // last updated record
            $last_notifications_count = 0;
            $last_notifications_time = time();

            while (true) {

                if (connection_aborted()) {

                    exit();
                }

                $this->db->select()
                    ->table($mvc->config['prefix'] . 'users')
                    ->where('uuid = ?')
                    ->column("*")
                    ->types("s")
                    ->param(array(

                        $mvc->global['user_uuid']

                    ))
                    ->execute($this->conn);

                $user_data = $this->db->fetch();

                if ($user_data['notifications'] != $last_notifications_count && $last_notifications_time != time()) {

                    if ($user_data['notifications'] > 0) {

                        echo "data:new\n\n";
                    } else{

                        echo "data:none\n\n";
                    }

                    $last_notifications_count = $user_data['notifications'];
                    $last_notifications_time = time();
                }

                ob_flush();
                flush();

                sleep(2);
            }

            return true;
        }

        echo "data:stop\n\n";
        exit();
    }
}