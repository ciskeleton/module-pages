<?php

/**
 * Pages — Public Controller
 *
 * Displays static pages on the front-end.
 *
 * @package    CiSkeleton\Modules
 * @category   Controllers
 * @author     Kader Bouyakoub <bkader[at]mail[dot]com>
 * @copyright   2018-present, Kader Bouyakoub
 * @license     MIT
 * @link        https://opensource.org/license/mit
 *
 * SPDX-License-Identifier: MIT
 */

class Pages extends Public_Controller
{
	/**
	 * Layout file to use for method that have output render.
	 *
	 * @var string|null
	 */
	protected $layout = 'default';

	/**
	 * index
	 *
	 * Display the selected page.
	 *
	 * @param 	string 	$slug 	Page's slug or username.
	 * @return 	void
	 */
	public function index($slug = null)
	{
		$slug = trim($slug ?? '');
		if (($username = sanitize_username($slug, true)) === '') {
			redirect('');
		}

		// Prepare WHERE clause for retrieving page.
		$where = ['entities.username' => $username];

		// Case 1: non-authenticated users can only see public, enabled,
		// and non-soft-deleted pages.
		if (!$this->user) {
			$where['entities.privacy'] = 2; // public
			$where['entities.enabled'] = 1; // enabled
			$where['entities.deleted'] = 0; // not soft-deleted

		// Case 2: authenticated users below staff can only see private+,
		// enabled, a non-soft-deleted pages.
		} elseif (!$this->user->is_level(UserLevel::STAFF)) {
			$where['entities.privacy >='] = 1; // private or public
			$where['entities.enabled'] = 1;    // enabled
			$where['entities.deleted'] = 0;    // not soft-deleted

		}

		// Attempt to retrieve page and make sure it exists.
		if (!($page = $this->app->pages->get_by($where))) {
			redirect('');
		}

		// Redirect to proper URI.
		if ($username !== $slug) {
			redirect('page/'.$board->username);
		}

		// Only increment views for pages that are enabled, not soft-deleted,
		// and not when the owner is visiting it.
		if (!CI_OFFLINE && $page->enabled === 1 && !$page->is_deleted && $page->owner_id !== $this->user?->id) {
			$page->incr('views');
		}

		// Pass info to data and render view.
		$this->data['page'] = $page;

		// Set cache, meta, and schema.
		$this->hub->theme
			// ->set_cache(CI_LIVE ? 5 : 0)
			->set_meta($page)
			->set_schema('WebPage', $page->raw());

		// Render view.
		$this->render($page->name);
	}

}
