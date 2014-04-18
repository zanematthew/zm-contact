<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/zm-easy-cpt/helpers.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/zm-easy-cpt/submission.php';

Class Contact extends zMCustomPostTypeBase {

    private $my_cpt;

    public function __construct(){
        $this->my_cpt = strtolower( __CLASS__ );

        add_action('init', array(&$this, 'init'));
        add_action( 'admin_init', array( &$this, 'adminInit' ) );
        add_action( 'save_post_project', array( &$this, 'save_post_project' ) );
        add_action( 'admin_menu', array( &$this, 'remove_meta_box') );

        add_shortcode( 'zm_contact', array( &$this, 'shortcode' ) );

        add_action( 'wp_ajax_nopriv_postTypeSubmit', array( &$this, 'postTypeSubmit' ) );
        add_action( 'wp_ajax_postTypeSubmit', array( &$this, 'postTypeSubmit' ) );

        add_action( 'before_meta_box_render_fields', array( &$this, 'extra_meta' ) );
    }


    public function init(){
        $this->registerPostType();
        $this->registerTaxonomy();
        $this->enqueueScripts();

        if ( is_admin() ){
            add_action( 'admin_enqueue_scripts', array( &$this, 'dumb' ) );
        }
    }


    public function adminInit(){
        add_action( 'add_meta_boxes', array( &$this, 'metaSection' ) );
        add_action( 'save_post', array( &$this, 'metaSave' ) );
    }


    public function remove_meta_box(){
        remove_meta_box( 'zmcontact_categorydiv', $this->my_cpt, 'side' );
        // Remove all meta boxes leave a placeholder div
        // remove_meta_box( 'submitdiv', $this->my_cpt, 'side' );
    }


    public function dumb(){
        $this->load_assets( $this->my_cpt );
        wp_enqueue_script();
    }


    public function shortcode(){
        ob_start();
        load_template( plugin_dir_path( dirname( __FILE__ ) ) . 'views/form.php' );
        return ob_get_clean();
    }


    /*
     * Contact form submission is handled here, note the form is submitted
     * via ajax.
     *
     * Our Contact form consists of the following:
     * Title, Body (plain text), Categroy, Name and Email
     * @return Prints out a succes message on success a crude die is used
     * for failure.
     */
    public function postTypeSubmit(){

        // Lame, we need this snippet cause this method is PUBLIC!
        // hence its called by everyone!
        if ( $_POST['post_type'] != $this->my_cpt )
            return;

        // Verify nonce
        $submission = New zMSubmission;
        $submission->verify( $_POST['post_type'] );


        // If this user is logged in set them as the author
        if ( get_current_user_id() )
            $author_ID = get_current_user_id();
        else
            $author_ID = null;


        // Build our data fields and sanitize them
        $data = array(
            'post_title'                    => sanitize_text_field( $_POST['post_title'] ),
            'post_content'                  => esc_textarea( $_POST['content'] ),
            'post_author'                   => $author_ID,
            'post_type'                     => sanitize_text_field( $_POST['post_type'] ),
            'post_date'                     => date( 'Y-m-d H:i:s' ),
            'post_status'                   => 'publish',
            'zmcontact_category'            => $_POST['zmcontact_category'],
            $this->my_cpt . '_first-name'   => sanitize_text_field( $_POST[ $this->my_cpt . '_first-name'] ),
            $this->my_cpt . '_last-name'    => sanitize_text_field( $_POST[ $this->my_cpt . '_last-name'] ),
            $this->my_cpt . '_phone-number' => sanitize_text_field( $_POST[ $this->my_cpt . '_phone-number'] ),
            $this->my_cpt . '_email'        => sanitize_text_field( $_POST[ $this->my_cpt . '_email'] ),
            $this->my_cpt . '_website'      => sanitize_text_field( $_POST[ $this->my_cpt . '_website'] )
        );


        // Insert our standard post data
        $post_id = wp_insert_post( array(
            'post_title'   => $data['post_title'],
            'post_content' => $data['post_content'],
            'post_author'  => $data['post_author'],
            'post_type'    => $data['post_type'],
            'post_date'    => $data['post_date'],
            'post_status'  => $data['post_status']
        ), true );


        if ( ! $post_id ) die('ooops');


        // Set contact category
        $this->set_taxonomy( $post_id, $data['zmcontact_category'], 'zmcontact_category' );


        // set contact budget
        // $this->set_budget( $post_id, $_POST[ $this->my_cpt . '_budget' ], 'budget' );


        // Update our meta fields
        update_post_meta( $post_id, '_' . $this->my_cpt . '_first-name',   $data[ $this->my_cpt . '_first-name' ] );
        update_post_meta( $post_id, '_' . $this->my_cpt . '_last-name',    $data[ $this->my_cpt . '_last-name' ] );
        update_post_meta( $post_id, '_' . $this->my_cpt . '_phone-number', $data[ $this->my_cpt . '_phone-number' ] );
        update_post_meta( $post_id, '_' . $this->my_cpt . '_email',        $data[ $this->my_cpt . '_email' ] );
        update_post_meta( $post_id, '_' . $this->my_cpt . '_website',      $data[ $this->my_cpt . '_website' ] );


        // Return our form
        $html  = null;
        $html .= '<div class="success-container freelance-contact-fade-out">';
        $html .= '<div class="message">';
        $html .= 'Thanks for contacting us!';
        $html .= '</div>';
        $html .= '</div>';


        // Check if all above successful
        $this->email( $post_id, $data );

        print $html;
        die();
    }


    public function set_taxonomy( $post_id=null, $term_id=null, $taxonomy=null ){
        if ( empty( $post_id ) ){
            return false;
        } elseif ( empty( $term_id ) ){
            return false;
        } else {
            return wp_set_post_terms( $post_id, array( (int)$term_id ), $taxonomy );
        }
    }


    public function email( $post_id=null, $message=null ){

        if ( mail( 'admin@zanematthew.com', 'mail test', 'none' ) != false){
            echo 'You can send email';
        } else {
            echo 'no';
        }
        // check setting

        $headers[] = 'From: Me Myself <zanematthew@gmail.com>';
        $headers[] = 'Cc: John Q Codex <zanematthew@gmail.com>';

        $email = array(
            'to' => 'zanematthew@gmail.com',
            'subject' => 'email test',
            'message' => "View the entry here {$post_id} detail below:\n" . print_r( $message, true ),
            'headers' => $headers
            );

        $v = wp_mail( $email['to'], $email['subject'], $email['message'], $email['headers'], $email['attachments'] );

        return $v;
    }


    public function extra_meta( $post ){?>
        <textarea><?php echo get_post_field( 'post_content', $post->ID ); ?></textarea>
        <?php $category_obj = wp_get_post_terms( $post->ID, 'zmcontact_category' ); ?>
        <?php if ( ! empty( $category_obj ) ) : ?>
            <strong>Category</strong><?php echo $category_obj[0]->name; ?>
        <?php endif; ?>
    <?php }


    // If need be override metaSectionRender
    // public function metaSectionRender( $post ){}
}