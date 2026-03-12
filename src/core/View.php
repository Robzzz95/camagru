<?php
// core/View.php
declare(strict_types=1);

class View
{
	private string $file;

	public function __construct(string $view)
	{
		$this->file = __DIR__ . '/../views/pages/' . $view . '.php';
	}

	public function render(array $data = []): void
	{
		$content = $this->renderFile($this->file, $data);
		echo $this->renderFile(__DIR__ . '/../views/layout/template.php', ['content' => $content,]);
	}

	private function renderFile(string $file, array $data): string
	{
		if (!file_exists($file))
			throw new Exception("View '$file' not found");

		extract($data);
		ob_start();
		require $file;
		return ob_get_clean();
	}

	public static function partial(string $partial, array $data = []): void
	{
		$file = __DIR__ . '/../views/partial/' . $partial . '.php';
		if (!file_exists($file))
			throw new Exception("Partial '$file' not found");
		extract($data);
		require $file;
	}
}
