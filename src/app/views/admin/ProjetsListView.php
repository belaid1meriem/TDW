<?php
namespace App\Views\Admin;

use App\Views\Table;
use Core\AutoCrud\Views\ListView;
use Core\AutoCrud\VirtualModel;
use Core\Components;

class ProjetListView extends ListView
{
    private $statsByTheme;
    private $statsByEncadrant;
    private $statsByYear;

    public function __construct(VirtualModel $model, array $result, $statsByTheme = [], $statsByEncadrant = [], $statsByYear = [])
    {
        $this->statsByTheme = $statsByTheme;
        $this->statsByEncadrant = $statsByEncadrant;
        $this->statsByYear = $statsByYear;
        return parent::__construct($model, $result);
    }
    protected function renderFilters(): string
    {
        ?>
        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
            <?= $this->renderThemeStats() ?>
            <?= $this->renderEncadrantStats() ?>
            <?= $this->renderYearStats() ?>
        </div>
        <?php
        return parent::renderFilters();
    }


    // -------------------- Stats Cards --------------------

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
