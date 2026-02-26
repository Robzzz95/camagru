<?php
// This file returns only modal content
?>

<div class="post-modal-content">
	<div class="post-header">
		<strong>@<?= htmlspecialchars($image['username']) ?></strong>
		<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $image['user_id']): ?>
			<form method="POST" action="/delete">
				<input type="hidden" name="image_id" value="<?= $image['id'] ?>">
				<button class="delete-post-btn">Delete</button>
			</form>
		<?php endif; ?>
	</div>

	<div class="post-image">
		<img src="/uploads/<?= htmlspecialchars($image['path']) ?>" alt="">
	</div>

	<div class="post-sidebar">
		<p>❤️ <?= (int)$image['likes_count'] ?> likes</p>
		<form class="likeForm" data-id="<?= $image['id'] ?>">
			<button type="submit">❤️</button>
		</form>
		<div class="comments">
			<?php foreach ($image['comments'] as $comment): ?>
				<div class="comment-row">
					<span>
						<strong><?= htmlspecialchars($comment['username']) ?></strong>
						<?= htmlspecialchars($comment['content']) ?>
					</span>

					<?php if (isset($_SESSION['user_id']) &&
						($_SESSION['user_id'] == $comment['user_id']
						|| $_SESSION['user_id'] == $image['user_id'])): ?>
						<form method="POST" action="/comment/delete">
							<input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
							<button class="delete-comment-btn">✖</button>
						</form>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if (isset($_SESSION['user_id'])): ?>
			<form id="commentForm" data-id="<?= $image['id'] ?>">
				<input type="text" name="content" placeholder="Add a comment..." required>
				<button type="submit">Post</button>
			</form>
		<?php endif; ?>
	</div>
</div>

<script>
document.addEventListener("submit", async function(e) {

	if (e.target.classList.contains("likeForm")) {
		e.preventDefault();
		const imageId = e.target.dataset.id;
		await fetch("/like", {
			method: "POST",
			headers: { "Content-Type": "application/x-www-form-urlencoded" },
			body: `image_id=${imageId}`
		});
		const reload = await fetch(`/post/${imageId}`, {
			headers: { "X-Requested-With": "XMLHttpRequest" }
		});
		document.getElementById("postModalBody").innerHTML = await reload.text();
	}
});
</script>