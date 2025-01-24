<?php

namespace App\Providers;

use App\Models\ChapterInfo;
use App\Models\Settings;
use App\Observers\ChapterInfoObserver;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Collection::macro('paginate', function($perPage, $total = null, $page = null, $pageName = 'page') {
        //     $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
        //     return new LengthAwarePaginator(
        //         $this->forPage($page, $perPage),
        //         $total ?: $this->count(),
        //         $perPage,
        //         $page,
        //         [
        //             'path' => LengthAwarePaginator::resolveCurrentPath(),
        //             'pageName' => $pageName,
        //         ]
        //     );
        // });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        ini_set('max_execution_time', 300);
        Schema::defaultStringLength(191);
       // Paginator::useBootstrap();
        if (Schema::hasTable('settings')) {
            foreach (Settings::all() as $setting) {
                Config::set('settings.'.$setting->key, $setting->value);
            }
        }
        // when update chapter name update chapter table also
        ChapterInfo::observe(ChapterInfoObserver::class);
        Paginator::useBootstrapFour();
    }
}
