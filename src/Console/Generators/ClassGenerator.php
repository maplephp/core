<?php

declare(strict_types=1);

namespace MaplePHP\Core\Console\Generators;

use MaplePHP\Blunder\Exceptions\BlunderSoftException;
use MaplePHP\DTO\Format\Clock;
use MaplePHP\DTO\Format\Str;
use MaplePHP\DTO\Traverse;
use MaplePHP\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use InvalidArgumentException;
use RuntimeException;

class ClassGenerator
{
	private string $stubsPath;
	private string $outputBasePath;
	private StreamFactoryInterface $streamFactory;
	private string $outputFile;

	// Map of type => [dir, namespace]
	private array $typeMap = [
		//'controller' => ['app/Controllers/', 'App\Controllers'],
		//'migration'  => ['database/migrations', 'Migrations'],
	];
	private string $file;
	private string $relativeDir;
	private string $date;

	private array $prefixes = [];

	private array $suffixes = [];

	public function __construct(string $stubsPath, string $outputBasePath, StreamFactoryInterface $streamFactory)
	{
		$this->stubsPath = Str::value($stubsPath)->trimTrailingSlash()->get();
		$this->outputBasePath = Str::value($outputBasePath)->trimTrailingSlash()->get();
		$this->streamFactory = $streamFactory;
		$this->date = Clock::value("now")->format("Y-m-d-His");
	}


	/**
	 * Bind a prefix to the type that is added to the file name
	 *
	 * @param string $type
	 * @param string $suffix
	 * @return $this
	 */
	public function bindPrefixToType(string $type, string $suffix): self
	{
		$this->validateMapType($type);
		$this->prefixes[$type] = $suffix;
		return $this;
	}

	/**
	 * Bind a suffix to the type that is added to the file name
	 *
	 * @param string $type
	 * @param string $suffix
	 * @return $this
	 */
	public function bindSuffixToType(string $type, string $suffix): self
	{
		$this->validateMapType($type);
		$this->suffixes[$type] = $suffix;
		return $this;
	}

	/**
	 * Generate a class file from a stub and return a PSR-7 Response.
	 *
	 * Response body  : the generated file path
	 * Response status: 201 Created on success
	 * Headers        : X-Generated-File, X-Generated-Class
	 */
	public function generate(string $type, string $name): ResponseInterface
	{
		$type = Str::value($type)->toLower()->get();
		$this->validateMapType($type);
		$stubFile = "$this->stubsPath/$type.stub";

		// Read stub via a PSR-7 stream
		$stubStream = $this->streamFactory->createStreamFromFile($stubFile, 'r');
		$content = $stubStream->getContents();

		[$outputDir, $namespace] = $this->typeMap[$type];

		$class = $this->toPascalCase($name);
		$typeSuffix = Str::value($type)->ucFirst()->get();

		$content = $this->replacePlaceholders($content, [
			'{{ class }}' => $class,
			'{{ name }}' => $this->toSnakeCase($class),
			'{{ namespace }}' => $namespace,
			'{{ date }}' => $this->date,
		]);

		$prefix = isset($this->prefixes[$type]) ? $this->prefixes[$type] . "_" : "";
		$suffix = isset($this->suffixes[$type]) ? "_" . $this->suffixes[$type] : "";
		$className = $class . $typeSuffix;
		$this->file = $prefix . $className . $suffix . ".php";
		$this->relativeDir = $outputDir;
		$outputDir = "$this->outputBasePath/$outputDir";
		$outputFile = "$outputDir/$this->file";

		if (class_exists($namespace . "\\" . $className, false)) {
			throw new BlunderSoftException("The migration name \"$class ($className)\" already exists, try again with a different name!");
		}

		if (!is_dir($outputDir)) {
			mkdir($outputDir, 0755, recursive: true);
		}

		if (file_exists($outputFile)) {
			throw new RuntimeException("File already exists: $outputFile");
		}

		// Write generated content via a PSR-7 writable stream
		$outStream = $this->streamFactory->createStreamFromFile($outputFile, 'w');
		$outStream->write($content);

		// Build the response body stream (carries the output path)
		$bodyStream = $this->streamFactory->createStream($outputFile);

		return (new Response($bodyStream))
			->withStatus(201, 'Created')
			->withHeader('Content-Type', 'text/plain')
			->withHeader('X-Generated-File', $outputFile)
			->withHeader('X-Generated-Class', $className);
	}


	public function getFile(): string
	{
		return $this->file;
	}

	public function getRelativeDir(): string
	{
		return $this->relativeDir;
	}

	public function getDate(): string
	{
		return $this->date;
	}

	public function supportedTypes(): array
	{
		return array_keys($this->typeMap);
	}

	public function registerType(string $type, string $outputDir, string $namespace): void
	{
		$this->typeMap[strtolower($type)] = [$outputDir, $namespace];
	}

	private function validateMapType(string $type): void
	{
		if (!isset($this->typeMap[$type])) {
			throw new InvalidArgumentException(
				"Unknown type: '$type'. Available: " . implode(', ', array_keys($this->typeMap))
			);
		}
	}

	private function replacePlaceholders(string $content, array $replacements): string
	{
		return str_replace(array_keys($replacements), array_values($replacements), $content);
	}

	private function toPascalCase(string $name): string
	{
		// normalizeSeparators collapses -, _, and extra spaces into a single space
		// then ucWords capitalises each word, and replaceSpaces removes the gaps
		return Str::value($name)
			->normalizeSeparators()
			->ucWords()
			->replaceSpaces('')
			->get();
	}

	private function toSnakeCase(string $name): string
	{
		return Traverse::value(Str::value($name)->explodeCamelCase()->get())
			->implode('_')
			->strToLower()
			->get();
	}
}
