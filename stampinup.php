<?php
/**
 * @package Stampinup
 * @version 0.1
 */
/*
Plugin Name: Stampin Up easy
Plugin URI: http://www.hosl.de/
Description: This Plugin adds a shortcode for easy Stampin Up Product presentation and adds your dbwsdemoid to every stampinup shop link to you place in the content.
Author: Andreas H.
Version: 0.1
Author URI: http://hosl.de/
*/

/**
 * Add parent stylesheet to child
 */

function stampinup_enqueue_styles() {
    wp_enqueue_style( 'stampinup-style', plugins_url('stampinup.css', __FILE__));
}
add_action( 'wp_enqueue_scripts', 'stampinup_enqueue_styles' );

/**
 * Add Shortcode stampused
 * adds Stampin Up products to your post
 */
function stempeltier_stampused_sc( $atts , $content = null ) {
    // Attributes
    extract( shortcode_atts(
            array(
                'size' => 'small',
                'style' => 'stampused',
            ), $atts )
            );

    switch( true ) {
        case strtolower($size) === "small":
            $imgsize = "s";
            break;
        case strtolower($size) === "big":
            $imgsize = "g";
            break;
        default:
            $size = "s";
    }

    $size = sanitize_text_field( $size );
    $style = sanitize_text_field( $style );
    $products = sanitize_text_field( $products );

    // Content
    $products = explode(" ", $content, 20);

    // DemoID from DB
    $demoid = get_option( 'stampinup-demoid' );

    // Code
    $return = "<div class=\"$style $style-wrap\">\n";
    //$return.= "<!-- $content :".count($products)." -->\n";
    foreach ($products as $product) {
        //$return.= "<!-- $product -->\n";
        if ( preg_match( "/^[0-9]{6}$/", $product) ) {
            $return.= "  <div class=\"$style-item $style-item-$size\">\n";
            $return.= "    <a href=\"http://www2.stampinup.com/ECWeb/ProductDetails.aspx?productID=$product&dbwsdemoid=$demoid\" 
                alt=\"Stampin Up Product $product\" title=\"Stampin Up Online Shop Product $product\" target=\"_blank\">
                <img src=\"//www2.stampinup.com/images/EC/$product$imgsize.jpg\" alt=\"Stampin Up Product $product\"></a>";
            $return.= "  </div>\n";
        }
    }
    $return.= "</div>\n";

    return $return;   
}
add_shortcode( 'stampused', 'stempeltier_stampused_sc' );

/**
 * Add Shortcode stampprod
 * adds Stampin Up products to your post
 */
function stempeltier_stampprod_sc( $atts , $content = null ) {
    // Attributes
    extract( shortcode_atts(
            array(
                'id' => '0',
                'hover' => 'yes',
                'style' => 'stampprod',
            ), $atts )
            );

    switch( true ) {
        case strtolower($hover) === "yes":
            $hover = "true";
            break;
        default:
            $hover = "false";
    }

    if (  preg_match( "/^[0-9]{6}$/", $id) ) {
        $product = $id;
    }
    else {
        $id = 0;
    }
    $style = sanitize_text_field( $style );

    // Content
    $linktext = sanitize_text_field( $content );

    // DemoID from DB
    $demoid = get_option( 'stampinup-demoid' );

    // Code
    if ( $id != 0 ) {
        wp_enqueue_script( 'stempprod-js', get_stylesheet_directory_uri() . '/js/stampsc.js', array(), '0.0.1', true);
        $return = "<a href=\"http://www2.stampinup.com/ECWeb/ProductDetails.aspx?productID=$product&dbwsdemoid=$demoid\" 
                    alt=\"Stampin Up Product $product\" title=\"Stampin Up Online Shop Product $product\" target=\"_blank\" class=\"$style\">";
            if ( $hover ) {
                $return.= "  <span id=\"stampprod-span\">";
                $return.= "    <img src=\"//www2.stampinup.com/images/EC/${product}s.jpg\" alt=\"Stampin Up Product $product\">";
                $return.= "  </span>";
            }
        $return.= $linktext;
        $return.= "</a>";
    }
    else {
        $return = $linktext;
    }

    return $return;   
}
add_shortcode( 'stampprod', 'stempeltier_stampprod_sc' );

/**
 * Add content filter for demoid
 */
function stampinup_demoid_content_filter($content) {
//    if( is_singular() && is_main_query() ) {
    if( is_main_query() ) {
        $demoid = get_option( 'stampinup-demoid' );
        $new_content = preg_replace
        $new_content = preg_replace( '/(href=.http:\/\/www2\.stampinup\.com\/ECWeb\/[a-z.?&=0-9]+)/i', '$1&dbwsdemoid='.$demoid, $content) ;
        $content = $new_content;   
    }   
    return $content;
}
add_filter('the_content', 'stampinup_demoid_content_filter');

/**
 * Add Settings Menu
 * for Stampinup Plugin
 */
add_action( 'admin_menu', 'stampinup_menu' );

/**
 * Register Settings Page
 */
function stampinup_menu() {
    add_options_page( 'Stampin Up Options', 'Stampin Up', 'manage_options','stampinup-plugin',  'stampinup_options' );
    add_action( 'admin_init', 'register_stampinup_settings' );
}

/**
 * Register Stampinup settings
 */
function register_stampinup_settings() {
    //register our settings
    register_setting( 'stampinup-settings-group', 'stampinup-demoid' );
}

/**
 * Build the Settins Page
 */
function stampinup_options() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

?>
<div class="wrap">
<h2>Stampinup Settings</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'stampinup-settings-group' ); ?>
    <?php do_settings_sections( 'stampinup-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Your Stampin Up Demo ID</th>
        <td><input type="text" name="stampinup-demoid" value="<?php echo esc_attr( get_option('stampinup-demoid') ); ?>" /></td>
        </tr>
         
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php } 

?>
