<?php require __DIR__ . '/layout/header.php'; ?>

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
			<?php
			$assetsPath = $_SERVER['DOCUMENT_ROOT'] . '/assets';
			$stickers = glob($assetsPath . '/*.png');
			foreach ($stickers as $sticker):
				$filename = basename($sticker); ?>
				<img src="/assets/<?= $filename ?>" class="sticker-option">
			<?php endforeach; ?>
		</div>
	</div>

	<!-- SIDEBAR -->
	<div class="sidebar">
		<h3>Your Posts</h3>
		<div class="sidebar-grid">
			<?php foreach ($myImages as $img): ?>
				<img src="/uploads/<?= htmlspecialchars($img['path']) ?>">
			<?php endforeach; ?>
		</div>
	</div>

</div>

<script>
const video = document.getElementById("video");
const canvas = document.getElementById("canvas");
const ctx = canvas.getContext("2d");
const stickerLayer = document.getElementById("stickerLayer");

const toggleCameraBtn = document.getElementById("toggleCamera");
const captureBtn = document.getElementById("captureBtn");
const saveBtn = document.getElementById("saveBtn");
const fileInput = document.getElementById("fileInput");

let stream = null;
let cameraOn = false;
let selectedStickerSrc = null;
let animationFrameId = null;

/* ================================= */
/* FORCE FIXED CANVAS SIZE (IMPORTANT) */
/* ================================= */

function setupCanvas() {
	canvas.width = 600;
	canvas.height = 600;
}

setupCanvas();

/* ================================= */
/* WEBCAM DRAW LOOP */
/* ================================= */

function startDrawLoop() {
	function draw() {
		if (!cameraOn) return;
		ctx.drawImage(video, 0, 0, 600, 600);
		animationFrameId = requestAnimationFrame(draw);
	}
	draw();
}

function stopDrawLoop() {
	if (animationFrameId) {
		cancelAnimationFrame(animationFrameId);
		animationFrameId = null;
	}
}

/* ================================= */
/* CAMERA TOGGLE */
/* ================================= */

toggleCameraBtn.onclick = async () => {

	if (!cameraOn) {
		try {
			stream = await navigator.mediaDevices.getUserMedia({ video: true });
			video.srcObject = stream;
			await video.play();

			cameraOn = true;
			video.style.display = "none";   // hide <video>, we paint it ourselves
			canvas.style.display = "block"; // canvas shows the live feed
			captureBtn.style.display = "inline-block";
			saveBtn.style.display = "none";
			toggleCameraBtn.textContent = "Stop Webcam";

			startDrawLoop();

		} catch (err) {
			alert("Camera access denied");
		}

	} else {
		stopCamera();
	}
};

function stopCamera() {
	stopDrawLoop();
	if (stream) {
		stream.getTracks().forEach(track => track.stop());
		video.srcObject = null;
		stream = null;
	}
	cameraOn = false;
	video.style.display = "none";
	canvas.style.display = "block";
	captureBtn.style.display = "none";
	toggleCameraBtn.textContent = "Use Webcam";
}

/* ================================= */
/* FILE UPLOAD */
/* ================================= */

fileInput.addEventListener("change", function () {

	const file = this.files[0];
	if (!file) return;

	// If camera is on, turn it off first
	if (cameraOn) stopCamera();

	const reader = new FileReader();
	reader.onload = function (e) {

		const uploadedImage = new Image();

		uploadedImage.onload = function () {
			setupCanvas();
			ctx.clearRect(0, 0, 600, 600);
			ctx.drawImage(uploadedImage, 0, 0, 600, 600);

			video.style.display = "none";
			canvas.style.display = "block";
			saveBtn.style.display = "inline-block";
		};

		uploadedImage.src = e.target.result;
	};

	reader.readAsDataURL(file);
});

/* ================================= */
/* SELECT STICKER (RED BORDER) */
/* ================================= */

document.querySelectorAll(".sticker-option").forEach(img => {

	img.addEventListener("click", () => {

		document.querySelectorAll(".sticker-option")
			.forEach(el => el.classList.remove("selected"));

		img.classList.add("selected");
		selectedStickerSrc = img.src;
	});
});

/* ================================= */
/* ADD STICKER ON CLICK */
/* ================================= */

canvas.addEventListener("click", function (e) {

	if (!selectedStickerSrc) return;

	const rect = stickerLayer.getBoundingClientRect();
	const x = e.clientX - rect.left;
	const y = e.clientY - rect.top;

	const sticker = document.createElement("img");
	sticker.src = selectedStickerSrc;
	sticker.className = "live-sticker";

	const size = 100;
	sticker.style.position = "absolute";
	sticker.style.width = size + "px";
	sticker.style.left = (x - size / 2) + "px";
	sticker.style.top  = (y - size / 2) + "px";
	sticker.style.zIndex = "10";
	sticker.style.pointerEvents = "auto";

	stickerLayer.appendChild(sticker);
	makeInteractive(sticker);
});

/* ================================= */
/* STICKER INTERACTIONS */
/* ================================= */

function makeInteractive(el) {

	let offsetX = 0;
	let offsetY = 0;
	let dragging = false;

	el.addEventListener("mousedown", e => {
		dragging = true;
		offsetX = e.offsetX;
		offsetY = e.offsetY;
		e.stopPropagation();
	});

	window.addEventListener("mousemove", e => {
		if (!dragging) return;

		const rect = stickerLayer.getBoundingClientRect();
		el.style.left = (e.clientX - rect.left - offsetX) + "px";
		el.style.top = (e.clientY - rect.top - offsetY) + "px";
	});

	window.addEventListener("mouseup", () => dragging = false);

	el.addEventListener("wheel", e => {
		e.preventDefault();
		let size = el.offsetWidth + (e.deltaY < 0 ? 10 : -10);
		el.style.width = Math.max(30, size) + "px";
	});

	el.addEventListener("contextmenu", e => {
		e.preventDefault();
		el.remove();
	});
}

/* ================================= */
/* MERGE STICKERS INTO CANVAS */
/* ================================= */

function mergeStickersIntoCanvas() {
	return new Promise((resolve) => {
		const stickers = [...document.querySelectorAll(".live-sticker")];

		if (stickers.length === 0) {
			stickerLayer.innerHTML = "";
			resolve();
			return;
		}

		const canvasRect = canvas.getBoundingClientRect();
		const scaleX = canvas.width / canvasRect.width;
		const scaleY = canvas.height / canvasRect.height;

		let loaded = 0;

		function onStickerDrawn() {
			loaded++;
			if (loaded === stickers.length) {
				stickerLayer.innerHTML = "";
				resolve();
			}
		}

		stickers.forEach(sticker => {
			const x    = parseFloat(sticker.style.left)  * scaleX;
			const y    = parseFloat(sticker.style.top)   * scaleY;
			const size = parseFloat(sticker.style.width) * scaleX;

			const img = new Image();
			img.crossOrigin = "anonymous";

			img.onload = () => {
				ctx.drawImage(img, x, y, size, size);
				onStickerDrawn();
			};

			img.onerror = () => onStickerDrawn();

			img.src = sticker.src;

			if (img.complete && img.naturalWidth > 0) {
				img.onload = null;
				ctx.drawImage(img, x, y, size, size);
				onStickerDrawn();
			}
		});
	});
}

/* ================================= */
/* CAPTURE FROM WEBCAM */
/* ================================= */

captureBtn.onclick = () => {

	if (!cameraOn) return;

	// Freeze the current frame — last drawn frame stays on canvas
	stopDrawLoop();
	stopCamera();

	saveBtn.style.display = "inline-block";
};

/* ================================= */
/* SAVE POST */
/* ================================= */

saveBtn.onclick = async () => {

	if (canvas.width === 0) {
		alert("No image to post");
		return;
	}

	await mergeStickersIntoCanvas();

	const imageData = canvas.toDataURL("image/jpeg", 0.9);
	try {
		const response = await fetch("/gallery/store", {
			method: "POST",
			headers: { "Content-Type": "application/json" },
			body: JSON.stringify({ image: imageData })
		});

		const data = await response.json();
		if (data.success)
			window.location.reload();
		else
			alert(data.message || "Upload failed");
	} catch (err) {
		console.error(err);
		alert("Server error");
	}
};
</script>

<?php require __DIR__ . '/layout/footer.php'; ?>