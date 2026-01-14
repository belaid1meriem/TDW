<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\EquipesModel;
use App\Views\Admin\ShowEquipeView;
use Core\AutoCrud\VirtualModel;
use Core\Request;

class EquipesController extends Controller
{
    private EquipesModel $equipesModel;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->equipesModel = new EquipesModel();
    }

    /**
     * Show single équipe with members, publications, and resources
     */
    public function show($id)
    {
        $equipe = $this->equipesModel->getById($id);
        if (!$equipe) {
            $this->redirectWithError(BASE_PATH . '/admin/equipes', 'Équipe non trouvée.');
            return;
        }

        $members      = $this->equipesModel->getMembers($id);
        $publications = $this->equipesModel->getPublications($id);
        $resources    = $this->equipesModel->getResources($id);

        // Create VirtualModels for each table
        $equipeVm       = $this->equipesModel->vm();
        $membersVm      = VirtualModel::fromTable('equipe_member');
        $publicationsVm = VirtualModel::fromTable('publications');
        $resourcesVm    = VirtualModel::fromTable('ressources');

        $view = new ShowEquipeView(
            $equipeVm,
            $membersVm,
            $publicationsVm,
            $resourcesVm,
            $equipe,
            $members,
            $publications,
            $resources
        );

        $this->render($view);
    }
}
