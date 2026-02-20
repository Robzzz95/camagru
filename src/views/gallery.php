<?php require __DIR__ . '/layout/header.php'; ?>

<?php if (isset($_SESSION['user_id'])): ?>
	<form action="/upload" method="POST" enctype="multipart/form-data" style="margin-bottom:30px">
		<input type="file" name="image" required>
		<button>Upload</button>
	</form>
<?php endif; ?>


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


<div class="feed">
	<?php foreach ($images as $img): ?>
		<div class="post">

			<div class="post-header">
				<span class="post-username">
					<?= htmlspecialchars($img['username']) ?>
				</span>
			</div>

			<div class="post-image">
				<img src="/uploads/<?= htmlspecialchars($img['path']) ?>" alt="post">
			</div>

			<div class="post-actions">
				<form method="POST" action="/like">
					<input type="hidden" name="image_id" value="<?= $img['id'] ?>">
					<button class="like-btn">
						❤️
					</button>
				</form>
				<span class="likes-count">
					<?= $img['likes'] ?> likes
				</span>
			</div>

			<div class="post-comments">
				<?php foreach ($img['comments'] as $comment): ?>
					<p>
						<strong><?= htmlspecialchars($comment['username']) ?></strong>
						<?= htmlspecialchars($comment['content']) ?>
					</p>
				<?php endforeach; ?>
			</div>

			<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $img['user_id']): ?>
				<form method="POST" action="/delete">
					<input type="hidden" name="image_id" value="<?= $img['id'] ?>">
					<button class="delete-btn">Delete</button>
				</form>
			<?php endif; ?>

		</div>
	<?php endforeach; ?>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
