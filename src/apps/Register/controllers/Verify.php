<?php

namespace apps\Register;

class Verify
{

    /** 
     * validate inputs
     * 
     * @return boolean, if the inputs are validated and ready
     * 
     */

    function validate_inputs()
    {

        // define user input
        $input['password'] = trim($_POST['password']);
        $input['cpassword'] = trim($_POST['cpassword']);

        $errors = [];

        $ready['password'] = false;
        $ready['cpassword'] = false;

        // password - - -
        if (empty($input['password'])) {

            array_push($errors, 'Please enter a password.');
        } elseif (strlen($input['password']) > 50 || strlen($input['password']) < 8) {

            array_push($errors, 'Your password must be between 8 and 50 characters long.  Please try again.');
        } else {

            $ready['password'] = true;
        }

        // cpassword - - -
        if (empty($input['password'])) {

            array_push($errors, 'Please confirm your password.');
        } elseif ($input['password'] != $input['cpassword']) {

            array_push($errors, 'Your passwords do not match.  Please try again.');
        } else {

            $ready['cpassword'] = true;
        }

        if ($ready['password'] == true && $ready['cpassword'] == true) {

            return true;
        }

        $this->errors = $errors;
        return false;
    }

    /** 
     * verify token info
     * 
     * @param string, auth token 1
     * 
     * @param string, auth token 2
     * 
     * @param string, auth token 3
     * 
     * @param string, auth user
     * 
     */

    function validate_url_tokens($auth_1, $auth_2, $auth_3, $auth_user)
    {

        $this->db->select()
            ->table($this->mvc->config['prefix'] . 'users')
            ->where('authToken1 = ? AND authToken2 = ? AND authToken3 = ? AND username = ?')
            ->param(array(

                $auth_1,
                $auth_2,
                $auth_3,
                $auth_user

            ))
            ->types("ssss")
            ->column('username, mode')
            ->execute($this->conn);

        while ($row = $this->db->fetch()) {

            if (isset($row['username']) && $row['mode'] == 'unverified') {

                return true;
            } else {

                return false;
            }
        }

        return false;
    }

    function __construct($mvc)
    {

        $this->mvc = $mvc;
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;

        // method
        if (ajaxID == 'verify') {

            // define user input
            $input['password'] = $_POST['password'];
            $input['cpassword'] = $_POST['cpassword'];

            // define verify data
            $auth['token1'] = $_POST['token1'];
            $auth['token2'] = $_POST['token2'];
            $auth['token3'] = $_POST['token3'];
            $auth['user'] = $_POST['user'];

            if ($this->validate_url_tokens($auth['token1'], $auth['token2'], $auth['token3'], $auth['user'])) {

                // validate inputs
                if (!$this->validate_inputs()) {

                    $errors = $this->errors;
                    process_errors($errors);

                    return false;
                }

                // hash password
                $hpassword = password_hash($input['password'], PASSWORD_DEFAULT);
                $avatar_base64 = $mvc->config['app']['user']['def-avatar'];

                // get user data
                $this->db->select()
                    ->table($mvc->config['prefix'] . 'users')
                    ->column("*")
                    ->where('username = ? LIMIT 1')
                    ->param(array(

                        $auth['user']

                    ))
                    ->types("s")
                    ->execute($this->conn);

                $user_data = $this->db->fetch();

                // data update
                $this->db->update()
                    ->table($mvc->config['prefix'] . 'users')
                    ->set('password = ?, joined = ?, avatar = ?, mode = ?')
                    ->param(array(

                        $hpassword,
                        time(),
                        $avatar_base64,
                        'verified',
                        $auth['user']

                    ))
                    ->types("sssss")
                    ->where('username = ?')
                    ->execute($this->conn);

                // reputation create record
                $this->db->insert()
                    ->table($mvc->config['prefix'] . 'users_rep')
                    ->column('uuid, reputation, messages, threads')
                    ->param(array(

                        $user_data['uuid'],
                        0,
                        0,
                        0

                    ))
                    ->types("siii")
                    ->execute($this->conn);
            } else {

                redirect($mvc->config['url']['root'] . '/register/verify/' . $auth['token2'] . '/' . $auth['token3'] . '/' . $auth['token1'] . '/' . $auth['user']);
            }

            return true;
        }

        if ($this->validate_url_tokens($this->mvc->router->get_url()[2], $this->mvc->router->get_url()[3], $this->mvc->router->get_url()[4], $this->mvc->router->get_url()[5])) {

            $mvc->parse_page($mvc->router->get_request_app(), "Verify", $_POST['layout'], 'Register-verify.json', array(

                'layout' => $_POST['layout']

            ));
        } else {

            $mvc->parse_page($mvc->router->get_request_app(), "VerifyFailed", $_POST['layout'], 'Register-verifyfailed.json', array(

                'layout' => $_POST['layout']

            ));
        }
    }
}

$app = new Verify($this);
