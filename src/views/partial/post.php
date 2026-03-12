<div class="post-modal-content">
	<div class="post-header">
		<a href="/profile/<?= (int)$image['user_id']?>">
			<strong>@<?= htmlspecialchars($image['username']) ?></strong>
		</a>
		<?php if (Auth::check() && Auth::id() === (int)$image['user_id']): ?>
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
		<div class="post-actions">
			<?php if (Auth::check()): ?>
				<form class="likeForm" data-id="<?= $image['id'] ?>">
					<button class="like-btn">
						<img src="/assets/<?= (int)$image['liked_by_me'] ? 'full_heart_icon.png' : 'like_icon.svg' ?>" width="24" height="24" alt="like">
					</button>
				</form>
				<span class="likes-count"><?= (int)$image['like_count'] ?> likes</span>
			<?php endif; ?>
		</div>
		<hr>

		<div class="comments" 
			data-image-id="<?= $image['id'] ?>" 
			data-offset="<?= count($image['comments']) ?>">
			<?php foreach ($image['comments'] as $comment): ?>
				<div class="comment-row">
					<span>
						<a href="/profile/<?= (int)$comment['user_id']?>">
							<strong>@<?= htmlspecialchars($comment['username']) ?></strong>
						</a>
						<?= htmlspecialchars($comment['content']) ?>
					</span>
					<?php if (Auth::check() && (
						Auth::id() === (int)$comment['user_id'] ||
						Auth::id() === (int)$image['user_id']
					)): ?>
						<form class="deleteCommentForm" method="POST" action="/comment/delete">
							<input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
							<button class="delete-comment-btn">✖</button>
						</form>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<?php if ($image['hasMore']): ?>
				<button class="load-more-btn" 
						data-image-id="<?= $image['id'] ?>"
						data-offset="<?= count($image['comments']) ?>">
					Load more comments
				</button>
			<?php endif; ?>
		</div>

		<?php if (Auth::check()): ?>
			<form id="commentForm" data-id="<?= $image['id'] ?>">
				<input type="text" name="content" placeholder="Add a comment..." required>
				<button type="submit">Post</button>
			</form>
		<?php endif; ?>
	</div>
</div>
