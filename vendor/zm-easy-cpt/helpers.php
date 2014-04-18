<?php

Class zMHelpers {

    /**
     * Checks if is a valid email using PHPs email filter.
     *
     * @param $email The email to validate.
     * @param $is_ajax Bool prints json obj otherwise returns it.
     */
    private function zm_validate_email( $email=null, $is_ajax=true ) {

        $status = array(
            0 => array(
                'status' => 0,
                'cssClass' => 'error',
                'msg' => 'Invalid Email',
                'description' => '<div class="error-container">Invalid Email</div>',
                'field' => ''
                ),
            1 => array(
                'status' => 1,
                'msg' => 'Pass',
                'cssClass' => 'success',
                'field' => '',
                'description' => '<div class="success-container">Valid Email</div>'
                )
            );

        if ( ! is_null( $email ) ) {
            $email = $_POST['email'];
        }


        if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            if ( $is_ajax ) {
                print json_encode( $status[0] );
                die();
            } else {
                return $status[0];
            }
        }
        die();
    }
    // add_action( 'wp_ajax_nopriv_zm_validate_email', 'zm_validate_email' );
    // add_action( 'wp_ajax_zm_validate_email', 'zm_validate_email' );


    /**
     * Build an option list of Terms based on a given Taxonomy.
     *
     * @package helper
     * @uses zm_base_get_terms to return the terms with error checking
     * @param string $taxonomy
     * @param mixed $value, the value to be used in the form field, can be term_id or term_slug
     * @todo switch white list params
     * @todo use more core
     */
    public function build_options( $taxonomy=null, $value=null ) {

        if ( is_null ( $value ) )
            $value = 'term_id';

        if ( is_array( $taxonomy ) )
            extract( $taxonomy );

        // white list
        if ( empty( $prepend ) )
            $prepend = null;

        if ( empty( $extra_data ) )
            $extra_data = null;

        if ( empty( $extra_class ) )
            $extra_class = null;

        if ( ! empty( $multiple ) ) {
            $multiple = 'multiple="multiple"';
        } else {
            $multiple = false;
        }

        if ( empty( $default ) ){
            $default = null;
        }

        if ( !isset( $label ) )
            $label = $taxonomy;

        if ( empty( $post_id ) )
            $post_id = null;

        /** All Terms */
        $args = array(
            'orderby' => 'name',
            'hide_empty' => false
             );

        $terms = get_terms( $taxonomy, $args );

        if ( is_wp_error( $terms ) ) {
    //        exit( "Opps..." . $terms->get_error_message() . "..dog, cmon, fix it!" );
            $terms = false;
        }

        // This hackiness is coming from...
        // we might be on a single page or our id is
        // being passed in explictiyly
        if ( is_single() ) {
            global $post;
            $current_terms = get_the_terms( $post->ID, $taxonomy );
            $index = null;
        } else {
            if ( ! empty( $post_id ) ) {
                $current_terms = get_the_terms( $post_id, $taxonomy );
                $index = 0;
            }
        }

        $temp = null;
        ?>
        <?php if ( $terms ) : ?>
        <fieldset class="zm-base-<?php echo $taxonomy; ?>-container <?php echo $taxonomy; ?>-container">
        <label class="zm-base-title"><?php echo $label; ?></label>
        <select name="<?php echo $taxonomy; ?><?php if ( $multiple=='multiple="multiple"') print '[]'; ?>" <?php echo $multiple; ?> <?php echo $extra_data; ?> class="<?php echo $extra_class; ?>" id="" <?php echo $multiple; ?>>
            <option><?php print $default; ?></option>
            <?php foreach( $terms as $term ) : ?>
                <?php if ( ! empty( $current_terms )) : ?>
                <?php for ( $i=0, $count=count($current_terms); $i <= $count; $i++ ) : ?>
                    <?php

                    // Check if we have an index, if we do start our loop
                    // using the term id because our current_terms array
                    // will be index based on the term id.

                    // This is because we are on the single post page
                    // if not it might be an ajax request or the id is
                    // being passed in explictiyly
                    if ( is_null( $index ) )
                        $tmp_index = $term->term_id;
                    else
                        $tmp_index = 0;

                    if ( $current_terms[ $tmp_index ]->name ) {
                        $temp = $current_terms[ $tmp_index ]->name;
                    } else {
                        $temp = null;
                    }
                    ?>
                <?php endfor; ?>
                <?php endif; ?>
                <?php $term->name == $temp ? $selected = 'selected="selected"' : $selected = null; ?>
                <option
                value="<?php echo $prepend; ?><?php echo $term->$value; ?>"
                data-value="<?php echo $term->slug; ?>"
                class="taxonomy-<?php echo $taxonomy; ?> term-<?php echo $term->slug; ?> <?php echo $taxonomy; ?>-<?php echo $term->term_id; ?>"
                <?php echo $selected; ?>>
                <?php echo $term->name; ?>
                </option>
            <?php endforeach; ?>
        </select>
        </fieldset>
        <?php endif; ?>
    <?php }


    /**
     * @param $items (array) id, name, of items to build option boxes from
     * @param $current (array|string) item_id to match against, $array = json_decode(json_encode($object), true);
     *
     */
    private function zm_base_build_select( $params=null ){

        extract( $params );
        $class = $key;
        if ( empty( $extra_data ) )
            $extra_data = null;

        if ( empty( $extra_class ) )
            $extra_class = null;

        if ( ! empty( $multiple ) ) {
            $multiple = 'multiple="multiple"';
            $key .= "[]";
        } else {
            $multiple = false;
        }

        if ( empty( $default ) ){
            $default = false;
        }
        if ( empty( $current ) )
            $current = false;
        ?>
        <fieldset class="zm-ev-state-container">
        <?php if ( ! empty( $label ) ) : ?><label class="zm-base-title">State</label><?php endif; ?>
        <select name="<?php echo $key; ?>" <?php echo $multiple; ?> <?php echo $extra_data; ?> class="<?php echo $extra_class; ?> <?php print $class; ?>" id="">
            <?php if ( $default ) : ?><option value=""><?php print $default; ?></option><?php endif; ?>
            <?php foreach( $items as $item ) : ?>
                <option value="<?php print $item['id']; ?>"
                    <?php if ( is_array( $current ) ) : foreach( $current as $c ) : selected( $item['id'], $c ); ?>
                    <?php endforeach; else : selected( $item['id'], $current ); endif; ?> class="<?php print $item['id']; ?>">
                    <?php print $item['name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        </fieldset>
    <?php }
}