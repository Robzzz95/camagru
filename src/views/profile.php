<?php require __DIR__ . '/layout/header.html'; ?>

<div class="profile-container">
	<div class="profile-header">
		<img class="avatar" src="/front/assets/default-avatar.jpg" alt="avatar">

		<div class="profile-info">
			<h2><?= htmlspecialchars($user['username']) ?></h2>
			<button>Edit profile</button>
		</div>
	</div>

	<div class="profile-meta">
		<span><strong>0</strong> posts</span>
		<span><strong>0</strong> followers</span>
		<span><strong>0</strong> following</span>
	</div>

	<div class="profile-bio">
		<?= htmlspecialchars($user['email']) ?>
	</div>
</div>

<?php require __DIR__ . '/layout/footer.html'; ?>