<?php

namespace apps\Account;

class App
{

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
     * validate inputs
     * 
     * @return boolean, if the inputs are valid
     * 
     */

    function validate_inputs()
    {

        // define input vars
        $input['username-new'] = trim($_POST['change-username']) ?? '';
        $input['email-new'] = trim($_POST['change-email']) ?? '';
        $input['about-new'] = trim($_POST['change-about']) ?? '';

        $errors = [];

        $ready['username-new'] = false;
        $ready['email-new'] = false;
        $ready['about-new'] = false;

        // username change - - -
        if (strcasecmp($input['username-new'], $this->mvc->global['user_username']) == 0) {

            $ready['username-new'] = true;
        } else {

            if (empty($input['username-new'])) {

                array_push($errors, $this->lang_data['error-new-username-empty']);
            } elseif (!preg_match("/^[a-zA-Z0-9_]*$/", $input['username-new'])) {

                array_push($errors, $this->lang_data['error-username-wrong-length']);
            } elseif ($this->username_exists($input['username-new'])) {

                array_push($errors, $this->lang_data['error-username-exists']);
            } elseif (strlen($input['username-new']) < 3 || strlen($input['username-new']) > 14) {

                array_push($errors, $this->lang_data['error-invalid-chars']);
            } else {

                $ready['username-new'] = true;
            }
        }

        // email change - - -
        if ($input['email-new'] == $this->mvc->global['user_email']) {

            $ready['email-new'] = true;
        } else {

            if (empty($input['email-new'])) {

                array_push($errors, $this->lang_data['error-email-empty']);
            } elseif (!filter_var($input['email-new'], FILTER_VALIDATE_EMAIL)) {

                array_push($errors, $this->lang_data['error-invalid-email']);
            } else {

                $domain = explode("@", $input['email-new']);
                $domain = $domain[1];

                $email_exists = checkdnsrr($domain, "MX");

                if (!$email_exists) {

                    array_push($errors, $this->lang_data['error-invalid-email']);
                } elseif ($this->email_exists($input['email-new'])) {

                    array_push($errors, $this->lang_data['error-email-exists']);
                } else {

                    $ready['email-new'] = true;
                }
            }
        }

        // about change - - -
        if($input['about-new'] == $this->mvc->global['user_about']) {

            $ready['about-new'] = true;
        } else {


        }

        $ready['about-new'] = true;

        $ready_max = count($ready);
        $ready_current = 0;

        // run checks
        foreach($ready as $ready_i) {

            if($ready_i == true) {

                $ready_current++;
            }

            if($ready_max == $ready_current) {

                return true;
            }
        }

        $this->errors = $errors;
        return false;
    }

    /** 
     * get an array of all the settings that are being changed
     * 
     * @return array, array of all the settings that where changed
     * 
     */

    function check_setting_diff($input)
    {

        $settings = [];

        foreach ($input as $key => $value) {

            if($value != $this->mvc->global['user_' . $key]) {
                
                array_push($settings, $key);
            }

        }

        return $settings;
    }

    /** 
     * app constructor
     * 
     * @param object, mvc framework object
     * 
     */

    function __construct($mvc)
    {

        // define vars
        $this->mvc = $mvc;
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;

        $this->lang_data = $mvc->get_lang_data('Account.json');

        // setting change logic
        if (ajaxID == 'settings') {

            // define input vars
            $input['username'] = trim($_POST['change-username']) ?? '';
            $input['email'] = trim($_POST['change-email']) ?? '';
            $input['about'] = trim($_POST['change-about']) ?? '';

            // check if user is logged in
            if ($mvc->global['loggedIn'] == true) {

                // validate inputs and input changed data
                if (!$this->validate_inputs()) {

                    $errors = $this->errors;
                    process_errors($errors);

                    return false;
                }

                // check if user is changing anything
                foreach ($this->check_setting_diff($input) as $setting_array) {
                    
                    // update data accordingly
                    $this->db->update()
                    ->set($setting_array . ' = ?')
                    ->table($mvc->config['prefix'] . 'users')
                    ->where('uuid = ?')
                    ->param(array(

                        $input[$setting_array],
                        $mvc->global['user_uuid']
                        
                    ))
                    ->types("ss")
                    ->execute($this->conn);

                }

                return true;
            }

            reload();

            return true;
        }

        if ($mvc->global['loggedIn'] == true) {

            $settings = array(

                'change-username' => array(

                    'name' => 'change-username',
                    'title' => $this->lang_data['form-change-username-label'],
                    'desc' => $this->lang_data['form-change-username-desc'],
                    'type' => 'username',
                    'value' => $mvc->global['user_username']

                ),

                'change-email' => array(

                    'name' => 'change-email',
                    'title' => $this->lang_data['form-change-email-label'],
                    'desc' => $this->lang_data['form-change-email-desc'],
                    'type' => 'email',
                    'value' => $mvc->global['user_email']

                )

            );

            $mvc->parse_page($mvc->router->get_request_app(), "Index", $_POST['layout'], "Account.json", array(

                'layout' => $_POST['layout'],
                'settings' => $settings

            ));

            return true;
        }

        $mvc->parse_page($mvc->router->get_request_app(), "NotLoggedIn", $_POST['layout'], "Account-notloggedin.json", array(

            'layout' => $_POST['layout']

        ));
    }
}

$app = new App($this);
