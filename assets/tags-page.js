jQuery(document).ready(function($) {
    // Real-time conversion when adding/editing tags
    $('#tag-name').on('blur change', function() {
        var tagName = $(this).val();
        if (tagName && tagName !== tagName.toLowerCase()) {
            $(this).val(tagName.toLowerCase());
        }
    });
    
    // Convert tag name to lowercase before form submission
    $('#addtag, #edittag').on('submit', function(e) {
        var tagName = $('#tag-name').val();
        if (tagName) {
            $('#tag-name').val(tagName.toLowerCase());
        }
    });
    
    // Convert inline edit
    $(document).on('focus', '.inline-edit-row input[name="name"]', function() {
        var originalValue = $(this).val();
        $(this).data('original-value', originalValue);
    });
    
    $(document).on('blur', '.inline-edit-row input[name="name"]', function() {
        var currentValue = $(this).val();
        if (currentValue && currentValue !== currentValue.toLowerCase()) {
            $(this).val(currentValue.toLowerCase());
        }
    });
});
