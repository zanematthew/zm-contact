Description
===========

Organize and standarize the creation of Custom Post Types and Custom Taxonomies in WordPress.

Creates an interface, seperates concerns, add structure, yet be able to break out of it...

Features
========

* Meta field support
* Auto enqued front-end of CSS (per post_type)
* Auto enqued front-end of JavaScript (per post_type)
* Auto enqued admin CSS files (per post_type)
* Auto enqued admin JavaScript files (per post_type)
* Auto requiring of PHP files
* Ajax form submission
* Ajax Validation
* Ajax security validation
* Automatic file creation (CSS and JS)


Usage
=====

1. Download to wp-content/plugins/
1. Activate the Plugin
1. Start developing...


Requirements
============

* WordPress (Latest)


Documentation
=============

* [Including the library](#including-the-library)
* [Creating Custom Post Types](#creating-custom-post-types)
* [Creating Taxonoimes](#creating-taxonomies)
* [Creating Meta Fields](#creating-meta-fields)
* [Adding Functions](#adding-functions)
* [Themeing](#themeing)
* [Fron-end Ajax Form Submisson](#front-end-ajax-form-submission)


Including the library <a id="including-the-library"></a>
---------------------

We require the core plugin file and then run the function `zm_easy_cpt_reqiure()`, which will auto-require the needed files.
1.

```
php
<?php
/**
 * Auto load our events.php, events_controller.php, etc.
 * and enqueue our admin and front end asset files.
 */
require_once plugin_dir_path( __FILE__ ) . '../zm-easy-cpt/plugin.php';
if ( ! function_exists( 'zm_easy_cpt_reqiure' ) ) return;
zm_easy_cpt_reqiure( plugin_dir_path(__FILE__) );
?>
```
* Optional We can add the following code below the WordPress plugin headers to ensure that zM Easy Custom Post Types is succesfully installed.

```
php
<?php
/**
 * Check if zM Easy Custom Post Types is installed. If it
 * is NOT installed we display an admin notice and return.
 */
if ( ! get_option('zm_easy_cpt_version' ) ){
    function zm_ev_admin_notice(){
        echo '<div class="updated"><p>This plugin requires <strong>zM Easy Custom Post Types</strong>.</p></div>';
    }
    add_action('admin_notices', 'zm_ev_admin_notice');
    return;
}
?>
```

Creating a Custom Post Type <a id="creating-a-custom-post-type"></a>
---------------------------

We are going to seperate our post type into two files. The first file will include out arguments for our post_type and the second the post_type functions. Please refer to `register_post_type()` in the [Codex](http://codex.wordpress.org/Function_Reference/register_post_type#Arguments) for the list of arguments.

1. Create a folder named `post_types`
1. Create a file inside of `post_types` named `NAME_OF_YOUR_POST_TYPE.php` with the following:

```
php
<?php
$books = New Books();
$books->post_type = array(
    array(
        'name' => 'Books',
        'type' => 'books',
        'rewrite' => array(
            'slug' => 'books'
            ),
        'supports' => array(
            'title',
            'editor',
        )
    )
);
?>
```

1. Advanced approach -- By using the code below we can derive everything from the name of our file

```
php
<?php

$file_name = basename( __FILE__, ".php" );

$my_cpt['name'] = ucfirst( $file_name );
$my_cpt['type'] = $file_name;
$my_cpt['slug'] = strtolower( $file_name );

$plugin = new $my_cpt['name']();
$plugin->post_type = array(
    array(
        'name' => $my_cpt['name'],
        'type' => $my_cpt['type'],
        'menu_name' => $my_cpt['name'],
        'rewrite' => array(
            'slug' => $my_cpt['slug']
            ),
        'supports' => array(
            'title',
            'editor'
        )
    )
);
?>
```

1. Create a folder called `functions`
1. Create a file named `{my_post_type}_functions.php` with the following:

```
<?php
Class Books extends zMCustomPostTypeBase {

    private $my_cpt;

    public function __construct(){
        /**
         * Run the parent construct method.
         *
         * Our parent construct has the init's for register_post_type
         * register_taxonomy and many other usefullness.
         */
        parent::__construct();
    }
}
?>
```

1. (Bonus) If you want to include css and js files for each `post_type` and have them load ONLY on the admin post type pages then add the code below to your activation hook and re-activate the plugin.

*Note you may have to change permissions in order for the files to be created.*

`do_action( 'zm_easy_cpt_create_assets', array('post_type_name_a','post_type_name_b'), plugin_dir_path(__FILE__) );`

Creating Taxonoimes <a id="#creating-taxonomies"></a>
-------------------

In order to create a taxonomy we must create the post type first, see Creating Post Types. Once the post type is created we can pass in our arguments as an array. The argument is an array of arrays where each array is the taxonomy you want to create. Please refer to `register_taxonomy()` in the [Codex](http://codex.wordpress.org/Function_Reference/register_taxonomy#Arguments) for the list of arguments.

1. Add the following into the file `NAME_OF_YOUR_POST_TYPE.php` below where we placed the post type.

```
php
<?php
$books->taxonomy = array(
    array(
        'name' => 'type'
        'post_type' => 'books',
        ),
    array(
        'name' => 'author',
        'post_type' => 'books',
        'menu_name' => 'Author',
        'slug' => 'books-author',
        'hierarchical' => false
        )
);
?>
```


Creating Meta Fields <a id="#creating-meta-fields"></a>
-------------------
In order to create a meta field we pass in our arguments into the variable `$meta_sections['my_meta_field']`.

1. Add the following into the file `NAME_OF_YOUR_POST_TYPE.php` below where we placed the post type.

```
<?php
$books->meta_sections['date'] = array(
    'name' => 'date',
    'label' => __('Event Date'),
    'fields' => array(
        array(
            'type' => 'text',
            ),
        array(
            'label' => 'End Date',
            'type' => 'text',
            'class' => 'datetime-picker-end',
            'placeholder' => 'yyyy-mm-dd',
            'name' => 'form-field-name'
            )
    )
);
?>
```

Adding Functions <a id="#adding-functions"></a>
----------------

TBD


Themeing <a id="#themeing"></a>
--------

TBD

Front-end Ajax Form Submisson <a id="#front-end-ajax-form-submission"></a>
-----------------------------

TBD


Known Issues
============

* Capbilities are not fully supported


Where To Get Help
=================

* [http://twitter.com/zanematthew](http://twitter.com/zanematthew)


Sites Using zM Easy CPT
=======================

* http://bmxraceevents.com
* http://zanematthew.com
* Are you using zM Easy CPT?


Contributing
=============

* Found a Bug? Please add it to the GitHub Issue tracker
* Have a Enhancment request? Please add it to the GitHub Issue tracker
* Want to write code? Fork/Pull request
* Star it


Contribution Guidelines
=======================
1. [S.O.L.I.D](http://en.wikipedia.org/wiki/SOLID_(object-oriented_design)
1. PHP code should be e-notice compliant
1. [WordPress CSS Standards](http://make.wordpress.org/core/handbook/coding-standards/css/)
1. [WordPress Coding Standars](http://codex.wordpress.org/WordPress_Coding_Standards)
1. I practice a "seperation of concerns" as much as possible, i.e. seperate your business logic from presentational logic.


Author
======

**Zane Matthew**

* [http://twitter.com/zanematthew](http://twitter.com/zanematthew)
* [http://github.com/zanematthew](http://github.com/zanematthew)
* [http://zanematthew.com](http://zanematthew.com)


Inspiration, Alternatives
=========================

* [http://www.farinspace.com/wpalchemy-metabox/](http://www.farinspace.com/wpalchemy-metabox/)
* [http://themergency.com/generators/wordpress-custom-post-types/](http://themergency.com/generators/wordpress-custom-post-types/)


Copyright and license
=====================

Copyright 2013 Zane Matthew

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA