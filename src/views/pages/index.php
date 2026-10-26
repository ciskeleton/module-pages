<section class="row py-4" role="banner" aria-label="<?= $page->name ?>">
	<div class="col">
		<h1 class="display-6 fw-bold text-center" id="page-title"><?= $page->name ?></h1>
	</div><!--/.col-->
</section>

<section class="row">
	<div class="col">
		<div class="card">
			<?php if ($user_is_staff): ?>
			<div class="card-body py-2 border-bottom">
				<div class="d-flex align-items-center justify-content-between">

					<?php
					// Delete badge
					if ($page->is_deleted) {
						echo '<span class="h6 m-0">'.line('deleted').'</span>';
					} else {
						echo '<span class="h6 m-0">'.(match ($page->enabled) {
							-1 => line('draft'),
							0 => line('pending_review'),
							1 => line('published'),
							default => '',
						}).'</span>';
					}

					// Edit button:
					if ($user_is_staff) {
						$uri = 'admin/pages/edit/'.$page->id;
						if ($page->translated_to($idiom = $this->lang->current)) {
							$uri .= '/'.$idiom;
						}
						echo anchor($uri, line('edit'), 'class="btn btn-sm btn-success px-3"');
					}
					?>

				</div><!--/.d-flex-->
			</div><!--/.card-body-->
			<?php endif ?>

			<article class="card-body" aria-labelledby="page-title" dir="auto">
				<?= $page->content ?>
			</article>
		</div><!--/.card-->
	</div><!--/.col-->
</section><!--/.row-->
