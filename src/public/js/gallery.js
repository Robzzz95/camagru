document.addEventListener("click", async function(e) {
	const trigger = e.target.closest(".gallery-post");
	if (!trigger)
		return;

	const id = trigger.dataset.id;
	const response = await fetch(`/post/${id}`, {headers: { "X-Requested-With": "XMLHttpRequest" }});

	document.getElementById("postModalBody").innerHTML = stripScripts(await response.text());
	document.getElementById("postModal").style.display = "flex";
});

document.addEventListener("keydown", e => {
	if (e.key === "Escape")
		document.getElementById("postModal").style.display = "none";
});
window.addEventListener("click", e => {
	if (e.target.id === "postModal")
		document.getElementById("postModal").style.display = "none";
});

document.addEventListener("submit", async function(e) {
	if (e.target.classList.contains("likeForm")) {
		e.preventDefault();
		const imageId = e.target.dataset.id;

		const response = await fetch("/like", {
			method: "POST",
			headers: { "Content-Type": "application/x-www-form-urlencoded" },
			body: `image_id=${imageId}`
		});

		const data = await response.json();
		if (!data.success)
			return;

		const feedCount = document.querySelector(`.likes-count[data-id="${imageId}"]`);
		if (feedCount) {
			const n = parseInt(feedCount.textContent);
			feedCount.textContent = (data.liked ? n + 1 : n - 1) + ' likes';
		}

		const feedBtn = document.querySelector(`.likeForm[data-id="${imageId}"] img`);
		if (feedBtn)
			feedBtn.src = data.liked ? '/assets/full_heart_icon.png' : '/assets/like_icon.svg';

		if (e.target.closest("#postModal"))
			await reloadModal(imageId);
	}

	if (e.target.id === "commentForm") {
		e.preventDefault();
		const form	= e.target;
		const imageId = form.dataset.id;
		const content = form.querySelector("input[name='content']").value;

		const response = await fetch("/comment", {
			method: "POST",
			headers: { "Content-Type": "application/x-www-form-urlencoded" },
			body: `image_id=${imageId}&content=${encodeURIComponent(content)}`
		});

		const data = await response.json();
		if (!data.success)
			return;

		form.reset();

		// Append new comment directly instead of reloading the whole modal
		const container = document.querySelector(".comments");
		const loadMoreBtn = container.querySelector(".load-more-btn");
		const div = document.createElement("div");
		div.className = "comment-row";
		div.innerHTML = `
			<span>
				<a href="/profile/${data.comment.user_id}">
					<strong>@${data.comment.username}</strong>
				</a>
				${data.comment.content}
			</span>
			<form class="deleteCommentForm" method="POST" action="/comment/delete">
				<input type="hidden" name="comment_id" value="${data.comment.id}">
				<button class="delete-comment-btn">✖</button>
			</form>
		`;
		container.prepend(div);
		container.scrollTop = 0;
	}

	if (e.target.classList.contains("deleteCommentForm")) {
		e.preventDefault();
		const form	  = e.target;
		const commentId = form.querySelector("input[name='comment_id']").value;
		const imageId   = document.querySelector("#commentForm")?.dataset.id
					   ?? document.querySelector(".likeForm")?.dataset.id;
		await fetch("/comment/delete", {
			method: "POST",
			headers: { "Content-Type": "application/x-www-form-urlencoded" },
			body: `comment_id=${commentId}`
		});
		await reloadModal(imageId);
	}
});


document.addEventListener("click", async function(e) {
	const trigger = e.target.closest(".gallery-post");
	if (trigger) {
		const id = trigger.dataset.id;
		const response = await fetch(`/post/${id}`, {
			headers: { "X-Requested-With": "XMLHttpRequest" }
		});
		document.getElementById("postModalBody").innerHTML = stripScripts(await response.text());
		document.getElementById("postModal").style.display = "flex";
		return;
	}

	if (e.target.classList.contains("load-more-btn")) {
		const btn = e.target;
		const imageId = btn.dataset.imageId;
		const offset = btn.dataset.offset;

		const response = await fetch(`/comments?image_id=${imageId}&offset=${offset}`, {
			headers: { "X-Requested-With": "XMLHttpRequest" }
		});

		const data = await response.json();
		const container = document.querySelector(".comments");

		data.comments.forEach(comment => {
			const div = document.createElement("div");
			div.className = "comment-row";
			div.innerHTML = `
				<span>
					<a href="/profile/${comment.user_id}">
						<strong>@${comment.username}</strong>
					</a>
					${comment.content}
				</span>
				<form class="deleteCommentForm" method="POST" action="/comment/delete">
					<input type="hidden" name="comment_id" value="${comment.id}">
					<button class="delete-comment-btn">✖</button>
				</form>
			`;
			container.insertBefore(div, btn);
		});

		if (!data.hasMore)
			btn.remove();
		else
			btn.dataset.offset = parseInt(offset) + data.comments.length;
	}
});

async function reloadModal(imageId) {
	const response = await fetch(`/post/${imageId}`, {
		headers: { "X-Requested-With": "XMLHttpRequest" }
	});
	document.getElementById("postModalBody").innerHTML = stripScripts(await response.text());
}

function stripScripts(html) {
	return html.replace(/<script\b[\s\S]*?<\/script>/gi, '');
}

//Profile infinite scroll
const sentinel = document.getElementById("profileScrollSentinel");
if (sentinel) {
	const grid = document.getElementById("profileGrid");
	const userId = grid.dataset.userId;

	const observer = new IntersectionObserver(async (entries) => {
		if (!entries[0].isIntersecting)
			return;

		// Disconnect immediately to prevent multiple fires while fetching
		observer.disconnect();

		const offset   = grid.dataset.offset;
		const response = await fetch(`/profile/${userId}/posts?offset=${offset}`);
		const data	 = await response.json();

		data.posts.forEach(post => {
			const div = document.createElement("div");
			div.className = "profile-post";
			div.innerHTML = `<img src="/uploads/${post.path}"
					 class="gallery-post" data-id="${post.id}"
					 style="cursor:pointer;">`;
			grid.appendChild(div);
		});

		grid.dataset.offset = parseInt(offset) + data.posts.length;

		if (data.hasMore)
			observer.observe(sentinel);
		else
			sentinel.remove();

	}, { threshold: 1.0 });

	observer.observe(sentinel);
}

//Gallery infinite scroll
const feedSentinel = document.getElementById("feedSentinel");
if (feedSentinel) {
	const feed = document.getElementById("galleryFeed");

	const observer = new IntersectionObserver(async (entries) => {
		if (!entries[0].isIntersecting)
			return;

		const offset   = feed.dataset.offset;
		const response = await fetch(`/feed/posts?offset=${offset}`);
		const data	 = await response.json();

		data.posts.forEach(post => {
			const div = document.createElement("div");
			div.className = "post";
			div.innerHTML = `
				<div class="post-header">
					<a href="/profile/${post.user_id}">
						<span class="post-username">@${post.username}</span>
					</a>
				</div>
				<div class="post-image">
					<img class="gallery-post" data-id="${post.id}"
						 src="/uploads/${post.path}" style="cursor:pointer;">
				</div>
				<div class="post-actions">
					<form class="likeForm" data-id="${post.id}">
						<input type="hidden" name="image_id" value="${post.id}">
						<button class="like-btn">
							<img src="/assets/${post.liked_by_me ? 'full_heart_icon.png' : 'like_icon.svg'}"
								 width="24" height="24" alt="like">
						</button>
					</form>
					<span class="likes-count" data-id="${post.id}">${post.like_count} likes</span>
				</div>
				<div class="post-comments">
					<p class="view-comments gallery-post" data-id="${post.id}">
						View all ${post.comment_count} comments
					</p>
				</div>`;
			feed.appendChild(div);
		});

		feed.dataset.offset = parseInt(offset) + data.posts.length;

		if (!data.hasMore) {
			observer.disconnect();
			feedSentinel.remove();
		}

	}, { threshold: 1.0 });

	observer.observe(feedSentinel);
}

//Open the post fro he direct url like fro the mailhog
if (window.__openPostId) {
	(async () => {
		const response = await fetch(`/post/${window.__openPostId}`, {headers: {"X-Requested-With": "XMLHttpRequest"}});
		
		document.getElementById("postModalBody").innerHTML = stripScripts(await response.text());
		document.getElementById("postModal").style.display = "flex";
	})();
}
