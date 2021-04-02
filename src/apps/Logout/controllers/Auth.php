<?php

namespace apps\Logout;

class Auth
{

    function __construct($mvc)
    {

        // authentication
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;

        if (isset($mvc->global['loggedIn']) && $mvc->global['loggedIn'] == true) {

            $this->db->select()
                ->column('username')
                ->table($mvc->config['prefix'] . 'users')
                ->where('authKey1 = ? AND authKey2 = ? AND authKey3 = ? AND endActionAuth = ? LIMIT 1')
                ->param(array(

                    $_COOKIE[$mvc->config['prefix'] . 'authKey1'],
                    $_COOKIE[$mvc->config['prefix'] . 'authKey2'],
                    $_COOKIE[$mvc->config['prefix'] . 'authKey3'],
                    $mvc->router->get_url()[2]

                ))
                ->types("ssss")
                ->execute($this->conn);

            while ($row = $this->db->fetch()) {

                if (isset($row['username']))
                    if ($row['username'] != null) {

                        setcookie($mvc->config['prefix'] . 'authKey1', null, time() - 3600, "/");
                        setcookie($mvc->config['prefix'] . 'authKey2', null, time() - 3600, "/");
                        setcookie($mvc->config['prefix'] . 'authKey3', null, time() - 3600, "/");

                        redirect("/");

                        $mvc->parse_page($mvc->router->get_request_app(), "Auth", $_POST['layout'], 'Logout-auth.json', array(

                            'layout' => $_POST['layout']

                        ));

                        return true;
                    } else {

                        $mvc->parse_page($mvc->router->get_request_app(), "Failed", $_POST['layout'], 'Logout-failed.json', array(

                            'layout' => $_POST['layout']

                        ));

                        return false;
                    }
            }

            $mvc->parse_page($mvc->router->get_request_app(), "Failed", $_POST['layout'], 'Logout-failed.json', array(

                'layout' => $_POST['layout']

            ));
        } else {

            $mvc->parse_page($mvc->router->get_request_app(), "NotLoggedIn", $_POST['layout'], 'Logout-notloggedin.json', array(

                'layout' => $_POST['layout']

            ));
        }
    }
}

$app = new Auth($this);
