<?php

/*
    Plugin Name: pepper-square-slider
    Plugin URI: http://pepper-square.com/plugin
    Description: Custom carousel plugin for pepper square assignment
    Version: 1.0.0
    Author: Anurag Nair
    License: GPLv2 or later
 */

//Check if wordpress in instantiated, if not exit.
defined( 'ABSPATH' ) or die( "Access Denied !!" );

class PepperSquareSlider
{

    function __construct() {

        add_action('init', array($this, 'register_slider'));
        add_action('admin_menu', array( $this, 'peppersq_plugin_settings' ));


        //Save Slider Options to database
        add_action('save_post', array($this, 'save_slider_info'));


        //Add shortcode
        $this->peppersq_add_shortcode();

        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

    }


    function activate() {
        //flush rewrite rules
        flush_rewrite_rules();

    }

    function peppersq_add_shortcode() {
        add_shortcode("peppersq_carousel", array($this, "display_slider"));
        /* Define shortcode column in Slider List View */
        add_filter('manage_edit-peppersq_slider_columns', array($this, 'set_custom_edit_peppersq_slider_columns'));
        add_action('manage_peppersq_slider_posts_custom_column', array($this, 'custom_peppersq_slider_column'), 10, 2);
        add_action('add_meta_boxes', array($this, 'slider_meta_box'));
    }


    function deactivate() {
        //flush rewrite rules
        flush_rewrite_rules();
    }


    function enqueue_scripts() {
        global $post;

        wp_enqueue_script('jquery');

       

        wp_register_script('slick_init', plugins_url('js/slick.initialize.js', __FILE__), array('jquery'));
        

        $speed    = (get_option('peppersq_speed') == '') ? 100 : get_option('peppersq_speed');
        $slide_num    = (get_option('peppersq_slide_num') == '') ? 1 : get_option('peppersq_slide_num');
        $dots    = (get_option('peppersq_dots') == 'enabled') ? 'true' : false;
        $arrows    = (get_option('peppersq_arrows') == 'enabled') ? 'true' : false;
        $config_array = array(
            'ps_speed' => $speed,
            'ps_dots' => $dots,
            'ps_arrows' => $arrows,
            'ps_slide_num' => $slide_num
        );

        // echo "<pre>";
        // print_r(plugins_url('js/slick.initialize.js', __FILE__));
        // exit;

        wp_localize_script('slick_init', 'setting', $config_array);
        wp_enqueue_script('slick_init');


        wp_register_script('slick_core', plugins_url('js/slick.min.js', __FILE__), array("jquery"));
        wp_enqueue_script('slick_core');



    }


    function enqueue_styles() {

        wp_register_style('slick_css', plugins_url('css/slick.css', __FILE__));
        wp_enqueue_style('slick_css');
        wp_register_style('slick_theme_css', plugins_url('css/slick-theme.css', __FILE__));
        wp_enqueue_style('slick_theme_css');
    
    }


    function display_slider($attr, $content) {

        extract(shortcode_atts(array(
                    'id' => ''
                        ), $attr));

        $gallery_images = get_post_meta($id, "_gallery_images", true);
        $gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();



        $plugins_url = plugins_url();


        $html = '<div class="container">
        <div id="slides">';

        foreach ($gallery_images as $gal_img) {
            if ($gal_img != "") {
                $html .= "<div><img src='" . $gal_img . "' /></div>";
            }
        }

        $html .= '
        </div>
      </div>';

        return $html;
    }


    function register_slider() {
        $labels = array(
            'menu_name' => _x('Pepper Sq Sliders', 'peppersq_slider'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'description' => 'Slideshows',
            'supports' => array('title'),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => true,
            'capability_type' => 'post'
        );

        register_post_type('peppersq_slider', $args);
    }


    function slider_meta_box() {

        add_meta_box("slider-images", "Slider Images", array($this, 'view_slider_images_box'), "peppersq_slider", "normal");
    }

    function view_slider_images_box() {
        global $post;

        $gallery_images = get_post_meta($post->ID, "_gallery_images", true);
        // print_r($gallery_images);exit;
        $gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();

        // Use nonce for verification
        $html = '<input type="hidden" name="slider_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';

        $html .= '<table class="form-table">';

        $html .= "
              <tr>
                <th style=''><label for='Upload Images'>Image 1</label></th>
                <td><input name='gallery_img[]' id='slider_upload' type='text' value='" . (!empty($gallery_images) && isset($gallery_images[0]) ? $gallery_images[0] : '') . "'  /></td>
              </tr>
              <tr>
                <th style=''><label for='Upload Images'>Image 2</label></th>
                <td><input name='gallery_img[]' id='slider_upload' type='text' value='" . (!empty($gallery_images) && isset($gallery_images[1]) ? $gallery_images[1] : '') . "' /></td>
              </tr>
              <tr>
                <th style=''><label for='Upload Images'>Image 3</label></th>
                <td><input name='gallery_img[]' id='slider_upload' type='text'  value='" . (!empty($gallery_images) && isset($gallery_images[2]) ? $gallery_images[2] : '' ). "' /></td>
              </tr>
              <tr>
                <th style=''><label for='Upload Images'>Image 4</label></th>
                <td><input name='gallery_img[]' id='slider_upload' type='text' value='" . (!empty($gallery_images) && isset($gallery_images[3]) ? $gallery_images[3] : '') . "' /></td>
              </tr>
              <tr>
                <th style=''><label for='Upload Images'>Image 5</label></th>
                <td><input name='gallery_img[]' id='slider_upload' type='text' value='" . (!empty($gallery_images) && isset($gallery_images[4]) ? $gallery_images[4] : '') . "' /></td>
              </tr>          

            </table>";

        echo $html;
    }


    function set_custom_edit_peppersq_slider_columns($columns) {
        return $columns
        + array('slider_shortcode' => __('Shortcode'));
    }

    function custom_peppersq_slider_column($column, $post_id) {

        $slider_meta = get_post_meta($post_id, "_slider_meta", true);
        $slider_meta = ($slider_meta != '') ? json_decode($slider_meta) : array();

        switch ($column) {
            case 'slider_shortcode':
                echo "[peppersq_carousel id='$post_id' /]";
                break;
        }
    }


    function save_slider_info($post_id) {


        // verify nonce
        if (isset($_POST['slider_box_nonce']) && !wp_verify_nonce($_POST['slider_box_nonce'], basename(__FILE__))) {
            return $post_id;
        }

        // check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // check permissions
        if (isset($_POST['post_type']) && 'peppersq_slider' == $_POST['post_type'] && current_user_can('edit_post', $post_id)) {

            /* Save Slider Images */
            //echo "<pre>";print_r($_POST['gallery_img']);exit;
            $gallery_images = (isset($_POST['gallery_img']) ? $_POST['gallery_img'] : '');
            $gallery_images = strip_tags(json_encode($gallery_images));
            update_post_meta($post_id, "_gallery_images", $gallery_images);

           
        } else {
            return $post_id;
        }
    }


    


    function peppersq_plugin_settings() {
        //create top-level menu
        add_menu_page('PepperSq Slider Settings', 'PepperSq Slider Settings', 'administrator', 'peppersq_settings', array( $this, 'peppersq_display_settings'));
    }

    function peppersq_display_settings() {

        $speed = (get_option('peppersq_speed') != '') ? get_option('peppersq_speed') : '100';
        $slide_num = (get_option('peppersq_slide_num') != '') ? get_option('peppersq_slide_num') : '1';
        $dots  = (get_option('peppersq_dots') == 'enabled') ? 'checked' : '' ;
        $arrows  = (get_option('peppersq_arrows') == 'enabled') ? 'checked' : '' ;

        $html = '<div class="wrap">

                <form method="post" name="options" action="options.php">

                <h2>Select Your Settings</h2>' . wp_nonce_field('update-options') . '
                <table width="100%" cellpadding="10" class="form-table">
                    <tr>
                        <td align="left" scope="row">
                        <label>Enable Dots</label><input type="checkbox" '.$dots.' name="peppersq_dots" 
                        value="enabled" />

                        </td> 
                    </tr>
                    <tr>
                        <td align="left" scope="row">
                        <label>Enable Arrows</label><input type="checkbox" '.$arrows.' name="peppersq_arrows" 
                        value="enabled" />

                        </td> 
                    </tr>
                    <tr>
                        <td align="left" scope="row">
                        <label>Transition speed</label><input type="text" name="peppersq_speed" 
                        value="' . $speed . '" />

                        </td> 
                    </tr>
                    <tr>
                        <td align="left" scope="row">
                        <label>Number of slides</label><input type="text" name="peppersq_slide_num" 
                        value="' . $slide_num . '" />

                        </td> 
                    </tr>
                </table>
                <p class="submit">
                    <input type="hidden" name="action" value="update" />  
                    <input type="hidden" name="page_options" value="peppersq_dots,peppersq_speed,peppersq_arrows,peppersq_slide_num" /> 
                    <input type="submit" name="Submit" value="Update" />
                </p>
                </form>

            </div>';
        echo $html;
    }


}


if ( class_exists('PepperSquareSlider') ){
    $PepperSquareSliderObj = New PepperSquareSlider();
}

//Activation
register_activation_hook(__FILE__, 'activate');

//Deactivation
register_deactivation_hook(__FILE__, 'deactivate');
