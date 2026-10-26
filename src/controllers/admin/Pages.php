<?php

/**
 * Pages — Admin Controller
 *
 * Allows users to manage static pages.
 *
 * @package     CiSkeleton\Modules
 * @subpackage  Pages\Admin
 * @category    Controllers
 * @author      Kader Bouyakoub <bkader[at]mail[dot]com>
 * @copyright   2018-present, Kader Bouyakoub
 * @license     MIT
 * @link        https://opensource.org/license/mit
 *
 * SPDX-License-Identifier: MIT
 */

final class Pages extends Staff_Controller
{
	/**
	 * List of methods and the required user level to perform them.
	 *
	 * @var array
	 */
	protected $action_level = [
		'delete'  => UserLevel::STAFF,
		'empty'   => UserLevel::STAFF,
		'restore' => UserLevel::STAFF,
		'remove'  => UserLevel::ADMIN,
	];

	/**
	 * Maps method names to redirect URIs used when access is denied.
	 * GET requests are allowed; other request methods are restricted.
	 *
	 * @var array<string, string>
	 */
	protected $demo_protected = ['*' => 'admin/pages'];

	/**
	 * Language files to automatically load on controller initialization.
	 *
	 * @var string|string[]|null
	 */
	protected $lang_files = 'admin/pages';

	/**
	 * Page title used in the admin header.
	 *
	 * @var string
	 */
	protected $page_title = 'admin_pages_manage';

	/**
	 * Page icon used in the admin header.
	 *
	 * @var string
	 */
	protected $page_icon = 'file';

	/**
	 * List of GET arguments that are considered when filtering pages.
	 *
	 * @var string[]
	 */
	private $filter_keys = [
		'author',
		'status',
		'visibility',
		'sort',
		'order',
		'q',
	];

	/**
	 * List of filters currently being applied.
	 *
	 * @var array|null
	 */
	private $filters;

	/**
	 * User ID used for filtering by page author.
	 *
	 * @var int|null
	 */
	private $owner_id;

	// --------------------------------------------------------------------

	/**
	 * Method executed before the requested controller method.
	 *
	 * @param  string $name     Controller method being dispatched.
	 * @param  mixed  ...$args  Parameters passed to the target controller method.
	 * @return mixed|null
	 */
	protected function before($name, ...$args)
	{
		// Index page → list and filter pages.
		if ($name === 'index') {
			// Collect filters.
			$this->filters = array_assoc_clean(array_map(
				'sanitize_key',
				$this->input->get($this->filter_keys, true)
			));

			$this->owner_id = (int) ($this->filters['author'] ?? null);

			// Queue search widget.
			$this->hub->theme->add_widget('admin/search', [
				'action'      => 'pages',
				'placeholder' => 'admin_pages_search',
				'term'        => $this->filters['q'] ?? '',
			]);

		// Add → add a new stat page.
		} elseif ($name === 'add') {
			// Queue assets we need for this page.
			$this->hub->assets
				// ->garlic()
				->summernote(true);

			// Set page title and icon:
			$this->page_title = $this->lang->line('admin_pages_add');
			$this->page_icon = 'file-circle-plus';

		// Edit → edit or translate an existing page.
		} elseif ($name === 'edit') {
			// Sanity check for page ID and language.
			[$id, $idiom] = array_pad($args, 2, null);

			// Make sure the page exists and is not deleted.
			$page = $this->app->pages
				->with_meta('meta_title', 'meta_description', 'password')
				->get_by_id((int) $id);

			if (!$page) {
				redirect('admin/pages');
			}

			/**
			 * If the page is being translated, we have two options:
			 * 1. Non-existing language → redirect back to pages list.
			 * 2. Same language as the page → redirect to regular edit page.
			 */
			if (!empty($idiom)) {
				// Non-existing language:
				if (!$this->lang->exists($idiom)) {
					redirect('admin/pages');
				}

				// Same language as the page's:
				if ($idiom === $page->language) {
					redirect('admin/pages/edit/'.$id);
				}
			}

			// Translate page if needed then store it with language in data
			empty($idiom) || $page->translate($idiom);
			$this->data['page'] = $page;
			$this->data['idiom'] = $idiom;

			// Queue assets we need for for this page.
			$this->hub->assets
				// ->garlic()
				->summernote(true);

			// Set page title and icon for translation.
			if (!empty($idiom)) {
				$this->page_title = sprintf(
					'%s: %s (%s)',
					$this->lang->line('translate'),
					$page->name,
					$this->lang->line('lang_'.$idiom)
				);
				$this->page_icon = 'language';
			// Set page title for regular page edit.
			} else {
				$this->page_title = sprintf('%s: %s', $this->lang->line('admin_pages_edit'), $page->name);
				$this->page_icon = 'file-pen';
			}
		}

		return parent::before($name, ...$args);
	}

	// --------------------------------------------------------------------

	/**
	 * Lists all created page.
	 *
	 * @return void
	 */
	public function index()
	{
		// Prepare sort order direction.
		$orderby = $this->app->pages->resolve_order(
			$this->filters['sort'] ?? null,
			$this->filters['order'] ?? null
		);

		// Searching pages?
		if (!empty($term = $this->filters['q'] ?? null)) {
			// Prepare pagination limit and offset.
			[$limit, $offset] = $this->hub->ui->paginate(
				'admin/pages',
				$this->app->pages->count_filtered($term)
			);

			// Retrieve all filtered pages.
			$this->data['pages'] = $this->app->pages
				->with_meta('views')
				->search($term, $limit, $offset, null, $orderby);

		// Other filters...
		} else {
			// Filtering by author, aka owner_id?
			if ($this->owner_id) {
				$where['entities.owner_id'] = $this->owner_id;
			}

			// Filtering by visibility.
			if (!empty($visibility = sanitize_key($this->filters['visibility'] ?? ''))) {
				$where['entities.privacy'] = match ($visibility) {
					'public'    => 2,
					'private'   => 1,
					'protected' => 0,
					default     => null,
				};
			}

			// Filtering by status.
			if (!empty($status = sanitize_key($this->filters['status'] ?? ''))) {
				if ($status === 'trash' || $status === 'deleted') {
					$where['entities.deleted !='] = 0;

				} else {
					$where['entities.enabled'] = match ($status) {
						'draft'     => -1,
						'pending'   => 0,
						'published' => 1,
						default     => null,
					};
					$where['entities.deleted'] = 0;

				}
			}

			// Prepare pagination limit and offset.
			$where = ($where ?? null) ? array_assoc_clean($where) : null;

			[$limit, $offset] = $this->hub->ui->paginate(
				'admin/pages',
				$this->app->pages->count($where)
			);

			$this->data['pages'] = $this->app->pages
				->with_meta('views')
				->get_many($where, null, $limit, $offset, $orderby);
		}

		$this->render();
	}

	// --------------------------------------------------------------------

	/**
	 * Creates new pages.
	 *
	 * @return void
	 */
	public function add()
	{
		// Prepare form's validation rules.
		$form = $this->hub->form->prep('admin/pages/add', '#add-page');

		// Before form is submitted.
		if ($this->form_validation->run() === false) {
			// Prepare form inputs and CSRF protect.
			$this->prepare_inputs($form);
			$form->protect('pages-add', true);

			// Render view.
			$this->render();

		// CSRF nonce check.
		} elseif (!service('nonce')->verify_request('pages-add')) {
			$this->hub->redirect
				->with('error_csrf', 'error')
				->to('admin/pages/add');

		// Validation passed.
		} else {
			/**
			 * We make sure to collect everything then create the page.
			 * After that's done, we redirect the user to the edit page.
			 */
			$post_data = array_assoc_clean([
				'username'         => $this->input->post('slug', true),
				'enabled'          => (int) $this->input->post('status', true),
				'privacy'          => (int) $this->input->post('visibility', true),
				'name'             => $this->input->post('name', true),
				'description'      => $this->input->post('description', true),
				'content'          => html_purify($this->input->post('content', false)),
				'meta_title'       => $this->input->post('meta_title', true),
				'meta_description' => $this->input->post('meta_description', true),
				'password'         => $this->input->post('password', true),
			]);

			// Create then redirect if successful.
			if (!($guid = $this->app->pages->create($post_data))) {
				$this->hub->redirect
					->with('admin_pages_add_error', 'error')
					->to('admin/pages/add');
			}

			$this->hub->redirect
				->with('admin_pages_add_success', 'success')
				->to('admin/pages#page-'.$guid);

		}
	}

	// --------------------------------------------------------------------

	/**
	 * Edits the given page.
	 *
	 * @param  int    $id     Page ID.
	 * @param  string $idiom  Language to translate page to.
	 * @return void
	 */
	public function edit($id = 0, $idiom = null)
	{
		$page = $this->data['page'];

		// Case 1: Change 'slug' validation behavior when editing a page.
		if (empty($idiom = $this->data['idiom'])) {
			$form = $this->hub->form->with_rule('slug', [
				'trim',
				'required',
				'alpha_dash[true]',
				'min_length[name_min]',
				'max_length[name_max]',
				"unique_slug[{$page->id}]",
				"remote[admin/check?context=slug&except={$page->id}]",
			]);

		// Case 2: Remove some fields when translating a page.
		} else {
			$form = $this->hub->form->without_fields(
				'slug',
				'status',
				'visibility',
				'password'
			);
		}

		// Prepare form validation rules.
		$form->prep('admin/pages/add', '#edit-page');

		// Before form is submitted.
		if ($this->form_validation->run() === false) {
			// Prepare form inputs and CSRF protect it.
			$this->prepare_inputs($form, $page, $idiom);
			$form->protect('pages-edit_'.$page->id, true);

			// Render view.
			$this->render();

		// CSRF nonce check.
		} elseif (!service('nonce')->verify_request('pages-edit_'.$page->id)) {
			$this->hub->redirect
				->with('error_csrf', 'error')
				->to(rtrim("admin/pages/edit/{$page->id}/".($idiom ?? '').'/', '/'));

		// Validation passed.
		} else {
			/**
			 * We make sure to collect everything then create the page.
			 * After that's done, we redirect the user to the edit page.
			 */
			$post_data = [
				'name'             => $this->input->post('name', true),
				'description'      => $this->input->post('description', true),
				'content'          => html_purify($this->input->post('content', false)),
				'meta_title'       => $this->input->post('meta_title', true),
				'meta_description' => $this->input->post('meta_description', true),
			];

			// Collect other inputs when NOT translating.
			if (empty($idiom)) {
				$post_data['username'] = $this->input->post('slug', true);
				$post_data['enabled']  = (int) $this->input->post('status', true);
				$post_data['privacy']  = (int) $this->input->post('visibility', true);
				$post_data['password'] = $this->input->post('password', true);
			}

			// Remove anything that didn't change.
			$post_data = array_assoc_clean($post_data);
			$post_data = $page->clean_input(clean_ignored_fields($post_data));

			// Empty post data? fake success.
			if (empty($post_data)) {
				$this->hub->redirect
					->with('admin_pages_edit_success', 'success')
					->to('admin/pages');
			}

			// Translating?
			if (!empty($idiom)) {
				if (!$this->app->pages->save_translations($page->id, $idiom, $post_data)) {
					$this->hub->redirect
						->with('admin_pages_edit_error', 'error')
						->to('admin/pages');
				}

				$this->hub->redirect
					->with('admin_pages_edit_success', 'success')
					->to('admin/pages');
			}

			// Page successfully updated?
			if ($this->app->pages->update($page->id, $post_data)) {
				if (!empty($medias = $this->input->post('medias', true))) {
					$this->hub->files->attach($medias, $page->id);
				}

				$this->hub->redirect
					->with('admin_pages_edit_success', 'success')
					->to('admin/pages');
			}

			$this->hub->redirect
				->with('admin_pages_edit_error', 'error')
				->to('admin/pages');

		}
	}

	// --------------------------------------------------------------------
	// POST METHOD
	// --------------------------------------------------------------------

	/**
	 * Handles POST actions.
	 *
	 * @param  string $action  Action being performed.
	 * @return void
	 */
	public function ajax_action($action = null)
	{
		// Unknown action.
		if (empty($action) || !isset($this->action_level[$action])) {
			$this->json_error($this->lang->line('action_error'));
			return;

		// Unauthorized.
		} elseif (!$this->can($action)) {
			$this->json_error([
				'type'    => 'warning',
				'message' => $this->lang->line('permission_error_action'),
			]);
			return;
		}

		$id = (int) $this->input->post('id', true);

		// Security check
		if (!service('nonce')->verify_request("pages-{$action}_{$id}")) {
			$this->json_error($this->lang->line('error_csrf'));
			return;
		}

		// Empty translation?
		if ($action === 'empty') {
			$idiom = sanitize_key($this->input->post('idiom', true));

			if (empty($idiom)) {
				$this->json_error($this->lang->line('action_error'));
				return;
			}

			$emptied = $this->app->pages->delete_translations($id, $idiom);

			if (!$emptied) {
				$this->json_error($this->lang->line('admin_pages_empty_error'));
				return;
			}

			$this->json_success(
				$this->lang->line('admin_pages_empty_success'),
				$this->url->admin('pages'),
				2
			);

		} elseif (!$this->app->pages->$action($id)) {
			$this->json_error($this->lang->line("admin_pages_{$action}_error"));

		} else {
			$this->json_success($this->lang->line("admin_pages_{$action}_success"));

		}
	}

	// --------------------------------------------------------------------

	/**
	 * Things to display on the left-hand side of the sub-menu (index).
	 *
	 * @return void
	 */
	protected function _submenu_index()
	{
		// Add user button.
		$html = ui_anchor(
			$this->lang->line('admin_pages_add'),
			$this->url->admin('pages/add'),
			'success btn-sm',
			[
				'aria-label' => sprintf(
					'%s: %s',
					$this->lang->line('admin_pages'),
					$this->lang->line('admin_pages_add')
				),
			],
			'plus-circle'
		);

		if ($this->owner_id) {
			$html .= $this->back_button(
				remove_query_arg('author', 'pages'),
				$this->lang->line('all'),
				null,
				'sliders'
			);
		}

		echo $html;
	}

	// --------------------------------------------------------------------

	/**
	 * Renders filter buttons in the middle of sub-menu.
	 *
	 * @return void
	 */
	protected function _submenu_center_index()
	{
		// Collect stats:
		$stats = $this->app->pages->get_counts([
			// Status
			'draft'     => ['entities.enabled' => -1, 'entities.deleted' => 0],
			'pending'   => ['entities.enabled' => 0, 'entities.deleted' => 0],
			'published' => ['entities.enabled' => 1, 'entities.deleted' => 0],
			'deleted'   => ['entities.deleted !=' => 0],

			// Visibility:
			'public'    => ['entities.privacy' => 2, 'entities.deleted' => 0],
			'private'   => ['entities.privacy' => 1, 'entities.deleted' => 0],
			'protected' => ['entities.privacy' => 0, 'entities.deleted' => 0],

		], array_filter(['entities.owner_id' => $this->owner_id]));

		// Current status and visibility.
		$status = $this->filters['status'] ?? null;
		$status !== 'trash' || $status = 'deleted';
		$visibility = $this->filters['visibility'] ?? null;

		// Status Filters:
		$html = sprintf(
			'<div class="btn-group btn-group-sm" role="group" aria-label="%s — %s: %s">',
			$this->lang->line('admin_pages'),
			$this->lang->line('filter'),
			$this->lang->line('status')
		);

		// Draft.
		$html .= ui_anchor(
			$this->lang->line('draft').' ('.$stats['draft'].')',
			$this->url->admin(toggle_query_arg('status', 'draft', 'pages')),
			'outline-secondary'.($status === 'draft' ? ' active' : ''),
			[
				'aria-current' => $status === 'draft' ? 'page' : null,
				'aria-label'   => sprintf(
					'%s — %s: %s (%s)',
					$this->lang->line('admin_pages'),
					$this->lang->line('status'),
					$this->lang->line('draft'),
					$stats['draft']
				),
			],
			'pen-to-square'
		);

		// Pending.
		$html .= ui_anchor(
			$this->lang->line('pending').' ('.$stats['pending'].')',
			$this->url->admin(toggle_query_arg('status', 'pending', 'pages')),
			'outline-warning'.($status === 'pending' ? ' active' : ''),
			[
				'aria-current' => $status === 'pending' ? 'page' : null,
				'aria-label'   => sprintf(
					'%s — %s: %s (%s)',
					$this->lang->line('admin_pages'),
					$this->lang->line('status'),
					$this->lang->line('pending'),
					$stats['pending']
				),
			],
			'clock'
		);

		// Published.
		$html .= ui_anchor(
			$this->lang->line('published').' ('.$stats['published'].')',
			$this->url->admin(toggle_query_arg('status', 'published', 'pages')),
			'outline-success'.($status === 'published' ? ' active' : ''),
			[
				'aria-current' => $status === 'published' ? 'page' : null,
				'aria-label'   => sprintf(
					'%s — %s: %s (%s)',
					$this->lang->line('admin_pages'),
					$this->lang->line('status'),
					$this->lang->line('published'),
					$stats['published']
				),
			],
			'paper-plane'
		);

		// Deleted.
		$html .= ui_anchor(
			$this->lang->line('deleted').' ('.$stats['deleted'].')',
			$this->url->admin(toggle_query_arg('status', 'deleted', 'pages')),
			'outline-danger'.($status === 'deleted' ? ' active' : ''),
			[
				'aria-current' => $status === 'deleted' ? 'page' : null,
				'aria-label'   => sprintf(
					'%s — %s: %s (%s)',
					$this->lang->line('admin_pages'),
					$this->lang->line('status'),
					$this->lang->line('deleted'),
					$stats['deleted']
				),
			],
			'trash'
		);

		$html .= '</div>';

		// Visibility Filters:
		$html .= sprintf(
			'<div class="btn-group btn-group-sm" role="group" aria-label="%s — %s: %s">',
			$this->lang->line('admin_pages'),
			$this->lang->line('filter'),
			$this->lang->line('visibility')
		);

		// Public.
		$html .= ui_anchor(
			$this->lang->line('public').' ('.$stats['public'].')',
			$this->url->admin(toggle_query_arg('visibility', 'public', 'pages')),
			'outline-primary'.($visibility === 'public' ? ' active' : ''),
			[
				'aria-current' => $visibility === 'public' ? 'page' : null,
				'aria-label'   => sprintf(
					'%s — %s: %s (%s)',
					$this->lang->line('admin_pages'),
					$this->lang->line('visibility'),
					$this->lang->line('public'),
					$stats['public']
				),
			],
			'globe'
		);

		// Private.
		$html .= ui_anchor(
			$this->lang->line('private').' ('.$stats['private'].')',
			$this->url->admin(toggle_query_arg('visibility', 'private', 'pages')),
			'outline-dark'.($visibility === 'private' ? ' active' : ''),
			[
				'aria-current' => $visibility === 'private' ? 'page' : null,
				'aria-label'   => sprintf(
					'%s — %s: %s (%s)',
					$this->lang->line('admin_pages'),
					$this->lang->line('visibility'),
					$this->lang->line('private'),
					$stats['private']
				),
			],
			'lock'
		);

		// Password protected.
		$html .= ui_anchor(
			$this->lang->line('protected').' ('.$stats['protected'].')',
			$this->url->admin(toggle_query_arg('visibility', 'protected', 'pages')),
			'outline-info'.($visibility === 'protected' ? ' active' : ''),
			[
				'aria-current' => $visibility === 'protected' ? 'page' : null,
				'aria-label'   => sprintf(
					'%s — %s: %s (%s)',
					$this->lang->line('admin_pages'),
					$this->lang->line('visibility'),
					$this->lang->line('protected'),
					$stats['protected']
				),
			],
			'key'
		);

		$html .= '</div>';

		echo $html;
	}

	// --------------------------------------------------------------------

	/**
	 * Displays per page selector and bulk delete button on the right-side
	 * of the dashboard sub-menu.
	 *
	 * @return void
	 */
	protected function _submenu_end_index()
	{
		// Per page dropdown selector.
		echo $this->per_page_selector('pages');
	}

	// --------------------------------------------------------------------

	/**
	 * Things to display on the left side of the sub-menu (add).
	 *
	 * @return void
	 */
	protected function _submenu_add()
	{
		echo $this->back_button('pages');
	}

	// --------------------------------------------------------------------

	/**
	 * Things to display on the left-hand side of the sub-menu (edit).
	 *
	 * @return void
	 */
	protected function _submenu_edit()
	{
		// Back button.
		echo $this->back_button('pages');
	}

	// --------------------------------------------------------------------

	/**
	 * Displays Remove button on the right-hand side of the sub-menu.
	 *
	 * @return void
	 */
	protected function _submenu_end_edit()
	{
		$page = $this->data['page'];
		$idiom = $this->data['idiom'];

		// Only remove button for editing pages.
		if (empty($idiom)) {
			echo ui_button(
				$this->lang->line('remove'),
				'danger btn-sm',
				[
					'aria-label'   => sprintf(
						'%s — %s: %s',
						$this->lang->line('admin_pages'),
						$this->lang->line('remove'), $page->name
					),
					'ajaxify'      => $this->url->nonce('admin/pages/action/remove', "pages-remove_{$page->id}"),
					'data-confirm' => 'lang:pages.remove',
					'data-fields'  => 'id:'.$page->id,
				],
				'trash'
			);

			return;
		}

		// Edit original page button.
		$html = ui_anchor(
			$this->lang->line('edit_original'),
			$this->url->admin('pages/edit/'.$page->id),
			'outline-primary btn-sm',
			[
				'aria-label' => sprintf(
					'%s — %s: %s',
					$this->lang->line('admin_pages'),
					$this->lang->line('edit_original'),
					$page->name
				),
			],
			'file-pen'
		);

		// Remove translation.
		$html .= ui_button(
			$this->lang->line('delete'),
			'outline-danger btn-sm',
			[
				'aria-label'   => sprintf(
					'%s — %s: %s',
					$this->lang->line('admin_pages'),
					$this->lang->line('delete'),
					$this->lang->line('lang_'.$idiom)
				),
				'ajaxify'      => $this->url->nonce('admin/pages/action/empty', "pages-empty_{$page->id}"),
				'data-confirm' => 'lang:pages.empty',
				'data-fields'  => "id:{$page->id};idiom:{$idiom}",
			],
			'trash-can'
		);

		echo $html;
	}

	// --------------------------------------------------------------------

	/**
	 * Extend the global `window.csk` JavaScript payload.
	 *
	 * This method allows controllers to inject page-specific data into
	 * the shared JS namespace before it is rendered.
	 *
	 * It is executed after framework-level extensions.
	 *
	 * Usage examples:
	 * - Add translations: $csk['i18n']['section'] = [...]
	 * - Add page data:     $csk['page'] = [...]
	 * - Add flags/config:  $csk['feature'] = true
	 *
	 * Rules:
	 * - MUST NOT output HTML or <script> tags
	 * - MUST ONLY modify the `$csk` array
	 * - SHOULD remain lightweight (no heavy processing)
	 *
	 * @param array  &$csk    Reference to the global JS payload
	 * @param string $method  Current controller method
	 * @return void
	 */
	protected function __pre_head_section(&$csk, $method = null)
	{
		$method ??= $this->router->method;

		if ($method === 'index') {
			$csk['i18n']['pages'] = [
				'delete'  => $this->lang->line('admin_pages_delete_confirm'),
				'remove'  => $this->lang->line('admin_pages_remove_confirm'),
				'restore' => $this->lang->line('admin_pages_restore_confirm'),
			];
		} elseif ($method === 'edit') {
			$csk['i18n']['pages'] = [
				'empty'  => $this->lang->line('admin_pages_empty_confirm'),
				'remove' => $this->lang->line('admin_pages_remove_confirm'),
			];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Common method for preparing form fields for adding, editing, or
	 * translating pages.
	 *
	 * @param  object      $form   CI_Hub_form object instance.
	 * @param  object|null $page   Optional page to edit.
	 * @param  string|null $idiom  Optional language to translate the page to.
	 * @return void
	 */
	private function prepare_inputs($form, $page = null, $idiom = null)
	{
		// Page title.
		$this->data['field_name'] = [
			'type'        => 'text',
			'name'        => 'name',
			'id'          => 'name',
			'class'       => error_class('name', 'form-control'),
			'value'       => $form->set_value('name', $page?->name),
			'placeholder' => $this->lang->line('title'),
			'autofocus'   => 'autofocus',
		];

		// Page content
		$this->data['field_content'] = [
			'type'        => 'textarea',
			'name'        => 'content',
			'id'          => 'content',
			'class'       => error_class('content', 'form-control summernote'),
			'value'       => $form->set_value('content', $page?->content ?? '', false),
			'placeholder' => $this->lang->line('content'),
			'data-height' => 480,
			'data-upload' => esc($this->url->admin('media/upload')),
			'data-fetch'  => esc($this->url->admin('media/list')),
		];

		// Page excerpt (description)
		$this->data['field_excerpt'] = [
			'type'        => 'textarea',
			'name'        => 'description',
			'id'          => 'description',
			'class'       => error_class('description', 'form-control form-control-sm no-resize'),
			'value'       => $form->set_value('description', $page?->description),
			'placeholder' => $this->lang->sline('excerpt_com', $this->lang->line('page')),
			'rows'        => 3,
		];

		// Meta title
		$this->data['field_meta_title'] = [
			'type'        => 'text',
			'name'        => 'meta_title',
			'id'          => 'meta_title',
			'class'       => error_class('meta_title', 'form-control form-control-sm'),
			'value'       => $form->set_value('meta_title', $page?->meta_title),
			'placeholder' => $this->lang->sline('optional_com', $this->lang->line('meta_title')),
		];;

		// Meta description
		$this->data['field_meta_description'] = [
			'type'        => 'textarea',
			'name'        => 'meta_description',
			'id'          => 'meta_description',
			'class'       => error_class('meta_description', 'form-control form-control-sm no-resize'),
			'value'       => $form->set_value('meta_description', $page?->meta_description),
			'placeholder' => $this->lang->sline('optional_com', $this->lang->line('meta_description')),
			'rows'        => 3,
		];

		// Fields below are only added when NOT translating a page.
		if (!$idiom) {
			// Page slug/username.
			$this->data['field_username'] = [
				'type'         => 'text',
				'name'         => 'slug',
				'id'           => 'slug',
				'class'        => error_class('slug', 'form-control form-control-sm'),
				'value'        => $form->set_value('slug', $page?->username),
				'placeholder'  => $this->lang->sline('optional_com', $this->lang->line('slug')),
				'autocomplete' => 'off',
			];

			// Page status: Draft, Published, Pending.
			$this->data['field_status'] = [
				'type'     => 'dropdown',
				'name'     => 'status',
				'id'       => 'status',
				'class'    => error_class('status', 'form-select form-select-sm'),
				'selected' => $form->set_value('status', $page?->enabled ?? '-1'),
				'options'  => [
					'-1' => $this->lang->line('draft'),
					'0'  => $this->lang->line('pending_review'),
					'1'  => $this->lang->line('published'),
				],
			];

			// Page privacy: Public, Private, Protected
			$this->data['field_privacy'] = [
				'type'               => 'dropdown',
				'name'               => 'visibility',
				'id'                 => 'visibility',
				'class'              => error_class('visibility', 'form-select form-select-sm'),
				'selected'           => $form->set_value('visibility', $page?->privacy ?? '2'),
				'options'            => [
					'2' => $this->lang->line('public'),
					'1' => $this->lang->line('private'),
					'0' => $this->lang->line('password_protected'),
				],
				'data-toggle-target' => '#more',
				'data-toggle-value'  => '0',
			];

			// Page password.
			$this->data['field_password'] = [
				'type'         => 'text',
				'name'         => 'password',
				'id'           => 'password',
				'class'        => error_class('password', 'form-control form-control-sm'),
				'value'        => $form->set_value('password', $page?->password),
				'placeholder'  => $this->lang->line('password'),
				'autocomplete' => 'off',
			];
		}
	}

}
