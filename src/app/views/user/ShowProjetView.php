<?php
namespace App\Views\User;

use App\Views\Table;
use Core\AutoCrud\VirtualModel;

class ShowProjetView extends UserShowView {
    private Table $publicationsTable;

    public function __construct(VirtualModel $model, VirtualModel $publicationModel, array $record, array $publications)
    {
        $this->publicationsTable = new Table($publicationModel, $publications);
        return parent::__construct($model, $record);
    }

    protected function renderDetails(): string
    {
        ob_start();
        echo parent::renderDetails();
        echo '<h3 class="text-lg font-semibold mb-3">Publications associés</h3>';
        $this->publicationsTable->render();
        return ob_get_clean();

    }

}