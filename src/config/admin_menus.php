<?php

/**
 * Pages Module - Admin Menus
 *
 * @package     CiSkeleton\Modules
 * @subpackage  Admin\Config
 * @category    Menus
 * @author      Kader Bouyakoub <bkader[at]mail[dot]com>
 * @copyright   2018-present, Kader Bouyakoub
 * @license     MIT
 * @link        https://opensource.org/license/mit
 *
 * SPDX-License-Identifier: MIT
 */

return ['content' => [
	[
		'type' => 'link',
		'title' => 'admin_pages_manage',
		'icon' => 'file',
		'uri' => 'pages',
		'level' => UserLevel::STAFF,
		'order' => 98
	]
]];
