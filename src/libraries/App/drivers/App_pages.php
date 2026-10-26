<?php

/**
 * CI_App_pages Class
 *
 * Main driver responsible for managing static pages.
 *
 * @package     CiSkeleton\Modules
 * @subpackage  Pages\Libraries
 * @category    App\Drivers
 * @author      Kader Bouyakoub <bkader[at]mail[dot]com>
 * @copyright   2018-present, Kader Bouyakoub
 * @license     MIT
 * @link        https://opensource.org/license/mit
 *
 * SPDX-License-Identifier: MIT
 */

final class CI_App_pages extends CI_ObjectEntityDriver
{
	/**
	 * Entity subtype managed by this driver.
	 *
	 * @var string
	 */
	protected $entity_subtype = 'page';

	/**
	 * Additional columns from the `objects` table that are explicitly allowed
	 * for sorting operations.
	 *
	 * These columns are merged with the base entity sortable columns defined
	 * in {@see CI_EntityDriver::SORTABLE}.
	 *
	 * @var string[]
	 */
	protected const SORTABLE_EXTRA = [
		'objects.name',
		'views',
	];

	/**
	 * Additional sortable aliases specific to the current entity driver.
	 *
	 * @var array<string,string>  Key = alias used in filters, Value = actual column name
	 */
	protected $sortable_aliases = [
		'author' => 'entities.owner_id',
		'title' => 'objects.name',
	];

	// --------------------------------------------------------------------

	/**
	 * Called before creating a page.
	 *
	 * Responsibilities:
	 * - Ensures 'username' is always set to 'name' if none provided.
	 * - Ensures 'owner_id' is set to the current user's if not provided.
	 * - Starts page 'views' counter.
	 *
	 * @param  array &$data  Reference to page data being create.
	 * @return array         Modified page data.
	 */
	protected function before_create(array &$data = [])
	{
		// Never use an empty username!
		if (!isset($data['username']) || empty(trim($data['username']))) {
			$data['username'] = $data['name'];
		}

		// User current user as owner if none provided.
		$data['owner_id'] ??= (int) $this->ci->user?->id;

		// Start views count.
		$data['views'] ??= 0;

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Called after a new pas is created to reset pages counter.
	 *
	 * @param  int   $id    Newly created entity ID.
	 * @param  array $data  Data used to create the entity.
	 * @return void
	 */
	protected function after_create(int $id, ?array $data)
	{
		$this->ci->hub->stats->forget('pages');
	}

	// --------------------------------------------------------------------

	/**
	 * Called after pages were permanently removed from database in order
	 * to reset static pages counter.
	 * @param  array $entities  Array of user objects with at lest `id` and `username`.
	 * @param  array $ids       Array of user IDs.
	 * @return void
	 */
	protected function after_remove(array $entities, array $ids)
	{
		$this->ci->hub->stats->forget('pages');
	}

}
