<?= form_open('', [
	'id' => 'edit-page',
	'class' => 'row gx-3 gy-3 gy-md-0',
	'role' => 'form',
	'rel' => 'persist',
	'autocomplete' => 'off',
], $hidden) ?>

	<div class="col-lg-9 col-sticky-top">
		<div class="d-flex flex-column gap-3">

			<!-- Post title -->
			<div>
				<label for="<?= $field_name['name'] ?>" class="form-label visually-hidden mb-1">
					<?= $field_name['placeholder'] ?>
				</label>

				<?= print_input($field_name), form_error($field_name['name']) ?>
			</div>

			<?php if (isset($field_username)): ?>
			<!-- Post slug -->
			<div>
				<label for="<?= $field_username['name'] ?>" class="form-label visually-hidden mb-1">
					<?= $field_username['placeholder'] ?>
				</label>

				<?= print_input($field_username), form_error($field_username['name']) ?>
			</div>
			<?php endif ?>

			<!-- Post content -->
			<div>
				<label for="<?= $field_content['name'] ?>" class="form-label visually-hidden mb-1">
					<?= $field_content['placeholder'] ?>
				</label>

				<?= print_input($field_content), form_error($field_content['name']) ?>
			</div>

		</div><!--/.d-flex-->
	</div><!--/.col-lg-9-->

	<div class="col-lg-3">
		<div class="d-flex flex-column gap-3">

			<div class="card">
				<div class="card-header text-center border-bottom-0 bg-body-secondary">
					<h2 class="card-title h4 mb-0"><?= line('excerpt') ?></h2>
				</div><!--/.card-header-->

				<div class="card-body p-2">
					<!-- Page excerpt -->
					<label for="<?= $field_excerpt['name'] ?>" class="form-label visually-hidden">
						<?= line($field_excerpt['name']) ?>
					</label>

					<?= print_input($field_excerpt), form_error($field_excerpt['name']) ?>
				</div><!--/.card-body-->
			</div><!--/.card.card-sm-->

			<div class="card">
				<div class="card-header text-center border-bottom-0 bg-body-secondary">
					<h2 class="card-title h4 mb-0"><?= line('metadata') ?></h2>
				</div>

				<div class="card-body p-2">
					<div class="d-flex flex-column gap-2">

						<!-- Meta title -->
						<div>
							<label for="<?= $field_meta_title['name']?>" class="form-label visually-hidden">
								<?= line($field_meta_title['name']) ?>
							</label>

							<?= print_input($field_meta_title), form_error($field_meta_title['name']) ?>
						</div>

						<!-- Meta description -->
						<div>
							<label for="<?= $field_meta_description['name']?>" class="form-label visually-hidden">
								<?= line($field_meta_description['name']) ?>
							</label>

							<?= print_input($field_meta_description), form_error($field_meta_description['name']) ?>
						</div>

					</div><!--/.d-flex-->
				</div><!--/.card-body--->
			</div><!--/.card.card-sm-->

			<div class="card">
				<div class="card-header text-center border-bottom-0 bg-body-secondary">
					<h2 class="card-title h4 mb-0"><?= line('publish') ?></h2>
				</div><!--/.card-header-->

				<div class="card-body p-2">
					<div class="d-flex flex-column gap-2">

						<?php if (isset($field_status)): ?>
						<!-- Page status -->
						<div>
							<label for="<?= $field_status['name'] ?>" class="form-label visually-hidden">
								<?= line('status') ?>
							</label>

							<?= print_input($field_status), form_error($field_status['name']) ?>
						</div>
						<?php endif ?>

						<?php if (isset($field_privacy)): ?>
						<!-- privacy -->
						<div>
							<label for="<?= $field_privacy['name'] ?>" class="form-label visually-hidden">
								<?= line('visibility') ?>
							</label>

							<?= print_input($field_privacy), form_error($field_privacy['name']) ?>
						</div>

						<div id="more" style="display:none">
							<label for="<?= $field_password['name'] ?>" class="form-label visually-hidden">
								<?= line('password') ?>
							</label>

							<?= print_input($field_password), form_error($field_password['name']) ?>
						</div>
						<?php endif ?>

						<div class="d-grid">
							<button role="button" type="submit" class="btn btn-primary btn-sm">
								<?= line('save_changes') ?>
							</button>
						</div><!--/.d-grid-->

					</div><!--/.d-flex-->
				</div><!--/.card-body-->
			</div><!--/.card.card-sm-->

		</div><!--/.d-flex-->
	</div><!--/.col-lg-3-->
</form>
