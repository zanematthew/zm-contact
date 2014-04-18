<?php

Class zMSubmission {

    /**
     * Verify post submission by checking nonce and ajax refer
     * will just die on failure
     * @package security
     * @todo may make check_ajax_refer an option
     * @return -1 ajax failure, 'no'
     * Usage: Helpers::verify( $nonce );
     *
     * Note: You still need to create your nonce's
     * <input type="hidden" name="security" value="<?php print wp_create_nonce( 'ajax-form' );?>">
     * <?php wp_nonce_field( 'new_submission','_new_'.$post_type.'' ); ?>
     */
    public function verify( $post_type=null, $ajax_action=null ){

        if ( is_null( $post_type ) )
            die('need a post_type');

        if ( is_null( $ajax_action ) )
            $ajax_action = 'ajax-form';

        check_ajax_referer( $ajax_action, 'security' );
    }


    /**
     * Print the needed security fields for an Ajax request.
     *
     * All post submissions using zM Easy CPT use Ajax to process form submissions.
     * They also use the following HTML below to verify the Ajax request.
     *
     * @package Ajax
     */
    public function security_fields( $action=null, $post_type=null ){
        $html  = '<input type="hidden" name="security" value="'.wp_create_nonce( 'ajax-form' ).'" />';
        $html .= wp_nonce_field( 'new_submission', $post_type, true, false );
        $html .= '<input type="hidden" name="post_type" value="'.$post_type.'"/>';
        $html .= '<input type="hidden" name="action" value="'.$action.'" />';

        print $html;
    }


    /**
     * Prints the html for ajax status responses.
     *
     * All post submissions using zM Easy CPT use Ajax to process form submissions.
     * These submissions display a message to the user to unsure uniformity amongst
     * our HTMl we have ALL status html come from this function.
     *
     * @package Ajax
     */
    public function status(){
        print '<div class="zm-status-container" style="width: 100%; float: left; margin: 0 0 10px;"><div class="zm-msg-target"></div></div><div class="message-target" style="margin: -10px 0 10px;"></div>';
    }
}