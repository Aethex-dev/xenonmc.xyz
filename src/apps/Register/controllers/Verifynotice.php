<?php

namespace apps\Register;

class VerifyNotice
{

    function onReady($mvc)
    {

        $mvc->parse_page($mvc->router->get_request_app(), "VerifyNotice", $_POST['layout'], "Register-verifynotice.json", array(

            'layout' => $_POST['layout']

        ));
    }
}