<?php

/**
 * Module Bootstrap File
 *
 * Registers module hooks for runtime bootstrapping, activation,
 * and deactivation.
 *
 * @package     CiSkeleton\Modules
 * @subpackage  Pages
 * @category    Bootstrap
 * @author      Kader Bouyakoub <bkader[at]mail[dot]com>
 * @copyright   2018-present, Kader Bouyakoub
 * @license     MIT
 * @link        https://opensource.org/license/mit
 *
 * SPDX-License-Identifier: MIT
 */

once_action('module_boot_pages', static function () {

	/**
	 * Registers `App_pages` driver.
	 *
	 * @param  array  $drivers  Empty array of drivers to be filled.
	 * @param  object $ci       CI super-object instance.
	 * @param  object $app      CI_App driver object instance.
	 */
	once_filter('app_drivers', static function ($drivers, $ci, $app) {
		$drivers[] = 'pages';
		return $drivers;
	});

	// ----------------------------------------------------------------

	/**
	 * Registers pages as entities to be added to sitemap.
	 *
	 * @param  array   $subtypes
	 * @param  object  $ci
	 * @return void
	 */
	once_filter('sitemap', static function ($subtypes) {
		$subtypes[] = 'page';
		return $subtypes;
	});

	// ----------------------------------------------------------------

	/**
	 * Adds number of created pages as a stat box only for admins
	 * and on dashboard root page.
	 *
	 * @param  bool   $root  Whether the current page is dashboard's root.
	 * @param  object $ci    CI super-object instance.
	 * @return void
	 */
	once_action('for_dashboard', static function ($root, $ci) {
		// Requires at least staff+ and root page.
		if (!$ci->user?->in_dashboard(UserLevel::STAFF) || !$root) {
			return;
		}

		// Remember pages count.
		$count = $ci->hub->stats->remember('pages', [$ci->app->pages, 'count']);

		// Add stats box.
		$ci->hub->admin->add_stat(
			$count,
			'admin_pages',
			'pages',
			'file',
			'lavender',
			10
		);
	});

});
