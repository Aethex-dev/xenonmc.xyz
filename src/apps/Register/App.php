<?php

namespace apps\Register;

class App
{

    /** 
     * username exists
     * 
     * @param string, username
     * 
     */

    function username_exists($username)
    {

        $this->db->select()
            ->table($this->mvc->config['prefix'] . 'users')
            ->column('username')
            ->where('username = ? LIMIT 1')
            ->param(array(

                $username

            ))
            ->types("s")
            ->execute($this->conn);

        if ($this->db->fetch() !== null) {

            return true;
        }

        return false;
    }

    /** 
     * email exists
     * 
     * @param string, email
     * 
     */

    function email_exists($email)
    {

        $this->db->select()
            ->table($this->mvc->config['prefix'] . 'users')
            ->column('email')
            ->where('email = ? LIMIT 1')
            ->param(array(

                $email

            ))
            ->types("s")
            ->execute($this->conn);

        if ($this->db->fetch() !== null) {

            return true;
        }

        return false;
    }

    /** 
     * validate all user input
     * 
     * @return boolean, if the inputs are validated and ready
     * 
     */

    function validate_inputs()
    {

        // define user input
        $input['username'] = trim($_POST['username']);
        $input['email'] = trim($_POST['email']);
        $input['tos'] = trim($_POST['terms-of-service'] ?? 'false');

        // defaults
        $errors = [];
        $ready['username'] = false;
        $ready['email'] = false;
        $ready['tos'] = true;

        // username - - -
        if (empty($input['username'])) {

            array_push($errors, 'Please enter a username.');
        } elseif (strlen($input['username']) < 3 || strlen($input['username']) > 14) {

            array_push($errors, 'Your username must be between 3 and 14 characters long.  Please try again.');
        } elseif (!preg_match("/^[a-zA-Z0-9_]*$/", $input['username'])) {

            array_push($errors, 'Your username may only contain letters, numbers, and underscores.  Please try again.');
        } elseif ($this->username_exists($input['username'])) {

            array_push($errors, 'An account with the username [ ' . $input['username'] . ' ] already exists.  Please choose another username.');
        } else {

            $ready['username'] = true;
        }

        // email - - -
        if (empty($input['email'])) {

            array_push($errors, 'Please enter your email address.');
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {

            array_push($errors, 'The email address [ ' . $input['email'] . ' ] is not valid.  Please try again.');
        } else {

            $domain = explode("@", $input['email']);
            $domain = $domain[1];

            $email_exists = checkdnsrr($domain, "MX");

            if (!$email_exists) {

                array_push($errors, 'The email address [ ' . $input['email'] . ' ] is not valid.  Please try again.');
            } elseif ($this->email_exists($input['email'])) {

                array_push($errors, 'An account with the email address [ ' . $input['email'] . ' ] already exists.  Please choose another email address.');
            } else {

                $ready['email'] = true;
            }
        }

        // terms of service - - -
        if ($input['tos'] == "false") {

            array_push($errors, 'Please accept the Terms of Service and Privacy Policy.');
        } else {

            $ready['tos'] == true;
        }

        // finalize
        if ($ready['username'] == true && $ready['email'] == true && $ready['tos'] == true) {

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

        // wait before call
        sleep(1);

        // prepare
        $this->conn = $mvc->conn;
        $this->db = $mvc->db;
        $this->mvc = $mvc;

        // process
        if (ajaxID == 'register') {

            // define user input
            $input['username'] = trim($_POST['username']);
            $input['email'] = trim($_POST['email']);
            $input['tos'] = trim($_POST['terms-of-service'] ?? 'false');

            // defaults
            $errors = array();

            // input validation
            if (!$this->validate_inputs()) {

                $errors = $this->errors;
                process_errors($errors);

                return false;
            }

            $auth[0] = bin2hex(random_bytes(50)) . $input['username'] . $input['email'];
            $auth[1] = bin2hex(random_bytes(50)) . $input['username'] . $input['email'];
            $auth[2] = bin2hex(random_bytes(50)) . $input['username'] . $input['email'];
            $auth[3] = bin2hex(random_bytes(50)) . $input['username'] . $input['email'];
            $auth[4] = bin2hex(random_bytes(50)) . $input['username'] . $input['email'];
            $auth[5] = bin2hex(random_bytes(50)) . $input['username'] . $input['email'];

            // data insertion
            $this->db->insert()
                ->table($mvc->config['prefix'] . 'users')
                ->column('uuid, username, email, password, mode, authKey1, authKey2, authKey3, authToken1, authToken2, authToken3, ip, joined, rank')
                ->param(array(

                    bin2hex(random_bytes(5)) . $input['username'],
                    $input['username'],
                    $input['email'],
                    'null',
                    'unverified',
                    $auth[0],
                    $auth[1],
                    $auth[2],
                    $auth[3],
                    $auth[4],
                    $auth[5],
                    $_SERVER['REMOTE_ADDR'],
                    time(),
                    1

                ))
                ->types("ssssssssssssss")
                ->execute($this->conn);

            $auth_url = $mvc->config['url']['root'] . '/register/verify/' . $auth[3] . '/' . $auth[4] . '/' . $auth[5] . '/' . $input['username'];

            echo "You are not redirecting, email servers down, use this link instead: <a href='$auth_url'>VERIFY</a>";
            return;

            redirect("/register/verifynotice/" . $input['username'] . '/' . $input['email']);

            return true;
        }

        // render page
        $mvc->parse_page($mvc->router->get_request_app(), "Index", $_POST['layout'], 'Register.json', array(

            'layout' => $_POST['layout']

        ));
    }
}

$app = new App($this, $this->conn);
