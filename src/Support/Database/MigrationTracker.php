<?php

declare(strict_types=1);

namespace MaplePHP\Core\Support\Database;

use MaplePHP\DTO\Traverse;
use Psr\Http\Message\StreamFactoryInterface;

class MigrationTracker
{
	private string $trackFile;
	private StreamFactoryInterface $streamFactory;
	private array $migrated = [];

	public function __construct(string $migrationsDir, StreamFactoryInterface $streamFactory)
	{
		$this->trackFile = rtrim($migrationsDir, '/') . '/migrations.json';
		$this->streamFactory = $streamFactory;
		$this->load();
	}

	/**
	 * Check if a migration has already been run in a given direction.
	 */
	public function has(string $file, string $direction): bool
	{
		$filename = basename($file);
		$entries = Traverse::value($this->migrated);

		foreach ($entries->fetch() as $entry) {
			$matchFile = $entry->file->get() === $filename;
			$matchDirection = $entry->direction->get() === $direction;

			if ($matchFile && $matchDirection) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Mark a migration as run.
	 */
	public function add(string $file, string $class, string $direction): void
	{
		$this->migrated[] = [
			'class' => $class,
			'file' => basename($file),
			'direction' => $direction,
			'migrated_at' => date('Y-m-d H:i:s'),
		];

		$this->save();
	}

	/**
	 * Remove a specific migration entry so it can be re-run.
	 */
	public function remove(string $file, string $direction): void
	{
		$filename = basename($file);
		$this->migrated = Traverse::value($this->migrated)
			->filter(function ($entry) use ($filename, $direction) {
				$item = $entry->toArray();
				return !(
					$item['file'] === $filename &&
					$item['direction'] === $direction
				);
			})
			->toArray();

		$this->save();
	}

	/**
	 * Return the last successfully migrated "up" entry, or null if none.
	 */
	public function last(): ?array
	{
		$ups = array_filter(
			$this->migrated,
			fn(array $entry) => $entry['direction'] === 'up'
		);

		return !empty($ups) ? end($ups) : null;
	}

	private function load(): void
	{
		if (!file_exists($this->trackFile)) {
			$this->migrated = [];
			return;
		}

		$stream = $this->streamFactory->createStreamFromFile($this->trackFile, 'r');
		$data = Traverse::value(json_decode($stream->getContents(), true) ?? []);
		$this->migrated = $data->eq('migrations')->toArray() ?? [];
	}

	private function save(): void
	{
		$data = json_encode(['migrations' => $this->migrated], JSON_PRETTY_PRINT);
		$stream = $this->streamFactory->createStreamFromFile($this->trackFile, 'w');
		$stream->write($data);
	}
}