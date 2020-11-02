<?php
/**
 * Plugin Name:       The Events Calendar Extension: Event Progress Bar
 * Plugin URI:        https://theeventscalendar.com/extensions/event-progress-bar/
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-event-progress-bar
 * Description:       This experimental extension adds a progress bar below the event description in list view and day view (V2).
 * Version:           0.1.0
 * Extension Class:   Tribe\Extensions\EventProgressBar\Main
 * Author:            Modern Tribe, Inc.
 * Author URI:        http://m.tri.be/1971
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tribe-ext-event-progress-bar
 *
 *     This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *     GNU General Public License for more details.
 */

namespace Tribe\Extensions\EventProgressBar;

use Tribe__Extension;

/**
 * Define Constants
 */

if ( ! defined( __NAMESPACE__ . '\NS' ) ) {
	define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );
}

// Do not load unless Tribe Common is fully loaded and our class does not yet exist.
if (
	class_exists( 'Tribe__Extension' )
	&& ! class_exists( NS . 'Main' )
) {
	/**
	 * Extension main class, class begins loading on init() function.
	 */
	class Main extends Tribe__Extension {

		/**
		 * Is Events Calendar PRO active. If yes, we will add some extra functionality.
		 *
		 * @return bool
		 */
		public $ecp_active = false;

		/**
		 * Is Event Tickets active. If yes, we will add some extra functionality.
		 *
		 * @return bool
		 */
		public $et_active = false;

		/**
		 * Setup the Extension's properties.
		 *
		 * This always executes even if the required plugins are not present.
		 */
		public function construct() {
			$this->add_required_plugin( 'Tribe__Events__Main', '5.0' );
		}

		/**
		 * Extension initialization and hooks.
		 */
		public function init() {
			// Load plugin textdomain
			load_plugin_textdomain( 'tribe-ext-event-progress-bar', false, basename( dirname( __FILE__ ) ) . '/languages/' );

			if ( ! $this->php_version_check() ) {
				return;
			}

			if ( ! $this->is_using_compatible_view_version() ) {
				return;
			}

			add_action( 'tribe_template_after_include:events/list/event/description', [ $this, 'progressbar' ], 10, 3 );
			add_action( 'tribe_template_after_include:events/day/event/description', [ $this, 'progressbar' ], 10, 3 );
			add_action( 'wp_enqueue_scripts', [ $this, 'safely_add_scripts' ] );
		}

		/**
		 * Compiles the data for the Progress Bar.
		 *
		 * @param $file
		 * @param $name
		 * @param $template
		 */
		public function progressbar( $file, $name, $template ) {

			$event = tribe_get_event( get_the_ID() );

			/**
			 * Leaving here for testing purposes.
			 *
			 * @todo Remove before publishing
			 */
			/*
			echo date('Z' );
			echo '<br>';
			echo strtotime('now');
			echo '<br>';
			echo strtotime( $event->end_date_utc );
			echo '<br>';
			echo strtotime('now') - strtotime( $event->end_date_utc );
			echo '<br>';
			echo $event->end_date_utc;*/

			// Bail if event (start) is not in the past, so event hasn't started yet
			if ( ! $event->is_past ) {
				/* ?>
				<div>Event is coming.</div>
				<?php */

				return;
			}

			/**
			 * Bail if the event has passed.
			 *
			 * `strtotime( 'now' )` will use the server timezone, which should be UTC.
			 */
			if ( strtotime( 'now' ) > strtotime( $event->end_date_utc ) ) {
				/* ?>
				<div>Event has passed.</div>
				<?php */
				return;
			}

			// Creating data for JavaScript
			$start_date = date( 'F j, Y, g:i a', strtotime( $event->start_date_utc ) );
			$end_date   = date( 'F j, Y, g:i a', strtotime( $event->end_date_utc ) );
			?>

			<div class="progress-bar-container progress-bar-container__on tribe-common-b2">
				<div class="progress-bar-container__live" data-start-date="<?php echo $start_date ?>" data-end-date="<?php echo $end_date ?>">
					<?php echo esc_html_x( 'Live now', 'Label of live event before the progress bar.', 'tribe-ext-event-progress-bar' ); ?>
				</div>
				<div class="progress-bar-container__background">
					<div class="progress-bar-container__progressbar"></div>
					<div class="progress-bar-container__ball"></div>
				</div>
				<div class="progress-bar-container__timeleft">
					<?php
					printf(
						// translators: %1$s: The remaining time with markup, %2$s: Closing </span>.
						esc_html_x( '%1$s left.%2$s', 'The remaining time of a live event.', 'tribe-ext-event-progress-bar' ),
						'<span class="progress-bar-container__timeleft-time"></span> <span class="progress-bar-container__timeleft-string">',
						'</span>'
					);
					?>
				</div>
			</div>

			<?php
		}

		/**
		 * Check if we have a sufficient version of PHP. Admin notice if we don't and user should see it.
		 *
		 * @return bool
		 */
		private function php_version_check() {
			$php_required_version = '7.0';

			if ( version_compare( PHP_VERSION, $php_required_version, '<' ) ) {
				if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
					$message = '<p>';
					$message .= sprintf(
						__(
							'%s requires PHP version %s or newer to work. Please contact your website host and inquire about updating PHP.',
							'tribe-ext-event-progress-bar'
						),
						$this->get_name(),
						$php_required_version
					);
					$message .= sprintf( ' <a href="%1$s">%1$s</a>', 'https://wordpress.org/about/requirements/' );
					$message .= '</p>';
					tribe_notice( 'tribe-ext-event-progress-bar' . '-php-version', $message, [ 'type' => 'error' ] );
				}

				return false;
			}

			return true;
		}

		/**
		 * Add scripts to the page
		 */
		function safely_add_scripts() {
			wp_enqueue_script( 'tribe-ext-event-progress-bar-script', plugins_url( 'src/event-progress-bar.js', __FILE__ ) );
			wp_enqueue_style( 'tribe-ext-event-progress-bar-style', plugins_url( 'src/style.css', __FILE__ ) );
		}

		/**
		 * Check if we have the required TEC view. Admin notice if we don't and user should see it.
		 *
		 * @return bool
		 */
		private function is_using_compatible_view_version() {
			$view_required_version = 2;

			$meets_req = true;

			// Is V2 enabled?
			if ( function_exists( 'tribe_events_views_v2_is_enabled' ) && ! empty( tribe_events_views_v2_is_enabled() ) ) {
				$is_v2 = true;
			} else {
				$is_v2 = false;
			}

			// V1 compatibility check.
			if ( 1 === $view_required_version && $is_v2 ) {
				$meets_req = false;
			}

			// V2 compatibility check.
			if ( 2 === $view_required_version && ! $is_v2 ) {
				$meets_req = false;
			}

			// Notice, if should be shown.
			if ( ! $meets_req && is_admin() && current_user_can( 'activate_plugins' ) ) {
				if ( 1 === $view_required_version ) {
					$view_name = _x( 'Legacy Views', 'name of view', 'tribe-ext-event-progress-bar' );
				} else {
					$view_name = _x( 'Updated (V2) Views', 'name of view', 'tribe-ext-event-progress-bar' );
				}

				$view_name = sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'edit.php?page=tribe-common&tab=display&post_type=tribe_events' ) ),
					$view_name
				);

				// Translators: 1: Extension plugin name, 2: Name of required view, linked to Display tab.
				$message = sprintf(
					__(
						'%1$s requires the "%2$s" so this extension\'s code will not run until this requirement is met. You may want to deactivate this extension or visit its homepage to see if there are any updates available.',
						'tribe-ext-event-progress-bar'
					),
					$this->get_name(),
					$view_name
				);

				tribe_notice(
					'tribe-ext-event-progress-bar-view-mismatch',
					'<p>' . $message . '</p>',
					[ 'type' => 'error' ]
				);
			}

			return $meets_req;
		}

	}
}

