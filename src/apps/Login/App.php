<?php

namespace apps\Login;

class App
{

    /** 
     * username or email exists
     * 
     * @param string, username or email
     * 
     */

    function username_email_exists($username_email)
    {

        $this->db->select()
            ->table($this->mvc->config['prefix'] . 'users')
            ->column('mode')
            ->where('username = ? OR email = ? LIMIT 1')
            ->param(array(

                $username_email,
                $username_email

            ))
            ->types("ss")
            ->execute($this->conn);

        while ($row = $this->db->fetch()) {

            if ($row['mode'] == 'verified') {

                return true;
            } else {

                return false;
            }
        }

        return false;
    }

    /** 
     * get user password hash
     * 
     * @param string, username or email
     * 
     */

    function get_password_hash($username_email)
    {

        $this->db->select()
            ->table($this->mvc->config['prefix'] . 'users')
            ->column('password')
            ->where('username = ? OR email = ? LIMIT 1')
            ->param(array(

                $username_email,
                $username_email

            ))
            ->types("ss")
            ->execute($this->conn);

        while ($row = $this->db->fetch()) {

            return $row['password'];
        }

        return false;
    }

    /** 
     * validate all inputs
     * 
     * @return boolean, if the inputs are validated and ready
     * 
     */

    function validate_inputs()
    {

        // define user input
        $input['username-email'] = trim($_POST['username-email']);
        $input['password'] = trim($_POST['password']);
        $input['sli'] = trim($_POST['sli'] ?? 'false');

        $ready['username-email'] = false;
        $ready['password'] = false;

        $errors = [];

        // username-email - - -
        if (empty($input['username-email'])) {

            array_push($errors, 'Please enter your username or email address.');
        } else {

            $ready['username-email'] = true;
        }

        // password - - -
        if (empty($input['password'])) {

            array_push($errors, 'Please enter your password.');
        } else {

            $ready['password'] = true;
        }

        if ($ready['username-email'] == true && $ready['password'] == true) {

            return true;
        }

        $this->errors = $errors;
        return false;
    }

    /** 
     * main constructor
     * 
     */

    function __construct($mvc)
    {

        sleep(1);

        if (ajaxID == 'login') {

            // define data
            $input['username-email'] = $_POST['username-email'];
            $input['password'] = $_POST['password'];
            $input['sli'] = $_POST['sli'] ?? 'false';

            $this->mvc = $mvc;

            $errors = [];

            $this->conn = $mvc->conn;
            $this->db = $mvc->db;

            // validate inputs
            if (!$this->validate_inputs()) {

                $errors = $this->errors;
                process_errors($errors);

                return false;
            }

            // check account exists
            if ($this->username_email_exists($input['username-email'])) {

                // get password
                $user['password'] = $this->get_password_hash($input['username-email']);

                if (password_verify($input['password'], $user['password'])) {

                    // store user info and finalize login
                    if ($input['sli'] == "true") {

                        $sli_time = time() + (86400 * 30);
                    } else {

                        $sli_time = null;
                    }

                    $this->db->select()
                        ->table($mvc->config['prefix'] . 'users')
                        ->where('username = ? OR email = ? LIMIT 1')
                        ->param(array(

                            $input['username-email'],
                            $input['username-email']

                        ))
                        ->types("ss")
                        ->column('authKey1, authKey2, authKey3')
                        ->execute($this->conn);

                    // get authKeys
                    while ($authkeys = $this->db->fetch()) {

                        // initialize cookies
                        setcookie($mvc->config['prefix'] . 'authKey1', $authkeys['authKey1'], $sli_time, "/");
                        setcookie($mvc->config['prefix'] . 'authKey2', $authkeys['authKey2'], $sli_time, "/");
                        setcookie($mvc->config['prefix'] . 'authKey3', $authkeys['authKey3'], $sli_time, "/");

                        // setup logout sessions
                        $logout_db = clone $this->db;
                        $logout_db->update()
                            ->table($mvc->config['prefix'] . 'users')
                            ->where('username = ? OR email = ?')
                            ->set('endActionAuth = ?')
                            ->param(array(

                                bin2hex(random_bytes(32)),
                                $input['username-email'],
                                $input['username-email']

                            ))
                            ->types("sss")
                            ->execute($this->conn);

                        redirect("/");
                    }
                } else {

                    array_push($errors, 'Invalid password.  Please try again.');
                    process_errors($errors);
                }
            } else {

                array_push($errors, 'The username or email [ ' . $input['username-email'] . ' ] does not exists.  Please try again.');
                process_errors($errors);
            }

            return true;
        }

        // render page
        $mvc->parse_page($mvc->router->get_request_app(), "Index", $_POST['layout'], 'Login.json', array(

            'layout' => $_POST['layout']

        ));
    }
}

$app = new App($this);
