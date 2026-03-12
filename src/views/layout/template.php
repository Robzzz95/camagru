<?php require __DIR__ . '/header.php' ?>

<main>
	<?php if (!empty($_SESSION['flash_error'])): ?>
		<div class="alert alert-error">
			<?= htmlspecialchars($_SESSION['flash_error']) ?>
		</div>
		<?php unset($_SESSION['flash_error']); ?>
	<?php endif; ?>

	<?php if (!empty($_SESSION['flash_success'])): ?>
		<div class="alert alert-success">
			<?= htmlspecialchars($_SESSION['flash_success']) ?>
		</div>
		<?php unset($_SESSION['flash_success']); ?>
	<?php endif; ?>

	<?php if (!empty($_GET['error'])): ?>
		<div class="alert alert-error">
			<?= htmlspecialchars($_GET['error']) ?>
		</div>
	<?php endif; ?>

	<?php if (!empty($_GET['success'])): ?>
		<div class="alert alert-success">
			<?= htmlspecialchars($_GET['success']) ?>
		</div>
	<?php endif; ?>

	<?= $content ?>
</main>

<?php require __DIR__ . '/footer.php' ?>