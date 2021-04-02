<?php

namespace apps\Forums;

class Post
{

    /** 
     * validate user input
     * 
     */

    function validate_inputs()
    {

        // define user data
        $input['title'] = trim($_POST['title']);
        $input['post'] = trim($_POST['post']);

        $errors = [];

        $ready['title'] = false;
        $ready['post'] = false;

        // title - - -
        if (empty($input['title'])) {

            array_push($errors, 'Please enter a title for your post.');
        } elseif (strlen($input['title']) > 80 || strlen($input['title']) < 5) {

            array_push($errors, 'Post title must be between 5 and 80 characters long.  Please try again.');
        } else {

            $ready['title'] = true;
        }

        // post - - -
        if (empty($input['post'])) {

            array_push($errors, 'Please enter a post body.');
        } elseif (strlen($input['post']) > 5000 || strlen($input['post']) < 5) {

            array_push($errors, 'Post body must be between 5 and 5000 characters long.  Please try again.');
        } else {

            $ready['post'] = true;
        }

        // validate
        if ($ready['title'] == true && $ready['post'] == true) {

            return true;
        }

        $this->errors = $errors;
        return false;
    }

    public function onReady($mvc)
    {

        // define vars
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;
        $this->mvc = $mvc;

        if (ajaxID == 'post') {

            if ($mvc->global['loggedIn'] == true) {

                $auth['topic'] = $_POST['topic'] ?? '';
                $auth['subforum'] = $_POST['subforum'] ?? '';

                // find subforum
                $this->db->select()
                    ->table($mvc->config['prefix'] . 'forums_subforums')
                    ->column("name")
                    ->where('name = ? AND of = ? LIMIT 1')
                    ->types("ss")
                    ->param(array(

                        $auth['subforum'],
                        $auth['topic']

                    ))
                    ->execute($this->conn);

                while ($this->db->fetch()) {

                    // get user data
                    $user[1] = $mvc->global['user_authToken1'] ?? '';
                    $user[2] = $mvc->global['user_authToken2'] ?? '';
                    $user[3] = $mvc->global['user_authToken3'] ?? '';

                    // validate user data
                    $this->db->select()
                        ->table($mvc->config['prefix'] . 'users')
                        ->where('authToken1 = ? AND authToken2 = ? AND authToken3 = ?')
                        ->column("username")
                        ->param(array(

                            $user[1],
                            $user[2],
                            $user[3]

                        ))
                        ->types("sss")
                        ->execute($this->conn);

                    while ($this->db->fetch()) {

                        // prepare to post
                        $input['title'] = trim($_POST['title']);
                        $input['post'] = trim($_POST['post']);
                        $input['lock'] = $_POST['lock'] ?? 'false';

                        if (!$this->validate_inputs()) {

                            $errors = $this->errors;
                            process_errors($errors);

                            return false;
                        }

                        $id = bin2hex(random_bytes(4));

                        $Parsedown = new \Parsedown();
                        $Parsedown->setSafeMode(true);
                        $input['post'] = $Parsedown->text($input['post']);

                        $input['post'] = preg_replace('~\:(.+?)\:~',"<i class='xe-emoji xe-emoji-$1'></i>", $input['post']);

                        // data insert
                        $this->db->insert()
                            ->table($mvc->config['prefix'] . 'forums_threads')
                            ->column('of, user, createdate, name, replies, views, locked, data, id')
                            ->param(array(

                                $auth['topic'] . '/' . $auth['subforum'],
                                $mvc->global['user_uuid'],
                                time(),
                                str_replace(" ", "-", $input['title']),
                                '0',
                                '1',
                                $input['lock'],
                                $input['post'],
                                $id

                            ))
                            ->types("sssssssss")
                            ->execute($this->conn);

                        // get subforum data
                        $this->db->select()
                            ->table($mvc->config['prefix'] . 'forums_subforums')
                            ->where('of = ? AND name = ?')
                            ->column("*")
                            ->param(array(

                                $auth['topic'],
                                $auth['subforum']

                            ))
                            ->types("ss")
                            ->execute($this->conn);

                        while ($subforum = $this->db->fetch()) {

                            // update stat data on subforum
                            $this->db->update()
                                ->table($mvc->config['prefix'] . 'forums_subforums')
                                ->set('threads = ?')
                                ->types("sss")
                                ->where('of = ? AND name = ?')
                                ->param(array(

                                    $subforum['threads'] + 1,
                                    $auth['topic'],
                                    $auth['subforum']

                                ))
                                ->execute($this->conn);

                            // get user data
                            $this->db->select()
                                ->table($mvc->config['prefix'] . 'users_rep')
                                ->where('uuid = ?')
                                ->column("*")
                                ->param(array(

                                    $mvc->global['user_uuid']

                                ))
                                ->types("s")
                                ->execute($this->conn);

                            while ($user_rep = $this->db->fetch()) {

                                // update stats on user
                                $this->db->update()
                                    ->table($mvc->config['prefix'] . 'users_rep')
                                    ->set('threads = ?')
                                    ->types("ss")
                                    ->where('uuid = ?')
                                    ->param(array(

                                        $user_rep['threads'] + 1,
                                        $user_rep['uuid']

                                    ))
                                    ->execute($this->conn);

                                // update stats on subforum
                                $this->db->update()
                                    ->table($mvc->config['prefix'] . 'forums_subforums')
                                    ->set('lastthread = ?, lastthreadid = ?, lastmember = ?, lastthreadtime = ?')
                                    ->types("ssssss")
                                    ->where('of = ? AND name = ?')
                                    ->param(array(

                                        $input['title'],
                                        $id,
                                        $mvc->global['user_uuid'],
                                        time(),
                                        $auth['topic'],
                                        $auth['subforum']

                                    ))
                                    ->execute($this->conn);

                                redirect("/forums/thread/" . $auth['topic'] . '/' . $auth['subforum'] . '/' . $id . '/' . str_replace(" ", "-", $input['title']));

                                break;
                            }

                            break;
                        }

                        return true;
                    }

                    redirect($_SERVER['REQUEST_URI']);

                    return true;
                }

                redirect($_SERVER['REQUEST_URI']);

                return true;
            }

            redirect($_SERVER['REQUEST_URI']);
        }

        if ($mvc->global['loggedIn'] == true) {

            // check page
            if ($mvc->router->get_url()[2] != "" && $mvc->router->get_url()[3] != "") {

                // find subforum
                $this->db->select()
                    ->table($mvc->config['prefix'] . 'forums_subforums')
                    ->column("name")
                    ->where('name = ? AND of = ? LIMIT 1')
                    ->types("ss")
                    ->param(array(

                        $mvc->router->get_url()[3],
                        $mvc->router->get_url()[2]

                    ))
                    ->execute($this->conn);

                while ($this->db->fetch()) {

                    $mvc->parse_page($mvc->router->get_request_app(), "Post", $_POST['layout'], "Forums-post.json", array(

                        'layout' => $_POST['layout'],

                    ));

                    return true;
                }

                $mvc->parse_page($mvc->router->get_request_app(), "PostSelect", $_POST['layout'], "Forums-postselect.json", array(

                    'layout' => $_POST['layout'],

                ));

                return true;
            } else {

                $mvc->parse_page($mvc->router->get_request_app(), "PostSelect", $_POST['layout'], "Forums-postselect.json", array(

                    'layout' => $_POST['layout'],

                ));

                return true;
            }
        } else {

            $mvc->parse_page($mvc->router->get_request_app(), "NotLoggedIn", $_POST['layout'], "Forums-notloggedin.json", array(

                'layout' => $_POST['layout'],

            ));

            return true;
        }
    }
}