<?php

namespace WaxFramework;

use DI\Container;
use WaxFramework\Contracts\Provider;
use WaxFramework\Providers\RouteServiceProvider;

class App
{
	public static bool $loaded;

	public static App $instance;

	public static Container $container;

	protected static string $root_dir;

	protected static string $root_url;

	public static function instance()
	{
		if (empty(static::$instance)) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	public function load(string $plugin_root_file, string $plugin_root_dir)
	{
		if (!empty(static::$loaded)) {
			return;
		}

		$container = new Container();
		$container->set(static::class, static::$instance);
		static::$container = $container;

		$this->set_path($plugin_root_file, $plugin_root_dir);

		$this->boot_core_service_providers();

		static::$loaded = true;
	}

	protected function set_path(string $plugin_root_file, string $plugin_root_dir)
	{
		static::$root_url = trailingslashit(plugin_dir_url($plugin_root_file));
		static::$root_dir = trailingslashit($plugin_root_dir);
	}

	public static function get_dir(string $dir = '')
	{
		return static::$root_dir . trim($dir, '/');
	}

	public static function get_url(string $url = '')
	{
		return static::$root_url . trim($url, '/');
	}

	protected function boot_core_service_providers(): void
	{
		foreach ($this->core_service_providers() as $provider) {

			$provider_instance = static::$container->get($provider);

			if ($provider_instance instanceof Provider) {
				$provider_instance->boot();
			}
		}
	}

	protected function core_service_providers()
	{
		return [
			RouteServiceProvider::class
		];
	}
}