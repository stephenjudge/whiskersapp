jQuery(document).ready(function($) {
    // Handle actions in the posting text box.
    $('#text').focus().keyup(function() {
        $('#count').html($(this).val().length + ' chars');
    }).keydown(function(e) {
        if ((e.ctrlKey || e.metaKey) && e.keyCode == 13) {
            // Should probably use $('form#post').submit(), but not working.
            $('#post-form-submit').click();
        }
    });

    // confirm removals
    $('form.remove').bind('submit', function() {
        confirm('Are you sure you want to remove? This will also attempt to delete the data from any linked services.');
    });
});