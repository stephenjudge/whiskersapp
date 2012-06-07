jQuery(document).ready(function($) {
    /*
        Stacks posting interface
    */
    $('.driver a.remove').click(function() {
        $(this).parent('header').parent('.driver').fadeOut(400, function() { $(this).remove(); });
        return false;
    });

    // reset focus, so our event is triggered.
    // temp fix, should find workaround without blur()
    $('textarea').blur();
    $('#text').live('focus', function() {
        // wait for keypress
        var $text = $(this);
        $text.keyup(function() {

            $('.driver-text').each(function(i,e) {
                var id = $(this).attr('id'),
                    do_update = true;
                // Twitter rules
                if (id === 'twitter_text') {
                    if ($(this).val().length > 140) {
                        $(this).addClass('error').attr('title', 'Message cannot excede 140 characters.');
                        do_update = true;
                    }
                    else {
                        $(this).removeClass('error');
                        do_update = true;
                    }
                    //$('.count').html($(this).val().length + ' chars');
                }

                if (do_update) {
                    $(this).val($text.val()); 
                }
            });
        });
    });

    // post to multiple services
    $('#post').bind('submit', function() {
        var endpoint = $(this).attr('data-endpoint'),
            $drivers = $(this).find('#drivers .driver .driver-text');

        if ($drivers.length === 0) {
            return false;
        }

        $drivers.each(function() {
            var $this = $(this),
                $parent = $(this).parent('.driver'),
                driver = $(this).attr('data-driver'),
                text = $(this).val();

            $.ajax({
                type: 'POST',
                url: endpoint,
                data: { 
                    driver: driver, 
                    text: text 
                },
                complete: function(jqXHR, textStatus) { $this.addClass(textStatus); console.log(jqXHR); },
                dataType: 'json'
            });
        });

        return false;
    });
});