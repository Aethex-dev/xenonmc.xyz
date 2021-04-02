function update_url(url, loading_callback, done_callback) 
{

            loading_callback();

            history.pushState({}, null, url);

            var content_wrapper = $("body .content-wrapper .main-wrapper");
            var external_data = $("iframe.external_data");

            external_data.attr("src", "about:blank");

            $.ajax({

                url: url,
                success: function(content) {

                    external_data.contents().find("html").append(content);
                    var page_content_ajax = external_data.contents().find("html > div.content-wrapper > div.main-wrapper").html();

                    content_wrapper.html(page_content_ajax);

                    navigation.close_sidenav();
                    modal.close_modal();

                    done_callback();
        }
    });
}