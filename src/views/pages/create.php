<div class="create-wrapper">

	<!-- EDITOR -->
	<div class="editor">
		<h2>Create Post</h2>

		<input type="file" id="fileInput" accept="image/*">
		<button id="toggleCamera">Use Webcam</button>

		<div class="editor-stage">
			<video id="video" playsinline></video>
			<canvas id="canvas"></canvas>
			<div id="stickerLayer"></div>
		</div>

		<button id="captureBtn" style="display:none;">Capture</button>
		<button id="saveBtn" style="display:none;">Post</button>

		<h3>Stickers</h3>
		<div class="stickers">
			<?php foreach ($stickers as $filename): ?>
				<img src="/assets/<?= htmlspecialchars($filename) ?>" class="sticker-option">
			<?php endforeach; ?>
		</div>
	</div>

	<!-- SIDEBAR -->
	<div class="sidebar">
		<h3>Your Posts</h3>
		<div class="sidebar-grid">
			<?php foreach ($myImages as $img): ?>
				<img src="/uploads/<?= htmlspecialchars($img['path']) ?>" alt="Your post">
			<?php endforeach; ?>
		</div>
	</div>

</div>

<script src="/js/create.js"></script>