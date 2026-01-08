<?php
namespace App\Views\Admin\Projets;

use App\Views\Admin\AdminLayout;
use Core\Components;
use App\Views\Components\ProjetsTable;

class ProjetsView extends AdminLayout
{
    private $projets;
    private $projetsTable;

    private $statsByTheme;
    private $statsByEncadrant;
    private $statsByYear;

    public function __construct($projets = [], $statsByTheme = [], $statsByEncadrant = [], $statsByYear = [])
    {
        parent::__construct();
        $this->projets = $projets;
        $this->projetsTable = new ProjetsTable();
        $this->statsByTheme = $statsByTheme;
        $this->statsByEncadrant = $statsByEncadrant;
        $this->statsByYear = $statsByYear;
    }

    public function renderContent()
    {
        ?>
        <main class="flex flex-col gap-6">

            <?php if ($this->hasFlash('success')): ?>
                <div class="alert-container">
                    <?= Components::Alert([
                        'variant' => 'success',
                        'message' => $this->flash('success'),
                        'dismissible' => true
                    ]) ?>
                </div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Gestion des projets</h2>
                <?= Components::Button([
                    'text' => 'Ajouter un projet',
                    'variant' => 'primary',
                    'href' => BASE_PATH . '/admin/projets/create'
                ]) ?>
            </div>

            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                <?= $this->renderThemeStats() ?>
                <?= $this->renderEncadrantStats() ?>
                <?= $this->renderYearStats() ?>
            </div>

            <!-- Projects Table -->
            <?= $this->projetsTable->renderProjetList($this->projets); ?>     

        </main>
        <?php
    }

    // -------------------- Stats Cards --------------------

    private function renderStatsHeader()
    {
        return Components::Card([
            'title' => 'Statistiques des projets',
            'description' => 'Répartition des projets par thématique, encadrant et année',
        ]);
    }

    private function renderThemeStats()
    {
        return Components::Card([
            'title' => 'Par thématique',
            'content' => Components::Table([
                'columns' => [
                    ['key' => 'theme', 'label' => 'Thématique'],
                    ['key' => 'total', 'label' => 'Nombre de projets'],
                ],
                'data' => $this->statsByTheme ?? [],
            ]),
        ]);
    }

    private function renderEncadrantStats()
    {
        return Components::Card([
            'title' => 'Par encadrant',
            'content' => Components::Table([
                'columns' => [
                    ['key' => 'encadrant', 'label' => 'Encadrant'],
                    ['key' => 'total', 'label' => 'Nombre de projets'],
                ],
                'data' => $this->statsByEncadrant ?? [],
            ]),
        ]);
    }

    private function renderYearStats()
    {
        return Components::Card([
            'title' => 'Par année',
            'content' => Components::Table([
                'columns' => [
                    ['key' => 'annee', 'label' => 'Année'],
                    ['key' => 'total', 'label' => 'Nombre de projets'],
                ],
                'data' => $this->statsByYear ?? [],
            ]),
        ]);
    }
}
