jQuery(document).ready(function($){

    /**
     * Validation to make sure this field is required
     */
    $('.zm_validate_required').on('blur', function(){
        if ( $.trim( $(this).val() ) != '' ) {
            $( 'input[type="submit"], input[type="button"]' ).animate({ opacity: 1 }).removeAttr('disabled');
        } else {
            $( 'input[type="submit"], input[type="button"]' ).animate({ opacity: 0.5 }).attr('disabled','disabled');
        }
     });


    /**
     * Generic AJAX form handler.
     *
     * ALL post submissions are pushed to this function, from here the
     * desired method "postTypeSubmit" is called, given the post type.
     */
    $( '#contact_form' ).on('submit', function( event ){
        event.preventDefault();

        var $this = $( this );

        $this.css('opacity', 0.5);
        /**
         * Note our 'data' is the following...
         * The 'action' is the name of the server side function to be ran.
         * Everything after that is the contents of our serialized form.
         * Since we have attached our event to the submit button, we can
         * trust that our closest form contains ALL our data to be submitted.
         */
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: "action=postTypeSubmit&" + $this.serialize(),
            success: function( msg ) {
                if ( msg.length ) {
                    $('.zm-msg-target').fadeIn().html( msg ).delay(5000).fadeOut();
                    $('html, body').animate({
                        scrollTop: ( $(".entry-title").offset().top ) - 75
                    },100);
                    $this.css('opacity', 1);
                }
            }
        });
    });
});