<?php

namespace apps\Forums;

class App
{

    function onReady($mvc)
    {

        // define vars
        $this->mvc = $mvc;
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;

        // get all topics
        $this->db->select()
            ->table($mvc->config['prefix'] . 'forums')
            ->column("*")
            ->execute($this->conn);

        $topics['required'] = [];
        $topics['name'] = [];

        $has_looped = [];

        while ($topic = $this->db->fetch()) {

            array_push($topics['name'], $topic['name']);
            array_push($topics['required'], $topic['required']);

            $has_looped[$topic['name']] = true;

            // get subforums
            $subforums_db = clone $this->db;
            $subforums_db->select()
                ->table($this->mvc->config['prefix'] . 'forums_subforums')
                ->where('of = ?')
                ->column("*")
                ->param(array(

                    $topic['name']

                ))
                ->types("s")
                ->execute($this->conn);

            $subforums[$topic['name']] = [];

            while ($subforum = $subforums_db->fetch()) {

                array_push($subforums[$topic['name']], $subforum);
            }

            foreach ($subforums as $subforum_key => $subforum_value) {

                foreach($subforums[$subforum_key] as &$subforum_user) {

                    if (isset($subforum_user['lastmember']) && $subforum_user['lastmember'] != null) {

                        // get user username
                        $this->db->select()
                            ->table($mvc->config['prefix'] . 'users')
                            ->column("username")
                            ->where('uuid = ?')
                            ->param(array(

                                $subforum_user['lastmember']

                            ))
                            ->types("s")
                            ->execute($this->conn);

                        $subforum_user['lastmember'] = $this->db->fetch()['username'];

                    }
                }
            }
        }

        $mvc->parse_page($mvc->router->get_request_app(), "Index", $_POST['layout'], "Forums.json", array(

            'layout' => $_POST['layout'],
            'topics' => $topics,
            'subforums' => $subforums

        ));
    }
}