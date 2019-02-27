<?php

/*
    Plugin Name: peppersq-qna
    Plugin URI: http://pepper-square.com/plugin
    Description: Custom question and answer plugin for pepper square assignment
    Version: 1.0.0
    Author: Anurag Nair
    License: GPLv2 or later
 */

//Check if wordpress in instantiated, if not exit.
defined( 'ABSPATH' ) or die( "Access Denied !!" );


class PepperSqQnA
{
	function __construct() {

		// Register a new shortcode: [cr_custom_registration]
		add_shortcode( 'peppersq_custom_registration', array( $this, 'custom_registration_shortcode' ));

	}


	function activate() {
	    //flush rewrite rules
	    flush_rewrite_rules();

	}


	function deactivate() {
	    //flush rewrite rules
	    flush_rewrite_rules();
	}


	//User registration form
	function registration_form( $first_name, $last_name, $email, $username, $password  ) {
	    echo '
	    <style>
	    div {
	        margin-bottom:2px;
	    }
	     
	    input{
	        margin-bottom:4px;
	    }
	    </style>
	    ';
	 
	    echo '
	    <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">

		    <div>
		    	<label for="firstname">First Name</label>
		    	<input type="text" name="fname" value="' . ( isset( $_POST['fname']) ? $first_name : null ) . '">
		    </div>
		     
		    <div>
		    	<label for="website">Last Name</label>
		    	<input type="text" name="lname" value="' . ( isset( $_POST['lname']) ? $last_name : null ) . '">
		    </div>
		    
		    <div>
		    	<label for="email">Email <strong>*</strong></label>
		    	<input type="text" name="email" value="' . ( isset( $_POST['email']) ? $email : null ) . '">
		    </div>

		    <div>
		    	<label for="username">Username <strong>*</strong></label>
		    	<input type="text" name="username" value="' . ( isset( $_POST['username'] ) ? $username : null ) . '">
		    </div>
		     
		    <div>
		   		<label for="password">Password <strong>*</strong></label>
		    	<input type="password" name="password" value="' . ( isset( $_POST['password'] ) ? $password : null ) . '">
		    </div>

		    <input type="submit" name="submit" value="Register"/>

	    </form>
	    ';
	}


	//User Registration Form Validation
	function registration_validation( $first_name, $last_name, $email, $username, $password )  {
		global $reg_errors;
		$reg_errors = new WP_Error;

		if ( empty( $username ) || empty( $password ) || empty( $email ) ) {
		    $reg_errors->add('field', 'Required form field is missing');
		}

		if ( 4 > strlen( $username ) ) {
		    $reg_errors->add( 'username_length', 'Username too short. At least 4 characters is required' );
		}

		if ( username_exists( $username ) ) {
		    $reg_errors->add('user_name', 'Sorry, that username already exists!');
		}

		if ( 5 > strlen( $password ) ) {
	        $reg_errors->add( 'password', 'Password length must be greater than 5' );
	    }

	    if ( !is_email( $email ) ) {
	        $reg_errors->add( 'email_invalid', 'Email is not valid' );
	    }

	    if ( email_exists( $email ) ) {
	        $reg_errors->add( 'email', 'Email Already in use' );
	    }

	    if ( is_wp_error( $reg_errors ) ) {
	     
	        foreach ( $reg_errors->get_error_messages() as $error ) {
	         
	            echo '<div>';
	            echo '<strong>ERROR</strong>:';
	            echo $error . '<br/>';
	            echo '</div>';
	             
	        }
	     
	    }
	}


	function registration_form_submit() {
	    global $reg_errors, $first_name, $last_name, $email, $username, $password;
	    if ( 1 > count( $reg_errors->get_error_messages() ) ) {
	        $userdata = array(
	        'first_name'    =>   $first_name,
	        'last_name'     =>   $last_name,
	        'user_email'    =>   $email,
	        'user_login'    =>   $username,
	        'user_pass'     =>   $password,
	        );
	        $user = wp_insert_user( $userdata );
	        echo 'Registration complete. <br/> Your Username is : '.$username.' <br/> Your Password is : '.$password.' <br/>Goto <a href="' . get_site_url() . '/wp-login.php">login page</a>.';   
	    }
	}


	function custom_registration_function() {
	    if ( isset($_POST['submit'] ) ) {
	        $this->registration_validation( $_POST['fname'], $_POST['lname'], $_POST['email'], $_POST['username'], $_POST['password'] );
	         
	        // sanitize user form input
	        global $first_name, $last_name, $email, $username, $password;
	        $first_name =   sanitize_text_field( $_POST['fname'] );
	        $last_name  =   sanitize_text_field( $_POST['lname'] );
	        $email      =   sanitize_email( $_POST['email'] );
	        $username   =   sanitize_user( $_POST['username'] );
	        $password   =   esc_attr( $_POST['password'] );
	 
	        // call registration_form_submit to create the user
	        // only when no WP_error is found
	        $this->registration_form_submit( $first_name, $last_name, $email, $username, $password );
	    } else {
	    	$first_name = $last_name = $email = $username = $password = '';
	    }
	 
	    $this->registration_form( $first_name, $last_name, $email, $username, $password );
	}

	// The callback function that will replace [book]
	function custom_registration_shortcode() {
	    ob_start();
	    $this->custom_registration_function();
	    return ob_get_clean();
	}


}


if ( class_exists('PepperSqQnA') ){
    $PepperSquareSliderObj = New PepperSqQnA();
}

//Activation
register_activation_hook(__FILE__, 'activate');

//Deactivation
register_deactivation_hook(__FILE__, 'deactivate');