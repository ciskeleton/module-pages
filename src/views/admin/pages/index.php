<div class="card bg-default overflow-visible">
	<div class="card-header p-2">
		<?= $hub->theme->widget('search') ?>
	</div><!--/.card-header-->

	<div class="card-body table-responsive p-0">
		<table class="table table-striped table-hover align-middle mb-0">
			<thead class="table-light">
				<tr>
					<th<?= aria_sort('id') ?>>
						<?= sort_anchor(
							'id',
							'admin/pages',
							'#',
							'class="text-decoration-none text-reset"'
						) ?>
					</th>

					<th<?= aria_sort('title') ?>>
						<?= sort_anchor(
							'title',
							'admin/pages',
							line('title'),
							'class="text-decoration-none text-reset"'
						) ?>
					</th>

					<th class="text-center"<?= aria_sort('author') ?>>
						<?= sort_anchor(
							'author',
							'admin/pages',
							line('author'),
							'class="text-decoration-none text-reset"'
						) ?>
					</th>

					<th class="text-center"<?= aria_sort('status') ?>>
						<?= sort_anchor(
							'status',
							'admin/pages',
							line('status'),
							'class="text-decoration-none text-reset"'
						) ?>
					</th>

					<th class="text-center"<?= aria_sort('visibility') ?>>
						<?= sort_anchor(
							'visibility',
							'admin/pages',
							line('visibility'),
							'class="text-decoration-none text-reset"'
						) ?>
					</th>

					<th class="text-center"<?= aria_sort('created_at') ?>>
						<?= sort_anchor(
							'created_at',
							'admin/pages',
							line('date_created_at'),
							'class="text-decoration-none text-reset"'
						) ?>
					</th>

					<th class="text-center"<?= aria_sort('updated_at') ?>>
						<?= sort_anchor(
							'updated_at',
							'admin/pages',
							line('date_updated_at'),
							'class="text-decoration-none text-reset"'
						) ?>
					</th>

					<th class="text-center"<?= aria_sort('views') ?>>
						<?= sort_anchor(
							'views',
							'admin/pages',
							line('views'),
							'class="text-decoration-none text-reset"'
						) ?>
					</th>

					<th class="text-end">
						<?= line('actions') ?>
					</th>

				</tr>
			</thead>
			<?php if (!empty($pages)): ?>
			<tbody id="pages-list">
			<?php foreach ($pages as $page): ?>
				<tr id="page-<?= $page->id ?>" data-id="<?= $page->id ?>">
					<td class="fw-bold"><?= $page->id ?></td>

					<td class="text-wrap text-break">
						<?= anchor('admin/pages/edit/'.$page->id, $page->name) ?>
					</td>

					<td class="text-center">
						<?= anchor(
							toggle_query_arg('author', $page->owner_id, 'admin/pages'),
							$page->owner?->username,
							[
								'title' => $page->owner?->name,
								'aria-current' => 'location',
							],
						) ?>
					</td>

					<td class="text-center">
						<?php
						if ($page->is_deleted) {
							echo anchor(
								toggle_query_arg('status', 'deleted', 'admin/pages'),
								line('deleted'),
								[
									'class' => 'badge bg-danger',
									'aria-label' => line('status').': '.line('deleted'),
									'aria-current' => 'location',
								]
							);
						} elseif ($page->enabled === -1) {
							echo anchor(
								toggle_query_arg('status', 'draft', 'admin/pages'),
								line('draft'),
								[
									'class' => 'badge bg-secondary',
									'aria-label' => line('status').': '.line('draft'),
									'aria-current' => 'location',
								]
							);
						} elseif ($page->enabled === 1) {
							echo anchor(
								toggle_query_arg('status', 'published', 'admin/pages'),
								line('published'),
								[
									'class' => 'badge bg-success',
									'aria-label' => line('status').': '.line('published'),
									'aria-current' => 'location',
								]
							);
						} else {
							echo anchor(
								toggle_query_arg('status', 'pending', 'admin/pages'),
								line('pending'),
								[
									'class' => 'badge bg-warning',
									'aria-label' => line('status').': '.line('pending'),
									'aria-current' => 'location',
								]
							);
						}
						?>
					</td>

					<td class="text-center">
						<?php
						if ($page->privacy > 1) {
							echo anchor(
								toggle_query_arg('visibility', 'public', 'admin/pages'),
								line('public'),
								[
									'class' => 'badge bg-primary',
									'aria-label' => line('visibility').': '.line('public'),
									'aria-current' => 'location',
								]
							);
						} elseif ($page->privacy < 1) {
							echo anchor(
								toggle_query_arg('visibility', 'protected', 'admin/pages'),
								line('protected'),
								[
									'class' => 'badge bg-info',
									'aria-label' => line('visibility').': '.line('protected'),
									'aria-current' => 'location',
								]
							);
						} else {
							echo anchor(
								toggle_query_arg('visibility', 'private', 'admin/pages'),
								line('private'),
								[
									'class' => 'badge bg-dark',
									'aria-label' => line('visibility').': '.line('private'),
									'aria-current' => 'location',
								]
							);
						}
						?>
					</td>

					<td class="text-center" title="<?= datecf($page->created_at, 'datetime') ?>">
						<?= datecf($page->created_at) ?>
					</td>

					<td class="text-center" title="<?= datecf($page->updated_at, 'datetime') ?>">
						<?= datecf($page->updated_at) ?>
					</td>

					<td class="text-center">
						<?= $page->views ?: 0 ?>
					</td>

					<td>
						<div class="d-flex align-items-start justify-content-end gap-1" role="toolbar" aria-label="<?= line('actions') ?>">
							<?php if (CI_POLYLANG && !$page->is_deleted): ?>
							<div class="dropdown position-static" data-bs-toggle="tooltip" data-bs-title="<?= line('translate') ?>">
								<button class="btn btn-primary btn-xs"
									aria-label="<?= line('translate') ?>: <?= $page->name ?>"
									data-bs-toggle="dropdown"
									data-bs-boundary="viewport"
									aria-expanded="false">
									<i class="fas fa-fw fa-language" aria-hidden="true"></i>
								</button>

								<div class="dropdown-menu dropdown-menu-end dropdown-menu-scroll">
									<?php foreach ($this->hub->lang->others($page->language) as $folder => $lang): ?>
									<a href="<?= admin_url("pages/edit/$page->id/$folder") ?>"
										class="dropdown-item"
										aria-label="<?= esc($lang['name_current']) ?>">
										<i class="flag flag-<?= esc($lang['flag']) ?>" data-text="<?= esc($lang['name_current']) ?>" aria-hidden="true"></i>
									</a>
									<?php endforeach ?>
								</div>
							</div>
							<?php endif; ?>

							<div class="btn-group btn-group-xs" role="group">
								<?php if ($page->is_deleted): ?>
								<button type="button"
									class="btn btn-outline-success"
									aria-label="<?= line('restore') ?>: <?= $page->name ?>"
									data-bs-toggle="tooltip"
									data-bs-title="<?= line('restore') ?>"
									ajaxify="<?= esc(nonce_url(
										'admin/pages/action/restore',
										'pages-restore_'.$page->id
										)) ?>"
									data-confirm="lang:pages.restore"
									data-fields="id:<?= $page->id ?>"
									data-item="<?= $page->name ?>">
									<i class="fas fa-fw fa-refresh" aria-hidden="true"></i>
								</button>

								<?php else: ?>
								<a href="<?= site_url('page/'.$page->username) ?>"
									class="btn btn-outline-info"
									aria-label="<?= line('view') ?>: <?= $page->name ?>"
									data-bs-toggle="tooltip"
									data-bs-title="<?= line('view') ?>"
									target="_blank">
									<i class="fas fa-fw fa-eye" aria-hidden="true"></i>
								</a>

								<a href="<?= admin_url('pages/edit/'.$page->id) ?>"
									class="btn btn-outline-primary"
									aria-label="<?= line('edit') ?>: <?= $page->name ?>"
									data-bs-toggle="tooltip"
									data-bs-title="<?= line('edit') ?>">
									<i class="fas fa-fw fa-edit" aria-hidden="true"></i>
								</a>

								<button type="button"
									class="btn btn-outline-danger"
									aria-label="<?= line('delete') ?>: <?= $page->name ?>"
									data-bs-toggle="tooltip"
									data-bs-title="<?= line('delete') ?>"
									ajaxify="<?= esc(nonce_url(
										'admin/pages/action/delete',
										'pages-delete_'.$page->id
										)) ?>"
									data-confirm="lang:pages.delete"
									data-fields="id:<?= $page->id ?>"
									data-item="<?= $page->name ?>">
									<i class="fas fa-fw fa-close"></i>
								</button>
								<?php endif ?>
							</div><!--/.btn-group-->
							<button type="button"
								class="btn btn-xs btn-danger"
								aria-label="<?= line('remove') ?>: <?= $page->name ?>"
								data-bs-toggle="tooltip"
								data-bs-title="<?= line('remove') ?>"
								ajaxify="<?= esc(nonce_url(
									'admin/pages/action/remove',
									'pages-remove_'.$page->id
									)) ?>"
								data-confirm="lang:pages.remove"
								data-fields="id:<?= $page->id ?>"
								data-item="<?= $page->name ?>">
								<i class="fas fa-fw fa-trash"></i>
							</button>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			<?= $summary ?>
			<?php else: ?>
			<caption class="text-center"><?= line('no_data_error') ?></caption>
			<?php endif; ?>
		</table>
	</div><!--/.card-body-->
</div><!--/.card-->
<?php
echo $pagination;
