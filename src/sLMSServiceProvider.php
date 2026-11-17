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
     * Load the schema and learner pages and register the Evo UI catalogue tables.
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
        }
    }
}
