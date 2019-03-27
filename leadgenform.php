<?php 
/*
    * Plugin Name: Lead Gen Form Plugin [LGFP]
    * Description: Plugin to paste a form by a shortcode to collect customer data and save it inside custom post type.
    * Version: 1.0.0
    * Author: Walled Mahmoud
    * Author URI: http://walledmahmoud.github.io/WalledMahmoud/
    * Text Domain: leadgenform
*/

// No Access Directly, Please
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Version.
*/
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/** 
 *  Plugin Path Constant 
*/
if ( ! defined('PLUGIN_PATH') ) {
    define('PLUGIN_PATH', plugin_dir_url(__FILE__));
}


if (! class_exists ('LeadGenForm') ) {

    class LeadGenForm {

        /**
         * Instance of this class.
        */
        protected static $instance = null;

        /**
         * The Unique Identifier OF This Plugin.
         * @since    1.0.0
         * @access   protected
         * @var      string    
         * $plugin_name    The String Used To Uniquely Identify This Plugin.
        */
        protected $plugin_name;

        /**
         * The Current Version OF The Plugin.
         *
         * @since    1.0.0
         * @access   protected
         * @var      string    
         * $version    The Current Version OF The Plugin.
         */
        protected $version;

        /**
         * Set A Single Instance If It Doesn't Exist 
        */
        public static function get_instance() {

            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        /**
         * Start Up Everything 
        */
        public function __construct() {
            
            if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
                $this->version = PLUGIN_NAME_VERSION;
            } else {
                $this->version = '1.0.0';
            }

            $this->plugin_name = 'leadgenform';

            add_action('wp_enqueue_scripts', array($this, 'enqueue_script_files') );
            add_shortcode('create_form', array($this, 'create_leadgenform') );
            add_action('init', array($this, 'create_custom_post_type') );
            add_action('wp_ajax_submit_data', array($this, 'create_post_validate_submit_data') );
            add_action('wp_ajax_nopriv_submit_data', array($this, 'create_post_validate_submit_data') );

        }

        public function enqueue_script_files() {
 
            wp_enqueue_style('my-plugin-stylesheet',  PLUGIN_PATH . 'assets/css/style.css' );
            wp_enqueue_script( 'my-plugin-script', PLUGIN_PATH . 'assets/js/script.js', array( 'jquery' ), null, true );

            /**
             * Including Ajax Script In The Plugin
            */
            wp_localize_script( 'my-plugin-script', 'ajax_object', 
                array ( 
                    'ajax_url' => admin_url( 'admin-ajax.php' ), 
                    'check_nonce' => wp_create_nonce('form-data-nonce'),
                ));
        }

        /**
         * Function To Create The Form By A Shortcode 
         * Allow The Admin To Edit The Fields Name By The Attributes
         * Fields Are:-
            * Name [text]
            * E-Mail [email]
            * Phone [tel]
            * Budget [number]
            * Message [textarea]
            * DateTime [hidden]
        */
        public function create_leadgenform( $atts = [], $content = null ) {

            $atts = shortcode_atts(
                array(
                    'name_label'    => 'Name',
                    'email_label'   => 'E-Mail',
                    'phone_label'   => 'Phone Number',
                    'budget_label'  => 'Desired Budget',
                    'message_label' => 'Message'
                ), $atts, 'create_form' );

            return '<div class="form-style">
                        <div class="response-message"></div>
                        <form method = "POST" action="" class="leadgen-form" id="myForm">
                            <label for="nameLabel">' . esc_html__($atts['name_label'], 'leadgenform') .' <span class="required">*</span></label>
                            <input id="nameLabel" type="text" name="nameLabel" required minLength="6" maxLength="25">
                            <label for="email">'. esc_html__($atts['email_label'], 'leadgenform') .' <span class="required">*</span></label>
                            <input id="emailLabel" type="email" name="emailLabel" required>
                            <label for="phoneLabel">'. esc_html__($atts['phone_label'], 'leadgenform') .' <span class="required">*</span></label>
                            <input id="phoneLabel" type="tel" name="phoneLabel" minLength="10" maxLength="15" required>
                            <label for="budgetLabel">'. esc_html__($atts['budget_label'], 'leadgenform') .' <span class="required">*</span></label>
                            <input id="budgetLabel" type="number" name="budgetLabel" maxLength="8" required>
                            <label for="message">'. esc_html__($atts['message_label'], 'leadgenform') .' <span class="required">*</span></label>
                            <textarea id="messageLabel" name="messageLabel" rows="3" cols="33" minLength="5" maxlength="200" required></textarea>
                            <input id="datetime" type="hidden" name="datetime" value="'. $this->get_current_datetime() .'">
                            <input class="submit-data" type="submit" name="submit_form" value="Submit">
                        </form>
                    </div>';
                    
        }


        /**
         * Create "Customer" Custom Post Type
         * Later, Add The Form Data To This Post Type 
        */
        public function create_custom_post_type() {

            // Set Up Customer Labels
            $labels = array(
                'name' => 'Customer',
                'singular_name' => 'Customer',
                'add_new' => 'Add New Customer',
                'add_new_item' => 'Add New Customer',
                'edit_item' => 'Edit Customer',
                'new_item' => 'New Customer',
                'all_items' => 'All Customers',
                'view_item' => 'View Customer',
                'search_items' => 'Search Customers',
                'not_found' =>  'No Customers Found',
                'not_found_in_trash' => 'No Customers found in Trash', 
                'parent_item_colon' => '',
                'menu_name' => 'Customers',
            );
            
            // Register The Custom Post Type
            $args = array(
                'labels' => $labels,
                'public' => true,
                'has_archive' => true,
                'show_ui' => true,
                'capability_type' => 'post',
                'hierarchical' => false,
                'taxonomies'    => array('category', 'post_tag'),
                'rewrite' => array('slug' => 'customer'),
                'query_var' => true,
                'menu_icon' => 'dashicons-star-filled',
                'supports' => array(
                    'title',
                    'editor',
                    'excerpt',
                    'trackbacks',
                    'custom-fields',
                    'comments',
                    'revisions',
                    'thumbnail',
                    'author',
                    'page-attributes'
                )
            );
            register_post_type( 'Customer', $args );
        }


        /**
         * Send The Data Over The WP AJAX 
         * Make Some Validation For The Form's Inputs
         * Create The Post After Form Submission
        */
        public function create_post_validate_submit_data() {

            /**
             * Check The Nonce in Our Ajax Callback
            */
            check_ajax_referer( 'form-data-nonce', 'security' );

            // Sanitize The Form Fields
            $name       = sanitize_text_field($_POST['nameLabel']);
            $email      = sanitize_email($_POST['emailLabel']);
            $message    = sanitize_textarea_field($_POST['messageLabel']);
            $phone      = preg_replace('/[^0-9+-]/', '', $_POST['phoneLabel']);
            $budget     = filter_var($_POST['budgetLabel'], FILTER_SANITIZE_NUMBER_INT);
            $post_date  = $_POST['datetime'];

            $errors = array();

            if( empty($name) || strlen($name) < 6 || strlen($name) > 25) {
                $errors['name'] = __("Please enter your name, with 6 characters minimum and 25 maximum.", "leadgenform");
            }

            if( empty($email) ) {
                $errors['email'] = __("Please enter a valid email.", "leadgenform");
            }

            if( strlen($phone) < 10 || strlen($phone) > 15 ) {
                $errors['phone'] = __("Your phone number can't be less than 10 digits, or more than 15 digits.", "leadgenform");
            }

            if(strlen($budget) < 1 || strlen($budget) > 8 ) {
                $errors['budget'] = __("Your budget can't be less than 1 digits, or more than 8 digits", "leadgenform");
            }

            if( empty($message) || strlen($message) < 5 || strlen($message) > 200 ) {
                $errors['message'] = __("Please enter your message, with 5 characters minimum and 200.", "leadgenform");
            }

            if(! empty($errors)) {
                ?>
                <div class="error">
                    <?php
                    foreach($errors as $error) {
                        echo  "<span>" . $error . "</span>";
                    }
                    ?>
                </div>
            <?php

            /**
             * If There're No Errors, Create The Post To The Custom Post Type
             * Create Posts For Every Submission Using Wp_inser_post()
            */
            } else {
 
                /**
                 * The Array OF Arguements To Be Inserted 
                 * Assign The Post Title To The Customer Name
                 * Assign The Post Content To The Customer Message 
                 * Store  All The Fields Provided By The Customer Into Post Meta
                 * Using 'meta_input'.
                */
                $create_post = array(
                    'post_title'    => $name,
                    'post_content'  => $message,
                    'post_date'     => $post_date,
                    'post_status'   => 'private',          
                    'post_type'     => 'Customer',
                    'meta_input' => array(
                        'customer_name'     => $name,
                        'customer_email'    => $email,
                        'customer_phone'    => $phone,
                        'customer_budget'   => $budget,
                        'customer_message'  => $message,
                        'post_datetime'     => $post_date,
                    )
                );
                
                /**
                 * Insert A New Post For The Customer In The Database
                 * Store The Post ID In the $post_ID Variable
                */
                $post_ID = wp_insert_post($create_post, $wp_error);

                if( ! $wp_error && $post_ID ) {
                    echo "<div class='success'><span>". __('Done ! Thank you for your time','leadgenform') ."</span></div>";                
                }
            }
            wp_die(); 
        }


        /**
         * Get The Current DataTime UTC
         * Using http://worldclockapi.com/api/json/utc/now API
         * https://codex.wordpress.org/HTTP_API
        */
        public function get_current_datetime() {

            $response = wp_remote_get( 'http://worldclockapi.com/api/json/utc/now' );

            if( is_wp_error( $response ) ) {
                return;
            }

            $DateTime = json_decode( wp_remote_retrieve_body( $response ) );

            if( empty( $DateTime ) ) {
                return;
            }

            return $DateTime->currentDateTime;
        }


        /**
         * @since     1.0.0
         * @return    string    
         * The Version Number OF The Plugin.
        */
        public function get_version() {
            return $this->version;
        }

        /**
         * @since     1.0.0
         * @return    string    
         * The Name OF The Plugin.
        */
        public function get_plugin_name() {
            return $this->plugin_name;
        }

    }

}

/**
 * Start Executing The Plugin 
*/
function execute_leadgenform() {
	return LeadGenForm::get_instance();
}
execute_leadgenform();