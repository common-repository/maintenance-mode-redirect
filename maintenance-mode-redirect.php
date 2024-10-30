<?php 

/**
 * Plugin Name: Maintenance Mode Redirect
 * Plugin URI: https://wordpress.org/plugins/maintenance-mode-redirect/
 * Description: Redirect and display a maintenance message to visitors and logs out non-admin users during maintenance periods.
 * Version: 1.0
 * Author: Sirius Pro
 * Author URI: https://siriuspro.pl
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Redirect non-admin users to the login page
function maintenance_mode_redirect_redirect() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_redirect( home_url( '/wp-login.php' ) );
        exit;
    }
}

// Display a maintenance message to visitors
function maintenance_mode_redirect_message() {
  $message = 'Sorry, this site is currently undergoing maintenance. Please check back later.';
  wp_die( htmlspecialchars( $message ), 'Maintenance Mode' );
}

function maintenance_mode_redirect_mode() {
    // Get the maintenance mode and times from the options
    $options = get_option('maintenance_mode_options');
    $maintenance_mode = $options['maintenance_mode'];
    $maintenance_start = strtotime($options['maintenance_start']);
    $maintenance_end   = strtotime($options['maintenance_end']);

    // Check if maintenance mode is on and current time is within the maintenance window
    if ($maintenance_mode == 1 && current_time( 'timestamp' ) >= $maintenance_start && current_time( 'timestamp' ) <= $maintenance_end ) {
        add_action( 'template_redirect', 'maintenance_mode_redirect_redirect' );
        add_action( 'wp_loaded', 'maintenance_mode_redirect_check_user' );
    }
}

function maintenance_mode_redirect_check_user() {
    if ( ! current_user_can( 'manage_options' ) ) {
        maintenance_mode_redirect_message();
    }
}

function maintenance_mode_options_page() {
  ?>
  <div class="wrap">
    <h1>Maintenance Mode Redirect</h1>
    <form method="post" action="options.php">
      <?php settings_fields('maintenance_mode_options_group'); ?>
      <?php do_settings_sections('maintenance_mode_options'); ?>
      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

function maintenance_register_settings() {
  // register options with WordPress
  register_setting('maintenance_mode_options_group', 'maintenance_mode_options');

  // add a new section to the options page
  add_settings_section(
    'maintenance_section',
    'Options and settings',
    'maintenance_section_callback',
    'maintenance_mode_options'
  );

  // add a new field to the section
  add_settings_field(
    'maintenance_mode',
    'Maintenance Mode',
    'maintenance_mode_redirect_callback',
    'maintenance_mode_options',
    'maintenance_section'
  );

  // add a new field to the section for the start date
  add_settings_field(
    'maintenance_start',
    'Maintenance Start',
    'maintenance_start_callback',
    'maintenance_mode_options',
    'maintenance_section'
  );

  // add a new field to the section for the end date
  add_settings_field(
    'maintenance_end',
    'Maintenance End',
    'maintenance_end_callback',
    'maintenance_mode_options',
    'maintenance_section'
  );
}

// callback function for the start date field
function maintenance_start_callback() {
  $options = get_option('maintenance_mode_options');
  ?>
  <input type="datetime-local" id="maintenance_start" name="maintenance_mode_options[maintenance_start]" value="<?php echo esc_attr( $options['maintenance_start'] ); ?>">
  <?php
}

// callback function for the end date field
function maintenance_end_callback() {
  $options = get_option('maintenance_mode_options');
  ?>
  <input type="datetime-local" id="maintenance_end" name="maintenance_mode_options[maintenance_end]" value="<?php echo esc_attr( $options['maintenance_end'] ); ?>">
  <?php
}

// callback function for the section
function maintenance_section_callback() {
  echo '<p>Select the options for your maintenance mode.</p>';
}

function maintenance_mode_redirect_callback() {
  $options = get_option('maintenance_mode_options');
  ?>
  <select id="maintenance_mode" name="maintenance_mode_options[maintenance_mode]">
    <option value="0" <?php selected($options['maintenance_mode'], 0); ?>>Off</option>
    <option value="1" <?php selected($options['maintenance_mode'], 1); ?>>On</option>
  </select>
  <?php
}

// add the options page to the WordPress admin menu
function maintenance_add_options_page() {
  add_options_page(
    'Maintenance Mode Redirect',
    'Maintenance Mode Redirect',
    'manage_options',
    'maintenance_mode_options',
    'maintenance_mode_options_page'
  );
}
add_action('admin_menu', 'maintenance_add_options_page');

// register the settings and fields for the options page
add_action('admin_init', 'maintenance_register_settings');

add_action('init', 'maintenance_mode_redirect_mode');