/** 
 * navigation class
 * 
*/

class p_navigation {

    /**
     * constructor
     * 
     */

    constructor() {

        Notification.requestPermission().then(function(result) {

            console.log(result);
        });

        // notifications
        var source = new EventSource("/notifications/checkupdates.sse");
        source.onmessage = function(event) {

            var noti_bell = $("body > nav.subnav-wrapper > div.buttons-wrapper > button.button.notifications-bell");

            console.log("NOTIFICATION: " + event.data);

            if(event.data == 'stop') {

                this.stop_notifications = true;
            } else {

                this.stop_notifications = false;
            }

            if(event.data == 'new') {

                noti_bell.addClass("alert");
                var notification = new Notification('XENONMC', { body: 'You have new notifications', icon: '/internal_data/cache/themes/Default/favicon.png' });
            } else {

                noti_bell.removeClass("alert");
            }
        }

        console.log(this.stop_notifications);
        source.onerror = function(err) {

            if(this.stop_notifications == false) {

                console.error("EventSource failed:", err);
                source = null;

                // notifications
                source = new EventSource("/notifications/checkupdates.sse");
                source.onmessage = function(event) {

                    var noti_bell = $("body > nav.subnav-wrapper > div.buttons-wrapper > button.button.notifications-bell");

                    console.log("NOTIFICATION: " + event.data);
                if(event.data == 'new') {

                        noti_bell.addClass("alert");
                        var notification = new Notification('XENONMC', { body: 'You have new notifications', icon: '/internal_data/cache/   themes/Default/favicon.png' });
                    } else {

                        noti_bell.removeClass("alert");
                    }
                }
            } else {

                source.close();
            }
        }   

        // urls
        var url = window.location.href.split("/");
        $(".links-wrapper .link[href='/" + url[3] + "/']").css({color: "#55ff55", opacity: '100%', 'border-bottom': '2px solid #55ff55'});

        window.addEventListener('popstate', function (e) {

            if(e.state){

                var url = window.location.href.split("/");

                $(".links-wrapper .link").prop('style', '');
                $(".links-wrapper .link[href='/" + url[3] + "/']").css({color: "#55ff55", opacity: '100%', 'border-bottom': '2px solid #55ff55'});
            }
        });

        $("a").click(function() {

            setInterval(() => {

                var url = window.location.href.split("/");

                $(".links-wrapper .link").prop('style', '');
                $(".links-wrapper .link[href='/" + url[3] + "/']").css({color: "#55ff55", opacity: '100%', 'border-bottom': '2px solid #55ff55'}); 
            }, 2000);
        });
    }

    /** 
     * open side bar navigation drawer
     * 
    */

    open_sidenav() {

        $(".sidenav-wrapper").css("left", "0px");
        $(".sidenav-overlay").fadeIn(300);

    }

    /** 
     * close side bar navigation drawer
     *  
    */

    close_sidenav() {

        $(".sidenav-wrapper").css("left", "-250px");
        $(".sidenav-overlay").fadeOut(300);

    }

}

// create navigation object
let navigation = new p_navigation();

// hide navbar links based on amount of space available
window.onresize = function () {

    // define navigation items
    var links = $(".navbar-wrapper .links-wrapper");
    var menu = $(".navbar-wrapper .group .menu");
    var logo = $(".navbar-wrapper .group .logo");

    // define space from edge
    var isOff = $(window).width() - (links.offset().left + links.outerWidth());

    // calculate space
    if (isOff >= 0) {

        links.css("opacity", "100%");
        links.css("pointer-events", "all");
        logo.css("margin-left", "20px");
        menu.css("display", "none");

    } else {

        links.css("opacity", "0%");
        logo.css("margin-left", "10px");
        links.css("pointer-events", "none");
        menu.css("display", "initial");

    }

};


// hide links based on amount of space available
$(document).ready(function () {

    // define navigation items
    var links = $(".navbar-wrapper .links-wrapper");
    var menu = $(".navbar-wrapper .group .menu");
    var logo = $(".navbar-wrapper .group .logo");

    // define space from edge
    var isOff = $(window).width() - (links.offset().left + links.outerWidth());

    // calculate space
    if (isOff >= 0) {

        links.css("opacity", "100%");
        links.css("pointer-events", "all");
        logo.css("margin-left", "20px");
        menu.css("display", "none");

    } else {

        links.css("opacity", "0%");
        logo.css("margin-left", "10px");
        links.css("pointer-events", "none");
        menu.css("display", "initial");

    }

});

// sidebar navigation
$(document).ready(function () {

    // close sidenav
    $(".sidenav-wrapper .header-wrapper .button").click(function () {

        navigation.close_sidenav();

    });

    $(".sidenav-overlay").click(function () {

        navigation.close_sidenav();

    });

    // open sidenav
    $(".navbar-wrapper .group .menu").click(function () {

        navigation.open_sidenav();

    });

});

// box shadow
$(window).scroll(function() {

    var top_dist = $(window).scrollTop();
    var banner_height = $(".banner-wrapper").height();

    if(!$(".banner-wrapper").length) { 

        banner_height = 10;

    }

    if(top_dist > banner_height) {

        $(".subnav-wrapper").css("box-shadow", "0px 0px 5px #000");

    } else {

        $(".subnav-wrapper").css("box-shadow", "0px 0px 0px #000");

    }

});