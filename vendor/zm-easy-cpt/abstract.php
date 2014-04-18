<?php

/**
 * This file contains the Base Class that is to be extended by your child class
 * to register a Custom Post Type, Custom Taxonomy, and Custom Meta Fields.
 */
if ( class_exists( 'zMCustomPostTypeBase' ) ) return;
abstract class zMCustomPostTypeBase {

    public $post_type;
    public $asset_url;
    private $current_post_type;

    public function __construct() {
        add_filter( 'post_class', array( &$this, 'addPostClass' ) );
        add_action( 'init', array( &$this, 'abstractInit' ) );
        add_action( 'wp_head', array( &$this, 'baseAjaxUrl' ) );
    }


    // Use this to load admin assets
    public function load_assets( $my_cpt=null ){
        $dependencies[] = 'jquery';
        $my_plugins_url = $this->asset_url;

        wp_enqueue_script( "zm-ev-{$my_cpt}-admin-script", $my_plugins_url . $my_cpt . '_admin.js', $dependencies  );
        wp_enqueue_style(  "zm-ev-{$my_cpt}-admin-style", $my_plugins_url . $my_cpt . '_admin.css' );
    }


    /**
     * Regsiter an unlimited number of CPTs based on an array of parmas.
     *
     * @uses register_post_type()
     * @uses wp_die()
     * @todo Currently NOT ALL the args are mapped to this method
     * @todo Support Capabilities
     */
    public function registerPostType( $args=NULL ) {

        foreach ( $this->post_type as $post_type ) {

            if ( is_array( $post_type ) ){
                foreach( $post_type as $k => $v ){
                    $args[ $k ] = $v;
                }
            }

            register_post_type( $post_type['type'], $args);

        } // End 'foreach'

        return $this->post_type;
    } // End 'function'


    /**
     * Wrapper for register_taxonomy() to register an unlimited
     * number of taxonomies for a given CPT.
     *
     * @uses register_taxonomy
     *
     * @todo re-map more stuff, current NOT ALL the args are params
     * @todo this 'hierarchical' => false' fucks up on wp_set_post_terms() for submitting and updating a cpt
     */
    public function registerTaxonomy( $args=NULL ) {

        if ( empty( $this->taxonomy ) )
            return;

        foreach ( $this->taxonomy as $taxonomy ) {
            if ( empty( $taxonomy['labels'] ) ){
                $tmp_label = ucfirst( str_replace('_', ' ', $taxonomy['name'] ) );

                $taxonomy['labels'] = array(
                    'name'              => $tmp_label . 's',
                    'singular_name'     => $tmp_label,
                    'search_items'      => 'Search' . $tmp_label . 's',
                    'all_items'         => 'All' . $tmp_label . 's',
                    'parent_item'       => 'Parent ' . $tmp_label,
                    'parent_item_colon' => 'Parent ' . $tmp_label . ':',
                    'edit_item'         => 'Edit ' . $tmp_labe,
                    'update_item'       => 'Update ' . $tmp_label,
                    'add_new_item'      => 'Add New ' . $tmp_label,
                    'new_item_name'     => 'New ' . $tmp_label . ' Name',
                    'menu_name'         => $tmp_label
                );
            }
            if ( is_array( $taxonomy ) ){
                foreach( $taxonomy as $k => $v ){
                    $args[ $k ] = $v;
                }
            }

            register_taxonomy( $taxonomy['name'], $taxonomy['post_type'], $args );

        } // End 'foreach'


        return $this->taxonomy;
    } // End 'function'


    /**
     * Auto enqueue Admin and front end CSS and JS files. Based ont the post type.
     * @note CSS and JS files MUST be located in the following location:
     * wp-content/{$my-plugin}/assets/{$my_post_type}.css
     * wp-content/{$my-plugin}/assets/{$my_post_type}_admin.css
     * wp-content/{$my-plugin}/assets/{$my_post_type}.js
     * wp-content/{$my-plugin}/assets/{$my_post_type}_admin.js
     */
    public function enqueueScripts(){

        if ( is_admin() ) return;

        $dependencies[] = 'jquery';
        $my_plugins_url = $this->asset_url;

        foreach( $this->post_type as $post ){
            wp_enqueue_script( "zm-ev-{$post['type']}-script", $my_plugins_url . $post['type'] . '.js', $dependencies  );
            wp_enqueue_style(  "zm-ev-{$post['type']}-style", $my_plugins_url . $post['type'] .  '.css' );
        }
    }

    /**
     * Delets a post given the post ID, post will be moved to the trash
     *
     * @package Ajax
     * @param (int) post id
     * @uses is_wp_error
     * @uses is_user_logged_in
     * @uses wp_trash_post
     *
     * @todo generic validateUser method, check ajax refer and if user can (?)
     */
    public function postTypeDelete( $id=null ) {

        // check_ajax_referer( 'bmx-re-ajax-forms', 'security' );

        $id = (int)$_POST['post_id'];

        if ( !is_user_logged_in() )
            return false;

        if ( is_null( $id )  ) {
            wp_die( 'I need a post_id to kill!');
        } else {
            $result = wp_trash_post( $id );
            if ( is_wp_error( $result ) ) {
                print_r( $result );
            } else {
                print_r( $result );
            }
        }

        die();
    } // postTypeDelete


    /**
     * Print our ajax url in the footer
     *
     * @uses plugin_dir_url()
     * @uses admin_url()
     *
     * @todo baseAjaxUrl() consider moving to abstract
     * @todo consider using localize script
     */
    public function baseAjaxUrl() {
        print '<script type="text/javascript"> var ajaxurl = "'. admin_url("admin-ajax.php") .'";</script>';
    } // End 'baseAjaxUrl'


    /**
     * Adds additional classes to post_class() for additional CSS styling and JavaScript manipulation.
     * term_slug-taxonomy_id
     *
     * @param classes
     *
     * @uses get_post_types()
     * @uses get_the_terms()
     * @uses is_wp_error()
     */
    public function addPostClass( $classes ) {
        global $post;
        $cpt = $post->post_type;

        $cpt_obj = get_post_types( array( 'name' => $cpt ), 'objects' );

        foreach( $cpt_obj[ $cpt ]->taxonomies  as $name ) {
            $terms = get_the_terms( $post->ID, $name );
            if ( !is_wp_error( $terms ) && !empty( $terms )) {
                foreach( $terms as $term ) {
                    $classes[] = $name . '-' . $term->term_id;
                }
            }
        }
        return $classes;
    } // End 'addPostClass'


    /**
     * Attempts to locate the called template from the child or parent theme.
     * If not it loads the one in the plugin.
     *
     * @param $template The file name, "settings.php"
     * @param $views_dir The path to the template/view as seen in the plugin, "views/"
     */
    public function loadTemplate( $template=null, $views_dir=null ){
        $template = ($overridden_template = locate_template( $template )) ? $overridden_template : $views_dir . $template;
        load_template( $template );
    }


    // This would be better as its own class
    // @todo option to choose which columns to load as a param
    public function load_columns( $my_cpt=null ){

        if ( isset( $_GET['post_type'] ) ){
            $this->current_post_type = $my_cpt;
        }

        global $pagenow;

        if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) ){
            add_filter( 'manage_edit-' . $this->current_post_type . '_columns', array( &$this, 'custom_columns' ) );
            add_action( 'manage_'.$this->current_post_type.'_posts_custom_column', array( &$this, 'render_custom_columns' ), 10, 2 );
            add_filter( 'manage_edit-' . $this->current_post_type . '_sortable_columns', array( &$this, 'sortable_custom_columns' ) );
        }
    }


    public function columns(){
        // get taxonomies for this post type
        $tax_objs = get_object_taxonomies( $this->current_post_type, 'objects' );

        // defaults
        $columns = array(
            'cb' => '<input type="checkbox"/>',
            'title' => 'Title'
            );

        // Build our columns array and remove _ from the taxonomy name and use it as the column label
        foreach( $tax_objs as $tax_obj ){
            $columns[ $tax_obj->name ] = $tax_obj->labels->name;
        }

        $columns['date'] = 'Date';

        return $columns;
    }


    public function custom_columns(){
        return $this->columns();
    }


    public function render_custom_columns( $column_name, $post_id ){
        if ( ! in_array( $column_name, array('cb','title','date') ) ){
            // would be nice to filter this
            // echo get_the_term_list( $post_id, $column_name, '',', ' );
            $tags = get_the_terms( $post_id, $column_name );
            if ( $tags ){
                $count = count( $tags );
                $i = 0;
                foreach( $tags as $tag ){
                    echo '<a href="'.admin_url('edit.php?'.$column_name.'=' . $tag->slug . '&post_type=' . $this->current_post_type).'">' . $tag->name . '</a>';
                    echo ( $count - 1) == $i ? null : ", ";
                    $i++;
                }
            } else {
                echo 'no tag';
            }
        }
    }


    /**
     * This method is a filter for the "manage_edit-submission_sortable_columns"
     * and dynamically builds a list of the columns that will be sortable.
     *
     * @param $columns
     * @return $columns
     */
    public function sortable_custom_columns( $columns ) {
        $columns = array();
        foreach( $this->columns() as $k => $v ){
            $columns[ $k ] = $k;
        }
        // We don't want to add sorting to our checkbox,
        // so we remove it
        unset( $columns['cb'] );

        return $columns;
    }
    //


    /**
     * Handles setting up the query to display our content for the table
     */
    public function sort_downloads( $vars ) {

        if ( isset( $vars['post_type'] ) && 'submission' == $vars['post_type'] ) {


            /**
             * If this is a regular search request we just return the $vars!
             */
            if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
                return $vars;
            }

            if ( isset( $_GET['orderby'] ) && ! empty( $_GET['orderby'] ) ){
                foreach( $this->columns() as $k => $v ){
                    if ( isset( $vars['orderby'] ) && $k == $vars['orderby']
                        && $vars['orderby'] != 'date'
                        && $vars['orderby'] != 'tag' ){
                        $vars = array_merge(
                            $vars,
                            array(
                                'meta_key' => '_' . $k,
                                'orderby' => 'meta_value'
                            )
                        );
                    }
                }

                if ( isset( $vars['orderby'] ) && $vars['orderby'] == 'tag' ){

                    /**
                     * All this to sort by tag?
                     *
                     * First we get ALL tags sorted by our 'order' param. Then we
                     * get ALL form submissions that are in our list of tag ids.
                     * From here we pass this list into our query vars as the
                     * post__in parameter, finally a sorted table of tags.
                     */
                    $tags_obj = get_terms('tag', array('orderby' => 'name', 'order'=> strtoupper($_GET['order'])) );
                    foreach( $tags_obj as $tag_obj ){
                        // echo $tag_obj->name . '<br />';
                        $term_ids[] = $tag_obj->term_id;
                    }

                    $args = array(
                        'post_type' => 'submission',
                        'post_status' => 'publish',
                        'posts_per_page' => -1,
                        'orderby' => 'tax_query',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'tag',
                                'field' => 'id',
                                'terms' => $term_ids,
                                'operator' => 'IN'
                            )
                        )
                    );

                    $tagged_submissions = New WP_Query( $args );
                    foreach( $tagged_submissions->posts as $tagged_submission ){
                        $submission_ids[] = $tagged_submission->ID;
                    }
                    wp_reset_postdata();

                    $vars = array_merge(
                        $vars,
                        array(
                            'post_status' => 'publish',
                            'post__in' => $submission_ids
                        )
                    );
                    // We remove tag from our query, since it will break our query
                    unset( $vars['tag'] );
                }
            }


            /**
             * Build query campaign AND tag (from select)
             */
            if ( isset( $_GET['select_tag'] ) && ! empty( $_GET['select_tag'] )
                && isset( $_GET['campaign_form_slug'] ) && ! empty( $_GET['campaign_form_slug'] ) ){
                $vars = array_merge(
                    $vars,
                        array(
                            'meta_query' => array(
                                array(
                                    'key' => '_campaign_form_slug',
                                    'value' => $_GET['campaign_form_slug']
                                )
                            ),
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'tag',
                                    'field'    => 'slug',
                                    'terms'    => $_GET['select_tag']
                                )
                            )
                        )
                );
            }

            /**
             * Build query for campaign form
             */
            elseif ( ! empty( $_GET['campaign_form_slug'] ) ){
                $vars = array_merge(
                    $vars,
                    array(
                        'meta_query' => array(
                            array(
                                'key' => '_campaign_form_slug',
                                'value' => $_GET['campaign_form_slug']
                            )
                        )
                    )
                );
                unset( $vars['tag'] );
            }

            elseif ( isset( $_GET['s'] ) && empty( $_GET['tag'] ) ){

                /**
                 * We have no tags, just return
                 */
                if ( isset( $_GET['select_tag'] ) && empty( $_GET['select_tag'] ) ){
                    return $vars;
                }

                /**
                 * Handle sorting, again?
                 */
                if ( isset( $_GET['orderby'] ) && ! empty( $_GET['orderby'] ) ){
                    foreach( $this->columns() as $k => $v ){
                        if ( isset( $vars['orderby'] ) && $k == $vars['orderby']
                            && $vars['orderby'] != 'date'
                            && $vars['orderby'] != 'tag' ){
                            $vars = array_merge(
                                $vars,
                                array(
                                    'meta_key' => '_' . $k,
                                    'orderby' => 'meta_value'
                                )
                            );
                        }
                    }

                    if ( isset( $vars['orderby'] ) && $vars['orderby'] == 'tag' ){

                        /**
                         * All this to sort by tag?
                         *
                         * First we get ALL tags sorted by our 'order' param. Then we
                         * get ALL form submissions that are in our list of tag ids.
                         * From here we pass this list into our query vars as the
                         * post__in parameter, finally a sorted table of tags.
                         */
                        $tags_obj = get_terms('tag', array('orderby'=> strtoupper($_GET['order'])) );
                        foreach( $tags_obj as $tag_obj ){
                            $term_ids[] = $tag_obj->term_id;
                        }


                        $args = array(
                            'post_type' => 'submission',
                            'post_status' => 'publish',
                            'posts_per_page' => -1,
                            'orderby' => 'tag',
                            'order' => 'ASC',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'tag',
                                    'field' => 'id',
                                    'terms' => $term_ids,
                                    'operator' => 'IN'
                                )
                            )
                        );
                        $tagged_submissions = New WP_Query( $args );
                        foreach( $tagged_submissions->posts as $tagged_submission ){
                            $submission_ids[] = $tagged_submission->ID;
                        }
                        wp_reset_postdata();

                        $vars = array_merge(
                            $vars,
                            array(
                                'post__in' => $submission_ids
                            )
                        );
                        // We remove tag from our query, since it will break our query
                        unset( $vars['tag'] );
                    }
                    return $vars;
                }

                if ( empty( $vars['s'] ) ){
                    $tag = $_GET['select_tag'];
                } else {
                    $tag = $vars['s'];
                }

                if ( empty( $tag ) )
                    return $vars;

                unset( $vars['s'] );
                unset( $vars['tag'] );
                $vars = array_merge(
                    $vars,
                    array(
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'tag',
                                'field'    => 'slug',
                                'terms'    => $tag
                            )
                        )
                    )
                );
            }

            // handle tags
            // @package tags
            elseif (
                isset( $_GET['tag'] )
                && empty( $_GET['s'] )
                || isset( $_GET['select_tag'] )
                ){
                echo 'selected tag';

                $vars = array_merge(
                    $vars,
                    array(
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'tag',
                                'field'    => 'slug',
                                'terms'    => $vars['tag']
                            )
                        )
                    )
                );
                // We remove tag from our query, since it will break our query
                unset( $vars['tag'] );
            }

            // Handle sorting of meta keys (table columns)
            else {
                // echo 'default';
            }
        }

        return $vars;
    }


    /**
     * Basically this is a wrapper for 'add_meta_box'. Allowing
     * us to register an unlimited number of meta sections in an
     * array format.
     *
     * @uses add_meta_box();
     *
     * @note A meta section IS bound by the CLASS NAME!
     * i.e., class name == post_type! PERIOD!
     */
    public function metaSection( $post_type=null ){
        global $post_type;

        // Enusre this ONLY gets ran on $post_type pages
        if ( $post_type != strtolower( get_called_class() ) )
            return;

        if ( ! empty( $this->meta_sections ) ){

            $context_default = array( 'normal', 'advanced', 'side' );

            foreach( $this->meta_sections as $section_id => $section ){
                if ( ! empty( $section['context'] ) && in_array( $section['context'], $context_default ) ){
                    $context = $section['context'];
                } else {
                    $context = 'normal';
                }
                add_meta_box( $section_id, $section['label'], array( &$this, 'metaSectionRender' ), $post_type, $context, $priority='default', $section );
            }
        }
    }


    /**
     * Renders the HTML for each 'meta section'
     *
     * @note The unique form field name, i.e. "key", is derived by the following
     * {$post_type}_{$label=converted to lower case, and replace spaces with a dash}
     * i.e., $label="First Name", $post_type="events", $key=events_first-name
     * @todo add checkbox, radio, and select type support.
     * @note you can override the field name
     */
    public function metaSectionRender( $post, $callback_args ){

        if ( ! empty( $callback_args['args']['description'] ) )
            echo '<p class="description">'.$callback_args['args']['description'].'</p>';

        do_action('before_meta_box_render_fields', $post);
        foreach( $callback_args['args']['fields'] as $field ){

            if ( empty( $field['name'] ) ){
                $name = '_' . $post->post_type . '_' . str_replace(' ', '-', strtolower( $field['label'] ) );
            } else {
                $name = $field['name'];
            }

            // Set our label
            if ( empty( $field['label'] ) ){
                $label = '';
            } else {
                $label = $field['label'];
            }

            // Set our values
            if ( ! empty( $field['value'] ) ){
                $tmp_value = $field['value'];
            } else if ( get_post_meta( $post->ID, "{$name}", true) ){
                $tmp_value = get_post_meta( $post->ID, "{$name}", true );
            } else {
                $tmp_value = null;
            }

            // Build classes, etc.
            $class = empty( $field['class'] ) ? null : $field['class'];
            $placeholder = empty( $field['placeholder'] ) ? null : $field['placeholder'];
            $id = sanitize_title( $name );

            // Render HTML
            switch( $field['type'] ){
                case 'datetime-local':
                case 'date':
                case 'text': // type="text"
                    print "<p id='{$id}'><label>{$label}</label><input type='{$field['type']}' class='{$class}' name='{$name}' value='{$tmp_value}' placeholder='{$placeholder}'/></p>";
                    break;
                case 'textarea':
                    print "<p id='{$id}'><label>{$label}</label><br /><textarea class='{$class}' name='{$name}'>{$tmp_value}</textarea></p>";
                    break;
                case 'description': // If using a function make sure it returns!
                case 'html': // Just add your stuff in the "value => 'anything you want"
                    print "$tmp_value";
                    break;
                default:
                    print 'This is the default type';
                    break;
            }
            do_action('after_meta_box_render_fields');
        }
    }


    /**
     * Build our unique keys for each meta section
     */
    public function buildMetaKeys(){
        global $post;

        foreach( $this->meta_sections as $section_id => $section ){
            foreach( $section['fields'] as $field ){
                if ( empty( $field['name'] ) )
                    $this->meta_keys[] = $post->post_type . '_' . str_replace(' ', '-', strtolower( $field['label'] ) );
                else
                    $this->meta_keys[] = $field['name'];
            }
        }
        return $this->meta_keys;
    }


    /**
     * Saves post meta information based on $_POST['post_id']
     * @todo Add support for $post_id
     * @todo Use a nonce?
     */
    public function metaSave( $post_id=null ){

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        if ( empty( $_POST['post_ID'] ) )
            return;

        /**
         * This is done to derive our meta keys, since wp doesn't scale well from a code
         * point of view. There's no direct access to post meta keys that don't already exists in
         * the db. So post meta keys are derived like {$post_type}_{name}.
         */
        $post_type = $_POST['post_type'];
        $new_meta = array();

        foreach( $_POST as $field => $value ){
            if ( ! is_array( $field ) ){
                $tmp = explode( '_', $field );
                if ( $tmp[1] == $post_type ){
                    $new_meta[$field] = $value;
                }
            }
        }

        $current_meta = get_post_custom( $_POST['post_ID'] );

        foreach( $new_meta as $key => $value ){
            if ( ! empty( $value ) ) {
                if ( is_array( $value ) ){
                    $value = $value[0]."\n";
                }
                update_post_meta( $_POST['post_ID'], $key, $value );
            }
        }
    }

} // End 'CustomPostTypeBase'<?php

/**
 * This file contains the Base Class that is to be extended by your child class
 * to register a Custom Post Type, Custom Taxonomy, and Custom Meta Fields.
 */
if ( class_exists( 'zMCustomPostTypeBase' ) ) return;
abstract class zMCustomPostTypeBase {

    public $post_type;
    public $asset_url;
    private $current_post_type;

    public function __construct() {
        add_filter( 'post_class', array( &$this, 'addPostClass' ) );
        add_action( 'init', array( &$this, 'abstractInit' ) );
        add_action( 'wp_head', array( &$this, 'baseAjaxUrl' ) );
    }


    // Use this to load admin assets
    public function load_assets( $my_cpt=null ){
        $dependencies[] = 'jquery';
        $my_plugins_url = $this->asset_url;

        wp_enqueue_script( "zm-ev-{$my_cpt}-admin-script", $my_plugins_url . $my_cpt . '_admin.js', $dependencies  );
        wp_enqueue_style(  "zm-ev-{$my_cpt}-admin-style", $my_plugins_url . $my_cpt . '_admin.css' );
    }


    /**
     * Regsiter an unlimited number of CPTs based on an array of parmas.
     *
     * @uses register_post_type()
     * @uses wp_die()
     * @todo Currently NOT ALL the args are mapped to this method
     * @todo Support Capabilities
     */
    public function registerPostType( $args=NULL ) {

        foreach ( $this->post_type as $post_type ) {

            if ( is_array( $post_type ) ){
                foreach( $post_type as $k => $v ){
                    $args[ $k ] = $v;
                }
            }

            register_post_type( $post_type['type'], $args);

        } // End 'foreach'

        return $this->post_type;
    } // End 'function'


    /**
     * Wrapper for register_taxonomy() to register an unlimited
     * number of taxonomies for a given CPT.
     *
     * @uses register_taxonomy
     *
     * @todo re-map more stuff, current NOT ALL the args are params
     * @todo this 'hierarchical' => false' fucks up on wp_set_post_terms() for submitting and updating a cpt
     */
    public function registerTaxonomy( $args=NULL ) {

        if ( empty( $this->taxonomy ) )
            return;

        foreach ( $this->taxonomy as $taxonomy ) {
            if ( empty( $taxonomy['labels'] ) ){
                $tmp_label = ucfirst( str_replace('_', ' ', $taxonomy['name'] ) );

                $taxonomy['labels'] = array(
                    'name'              => $tmp_label . 's',
                    'singular_name'     => $tmp_label,
                    'search_items'      => 'Search' . $tmp_label . 's',
                    'all_items'         => 'All' . $tmp_label . 's',
                    'parent_item'       => 'Parent ' . $tmp_label,
                    'parent_item_colon' => 'Parent ' . $tmp_label . ':',
                    'edit_item'         => 'Edit ' . $tmp_labe,
                    'update_item'       => 'Update ' . $tmp_label,
                    'add_new_item'      => 'Add New ' . $tmp_label,
                    'new_item_name'     => 'New ' . $tmp_label . ' Name',
                    'menu_name'         => $tmp_label
                );
            }
            if ( is_array( $taxonomy ) ){
                foreach( $taxonomy as $k => $v ){
                    $args[ $k ] = $v;
                }
            }

            register_taxonomy( $taxonomy['name'], $taxonomy['post_type'], $args );

        } // End 'foreach'


        return $this->taxonomy;
    } // End 'function'


    /**
     * Auto enqueue Admin and front end CSS and JS files. Based ont the post type.
     * @note CSS and JS files MUST be located in the following location:
     * wp-content/{$my-plugin}/assets/{$my_post_type}.css
     * wp-content/{$my-plugin}/assets/{$my_post_type}_admin.css
     * wp-content/{$my-plugin}/assets/{$my_post_type}.js
     * wp-content/{$my-plugin}/assets/{$my_post_type}_admin.js
     */
    public function enqueueScripts(){

        if ( is_admin() ) return;

        $dependencies[] = 'jquery';
        $my_plugins_url = $this->asset_url;

        foreach( $this->post_type as $post ){
            wp_enqueue_script( "zm-ev-{$post['type']}-script", $my_plugins_url . $post['type'] . '.js', $dependencies  );
            wp_enqueue_style(  "zm-ev-{$post['type']}-style", $my_plugins_url . $post['type'] .  '.css' );
        }
    }

    /**
     * Delets a post given the post ID, post will be moved to the trash
     *
     * @package Ajax
     * @param (int) post id
     * @uses is_wp_error
     * @uses is_user_logged_in
     * @uses wp_trash_post
     *
     * @todo generic validateUser method, check ajax refer and if user can (?)
     */
    public function postTypeDelete( $id=null ) {

        // check_ajax_referer( 'bmx-re-ajax-forms', 'security' );

        $id = (int)$_POST['post_id'];

        if ( !is_user_logged_in() )
            return false;

        if ( is_null( $id )  ) {
            wp_die( 'I need a post_id to kill!');
        } else {
            $result = wp_trash_post( $id );
            if ( is_wp_error( $result ) ) {
                print_r( $result );
            } else {
                print_r( $result );
            }
        }

        die();
    } // postTypeDelete


    /**
     * Print our ajax url in the footer
     *
     * @uses plugin_dir_url()
     * @uses admin_url()
     *
     * @todo baseAjaxUrl() consider moving to abstract
     * @todo consider using localize script
     */
    public function baseAjaxUrl() {
        print '<script type="text/javascript"> var ajaxurl = "'. admin_url("admin-ajax.php") .'";</script>';
    } // End 'baseAjaxUrl'


    /**
     * Adds additional classes to post_class() for additional CSS styling and JavaScript manipulation.
     * term_slug-taxonomy_id
     *
     * @param classes
     *
     * @uses get_post_types()
     * @uses get_the_terms()
     * @uses is_wp_error()
     */
    public function addPostClass( $classes ) {
        global $post;
        $cpt = $post->post_type;

        $cpt_obj = get_post_types( array( 'name' => $cpt ), 'objects' );

        foreach( $cpt_obj[ $cpt ]->taxonomies  as $name ) {
            $terms = get_the_terms( $post->ID, $name );
            if ( !is_wp_error( $terms ) && !empty( $terms )) {
                foreach( $terms as $term ) {
                    $classes[] = $name . '-' . $term->term_id;
                }
            }
        }
        return $classes;
    } // End 'addPostClass'


    /**
     * Attempts to locate the called template from the child or parent theme.
     * If not it loads the one in the plugin.
     *
     * @param $template The file name, "settings.php"
     * @param $views_dir The path to the template/view as seen in the plugin, "views/"
     */
    public function loadTemplate( $template=null, $views_dir=null ){
        $template = ($overridden_template = locate_template( $template )) ? $overridden_template : $views_dir . $template;
        load_template( $template );
    }


    // This would be better as its own class
    // @todo option to choose which columns to load as a param
    public function load_columns( $my_cpt=null ){

        if ( isset( $_GET['post_type'] ) ){
            $this->current_post_type = $my_cpt;
        }

        global $pagenow;

        if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) ){
            add_filter( 'manage_edit-' . $this->current_post_type . '_columns', array( &$this, 'custom_columns' ) );
            add_action( 'manage_'.$this->current_post_type.'_posts_custom_column', array( &$this, 'render_custom_columns' ), 10, 2 );
            add_filter( 'manage_edit-' . $this->current_post_type . '_sortable_columns', array( &$this, 'sortable_custom_columns' ) );
        }
    }


    public function columns(){
        // get taxonomies for this post type
        $tax_objs = get_object_taxonomies( $this->current_post_type, 'objects' );

        // defaults
        $columns = array(
            'cb' => '<input type="checkbox"/>',
            'title' => 'Title'
            );

        // Build our columns array and remove _ from the taxonomy name and use it as the column label
        foreach( $tax_objs as $tax_obj ){
            $columns[ $tax_obj->name ] = $tax_obj->labels->name;
        }

        $columns['date'] = 'Date';

        return $columns;
    }


    public function custom_columns(){
        return $this->columns();
    }


    public function render_custom_columns( $column_name, $post_id ){
        if ( ! in_array( $column_name, array('cb','title','date') ) ){
            // would be nice to filter this
            // echo get_the_term_list( $post_id, $column_name, '',', ' );
            $tags = get_the_terms( $post_id, $column_name );
            if ( $tags ){
                $count = count( $tags );
                $i = 0;
                foreach( $tags as $tag ){
                    echo '<a href="'.admin_url('edit.php?'.$column_name.'=' . $tag->slug . '&post_type=' . $this->current_post_type).'">' . $tag->name . '</a>';
                    echo ( $count - 1) == $i ? null : ", ";
                    $i++;
                }
            } else {
                echo 'no tag';
            }
        }
    }


    /**
     * This method is a filter for the "manage_edit-submission_sortable_columns"
     * and dynamically builds a list of the columns that will be sortable.
     *
     * @param $columns
     * @return $columns
     */
    public function sortable_custom_columns( $columns ) {
        $columns = array();
        foreach( $this->columns() as $k => $v ){
            $columns[ $k ] = $k;
        }
        // We don't want to add sorting to our checkbox,
        // so we remove it
        unset( $columns['cb'] );

        return $columns;
    }
    //


    /**
     * Handles setting up the query to display our content for the table
     */
    public function sort_downloads( $vars ) {

        if ( isset( $vars['post_type'] ) && 'submission' == $vars['post_type'] ) {


            /**
             * If this is a regular search request we just return the $vars!
             */
            if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
                return $vars;
            }

            if ( isset( $_GET['orderby'] ) && ! empty( $_GET['orderby'] ) ){
                foreach( $this->columns() as $k => $v ){
                    if ( isset( $vars['orderby'] ) && $k == $vars['orderby']
                        && $vars['orderby'] != 'date'
                        && $vars['orderby'] != 'tag' ){
                        $vars = array_merge(
                            $vars,
                            array(
                                'meta_key' => '_' . $k,
                                'orderby' => 'meta_value'
                            )
                        );
                    }
                }

                if ( isset( $vars['orderby'] ) && $vars['orderby'] == 'tag' ){

                    /**
                     * All this to sort by tag?
                     *
                     * First we get ALL tags sorted by our 'order' param. Then we
                     * get ALL form submissions that are in our list of tag ids.
                     * From here we pass this list into our query vars as the
                     * post__in parameter, finally a sorted table of tags.
                     */
                    $tags_obj = get_terms('tag', array('orderby' => 'name', 'order'=> strtoupper($_GET['order'])) );
                    foreach( $tags_obj as $tag_obj ){
                        // echo $tag_obj->name . '<br />';
                        $term_ids[] = $tag_obj->term_id;
                    }

                    $args = array(
                        'post_type' => 'submission',
                        'post_status' => 'publish',
                        'posts_per_page' => -1,
                        'orderby' => 'tax_query',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'tag',
                                'field' => 'id',
                                'terms' => $term_ids,
                                'operator' => 'IN'
                            )
                        )
                    );

                    $tagged_submissions = New WP_Query( $args );
                    foreach( $tagged_submissions->posts as $tagged_submission ){
                        $submission_ids[] = $tagged_submission->ID;
                    }
                    wp_reset_postdata();

                    $vars = array_merge(
                        $vars,
                        array(
                            'post_status' => 'publish',
                            'post__in' => $submission_ids
                        )
                    );
                    // We remove tag from our query, since it will break our query
                    unset( $vars['tag'] );
                }
            }


            /**
             * Build query campaign AND tag (from select)
             */
            if ( isset( $_GET['select_tag'] ) && ! empty( $_GET['select_tag'] )
                && isset( $_GET['campaign_form_slug'] ) && ! empty( $_GET['campaign_form_slug'] ) ){
                $vars = array_merge(
                    $vars,
                        array(
                            'meta_query' => array(
                                array(
                                    'key' => '_campaign_form_slug',
                                    'value' => $_GET['campaign_form_slug']
                                )
                            ),
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'tag',
                                    'field'    => 'slug',
                                    'terms'    => $_GET['select_tag']
                                )
                            )
                        )
                );
            }

            /**
             * Build query for campaign form
             */
            elseif ( ! empty( $_GET['campaign_form_slug'] ) ){
                $vars = array_merge(
                    $vars,
                    array(
                        'meta_query' => array(
                            array(
                                'key' => '_campaign_form_slug',
                                'value' => $_GET['campaign_form_slug']
                            )
                        )
                    )
                );
                unset( $vars['tag'] );
            }

            elseif ( isset( $_GET['s'] ) && empty( $_GET['tag'] ) ){

                /**
                 * We have no tags, just return
                 */
                if ( isset( $_GET['select_tag'] ) && empty( $_GET['select_tag'] ) ){
                    return $vars;
                }

                /**
                 * Handle sorting, again?
                 */
                if ( isset( $_GET['orderby'] ) && ! empty( $_GET['orderby'] ) ){
                    foreach( $this->columns() as $k => $v ){
                        if ( isset( $vars['orderby'] ) && $k == $vars['orderby']
                            && $vars['orderby'] != 'date'
                            && $vars['orderby'] != 'tag' ){
                            $vars = array_merge(
                                $vars,
                                array(
                                    'meta_key' => '_' . $k,
                                    'orderby' => 'meta_value'
                                )
                            );
                        }
                    }

                    if ( isset( $vars['orderby'] ) && $vars['orderby'] == 'tag' ){

                        /**
                         * All this to sort by tag?
                         *
                         * First we get ALL tags sorted by our 'order' param. Then we
                         * get ALL form submissions that are in our list of tag ids.
                         * From here we pass this list into our query vars as the
                         * post__in parameter, finally a sorted table of tags.
                         */
                        $tags_obj = get_terms('tag', array('orderby'=> strtoupper($_GET['order'])) );
                        foreach( $tags_obj as $tag_obj ){
                            $term_ids[] = $tag_obj->term_id;
                        }


                        $args = array(
                            'post_type' => 'submission',
                            'post_status' => 'publish',
                            'posts_per_page' => -1,
                            'orderby' => 'tag',
                            'order' => 'ASC',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'tag',
                                    'field' => 'id',
                                    'terms' => $term_ids,
                                    'operator' => 'IN'
                                )
                            )
                        );
                        $tagged_submissions = New WP_Query( $args );
                        foreach( $tagged_submissions->posts as $tagged_submission ){
                            $submission_ids[] = $tagged_submission->ID;
                        }
                        wp_reset_postdata();

                        $vars = array_merge(
                            $vars,
                            array(
                                'post__in' => $submission_ids
                            )
                        );
                        // We remove tag from our query, since it will break our query
                        unset( $vars['tag'] );
                    }
                    return $vars;
                }

                if ( empty( $vars['s'] ) ){
                    $tag = $_GET['select_tag'];
                } else {
                    $tag = $vars['s'];
                }

                if ( empty( $tag ) )
                    return $vars;

                unset( $vars['s'] );
                unset( $vars['tag'] );
                $vars = array_merge(
                    $vars,
                    array(
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'tag',
                                'field'    => 'slug',
                                'terms'    => $tag
                            )
                        )
                    )
                );
            }

            // handle tags
            // @package tags
            elseif (
                isset( $_GET['tag'] )
                && empty( $_GET['s'] )
                || isset( $_GET['select_tag'] )
                ){
                echo 'selected tag';

                $vars = array_merge(
                    $vars,
                    array(
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'tag',
                                'field'    => 'slug',
                                'terms'    => $vars['tag']
                            )
                        )
                    )
                );
                // We remove tag from our query, since it will break our query
                unset( $vars['tag'] );
            }

            // Handle sorting of meta keys (table columns)
            else {
                // echo 'default';
            }
        }

        return $vars;
    }

        /**
     * Basically this is a wrapper for 'add_meta_box'. Allowing
     * us to register an unlimited number of meta sections in an
     * array format.
     *
     * @uses add_meta_box();
     *
     * @note A meta section IS bound by the CLASS NAME!
     * i.e., class name == post_type! PERIOD!
     */
    public function metaSection( $post_type=null ){
        global $post_type;

        // Enusre this ONLY gets ran on $post_type pages
        if ( $post_type != strtolower( get_called_class() ) )
            return;

        if ( ! empty( $this->meta_sections ) ){

            $context_default = array( 'normal', 'advanced', 'side' );

            foreach( $this->meta_sections as $section_id => $section ){
                if ( ! empty( $section['context'] ) && in_array( $section['context'], $context_default ) ){
                    $context = $section['context'];
                } else {
                    $context = 'normal';
                }
                add_meta_box( $section_id, $section['label'], array( &$this, 'metaSectionRender' ), $post_type, $context, $priority='default', $section );
            }
        }
    }


    /**
     * Renders the HTML for each 'meta section'
     *
     * @note The unique form field name, i.e. "key", is derived by the following
     * {$post_type}_{$label=converted to lower case, and replace spaces with a dash}
     * i.e., $label="First Name", $post_type="events", $key=events_first-name
     * @todo add checkbox, radio, and select type support.
     * @note you can override the field name
     */
    public function metaSectionRender( $post, $callback_args ){

        if ( ! empty( $callback_args['args']['description'] ) )
            echo '<p class="description">'.$callback_args['args']['description'].'</p>';

        foreach( $callback_args['args']['fields'] as $field ){

            if ( empty( $field['name'] ) ){
                $name = '_' . $post->post_type . '_' . str_replace(' ', '-', strtolower( $field['label'] ) );
            } else {
                $name = $field['name'];
            }

            // Set our label
            if ( empty( $field['label'] ) ){
                $label = '';
            } else {
                $label = $field['label'];
            }

            // Set our values
            if ( ! empty( $field['value'] ) ){
                $tmp_value = $field['value'];
            } else if ( get_post_meta( $post->ID, "{$name}", true) ){
                $tmp_value = get_post_meta( $post->ID, "{$name}", true );
            } else {
                $tmp_value = null;
            }

            // Build classes, etc.
            $class = empty( $field['class'] ) ? null : $field['class'];
            $placeholder = empty( $field['placeholder'] ) ? null : $field['placeholder'];
            $id = sanitize_title( $name );

            // Render HTML
            switch( $field['type'] ){
                case 'datetime-local':
                case 'date':
                case 'text': // type="text"
                    print "<p id='{$id}'><label>{$label}</label><input type='{$field['type']}' class='{$class}' name='{$name}' value='{$tmp_value}' placeholder='{$placeholder}'/></p>";
                    break;
                case 'textarea':
                    print "<p id='{$id}'><label>{$label}</label><br /><textarea class='{$class}' name='{$name}'>{$tmp_value}</textarea></p>";
                    break;
                case 'description': // If using a function make sure it returns!
                case 'html': // Just add your stuff in the "value => 'anything you want"
                    print "$tmp_value";
                    break;
                default:
                    print 'This is the default type';
                    break;
            }
        }
    }


    /**
     * Build our unique keys for each meta section
     */
    public function buildMetaKeys(){
        global $post;

        foreach( $this->meta_sections as $section_id => $section ){
            foreach( $section['fields'] as $field ){
                if ( empty( $field['name'] ) )
                    $this->meta_keys[] = $post->post_type . '_' . str_replace(' ', '-', strtolower( $field['label'] ) );
                else
                    $this->meta_keys[] = $field['name'];
            }
        }
        return $this->meta_keys;
    }


    /**
     * Saves post meta information based on $_POST['post_id']
     * @todo Add support for $post_id
     * @todo Use a nonce?
     */
    public function metaSave( $post_id=null ){

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        if ( empty( $_POST['post_ID'] ) )
            return;

        /**
         * This is done to derive our meta keys, since wp doesn't scale well from a code
         * point of view. There's no direct access to post meta keys that don't already exists in
         * the db. So post meta keys are derived like {$post_type}_{name}.
         */
        $post_type = $_POST['post_type'];
        $new_meta = array();

        foreach( $_POST as $field => $value ){
            if ( ! is_array( $field ) ){
                $tmp = explode( '_', $field );
                if ( $tmp[1] == $post_type ){
                    $new_meta[$field] = $value;
                }
            }
        }

        $current_meta = get_post_custom( $_POST['post_ID'] );

        foreach( $new_meta as $key => $value ){
            if ( ! empty( $value ) ) {
                if ( is_array( $value ) ){
                    $value = $value[0]."\n";
                }
                update_post_meta( $_POST['post_ID'], $key, $value );
            }
        }
    }

} // End 'CustomPostTypeBase'