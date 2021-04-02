<?php

namespace apps\Account;

class Avatar
{

    function __construct($mvc)
    {

        // define vars
        $this->mvc = $mvc;
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;

        if (ajaxID == 'avatar') {

            // convert image format
            $avatar = base64_encode(file_get_contents($_FILES['avatar']['tmp_name']));

            // update image
            $this->db->update()
                ->table($mvc->config['prefix'] . 'users')
                ->set('avatar = ?')
                ->where('uuid = ?')
                ->param(array(

                    $avatar,
                    $mvc->global['user_uuid']

                ))
                ->types("ss")
                ->execute($this->conn);

            redirect("/");

            return true;
        }

        // render page
        $mvc->parse_page($mvc->router->get_request_app(), "Avatar", $_POST['layout'], "Account-avatar.json", array(

            'layout' => $_POST['layout']

        ));
    }
}

$app = new Avatar($this);
