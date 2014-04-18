<?php


/**
 * Systematically creat the asset files needed for your post_type.
 * Usage: just run the function once, it should create the following
 * files for you:
 *
 * Example: post type: "contacts"
 * my-contact-plugin/assets/contacts.js
 * my-contact-plugin/assets/contacts_admin.js
 * my-contact-plugin/assets/contacts.css
 * my-contact-plugin/assets/contacts_admin.css
 *
 * defaults to the current plugin directory
 */
function zm_create_assets( $params=null ){

    extract( $params );
    // $dir
    // $models
    // $admin_only

    // if ( ! is_dir( $dir . 'assets' ) ){
        if ( ! wp_mkdir_p( $dir . 'assets' ) )
            wp_die("Couldn't make assets dir, don't run the action or make the dir writeable");
    // }

    $utils = New zMUtils;

    $dir = empty( $dir ) ? $utils->plugin_root_dir() : $dir;
    $assets_dir = $dir . 'assets/';

    $utils->assets_dir = $assets_dir;

    $files = false;
echo '<pre>';
    foreach( $models as $model ){
echo "model: $model";

        $utils->model = $model;

        if ( $admin_only ){
            $files = $utils->admin_assets();
        } else {
            $files = $utils->admin_assets();
            $files = $utils->frontend_assets();
        }

        foreach( $files as $file ){
            if ( ! file_exists( $file['file'] ) ){
                echo "Creating file: {$file['file']} for the model: {$model}\n";
                $r = file_put_contents( $file['file'], $file['desc'] );
                var_dump($r);
            }
        }
    }
print_r( $files );
echo '</pre>';
}


Class zMUtils {

    public $model;
    public $assets_dir;
    private $plugins_root_dir;

    public function plugin_root_dir(){
        return dirname( dirname( plugin_dir_path( __FILE__ ) ) ) . '/';
    }

    public function front_end_assets(){

        $date = date('F j, Y, g:i a');

        $files = array(
            array(
                'file' => $this->assets_dir . $this->model . '.css',
                'desc' => "/* \nThis file is automatically created for you. \n It is your CSS file for the {$this->model} model. Do NOT place admin styling here, instead use the {$this->model}_admin.css file. \n\nCreated On: {$date} */"
            ),
            array(
                'file' => $this->assets_dir . $this->model . '.js',
                'desc' => "/* \nThis file is automatically created for you. \n It is your JS file for the {$this->model} model. Do NOT place admin JS here, instead use the {$this->model}_admin.js file. \n\nCreated On: {$date} */"
            )
        );
        return $files;
    }

    public function admin_assets(){

        $date = date('F j, Y, g:i a');

        $files = array(
            array(
                'file' => $this->assets_dir . $this->model . '_admin.css',
                'desc' => "/* \nThis file is automatically created for you. \n It is your Admin CSS file for the {$this->model} model. \n\nCreated On: {$date} */"
            ),
            array(
                'file' => $this->assets_dir . $this->model . '_admin.js',
                'desc' => "/* \nThis file is automatically created for you. \n It is your Admin JS file for the {$this->model} model. \n\nCreated On: {$date} */"
            )
        );
        return $files;
    }
}
// die();