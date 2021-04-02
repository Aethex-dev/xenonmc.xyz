$(window).scroll(function() {

    if($(window).scrollTop() < $(".banner-wrapper").height()) {

        $(".banner-wrapper img").css("margin-top", $(window).scrollTop());
        $(".banner-wrapper .logo").css({

            "opacity": 100 - $(window).scrollTop() + "%"
    
        });

        $(".banner-wrapper .overlay-wrapper").css("opacity", 70 - $(window).scrollTop() + "%");
        $(".banner-wrapper img").css("width", $(window).scrollTop() / 30 + 100 + "%");
    }
});