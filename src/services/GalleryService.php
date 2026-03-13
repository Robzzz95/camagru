<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Like.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../core/Auth.php';

class GalleryService
{
	private Post $post;
	private Like $like;

	public function __construct()
	{
		$this->post = new Post;
		$this->like = new Like;
	}

	public function all(int $limit = 20, int $offset = 0): array
	{
		return ($this->post->getFeed($limit, $offset, Auth::id()));
	}

	public function find(int $id): ?array
	{
		$image = $this->post->getById($id, Auth::id());
		if (!$image) 
			return (null);

		$result	= (new Comment)->getComments($id);
		$image['comments'] = $result['comments'];
		$image['hasMore'] = $result['hasMore'];
		return ($image);
	}

	public function byUser(int $userId, int $limit = 12, int $offset = 0): array
	{
		return ($this->post->getByUser($userId, $limit, $offset));
	}

	public function toggleLike(int $userId, int $imageId): void
	{
		$this->like->toggle($userId, $imageId);
	}

	public function delete(int $userId, int $imageId): void
	{
		$image = $this->post->getById($imageId);

		if (!$image)
			throw new Exception("Image not found");
		if ((int)$image['user_id'] !== $userId)
			throw new Exception("Unauthorized");

		$this->post->delete($imageId, $userId);

		$file = __DIR__ . '/../public/uploads/' . $image['path'];
		if (file_exists($file))
			unlink($file);
	}

	public function upload(int $userId, array $file): void
	{
		if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK)
			throw new Exception("Upload error");
		if ($file['size'] > 5 * 1024 * 1024)
			throw new Exception("File too large (max 5MB)");

		$imageInfo = @getimagesize($file['tmp_name']);
		if (!$imageInfo)
			throw new Exception("Invalid image file");

		$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
		$mime = $imageInfo['mime'];
		if (!isset($allowed[$mime]))
			throw new Exception("Unsupported image type");

		$filename	= bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
		$destination = __DIR__ . '/../public/uploads/' . $filename;

		if (!move_uploaded_file($file['tmp_name'], $destination))
			throw new Exception("Failed to save file");

		if ($mime !== 'image/gif')
			$this->reencodeImage($destination, $mime);

		$this->post->create($userId, $filename);
	}

	public function storeBase64(int $userId, string $base64): void
	{
		if (!preg_match('#^data:image/(png|jpeg|gif);base64,#', $base64, $matches))
			throw new Exception("Invalid image format");

		$mime = 'image/' . $matches[1];
		$ext  = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
		$decoded = base64_decode(
			preg_replace('#^data:image/\w+;base64,#', '', $base64),
			true
		);
		if (!$decoded || strlen($decoded) < 1000)
			throw new Exception("Invalid image data");

		$tmp = tempnam(sys_get_temp_dir(), 'img');
		file_put_contents($tmp, $decoded);
		if (!@getimagesize($tmp)) {
			unlink($tmp);
			throw new Exception("Decoded data is not a valid image");
		}
		unlink($tmp);

		$filename = bin2hex(random_bytes(16)) . '.' . $ext;
		if (!file_put_contents(__DIR__ . '/../public/uploads/' . $filename, $decoded))
			throw new Exception("Failed writing file");

		if ($mime !== 'image/gif')
			$this->reencodeImage(__DIR__ . '/../public/uploads/' . $filename, $mime);
		$this->post->create($userId, $filename);
	}

	private function reencodeImage(string $path, string $mime): void
	{
		switch ($mime) {
			case 'image/jpeg':
				$img = imagecreatefromjpeg($path);
				imagejpeg($img, $path, 90);
				break;
			case 'image/png':
				$img = imagecreatefrompng($path);
				imagepng($img, $path, 6);
				break;
		}
		imagedestroy($img);
	}

	public function countByUser(int $userId): int
	{
		return ($this->post->countByUser($userId));
	}
}
