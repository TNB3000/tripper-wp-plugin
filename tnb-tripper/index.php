<?php
/*
Plugin Name: Trip Planner
description: Adds shortcode support for embedding the Trip Planner widget
Version: 1.0
Author URI: https://thenew.business
Author: The New Business
License: GPL2
*/

function tripper_shortcode( $atts, $content = null ) {
    $a = shortcode_atts( array(
        'id' => '10904',
        'type' => 'ITINERARY',
        'style' => '',
        'lang' => 'en',
    ), $atts );
    $variant = $a['style'] == "" ? "" : " variant=" . $a['style'];
    $mobileVariant = $a['style'] == "INLINE" ? " mobile-variant='MAP_PATH'" : "";
    return '
    <trpr-launcher type=' . "{$a['type']}" . ' id=' . "{$a['id']}" . ' lang="en"' . $variant . $mobileVariant . '>' . $content . '</trpr-launcher>
    ';
}

function tripper_load_scripts() {
    echo '
    <script type="module" src="https://live-tnb-trpr-launcher.netlify.app/tripper-component/tripper-component.esm.js" data-stencil-namespace="tripper-component"></script>
    <script nomodule="" src="https://live-tnb-trpr-launcher.netlify.app/tripper-component/tripper-component.js" data-stencil-namespace="tripper-component"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Overpass:wght@400;700&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Francois+One&amp;display=swap">
    ';
}

add_shortcode( 'tripper', 'tripper_shortcode' );

function tripper_shortcode_scripts() {
    global $post;
    if ( has_shortcode( $post->post_content, 'tripper') ) {
        echo'
        <script type="module" src="https://live-tnb-trpr-launcher.netlify.app/tripper-component/tripper-component.esm.js" data-stencil-namespace="tripper-component"></script>
        <script nomodule="" src="https://live-tnb-trpr-launcher.netlify.app/tripper-component/tripper-component.js" data-stencil-namespace="tripper-component"></script>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Overpass:wght@400;700&amp;display=swap">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Francois+One&amp;display=swap">
        ';
    }
}

add_action( 'wp_enqueue_scripts', 'tripper_shortcode_scripts');
add_action( 'admin_menu', 'tripper_menu' );

function tripper_menu() {
	add_options_page( 'Trip Planner Settings', 'Trip Planner', 'manage_options', 'trip-planner', 'tripper_settings_page' );
}

add_filter( 'plugin_action_links_tnb-tripper/index.php', 'tripper_settings_link' );

function tripper_settings_link( $links ) {
    $url = esc_url( add_query_arg(
        'page',
        'trip-planner',
        get_admin_url() . 'options-general.php'
    ) );
    $settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
    array_push(
        $links,
        $settings_link
    );
    return $links;
}


function tripper_settings_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

    $field_hidden = 'tripper_submit_hidden';
    $field_tripper_id = 'tripper_id';
    $field_tripper_type = 'tripper_type';
    $field_tripper_style = 'tripper_style';

    $type_options = [
        'ITINERARY' => 'Itinerary',
        'ATLAS' => 'Atlas',
        'ROUTE' => 'Route',
    ];

    $style_options = [
        'DEFAULT' => 'Default',
        'INLINE' => 'Inline',
        'BUTTON' => 'Button Style',
        'MAP' => 'Stop Style',
        'MAP_PATH' => 'Route Style'
    ];

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $field_hidden ]) && $_POST[ $field_hidden ] == 'Y' ) {
        if( empty($_POST[ $field_tripper_id ]) ){
            ?>
            <div class="notice notice-error is-dismissible"><p><strong><?php _e('An ID is required to generate a shortcode.', 'trip-planner' ); ?></strong></p></div> <?php
        } else {
        ?>
        <div class="notice notice-success is-dismissible"><p><strong><?php _e('Shortcode Generated', 'trip-planner' ); ?>:</strong> <?php
                echo '<pre>[tripper';
                echo ' type=' .  $_POST[ $field_tripper_type ];
                echo ' id=' .  $_POST[ $field_tripper_id ];
                if( $_POST[ $field_tripper_style ] != 'default') {
                    echo ' style=' . $_POST[$field_tripper_style];
                }
                echo ']</pre>';
                ?>
        </p></div>
        <?php
        }

    }

    echo '<div class="wrap">';

    echo "<h2>" . __( 'Trip Planner', 'trip-planner' ) . "</h2>";
    echo "<p>" . _e('This plugin is currently under development.','trip-planner') . "</p>";

    ?>
    <hr/>
    <form name="trip-planner-generate-shortcode" method="post" action="">

        <input type="hidden" name="<?php echo $field_hidden; ?>" value="Y">

        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e("Launcher ID:", 'trip-planner' ); ?></th>
                    <td>
                        <input class=type="text" name="<?php echo $field_tripper_id; ?>" value="">
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e("Launcher Type:", 'trip-planner' ); ?></th>
                    <td>
                        <?php foreach( $type_options as $key => $value) {
                            echo '<p><input type="radio" id="' . $key . '" name="' . $field_tripper_type . '" value="' . $key . '"';
                                if( $key == 'ITINERARY') {
                                echo ' checked';
                            }
                            echo '>';
                            echo '<label for="' . $key . '">' . $value . '</label></p>';
                        }
                        ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e("Launcher Style:", 'trip-planner' ); ?></th>
                    <td>
                    <select name="<?php echo $field_tripper_style; ?>" value="DEFAULT">
                          <?php foreach( $style_options as $key => $value) {
                            echo '<option value="' . $key . '">' . $value . '</option>';
                        }
                        ?>
                    </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Generate Shortcode','trip-planner') ?>" />
        </p>

    </form>
    </div>

    <?php

}

