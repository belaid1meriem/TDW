<?php
namespace App\Views\Admin;

use App\Views\Table;
use Core\AutoCrud\VirtualModel;
use Core\AutoCrud\Views\ShowView;
use Core\Components;

class ShowEquipeView extends ShowView
{
    private Table $membresTable;
    private Table $publicationsTable;
    private Table $ressourcesTable;

    public function __construct(
        VirtualModel $model,
        VirtualModel $membreModel,
        VirtualModel $publicationModel,
        VirtualModel $ressourceModel,
        array $record,
        array $membres,
        array $publications,
        array $ressources
    ) {
        $this->membresTable       = new Table($membreModel, $membres);
        $this->publicationsTable = new Table($publicationModel, $publications);
        $this->ressourcesTable   = new Table($ressourceModel, $ressources);

        parent::__construct($model, $record);
    }

    protected function renderDetails(): string
    {
        ob_start();

        echo parent::renderDetails();

        /* ===================== Membres ===================== */
        ?>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Membres</h3>
            <?= Components::Button([
                'text' => 'Ajouter membre',
                'variant' => 'outline',
                'href' => BASE_PATH . '/admin/equipe_member/create'
            ]) ?>
        </div>
        <?php
        $this->membresTable->render();

        /* ===================== Publications ===================== */
        ?>
        <h3 class="text-lg font-semibold mb-3 mt-6">Publications</h3>
        <?php
        $this->publicationsTable->render();

        /* ===================== Ressources ===================== */
        ?>
        <h3 class="text-lg font-semibold mb-3 mt-6">Ressources Allouées</h3>
        <?php
        $this->ressourcesTable->render();

        return ob_get_clean();
    }
}
