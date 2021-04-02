<?php

namespace apps\Forums;

class Thread
{

    /**
     * translate rank
     * 
     * @param int, level
     * 
     * @return array/boolean, false if record was not found, and if found, the ranks data
     * 
     */

    function translate_rank(int $level)
    {
        $db = clone $this->db;

        $db->select()
            ->table($this->mvc->config['prefix'] . 'users_ranks')
            ->column('*')
            ->where('id = ?')
            ->param(array(

                $level

            ))
            ->types("i")
            ->execute($this->conn);

        while ($rank = $db->fetch()) {

            return array('name' => $rank['name'], 'color' => $rank['color']);
        }

        return false;
    }

    /** 
     * validate inputs for reply feature
     *
     * @return boolean, if the inputs are valid and ready to be used
     *  
     */

    function reply_validate_inputs()
    {

        // define input val
        $input['reply'] = trim($_POST['reply']);
        $input['attachments'] = $_FILES['attachments'];

        $errors = [];

        $ready['reply'] = false;
        $ready['attachments'] = false;

        // reply - - -
        if (empty($input['reply'])) {

            array_push($errors, $this->lang_data['error-empty-reply']);
        } elseif (strlen($input['reply']) > 5000 || strlen($input['reply']) < 15) {

            array_push($errors, $this->lang_data['error-reply-bad-length']);
        } else {

            $ready['reply'] = true;
        }

        // attachments - - -
        if (empty($input['attachments']['tmp_name'])) {

            $ready['attachments'] = true;
        } else {

            // get total files sizes
            $attachments_total_size = 0;

            foreach ($input['attachments']['size'] as $size) {

                $attachments_total_size += $size;
            }
            unset($size);

            if ($attachments_total_size > 16000000) {

                array_push($errors, $this->lang_data['error-reply-attachments-too-large']);
            } else {

                $ready['attachments'] = true;
            }
        }

        // validation
        if ($ready['reply'] == true && $ready['attachments'] == true) {

            return true;
        }

        $this->errors = $errors;
        return false;
    }

    function onReady($mvc)
    {

        // define vars
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;
        $this->mvc = $mvc;
        $this->lang_data = $mvc->get_lang_data('Forums-thread.json');

        // reply feature
        if (ajaxID == 'reply') {

            // authenticate thread and user sign in
            if ($mvc->global['loggedIn'] == true) {

                // thread validate
                $auth['thread_id'] = $_POST['thread_id'] ?? '';
                $auth['thread_name'] = urldecode($_POST['thread_name']) ?? '';
                $auth['topic'] = $_POST['topic'] ?? '';
                $auth['subforum'] = $_POST['subforum'] ?? '';

                // get thread data
                $this->db->select()
                    ->table($mvc->config['prefix'] . 'forums_threads')
                    ->where('of = ? AND name = ? AND id = ? LIMIT 1')
                    ->column("*")
                    ->param(array(

                        $auth['topic'] . '/' . $auth['subforum'],
                        $auth['thread_name'],
                        $auth['thread_id']

                    ))
                    ->types("sss")
                    ->execute($this->conn);

                // fetch data
                while ($thread_data = $this->db->fetch()) {

                    // get subforum data
                    $this->db->select()
                        ->table($mvc->config['prefix'] . 'forums_subforums')
                        ->where('of = ? AND name = ? LIMIT 1')
                        ->column("*")
                        ->param(array(

                            $auth['topic'],
                            $auth['subforum']

                        ))
                        ->types("ss")
                        ->execute($this->conn);

                    $subforum_data = $this->db->fetch();

                    // define input val
                    $input['reply'] = $_POST['reply'];
                    $input['attachments'] = $_FILES['attachments'];

                    // check thread locked status 
                    if ($thread_data['locked'] == 'true') {

                        if ($thread_data['user'] != $mvc->global['user_uuid']) {

                            $errors = [$this->lang_data['error-thread-locked']];
                            process_errors($errors);

                            return true;
                        }
                    }

                    // validate inputs
                    if (!$this->reply_validate_inputs()) {

                        $errors = $this->errors;
                        process_errors($errors);

                        return false;
                    }

                    $Parsedown = new \Parsedown();
                    $Parsedown->setSafeMode(true);
                    $input['reply'] = $Parsedown->text($input['reply']);

                    // insert data
                    $this->db->insert()
                        ->table($mvc->config['prefix'] . 'forums_replies')
                        ->column("user, thread_id, thread_name, data, of, createdate")
                        ->param(array(

                            $mvc->global['user_uuid'],
                            $auth['thread_id'],
                            $auth['thread_name'],
                            $input['reply'],
                            $auth['topic'] . '/' . $auth['subforum'],
                            time()

                        ))
                        ->types("ssssss")
                        ->execute($this->conn);

                    // get thread replies
                    $replies_per_page = 10;

                    $this->db->count()
                        ->table($mvc->config['prefix'] . "forums_replies")
                        ->where('of = ? AND thread_id = ? AND thread_name = ?')
                        ->param(array(

                            $auth['topic'] . '/' . $auth['subforum'],
                            $auth['thread_id'],
                            $auth['thread_name']

                        ))
                        ->types("sss")
                        ->execute($this->conn);

                    $replies_total = $this->db->result;
                    $replies_total = mysqli_fetch_array($replies_total)[0];
                    $replies_total += 1;

                    $total_pages = ceil($replies_total / $replies_per_page);

                    // add to authors notifications
                    if ($thread_data['user'] != $mvc->global['user_uuid']) {

                        // get thread author
                        $this->db->select()
                            ->table($mvc->config['prefix'] . 'users') 
                            ->column("*")
                            ->where('uuid = ?')
                            ->param(array(

                                $thread_data['user']

                            ))
                            ->types("s")
                            ->execute($this->conn);

                        $thread_author = $this->db->fetch();

                        $this->db->insert()
                            ->table($mvc->config['prefix'] . 'users_notifications')
                            ->column('time, name, url, message, user')
                            ->param(array(

                                time(),
                                $auth['thread_name'],
                                '/forums/thread/' . strtolower($auth['topic']) . '/' . strtolower($auth['subforum']) . '/' . $auth['thread_id'] . '/' . $auth['thread_name'] . '/' . $total_pages,
                                $this->lang_data['someone-replied-to-thread'],
                                $thread_data['user']

                            ))
                            ->types("sssss")
                            ->execute($this->conn);

                        $this->db->update()
                            ->table($mvc->config['prefix'] . 'users')
                            ->set('notifications = ?')
                            ->where('uuid = ?')
                            ->param(array(

                                $thread_author['notifications'] + 1,
                                $thread_data['user']

                            ))
                            ->types("ss")
                            ->execute($this->conn);
                    }

                    $this->db->update()
                        ->table($mvc->config['prefix'] . 'forums_subforums')
                        ->set('messages = ?')
                        ->where('of = ? AND name = ?')
                        ->param(array(

                            $subforum_data['messages'] + 1,
                            $auth['topic'],
                            $auth['subforum']

                        ))
                        ->types("sss")
                        ->execute($this->conn);

                    $this->db->update()
                        ->table($mvc->config['prefix'] . 'forums_threads')
                        ->set('replies = ?')
                        ->where('name = ? AND id = ? AND of = ?')
                        ->param(array(

                            $thread_data['replies'] + 1,
                            $auth['thread_name'],
                            $auth['thread_id'],
                            $auth['topic'] . '/' . $auth['subforum']

                        ))
                        ->types("ssss")
                        ->execute($this->conn);

                    return true;
                }

                echo 'thread not found';

                return true;
            }

            // error
            $errors = [];

            array_push($errors, $this->lang_data['error-reply-not-logged-in']);
            process_errors($errors);

            return true;
        }

        // get thread data
        $this->db->select()
            ->table($mvc->config['prefix'] . 'forums_threads')
            ->where('of = ? AND name = ? AND id = ? LIMIT 1')
            ->column("*")
            ->param(array(

                $mvc->router->get_url()[2] . '/' . $mvc->router->get_url()[3],
                urldecode($mvc->router->get_url()[5]),
                $mvc->router->get_url()[4]

            ))
            ->types("sss")
            ->execute($this->conn);

        while ($thread_data = $this->db->fetch()) {

            // get user data
            $this->db->select()
                ->table($mvc->config['prefix'] . 'users')
                ->where('uuid = ?')
                ->column("*")
                ->param(array(

                    $thread_data['user']

                ))
                ->types("s")
                ->execute($this->conn);

            while ($user_data = $this->db->fetch()) {

                // get thread replies
                $replies_per_page = 10;
                $page = intval($mvc->router->get_url()[6]);

                if ($page == "" || $page == 0) {

                    $page = '1';
                }

                $offset = ($page - 1) * $replies_per_page;

                $this->db->count()
                    ->table($mvc->config['prefix'] . "forums_replies")
                    ->where('of = ? AND thread_id = ? AND thread_name = ?')
                    ->param(array(

                        $mvc->router->get_url()[2] . '/' . $mvc->router->get_url()[3],
                        $mvc->router->get_url()[4],
                        urldecode($mvc->router->get_url()[5])

                    ))
                    ->types("sss")
                    ->execute($this->conn);

                $replies_total = $this->db->result;
                $replies_total = mysqli_fetch_array($replies_total)[0];

                $total_pages = ceil($replies_total / $replies_per_page);

                if ($page > $total_pages) {

                    $page = $total_pages;
                    $offset = ($page - 1) * $replies_per_page;
                }

                if ($offset < 0) {

                    $offset = 0;
                }

                // get data
                $this->db->select()
                    ->table($mvc->config['prefix'] . 'forums_replies')
                    ->column("*")
                    ->where('of = ? AND thread_id = ? AND thread_name = ? ORDER BY createdate ASC LIMIT ?, ?')
                    ->param(array(

                        $mvc->router->get_url()[2] . '/' . $mvc->router->get_url()[3],
                        $mvc->router->get_url()[4],
                        urldecode($mvc->router->get_url()[5]),
                        $offset,
                        $replies_per_page

                    ))
                    ->types("sssss")
                    ->execute($this->conn);

                $replies = [];

                while ($reply = $this->db->fetch()) {

                    array_push($replies, $reply);
                }

                $reply_index = -1;

                // add user data to each reply
                foreach ($replies as $rep_user) {

                    $reply_index++;

                    // get user data
                    $this->db->select()
                        ->table($mvc->config['prefix'] . 'users')
                        ->where('uuid = ?')
                        ->column("*")
                        ->param(array(

                            $rep_user['user']

                        ))
                        ->types("s")
                        ->execute($this->conn);

                    $user_rep_data = $this->db->fetch();

                    // set user
                    $replies[$reply_index]['user'] = $user_rep_data;
                }

                unset($reply);

                $reply_index = -1;

                foreach ($replies as $reply) {

                    $reply_index++;

                    $rank_data = $this->translate_rank($replies[$reply_index]['user']['rank']);

                    $replies[$reply_index]['user']['rank'] = [

                        'color' => null,
                        'name' => null

                    ];

                    $replies[$reply_index]['user']['rank']['name'] = strtoupper($rank_data['name']);
                    $replies[$reply_index]['user']['rank']['color'] = $rank_data['color'] ?? '#fff';
                }

                unset($reply);

                $user_data['rank'] = $this->translate_rank($user_data['rank']);
                $user_data['rank']['name'] = strtoupper($user_data['rank']['name']);

                $is_author_viewing_thread = false;

                if (isset($mvc->global['user_uuid']))
                    if ($user_data['uuid'] == $mvc->global['user_uuid']) {

                        $is_author_viewing_thread = true;
                    }

                // render page
                $mvc->parse_page($mvc->router->get_request_app(), "Thread", $_POST['layout'], "Forums-thread.json", array(

                    'layout' => $_POST['layout'],
                    'thread' => $thread_data,
                    'user' => $user_data,
                    'replies' => $replies,
                    'replies_total' => $replies_total,
                    'replies_per_page' => $replies_per_page,
                    'author_viewing_thread' => $is_author_viewing_thread

                ));

                return false;
            }

            // render page
            $mvc->parse_page($mvc->router->get_request_app(), "ThreadNotFound", $_POST['layout'], "Forums-threadnotfound.json", array(

                'layout' => $_POST['layout']

            ));

            return false;
        }

        // render page
        $mvc->parse_page($mvc->router->get_request_app(), "ThreadNotFound", $_POST['layout'], "Forums-threadnotfound.json", array(

            'layout' => $_POST['layout']

        ));

        return false;
    }
}