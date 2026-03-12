<div class="feed" id="galleryFeed" data-offset="<?= count($images) ?>">
	<?php foreach ($images as $img): ?>
		<div class="post">
			<div class="post-header">
				<a href="/profile/<?= (int)$img['user_id']?>">
					<span class="post-username">@<?= htmlspecialchars($img['username']) ?></span>
				</a>
			</div>
			<div class="post-image">
				<img class="gallery-post" data-id="<?= $img['id'] ?>"
					 src="/uploads/<?= htmlspecialchars($img['path']) ?>"
					 style="cursor:pointer;">
			</div>
				<div class="post-actions">
					<form class="likeForm" data-id="<?= $img['id'] ?>">
						<input type="hidden" name="image_id" value="<?= $img['id'] ?>">
						<button class="like-btn">
							<img src="/assets/<?= (int)$img['liked_by_me'] ? 'full_heart_icon.png' : 'like_icon.svg' ?>" width="24" height="24" alt="like">
						</button>
					</form>
					<span class="likes-count" data-id="<?= $img['id'] ?>"><?= $img['like_count'] ?> likes</span>
				</div>
			<div class="post-comments">
				<p class="view-comments gallery-post" data-id="<?= $img['id'] ?>">
					View all <?= $img['comment_count'] ?> comments
				</p>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<?php if ($hasMore): ?>
	<div id="feedSentinel" style="height:1px;"></div>
<?php endif; ?>

<div id="postModal" class="post-modal">
	<div class="post-modal-inner">
		<div id="postModalBody"></div>
	</div>
</div>

<?php if (!empty($openPostId)): ?>
	<script>
		window.__openPostId = <?= (int)$openPostId ?>;
	</script>
<?php endif; ?>
<script src="/js/gallery.js"></script>