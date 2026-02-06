async function fetchGallery() {
	try {
		const res = await fetch('/', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: new URLSearchParams({ action: 'get_gallery' })
		});
		const data = await res.json();
		renderGallery(data);
	} catch (err) {
		console.error('Gallery fetch error:', err);
	}
}

function renderGallery(items) {
	const container = document.getElementById('gallery');
	container.innerHTML = '';
	items.forEach(item => {
		const div = document.createElement('div');
		div.className = 'photo-card';
		div.innerHTML = `
			<img src="${item.url}" alt="User post">
			<p>Posted by ${item.username}</p>
			<p>${item.likes} likes</p>
			<button ${!item.canInteract ? 'disabled' : ''} data-id="${item.id}" class="like-btn">Like</button>
		`;
		container.appendChild(div);
	});

	document.querySelectorAll('.like-btn').forEach(btn => {
		btn.addEventListener('click', async () => {
			const id = btn.dataset.id;
			try {
				const res = await fetch('/', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: new URLSearchParams({ action: 'like', post_id: id })
				});
				const json = await res.json();
				if (json.success) fetchGallery(); // refresh gallery
			} catch (err) {
				console.error(err);
			}
		});
	});
}

document.addEventListener('DOMContentLoaded', fetchGallery);
