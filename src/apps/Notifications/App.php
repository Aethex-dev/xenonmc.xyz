<?php

namespace apps\Notifications;

class App {

    function onReady($mvc)
    {

        // define vars
        $this->mvc = $mvc;
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;

        // check if user is logged in
        if($mvc->global['loggedIn'] == true) {

            // get all notifications
            $this->db->select()
                ->table($mvc->config['prefix'] . 'users_notifications')
                ->column("*")
                ->where('user = ? ORDER BY time DESC LIMIT 10')
                ->param(array(

                    $mvc->global['user_uuid']

                ))
                ->types("s")
                ->execute($this->conn);

            $notifications = [];

            while($notification = $this->db->fetch()) {

                array_push($notifications, $notification);
            };

            // set notifications to read
            $this->db->update()
                ->table($mvc->config['prefix'] . 'users')
                ->where('uuid = ?')
                ->set('notifications = ?')
                ->param(array(

                    0,
                    $mvc->global['user_uuid']

                ))
                ->types("ss")
                ->execute($this->conn);

            // render page
            $mvc->parse_page($mvc->router->get_request_app(), "Index", $_POST['layout'], 'Notifications.json', array(

                'layout' => $_POST['layout'],
                'notifications' => $notifications

            ));

            return true;
        }

        // render page
        $mvc->parse_page($mvc->router->get_request_app(), "NotLoggedIn", $_POST['layout'], 'Notifications-notloggedin.json', array(

            'layout' => $_POST['layout']

        ));
    }
}