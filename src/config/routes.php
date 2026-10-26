<?php

/**
 * Pages Module - Routes
 *
 * Registers all required `pages` module routes.
 *
 * @package    CiSkeleton
 * @category   Modules\Controllers
 * @author     Kader Bouyakoub <bkader[at]mail[dot]com>
 * @copyright   2018-present, Kader Bouyakoub
 * @license     MIT
 * @link        https://opensource.org/license/mit
 *
 * SPDX-License-Identifier: MIT
 */

// Front-end routes
Route::get('page', 'pages/pages/index', 'page', static function () {
	Route::any(Route::METHOD, 'pages/pages/index/$1');
});
