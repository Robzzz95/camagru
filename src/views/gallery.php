<?php require __DIR__ . '/layout/header.html'; ?>

<?php if (isset($_SESSION['user_id'])): ?>
	<form action="/upload" method="POST" enctype="multipart/form-data" style="margin-bottom:30px">
		<input type="file" name="image" required>
		<button>Upload</button>
	</form>
<?php endif; ?>



<div class="gallery-grid">
	<?php foreach ($images as $img): ?>

		<div class="gallery-item">
			<img src="/uploads/<?= htmlspecialchars($img['path']) ?>">
			<p><?= htmlspecialchars($img['username']) ?></p>

			<form method="POST" action="/like">
				<input type="hidden" name="image_id" value="<?= $img['id'] ?>">
				<button>❤️ <?= $img['likes'] ?></button>
			</form>

			<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $img['user_id']): ?>
				<form method="POST" action="/delete">
					<input type="hidden" name="image_id" value="<?= $img['id'] ?>">
					<button style="color:red">Delete</button>
				</form>
			<?php endif; ?>

			<div class="comments">
				<?php foreach ($img['comments'] as $comment): ?>
				<p><strong><?= htmlspecialchars($comment['username']) ?>:</strong>
				<?= htmlspecialchars($comment['content']) ?></p>
				<?php endforeach; ?>
			</div>
		</div>

	<?php endforeach; ?>
</div>

<?php require __DIR__ . '/layout/footer.html'; ?>
