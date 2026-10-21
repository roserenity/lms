<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\SubTopic;
use App\Models\Topic;
use App\Models\User;
use App\Observers\CourseObserver;
use App\Observers\SubTopicObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Console\ClientCommand;
use Laravel\Passport\Console\InstallCommand;
use Laravel\Passport\Console\KeysCommand;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
      $this->app->register(RepositoryServiceProvider::class);

      // Passport::routes(null, ['middleware' => 'universal']);

      $this->commands([
          InstallCommand::class,
          ClientCommand::class,
          KeysCommand::class,
      ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
     

      DB::statement("SET SESSION sql_mode='ANSI,ALLOW_INVALID_DATES,ANSI_QUOTES,ERROR_FOR_DIVISION_BY_ZERO,HIGH_NOT_PRECEDENCE,IGNORE_SPACE,NO_AUTO_VALUE_ON_ZERO,NO_BACKSLASH_ESCAPES,NO_DIR_IN_CREATE,NO_ENGINE_SUBSTITUTION,NO_UNSIGNED_SUBTRACTION,NO_ZERO_DATE,NO_ZERO_IN_DATE,PAD_CHAR_TO_FULL_LENGTH,PIPES_AS_CONCAT,REAL_AS_FLOAT,STRICT_ALL_TABLES,STRICT_TRANS_TABLES,TRADITIONAL,TIME_TRUNCATE_FRACTIONAL'");

      // oauth_clients keeps its legacy auto-increment id; Passport 13 defaults to UUIDs.
      Passport::$clientUuids = false;

      Passport::loadKeysFrom(base_path(config('passport.key_path')));

      User::observe(UserObserver::class);
      Course::observe(CourseObserver::class);
      SubTopic::observe(SubTopicObserver::class);

    }
}
