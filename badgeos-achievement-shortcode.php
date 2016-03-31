<?php
/**
 * Plugin Name: BadgeOS Achievement Shortcode
 * Plugin URI: http://www.konnektiv.de/
 * Description: Adds a shortcode to show or hide content depeneding on if the user has earned a specific achievement
 * Tags: buddypress
 * Author: konnektiv
 * Version: 0.0.1
 * Author URI: https://konnektiv.de/
 * License: GNU AGPL
 * Text Domain: badgeos-activity-progress
 */

/*
 * Copyright © 2012-2013 LearningTimes, LLC
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
		$this->directory_url  = plugins_url( 'badgeos-activity-progress/' );

		// Load translations
		load_plugin_textdomain( 'badgeos-achievement-shortcode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


		// If BadgeOS is unavailable, deactivate our plugin
		add_action( 'admin_notices', array( $this, 'maybe_disable_plugin' ) );

		add_shortcode( 'user_earned_achievement', array( $this, 'shortcode' ) );
	}

	public function shortcode($atts, $content = null) {
		 $atts = shortcode_atts( array(
            'achievement'	=> false,    // achievement
        ), $atts );

		$achievement = $atts['achievement'];

		$user_id = get_current_user_id();

		$user_has_achievement = $user_id &&
			badgeos_has_user_earned_achievement( intval($achievement), $user_id );

		$return = '';

		if (!$achievement) {
			$return = '<div class="error">' . __('You have to specify a valid achievement id in the "achievement" parameter!','badgeos-achievement-shortcode') . '</div>';
		} elseif ($user_has_achievement) {
			$return = do_shortcode($content);
		}

		return $return;
	}

	/**
	 * Check if BadgeOS is available
	 *
	 * @since  0.0.1
	 * @return bool True if BadgeOS is available, false otherwise
	 */
	public static function meets_requirements() {

		if ( class_exists('BadgeOS') && version_compare( BadgeOS::$version, '1.4.0', '>=' ) ) {
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
			echo '<p>' . sprintf( __( 'BadgeOS Achievement Shortcode Add-On requires BadgeOS 1.4.0 or greater and has been <a href="%s">deactivated</a>. Please install and activate BadgeOS and then reactivate this plugin.', 'badgeos-achievement-shortcode' ), admin_url( 'plugins.php' ) ) . '</p>';
			echo '</div>';

			// Deactivate our plugin
			deactivate_plugins( $this->basename );
		}
	}

}
new BadgeOS_Achievement_Shortcode();