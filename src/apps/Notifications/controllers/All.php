<?php

namespace apps\Notifications;

class All
{

    function onReady($mvc)
    {

        // define vars
        $this->mvc = $mvc;
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;

        // check if user is logged in
        if ($mvc->global['loggedIn'] == true) {

            // get thread notifications
            $notifications_per_page = 10;
            $page = intval($mvc->router->get_url()[2]);

            if ($page == "" || $page == 0) {

                $page = '1';
            }

            $offset = ($page - 1) * $notifications_per_page;

            $this->db->count()
                ->table($mvc->config['prefix'] . "users_notifications")
                ->where('user = ?')
                ->param(array(

                    $mvc->global['user_uuid']

                ))
                ->types("s")
                ->execute($this->conn);

            $notifications_total = $this->db->result;
            $notifications_total = mysqli_fetch_array($notifications_total)[0];

            $total_pages = ceil($notifications_total / $notifications_per_page);

            if ($page > $total_pages) {

                $page = $total_pages;
                $offset = ($page - 1) * $notifications_per_page;
            }

            if ($offset < 0) {

                $offset = 0;
            }

            // get all notifications
            $this->db->select()
                ->table($mvc->config['prefix'] . 'users_notifications')
                ->column("*")
                ->where('user = ? ORDER BY time DESC LIMIT ?, ?')
                ->param(array(

                    $mvc->global['user_uuid'],
                    $offset,
                    $notifications_per_page

                ))
                ->types("sss")
                ->execute($this->conn);

            $notifications = [];

            while ($notification = $this->db->fetch()) {

                array_push($notifications, $notification);
            };

            // render page
            $mvc->parse_page($mvc->router->get_request_app(), "All", $_POST['layout'], 'Notifications-all.json', array(

                'layout' => $_POST['layout'],
                'notifications' => $notifications,
                'total_pages' => $total_pages

            ));

            return true;
        }

        // render page
        $mvc->parse_page($mvc->router->get_request_app(), "NotLoggedIn", $_POST['layout'], 'Notifications-notloggedin.json', array(

            'layout' => $_POST['layout']

        ));
    }
}