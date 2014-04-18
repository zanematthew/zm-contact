jQuery( document ).ready(function( $ ){

    /**
     * Ajax URL MUST be defined for this file to work!
     */
    if ( typeof( ajaxurl ) == "undefined" ) return;

    /**
     * Default ajax setup
     */
    $.ajaxSetup({
        type: "POST",
        url: ajaxurl
    });

    /**
     * Sends a request for server side email validation.
     */
    function zm_validate_email( myObj ){
        $this = myObj;

        if ( $.trim( $this.val() ) == '' ) return;

        data = {
            action: 'zm_validate_email',
            email: $this.val()
        };

        $.ajax({
            data: data,
            dataType: 'json',
            global: false,
            success: function( msg ){
                zm_show_message( msg );
            }
        });
    }

    /**
     * Toggles a message.
     */
    function zm_show_message( msg ) {
        if ( ! msg ) return;
        jQuery('.zm-msg-target').toggleClass( msg.cssClass );
        jQuery('.zm-msg-target').fadeIn().html( msg.description ).delay(2000).fadeOut();
    }

    /**
     * Validation to make sure this field is required
     */
    $('.zm_validate_required').live('blur', function(){
        if ( $.trim( $(this).val() ) != '' ) {
            $( 'input[type="submit"], input[type="button"]' ).animate({ opacity: 1 }).removeAttr('disabled');
        } else {
            $( 'input[type="submit"], input[type="button"]' ).animate({ opacity: 0.5 }).attr('disabled','disabled');
        }
     });

    $( '.zm_validate_email' ).live('blur', function(){
        zm_validate_email( $(this) );
    });

    /**
     * Generic AJAX form handler.
     *
     * ALL post submissions are pushed to this function, from here the
     * desired method "postTypeSubmit" is called, given the post type.
     */
    $( '#zm_form_submit' ).live( 'click', function(){

        /**
         * Note our 'data' is the following...
         * The 'action' is the name of the server side function to be ran.
         * Everything after that is the contents of our serialized form.
         * Since we have attached our event to the submit button, we can
         * trust that our closest form contains ALL our data to be submitted.
         */
        $.ajax({
            data: "action=postTypeSubmit&" + $( this ).closest('form').serialize(),
            success: function( msg ) {

                if ( msg.length ) {

                    $('.zm-msg-target').fadeIn().html( msg ).delay(4000).fadeOut();

                    // We need to reload twitter for addtional
                    // events submitted via ajax.
                    if ( typeof twttr != 'undefined' )
                        twttr.widgets.load();

                    // @chrome, safari bug to prevent select/deselect bug
                    $('.share-link').mouseup(function(e){
                        e.preventDefault();
                    });

                    $('.share-link').on('focus', function(){
                        $(this).select();
                    });
                }
            }
        });
    });
});