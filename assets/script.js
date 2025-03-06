jQuery(document).ready(function() {
    jQuery('#form-csv-upload').on("submit", function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        jQuery.ajax({
            url: cdu_object.ajax_url,
            data: formData,
            dataType: "json",
            method: "POST",
            processData: false,
            contentType: false,
            success: function(response) {
                jQuery('#show_upload_message').text(response.message).css({
                    color: "green"
                });

                // jQuery('#form-csv-upload')[0].reset();
                jQuery('#form-csv-upload').trigger("reset");
            }
        });
    });
});