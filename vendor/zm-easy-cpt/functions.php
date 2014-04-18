<?php

require_once plugin_dir_path( __FILE__ ) . 'abstract.php';

/**
 * This action was created to ease the redundancy of requiring files.
 *
 * This action scans the passed in directory for the post types and
 * does a require once for each file that is not in the ignore list.
 *
 * The files MUST follow the following format:
 *  1. Post type file name can NOT contain underscores "_"
 *  2. The functions file MUST match the post type file name.
 *
 * Example, given a post type of "contact" the following files
 * are automatically required for you:
 * my-plugin/post_type/contact.php
 * my-plugin/controllers/contact_controller.php
 *
 * @param $dir the full path the plugin.
 */
if ( class_exists( 'zMCore' ) ) return;
Class zMCore {
    public function zm_easy_cpt_reqiure( $dir=null ){

        /**
         * Read the contents of the directory into an array.
         */
        $tmp_controllers = scandir( $dir . 'controllers/' );

        /**
         * This is our list of items to ignore from the scaned directory
         */
        $ignore = array(
            '.',
            '..',
            '.DS_Store'
            );

        /**
         * Search our array for each item in the ignore list.
         * Since our list is indexed, we use array search, which returns
         * the index, i.e., 0, 1, 2, etc. From here we "unset" our value.
         * Thus removing the ignored file from the scanned directory array.
         */
        foreach( $ignore as $file ) {
            $ds = array_search( $file, $tmp_controllers );
            if ( ! is_null( $ds ) ){
                unset( $tmp_controllers[$ds] );
            }
        }

        /**
         *This loop performs a require once on each item in our functions
         * array. Once each item is loaded we split the items in the array on
         * an "_" and use the first part of item as the file name of our post type,
         * thus performing a require once on our post type.
         */
        $models = array();
        foreach( $tmp_controllers as $controller ) {
            require_once $dir . 'controllers/'.$controller;

            $model = array_shift( explode( '_', $controller ) );
            $models[] = $model;
            require_once $dir . 'models/'.$model . '.php';
        }
    }
}