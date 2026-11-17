<?php namespace Seiger\sLMS;

use EvolutionCMS\ServiceProvider;
use EvoUI\EvoUI;
use Seiger\sLMS\Components\CatalogueTable;
use Seiger\sLMS\Components\ModulePanel;

/**
 * Registers the sLMS manager, learner routes and database schema with Evolution CMS.
 *
 * @since 0.1.0
 */
class sLMSServiceProvider extends ServiceProvider
{
    /**
     * Add sLMS to the Evo manager using a stable module name.
     *
     * @since 0.1.0
     */
    public function register(): void
    {
        if (defined('IN_MANAGER_MODE') && IN_MANAGER_MODE === true) {
            $this->app->registerModule('sLMS', dirname(__DIR__) . '/module/sLMSModule.php', 'tabler-book');
        }
    }

    /**
     * Load the schema and learner pages and register the manager interface.
     *
     * @since 0.1.0
     */
    public function boot(): void
    {
        $root = dirname(__DIR__);
        $this->loadMigrationsFrom($root . '/database/migrations');
        $this->loadViewsFrom($root . '/views', 'sLMS');
        $this->loadTranslationsFrom($root . '/lang', 'sLMS');

        if ($this->app->isFrontend() || is_cli()) {
            $this->loadRoutesFrom($root . '/routes/learner.php');
        }

        if (defined('IN_MANAGER_MODE') && IN_MANAGER_MODE === true) {
            $this->mergeConfigFrom($root . '/config/courses/table.php', 'slms.courses.table');
            $this->mergeConfigFrom($root . '/config/curriculum/table.php', 'slms');
            $this->mergeConfigFrom($root . '/config/assessment/table.php', 'slms.assessment.table');
            $ui = $this->app->make(EvoUI::class);
            $ui->registerComponent('slms.module-panel', ModulePanel::class);
            $ui->registerComponent('slms.catalogue-table', CatalogueTable::class);

            $this->registerManagerMenuLabel();
        }
    }

    /**
     * Supply the translated label while the native manager builds its module menu.
     *
     * Both menu renderers use the rows built from the file module registry. Restore
     * the registered name once those rows exist so module execution keeps its name.
     *
     * @since 0.1.0
     */
    private function registerManagerMenuLabel(): void
    {
        $core = $this->app;
        $moduleId = md5('sLMS');
        $originalName = null;

        $core['events']->listen('evolution.OnManagerPreFrameLoader', static function () use ($core, $moduleId, &$originalName): void {
            if (isset($core->modulesFromFile[$moduleId]['name'])) {
                $originalName ??= $core->modulesFromFile[$moduleId]['name'];
                $core->modulesFromFile[$moduleId]['name'] = e(__('sLMS::global.courses.list'));
            }
        });

        $core['events']->listen('evolution.OnManagerMenuPrerender', static function () use ($core, $moduleId, &$originalName): void {
            if ($originalName !== null && isset($core->modulesFromFile[$moduleId])) {
                $core->modulesFromFile[$moduleId]['name'] = $originalName;
            }
            $originalName = null;
        });
    }
}
