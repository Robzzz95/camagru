<?php require __DIR__ . '/layout/header.php'; ?>

<div class="profile-container">
	<div class="profile-header">
		<img class="avatar" src="/assets/default-avatar.svg" alt="avatar">

		<div class="profile-info">
			<h2><?= htmlspecialchars($user['username']) ?></h2>
			<button>Edit profile</button>
		</div>
	</div>

	<div class="profile-meta">
		<span><b>0</b> posts</span>
		<span><b>0</b> followers</span>
		<span><b>0</b> following</span>
	</div>

	<div class="profile-bio">
		<?= htmlspecialchars($user['email']) ?>
	</div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>