<?php

namespace xframe\Mvc;

class Page 
{

    /**
     * main method
     * 
     */

    function __construct($mvc)
    {

        // define mvc object
        $this->mvc = $mvc;

        // start the application
        $this->onReady($this->mvc);
    }

    /**
     * default ready method
     * 
     * @param object, mvc object
     * 
     */

    function onReady($mvc) {

        echo "Error, the on ready method for this application was not overridden.  Please create an [ onReady() ] method in your application class.";
    }

}