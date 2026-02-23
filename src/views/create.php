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

/* ================================= */
/* FORCE FIXED CANVAS SIZE (IMPORTANT) */
/* ================================= */

function setupCanvas() {
	canvas.width = 600;
	canvas.height = 600;
}

setupCanvas();

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
			video.style.display = "block";
			canvas.style.display = "none";
			captureBtn.style.display = "inline-block";
			toggleCameraBtn.textContent = "Stop Webcam";

		} catch (err) {
			alert("Camera access denied");
		}

	} else {
		stopCamera();
	}
};

function stopCamera() {
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

	// Use stickerLayer rect since that's where the sticker lives
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
	sticker.style.zIndex = "10";  // force above everything
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
			// Capture position/size NOW before DOM is touched
			const x = parseFloat(sticker.style.left) * scaleX;
			const y = parseFloat(sticker.style.top) * scaleY;
			const size = parseFloat(sticker.style.width) * scaleX; // use style.width, NOT offsetWidth
			const img = new Image();
			img.crossOrigin = "anonymous";
			img.onload = () => {
				ctx.drawImage(img, x, y, size, size);
				onStickerDrawn();
			};
			img.onerror = () => {
				// Still count it so we don't hang forever
				onStickerDrawn();
			};
			img.src = sticker.src;
			// Only use complete if BOTH complete AND naturalWidth > 0 (truly loaded)
			// And unset onload first to avoid double firing
			if (img.complete && img.naturalWidth > 0) {
				img.onload = null;
				ctx.drawImage(img, x, y, size, size);
				onStickerDrawn();
			}
		});
	});
}

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

/* ================================= */
/* CAPTURE FROM WEBCAM */
/* ================================= */

captureBtn.onclick = async () => {

	if (!cameraOn)
		return;
	setupCanvas();
	ctx.clearRect(0, 0, 600, 600);
	ctx.drawImage(video, 0, 0, 600, 600);
	video.style.display = "none";
	canvas.style.display = "block";
	stopCamera();
	saveBtn.style.display = "inline-block";
};
</script>

<?php require __DIR__ . '/layout/footer.php'; ?>