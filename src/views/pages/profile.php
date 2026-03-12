<div class="profile-container">
	<div class="profile-header">
		<img class="avatar" src="/assets/avatars/default-avatar.svg" alt="avatar">
		<div class="profile-info">
			<h2>@<?= htmlspecialchars($user['username']) ?></h2>
			<?php if ($isOwner): ?>
				<a href="/settings"><button>Settings</button></a>
			<?php endif; ?>
		</div>
	</div>

	<div class="profile-meta">
		<span><b><?= $totalPosts ?></b> posts</span>
	</div>

	<div class="profile-grid" id="profileGrid" data-user-id="<?= $user['id'] ?>" data-offset="<?= count($posts) ?>">
		<?php foreach ($posts as $post): ?>
			<div class="profile-post">
				<img src="/uploads/<?= htmlspecialchars($post['path']) ?>"
					class="gallery-post" data-id="<?= $post['id'] ?>"
					style="cursor:pointer;">
			</div>
		<?php endforeach; ?>
	</div>
</div>

<?php if ($hasMore): ?>
	<div id="profileScrollSentinel" style="height:1px;"></div>
<?php endif; ?>

<div id="postModal" class="post-modal">
	<div class="post-modal-inner">
		<div id="postModalBody"></div>
	</div>
</div>

<script src="/js/gallery.js"></script>