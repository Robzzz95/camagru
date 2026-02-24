<?php require __DIR__ . '/layout/header.php'; ?>

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
				<span class="post-username">@
					<?= htmlspecialchars($img['username']) ?>
				</span>
			</div>

			<div class="post-image">
				<img class="gallery-post" data-id="<?= $img['id'] ?>" src="/uploads/<?=
					htmlspecialchars($img['path']) ?>" style="cursor:pointer;">
			</div>

			<div class="post-actions">
				<form method="POST" action="/like">
					<input type="hidden" name="image_id" value="<?= $img['id'] ?>">
					<button class="like-btn">
						❤️
					</button>
				</form>
				<span class="likes-count">
					<?= $img['likes_count'] ?> likes
				</span>
			</div>

			<div class="post-comments">
				<p class="view-comments gallery-post" data-id="<?= $img['id'] ?>">
   					View all <?= count($img['comments']) ?> comments</p>
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

<div id="postModal" class="post-modal">
	<div class="post-modal-inner">
		<div id="postModalBody"></div>
	</div>
</div>

<script>
document.addEventListener("click", async function(e) {
	const trigger = e.target.closest(".gallery-post");
	if (!trigger)
		return;
	const id = trigger.dataset.id;
	const response = await fetch(`/post/${id}`, {
		headers: { "X-Requested-With": "XMLHttpRequest" }
	});
	const html = await response.text();
	document.getElementById("postModalBody").innerHTML = html;
	document.getElementById("postModal").style.display = "flex";
});

document.addEventListener("keydown", e => {
	if (e.key === "Escape") {
		document.getElementById("postModal").style.display = "none";
	}
});

window.addEventListener("click", e => {
	if (e.target.id === "postModal") {
		document.getElementById("postModal").style.display = "none";
	}
});

document.addEventListener("submit", async function(e) {

    if (e.target.id === "commentForm") {

        e.preventDefault();

        const form = e.target;
        const imageId = form.dataset.id;
        const content = form.querySelector("input[name='content']").value;

        const response = await fetch("/comment", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `image_id=${imageId}&content=${encodeURIComponent(content)}`
        });

        const data = await response.json();

        if (data.success) {
            form.reset();

            // Reload modal content
            const reload = await fetch(`/post/${imageId}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            document.getElementById("postModalBody").innerHTML = await reload.text();
        }
    }
});
</script>

<?php require __DIR__ . '/layout/footer.php'; ?>
