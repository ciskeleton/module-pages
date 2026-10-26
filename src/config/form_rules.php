<?php

/**
 * Form Validation Rules
 *
 * This file contains all pages' form validation rules.
 *
 * @package     CiSkeleton\Modules
 * @subpackage  Pages\Config
 * @category    Validation
 * @author      Kader Bouyakoub <bkader[at]mail[dot]com>
 * @copyright   2018-present, Kader Bouyakoub
 * @license     MIT
 * @link        https://opensource.org/license/mit
 *
 * SPDX-License-Identifier: MIT
 */

$config['admin/pages/add'] = [
	// Page title
	'name' => [
		'field' => 'name',
		'label' => 'lang:title',
		'rules' => [
			'trim',
			'required',
			'min_length[name_min]',
			'max_length[name_max]',
		],
	],

	// Page slug (slug)
	'slug' => [
		'field' => 'slug',
		'label' => 'lang:slug',
		'rules' => [
			'trim',
			'min_length[name_min]',
			'max_length[name_max]',
			'alpha_dash[true]',
			'unique_slug',
			'remote[admin/check?context=slug]',
		],
	],

	// Page content
	'content' => [
		'field' => 'content',
		'label' => 'lang:content',
		'rules' => [
			'trim',
			'required',
			'min_length[5]',
		],
	],

	// Page excerpt (description)
	'description' => [
		'field' => 'description',
		'name_min' => 'lang:excerpt',
		'rules' => [
			'trim',
			'required',
			'max_length[155]',
		],
	],

	// Meta title
	'meta_title' => [
		'field' => 'meta_title',
		'label' => 'lang:meta_title',
		'rules' => [
			'trim',
			'min_length[name_min]',
			'max_length[name_max]',
		],
	],

	// Meta description
	'meta_description' => [
		'field' => 'meta_description',
		'label' => 'lang:meta_description',
		'rules' => [
			'trim',
			'max_length[155]',
		],
	],

	// Status / Enabled
	'status' => [
		'field' => 'status',
		'label' => 'lang:status',
		'rules' => [
			'required',
			'in_list[-1,0,1]',
		],
	],

	// Visibility / Privacy
	'visibility' => [
		'field' => 'visibility',
		'label' => 'lang:visibility',
		'rules' => [
			'required',
			'in_list[2,1,0]',
		]
	],

	// Password
	'password' => [
		'field' => 'password',
		'label' => 'lang:password',
		'rules' => [
			'trim',
			'required_if[visibility=0]',
			'min_length[password_min]',
			'max_length[password_max]',
			'strong_password[password_str]',
			'remote[admin/check?context=password]',
		],
	],
];
