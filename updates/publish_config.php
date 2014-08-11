<?php namespace Flynsarmy\DebugBar\Updates;

use File;
use October\Rain\Database\Updates\Migration;

/**
 * Publish the laravel-debugbar config
 */
class PublishConfig extends Migration
{
    public function __construct()
    {
        $this->package_filepath = __DIR__.'/../vendor/barryvdh/laravel-debugbar/src/config/config.php';
        $this->published_filepath = app_path('config/packages/barryvdh/laravel-debugbar/config.php');
    }

    public function up()
    {
        if ( File::exists($this->package_filepath) && !File::exists($this->published_filepath) )
        {
            File::makeDirectory(dirname($this->published_filepath), 0777, true);
            File::copy($this->package_filepath, $this->published_filepath);
        }
    }

    public function down()
    {
        File::delete($this->published_filepath);
    }
}
