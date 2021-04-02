<?php

namespace apps\Forums;

class Threads
{

    function onReady($mvc)
    {

        // define vars
        $this->db = $mvc->db;
        $this->conn = $mvc->conn;
        $this->mvc = $mvc;

        // get all threads from page
        $threads_per_page = 20;
        $page = intval($mvc->router->get_url()[4]);

        if ($page == "" || $page == 0) {

            $page = '1';
        }

        $offset = ($page - 1) * $threads_per_page;

        $this->db->count()
            ->table($mvc->config['prefix'] . "forums_threads")
            ->where('of = ?')
            ->param(array(

                $mvc->router->get_url()[2] . '/' . $mvc->router->get_url()[3]

            ))
            ->types("s")
            ->execute($this->conn);

        $threads_total = $this->db->result;

        if ($threads_total != null) {

            $threads_total = mysqli_fetch_array($threads_total)[0];
        }

        $total_pages = ceil($threads_total / $threads_per_page);

        if ($page > $total_pages) {

            $page = $total_pages;
            $offset = ($page - 1) * $threads_per_page;
        }

        // getting
        $this->db->select()
            ->table($mvc->config['prefix'] . 'forums_threads')
            ->column("*")
            ->where('of = ? ORDER BY createdate DESC LIMIT ?, ?')
            ->param(array(

                $mvc->router->get_url()[2] . '/' . $mvc->router->get_url()[3],
                $offset,
                $threads_per_page

            ))
            ->types("sss")
            ->execute($this->conn);

        $threads = [];

        while ($thread = $this->db->fetch()) {

            array_push($threads, $thread);
        }

        $mvc->parse_page($mvc->router->get_request_app(), "Threads", $_POST['layout'], "Forums-threads.json", array(

            'layout' => $_POST['layout'],
            'threads' => $threads,
            'threads_total' => $threads_total,
            'total_pages' => $total_pages

        ));
    }
}