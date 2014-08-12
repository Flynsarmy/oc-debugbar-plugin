<?php namespace Flynsarmy\DebugBar;

use App;
use System\Classes\PluginBase;
use Illuminate\Foundation\AliasLoader;
use Event;

/**
 * DebugBar Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'DebugBar',
            'description' => 'Laravel Debugbar integration plugin for OctoberCMS',
            'author'      => 'Flynsarmy',
            'icon'        => 'icon-code'
        ];
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot()
    {
        // Register ServiceProviders
        App::register('\Barryvdh\Debugbar\ServiceProvider');

        // Register aliases
        $alias = AliasLoader::getInstance();
        $alias->alias('Debugbar', 'Barryvdh\Debugbar\Facade');

        // Register Twig extensions
        Event::listen('cms.page.beforeDisplay', function($controller, $url, $page) {
            $twig = $controller->getTwig();
            $twig->addExtension(new \Barryvdh\Debugbar\Twig\Extension\Debug($this->app));
            $twig->addExtension(new \Barryvdh\Debugbar\Twig\Extension\Stopwatch($this->app));
        });
    }

    public function register()
    {
        $app = $this->app;

        // Middleware doesn't work in October, so call DebugBar manually
        if ( !$app->runningInConsole() )
        {
            $app->after(function ($request, $response) use($app)
            {
                // @var LaravelDebugbar $debugbar
                $debugbar = $app['debugbar'];
                $debugbar->modifyResponse($request, $response);

                // Update asset URLs for October
                $content = $response->getContent();

                $content = $this->update_asset_urls($content);

                $response->setContent($content);
            });
        }
    }

    /**
     * Replaces the default published asset package directories with the composer vendor
     * directory equivalents because October can't publish assets.
     *
     * @param  string $content [description]
     *
     * @return string          Content with asset URLs fixed.
     */
    public function update_asset_urls($content)
    {
        $from_dir = $this->app['url']->asset('packages/barryvdh/laravel-debugbar');
        $to_dir = $this->app['url']->asset('plugins/flynsarmy/debugbar/vendor/barryvdh/laravel-debugbar/public');
        $content = str_replace($from_dir, $to_dir, $content);

        $from_dir = $this->app['url']->asset('packages/maximebf/php-debugbar');
        $to_dir = $this->app['url']->asset('plugins/flynsarmy/debugbar/vendor/maximebf/debugbar/src/DebugBar/Resources');
        $content = str_replace($from_dir, $to_dir, $content);

        return $content;
    }
}
