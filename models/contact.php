<?php

// Derive custom post type name from file name
$file_name = basename( __FILE__, ".php" );
$my_cpt['name'] = ucfirst( $file_name );
$my_cpt['type'] = $file_name;
$my_cpt['slug'] = strtolower( $file_name );
$my_cpt['menu_name'] = $my_cpt['name'];

// change
$plugin = new $my_cpt['name']();
$plugin->asset_url = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/';
$plugin->post_type = array(
    array(
        'name' => $my_cpt['name'],
        'type' => $my_cpt['type'],
        'label' => 'Contact',
        'menu_name' => $my_cpt['menu_name'],
        'hierarchical' => true, // supports must contain 'page-attributes'
        'public' => true,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'rewrite' => array(
            'slug' => $my_cpt['slug']
            ),
        'supports' => array(
            'title',
            'editor',
            'revisions',
            'page-attributes'
        ),
        'taxonomies' => array(
            'zmcontact_category',
            'budget'
            )
    )
);

/**
 * @todo derive this from the abstract 'post_type' => $my_cpt['type']
 */
$plugin->taxonomy = array(
     array(
         'name' => 'zmcontact_category',
         'post_type' => $my_cpt['type'],
         'hierarchical' => false,
         'labels' => array(
            'name' => 'Categories',
            'singular_name' => 'Category',
            'add_new_item' => 'Add new Category'
            )
         ),
     array(
         'name' => 'budget',
         'post_type' => $my_cpt['type'],
         'hierarchical' => false
         )
);


$plugin->meta_sections[ $my_cpt['type'] ] = array(
    'name' => $my_cpt['type'],
    'label' => __( ucwords( $my_cpt['type'] ) ),
    'fields' => array(
        array(
            'label' => 'First Name',
            'type' => 'text'
            ),
        array(
            'label' => 'Last Name',
            'type' => 'text'
            ),
        array(
            'label' => 'Email',
            'type' => 'text'
            ),
        array(
            'label' => 'Website',
            'type' => 'text'
            ),
        array(
            'label' => 'Phone Number',
            'type' => 'text'
            )
    )
);