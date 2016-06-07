<?php
/**
 * Plugin Name: BadgeOS Achievement Shortcode Add-On
 * Plugin URI: https://wordpress.org/plugins/achievement-shortcode-add-on-for-badgeos/
 * Description: This BadgeOS Add-on adds a shortcode to show or hide content depending on if the user has earned a specific achievement
 * Tags: badgeos, restrict, shortcode
 * Author: konnektiv
 * Version: 1.0.1
 * Author URI: https://konnektiv.de/
 * License: GNU AGPL
 * Text Domain: achievement-shortcode-add-on-for-badgeos
 */
/*
 * Copyright © 2012-2016 LearningTimes, LLC; Konnektiv
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General
 * Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>;.
*/

class BadgeOS_Achievement_Shortcode {

	function __construct() {

		// Define plugin constants
		$this->basename       = plugin_basename( __FILE__ );
		$this->directory_path = plugin_dir_path( __FILE__ );
		$this->directory_url  = plugin_dir_url(  __FILE__ );

		// Load translations
		load_plugin_textdomain( 'achievement-shortcode-add-on-for-badgeos', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// If BadgeOS is unavailable, deactivate our plugin
		add_action( 'admin_notices', array( $this, 'maybe_disable_plugin' ) );

		add_action( 'init', array( $this, 'register_badgeos_shortcodes' ) );
	}

	public function register_badgeos_shortcodes() {
		badgeos_register_shortcode( array(
			'name'            => __( 'User earned achievement', 'achievement-shortcode-add-on-for-badgeos' ),
			'slug'            => 'user_earned_achievement',
			'description'     => __( 'Show or hide content depending on if the user has earned a specific achievement.', 'achievement-shortcode-add-on-for-badgeos' ),
			'output_callback' => array( $this, 'shortcode' ),
			'attributes'      => array(
				'id' => array(
					'name'        => __( 'Achievement ID', 'achievement-shortcode-add-on-for-badgeos' ),
					'description' => __( 'The ID of the achievement the user must have earned.', 'achievement-shortcode-add-on-for-badgeos' ),
					'type'        => 'text',
				),
			),
		) );
	}

	public function shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'id' => false,    // achievement
		), $atts );

		$achievement = $atts['id'];

		$user_id = get_current_user_id();

		$user_has_achievement = $user_id &&
		                        badgeos_has_user_earned_achievement( intval( $achievement ), $user_id );

		$return = '';

		if ( ! $achievement ) {
			$return = '<div class="error">' . __( 'You have to specify a valid achievement id in the "id" parameter!', 'achievement-shortcode-add-on-for-badgeos' ) . '</div>';
		} elseif ( $user_has_achievement ) {
			$return = do_shortcode( $content );
		}

		return $return;
	}

	/**
	 * Check if BadgeOS is available
	 *
	 * @since  1.0.0
	 * @return bool True if BadgeOS is available, false otherwise
	 */
	public static function meets_requirements() {

		if ( class_exists( 'BadgeOS' ) && version_compare( BadgeOS::$version, '1.4.0', '>=' ) ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Generate a custom error message and deactivates the plugin if we don't meet requirements
	 *
	 * @since 1.0.0
	 */
	public function maybe_disable_plugin() {
		if ( ! $this->meets_requirements() ) {
			// Display our error
			echo '<div id="message" class="error">';
			echo '<p>' . sprintf( __( 'BadgeOS Achievement Shortcode Add-On requires BadgeOS 1.4.0 or greater and has been <a href="%s">deactivated</a>. Please install and activate BadgeOS and then reactivate this plugin.', 'achievement-shortcode-add-on-for-badgeos' ), admin_url( 'plugins.php' ) ) . '</p>';
			echo '</div>';

			// Deactivate our plugin
			deactivate_plugins( $this->basename );
		}
	}

}

new BadgeOS_Achievement_Shortcode();
