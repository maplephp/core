<?php

namespace MaplePHP\Core\Interfaces;

use MaplePHP\Core\AbstractKernel;
use MaplePHP\Core\HttpKernelLoad;
use MaplePHP\Emitron\Contracts\KernelInterface;

interface KernelLoadInterface
{

	/**
	 * Initialize the HTTP kernel with default framework configuration.
	 *
	 * This method loads HTTP-related configuration from `/configs/http.php`
	 * and registers globally configured middleware. It also attaches the
	 * default error handler used for rendering exceptions and runtime errors.
	 *
	 * This method is intended to be called before booting the kernel.
	 *
	 * @return self
	 */
	public function init(): self;


	/**
	 * Boot the application and execute the request lifecycle.
	 *
	 * This method prepares the runtime environment, constructs the PSR-7
	 * request object, loads the application configuration, builds the
	 * kernel pipeline, and finally executes the request.
	 *
	 * @param array $parts Additional URI parts used to construct the request
	 *                     (typically provided by the front controller).
	 *
	 * @return KernelInterface The running kernel instance.
	 * @throws \Exception
	 */
	public function boot(array $parts): KernelInterface;
}