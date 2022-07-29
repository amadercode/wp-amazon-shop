console.log('Loading Feedback');
jQuery(function(o) {
    var r = {
        ajax: function(a) {
            return jQuery.post(wpas_feedback_object.ajax_url, a)
        },
        deActivator: o("#the-list").find('[data-plugin="wp-amazon-shop/wp-amazon-shop.php"] span.deactivate a')
    };
    o(document).on("ready", function() {
        r.deActivator.attr("id", "wpas-deactivate-self-button")
    }), r.deActivator.on("click", function(a) {
        a.preventDefault();
        var e = o("#wpas-deactivate-feedback-dialog-wrapper");
        0 < e.length && (e.show(), o(".wpas-deactivate-skip").attr("href", o(this).attr("href")))
    }), o("#wpas-deactivate-close-btn").on("click", function(a) {
        o("#wpas-deactivate-feedback-dialog-wrapper").hide()
    }), o(".wpas-deactivate-submit").on("click", function(a) {
        a.preventDefault();
        var e = o(".wpas-deactivate-skip").attr("href"),
            t = o("input[name=reason_key]:checked").val(),
            n = "reason_" + t;
        if (0 < o('input[name="' + n + '"]').length) var i = o('input[name="' + n + '"]').val();
        else i = "";
        var c = {
            action: "wpas_deactivate_feedback",
            reason_key: t,
            reason_val: i
        };
        r.ajax(c).done(function(a) {
            o("#wpas-deactivate-message").html(a.message), "success" == a.status ? setTimeout(function() {
                window.location.href = a.redirect_url + e
            }, 1e3) : alert("Please try again!")
        })
    })
});