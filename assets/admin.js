jQuery(document).ready(function($) {
    // Button to convert individual tag in the list
    $('.convert-tag-single').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var tagId = button.data('tag-id');
        
        button.prop('disabled', true).text(wc_tags_lowercase.texts.converting);
        
        $.ajax({
            url: wc_tags_lowercase.ajax_url,
            type: 'POST',
            data: {
                action: 'convert_tag_to_lowercase',
                tag_id: tagId,
                nonce: wc_tags_lowercase.nonce
            },
            success: function(response) {
                if (response.success) {
                    button.text(wc_tags_lowercase.texts.success).removeClass('button-primary').addClass('button-success');
                    // Reload page after 1 second
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    button.text(wc_tags_lowercase.texts.error).addClass('button-error');
                }
            },
            error: function() {
                button.text(wc_tags_lowercase.texts.error).addClass('button-error');
            }
        });
    });
});
