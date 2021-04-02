$.fn.extend({
    insertAtCaret: function(myValue) {
      this.each(function() {
        if (document.selection) {
          this.focus();
          var sel = document.selection.createRange();
          sel.text = myValue;
          this.focus();
        } else if (this.selectionStart || this.selectionStart == '0') {
          var startPos = this.selectionStart;
          var endPos = this.selectionEnd;
          var scrollTop = this.scrollTop;
          this.value = this.value.substring(0, startPos) +
            myValue + this.value.substring(endPos,this.value.length);
          this.focus();
          this.selectionStart = startPos + myValue.length;
          this.selectionEnd = startPos + myValue.length;
          this.scrollTop = scrollTop;
        } else {
          this.value += myValue;
          this.focus();
        }
      });
      return this;
    }
  });

class p_snackbar {

    close_snackbar() {

        var snackbar = $(".snackbar-wrapper");
        var snackbar_height = snackbar[0].offsetHeight;

        snackbar.css("bottom", "-" + snackbar_height + "px");

    }

    open_snackbar(text) {

        var snackbar = $(".snackbar-wrapper");
        var snackbar_content = $(".snackbar-wrapper .text");

        snackbar_content.html(text);
        snackbar.css("bottom", "10px");

        setTimeout(() => {

            this.close_snackbar();

        }, 5000);

    }

}

let snackbar = new p_snackbar();

class p_modal {

    close_modal() {

        var modal = $(".modal-wrapper");
        var modal_overlay = $(".modal-overlay");

        modal.animate({ top: '150vh', opacity: '0' }, 500, 'easeOutCubic');
        modal_overlay.fadeOut(300);

        this.caller.classList.remove('focused');

    }

    open_modal(url, caller, layout) {

        this.caller = caller;

        var modal = $(".modal-wrapper");
        var modal_overlay = $(".modal-overlay");
        var modal_content = $(".modal-wrapper .body-wrapper");
        var request_time = new Date().getTime();

        ajaxloader.show_ajaxloader();

        $.ajax({

            url: url,
            data: "layout=" + layout,
            method: "POST",

            success: function(result) {

                modal_content.html(result);

                ajaxloader.hide_ajaxloader();

                modal.show();
                modal.animate({ top: '20vh' }, 0);
                modal.animate({ top: '50vh', opacity: '1' }, 500, 'easeOutCubic');
                modal_overlay.fadeIn(300);

                request_time = new Date().getTime() - request_time;

                console.log('MODAL: content loaded from request [ ' + url + ' ], layout [ ' + layout + ' ].  Took ' + request_time);

                return true;

            },

            error: function() {

                snackbar.open_snackbar("Error, something went wrong. More info may be available in the console");
                console.log('MODAL: Failed to load from request [ ' + url + ' ], layout [ ' + layout + ' ]');
                ajaxloader.hide_ajaxloader();

            }

        });

    }

}

let modal = new p_modal();

class p_emodal {

    close_modal() {

        var modal = $(".emodal-wrapper");
        var modal_overlay = $(".emodal-overlay");

        modal.animate({ top: '150vh', opacity: '0' }, 500, 'easeOutCubic');
        modal_overlay.fadeOut(300);

    }

    open_modal(errors) {

        var modal = $(".emodal-wrapper");
        var modal_overlay = $(".emodal-overlay");
        var modal_content = $(".emodal-wrapper .body-wrapper");
        var errors_li = "";

        errors.forEach(function(error, key) {

            errors_li = errors_li + "<li>" + error + "</li>";

        });

        modal_content.html("<ul>" + errors_li + "</ul>");

        console.log('ERROR MODAL: Errors have been set.');

        modal.show();
        modal.animate({ top: '20vh' }, 0);
        modal.animate({ top: '50vh', opacity: '1' }, 500, 'easeOutCubic');
        modal_overlay.fadeIn(300);

    }

}

let emodal = new p_emodal();

class p_ajaxloader {

    show_ajaxloader() {

        var ajaxloader = $(".ajaxloader-wrapper .bar");

        ajaxloader.addClass("on");
        console.log('AJAXLOADER: load started.');

    }

    hide_ajaxloader() {

        var ajaxloader = $(".ajaxloader-wrapper .bar");

        ajaxloader.removeClass("on");
        console.log('AJAXLOADER: load ended.');

    }

}

let ajaxloader = new p_ajaxloader();

$(document).ready(function() {

    $("button").click(function() {

        if (this.classList.contains('focused')) {

            this.classList.remove('focused');
            modal.close_modal();

        } else {

            if (this.dataset.modal == "true") {

                if (typeof this.dataset.layout == 'undefined' || this.dataset.layout == 'undefined') {

                    var layout = "modal";

                } else {

                    var layout = this.dataset.layout;

                }

                modal.open_modal(this.dataset.href, this, layout);
                this.classList.add('focused');

            }

        }

    });

    $("a").click(function(event) {

        if (this.classList.contains('focused')) {

            this.classList.remove('focused');
            modal.close_modal();

        } else {

            if (this.dataset.modal == "true") {

                event.preventDefault();

                if (typeof this.dataset.layout == 'undefined' || this.dataset.layout == 'undefined') {

                    var layout = "modal";

                } else {

                    var layout = this.dataset.layout;

                }

                modal.open_modal($(this).prop("href"), this, layout);
                this.classList.add('focused');

            } else {

                ajaxloader.show_ajaxloader();

                $(".preloader-wrapper").fadeIn(300);

            }

        }

    });

});

$(document).on('keydown', function(event) {

    if (event.key == "Escape") {

        modal.close_modal();
    }
});

// banner
$(window).scroll(function() {

    if($(window).scrollTop() < $(".banner-wrapper").height()) {

        $(".banner-wrapper img").css("margin-top", $(window).scrollTop());
        $(".banner-wrapper .logo").css({
        
        "margin-top": $(window).scrollTop(),
        "opacity": 100 - $(window).scrollTop() + "%"
    
        });

        $(".banner-wrapper .overlay-wrapper").css("opacity", 70 - $(window).scrollTop() + "%");
        $(".banner-wrapper img").css("width", $(window).scrollTop() / 10 + 100 + "%");
    }
});

function listenCookieChange(callback, interval = 1000) {
    let lastCookie = document.cookie;
    setInterval(() => {
        let cookie = document.cookie;
        if (cookie !== lastCookie) {
            try {
                callback({ oldValue: lastCookie, newValue: cookie });
            } finally {
                lastCookie = cookie;
            }
        }
    }, interval);
}

var stopCookieRefresh = false;

listenCookieChange(({ oldValue, newValue }) => {

    if (stopCookieRefresh == false) {

        location.reload();

    }

}, 1000);

// preloader
$(document).ready(function() {

    $(".preloader-wrapper").fadeOut(300);

    // wysiwyg editor
    $(".row-wrapper .content").on('click', '.textarea button.copy', function () {

        var textarea = $(this).parent().parent();
        var textarea_area = textarea.find('textarea');

        textarea_area.select();
        document.execCommand('copy');

        snackbar.open_snackbar('Text copied to clipboard!');
    });

    $(".row-wrapper .content").on('click', '.textarea button.undo', function () {

        var textarea = $(this).parent().parent();
        var textarea_area = textarea.find('textarea');
    
        var items = [];

        textarea_area.select();
        document.execCommand('undo');
    });

    $(".row-wrapper .content").on('click', '.textarea button.redo', function () {

        var textarea = $(this).parent().parent();
        var textarea_area = textarea.find('textarea');
    
        textarea_area.select();
        document.execCommand('redo');
    });

    $(".row-wrapper .content").on('click', '.textarea button.emojies', function () {

        var textarea = $(this).parent().parent();
        var textarea_area = textarea.find('textarea');
    
        $(textarea_area).insertAtCaret('🙂');
    });

    // wysiwyg tab indenting
    var textarea = $(this).parent().parent();
    var textarea_area = textarea.find('textarea');

    $("body").on('keydown', textarea_area, function(e) {

        var keyCode = e.keyCode || e.which;

        if (keyCode === $.ui.keyCode.TAB) {
            
            e.preventDefault();
            document.execCommand('insertText', false, ' '.repeat(4));
        }
    });

});