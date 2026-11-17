<?php

namespace Seiger\sLMS\Components;

use EvoUI\Livewire\ModuleTable;
use Livewire\Attributes\Locked;
use Seiger\sLMS\Support\ManagerAccess;

/**
 * Extend Evo UI's table with navigation between levels of the course catalogue.
 *
 * @since 0.1.0
 */
class CatalogueTable extends ModuleTable
{
    #[Locked]
    public string $preset = '';

    /** @var array<string, mixed> */
    #[Locked]
    public array $context = [];

    /**
     * Ask the owning panel to open the selected course or module.
     *
     * @since 0.1.0
     */
    public function openStructure(int $id): void
    {
        ManagerAccess::authorize();

        match ($this->preset) {
            'slms.courses' => $this->dispatch('slms-open-course', id: $id)->to(ModulePanel::class),
            'slms.modules' => $this->dispatch('slms-open-module', id: $id)->to(ModulePanel::class),
            default => null,
        };
    }

    /**
     * Ask the catalogue panel to show the selected module's assessment.
     *
     * @since 0.1.0
     */
    public function openAssessment(int $id): void
    {
        ManagerAccess::authorize();

        if ($this->preset === 'slms.modules') {
            $this->dispatch('slms-open-module', id: $id, assessment: true)->to(ModulePanel::class);
        }
    }

    /**
     * Enable ordering only for managers who can edit course content.
     *
     * @since 0.1.0
     */
    public function reorderEnabled(): bool
    {
        return parent::reorderEnabled() && ManagerAccess::allows(write: true);
    }

    /**
     * Keep edited items in view and show newly appended curriculum records.
     *
     * @since 0.1.0
     */
    public function saveModal(): void
    {
        $creating = $this->modalMode === 'create';
        $page = $this->page;
        parent::saveModal();

        if ($this->preset !== 'slms.courses') {
            $target = $creating ? $this->lastPageNumber() : $page;
            if ($target !== $this->page) {
                $this->goToPage($target);
            }
        }
    }
}
