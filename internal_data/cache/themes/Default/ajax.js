function parse_errors(result) {

    emodal.open_modal(result);

}

$(document).on('submit', "form", function(event) {

    if(this.dataset.ajax == "true") {

        ajaxloader.show_ajaxloader();

        var action = $(this).attr("action");
    
        event.preventDefault();

        if (typeof action != 'undefined') {
        
            action = $(this).prop("action");

        } else {
        
            snackbar.open_snackbar("ERROR: Something went wrong, More information may be available in the console.");
            console.error("The action parameter hasn't been defined yet.");
            ajaxloader.hide_ajaxloader();
            modal.close_modal();
            return false;
        
        }

        var data = new FormData(this);

        if (typeof this.dataset.layout != 'undefined') {

            var layout = this.dataset.layout;
            
        } else {

            var layout = 'main';

        }

        var submitter = $(this);
        var id = submitter.attr("data-id");

        if (typeof id == 'undefined') {
            
            snackbar.open_snackbar("ERROR: Something went wrong, More information may be available in the console.");
            console.error("The for id parameter hasn't been defined yet.");
            ajaxloader.hide_ajaxloader();
            modal.close_modal();
            return false;

        }

        submitter.addClass('disabled');

        data.append('id', id);
        data.append('layout', layout);

        $.ajax({
        
            url: action,
            method: "POST",
            contentType: false,
            processData: false,
            dataType: "text",
            data: data,

            success: function (result) {

                function isJson(result) {

                    try {

                        JSON.parse(result);
                        return true;
                        
                    } catch (e) {

                        return false;

                    }

                }

                if (isJson(result)) {

                    result = JSON.parse(result);
                    
                    result.forEach(function (value, key) {
                    
                        console.log("AJAX: " + value);
    
                    });
   
                    parse_errors(result);
    
                } else {

                    $("body").append(result);

                }

                ajaxloader.hide_ajaxloader();
                submitter.removeClass('disabled');
            
            },

            error: function() {
            
                snackbar.open_snackbar("ERROR: Something went wrong, More information may be available in the console.");
                ajaxloader.hide_ajaxloader();
                submitter.removeClass('disabled');
            
            }
        
        });
    
    }

});