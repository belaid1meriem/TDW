<?php
namespace App\Controllers;
use Core\Controller;
use App\Models\EquipeModel;
use App\Views\Admin\Equipes\EquipesView;
use App\Views\Admin\Equipes\EquipeDetailsView;

class EquipesController extends Controller
{
    public function index()
    {
        $equipeModel = new EquipeModel();
        $equipes = $equipeModel->all();

        $view = new EquipesView($equipes);
        $this->render($view);
    }

    public function view($id)
    {
        $equipeModel = new EquipeModel();
        $equipe = $equipeModel->find($id);
        $members = $equipeModel->getMembers($id);
        $resources = $equipeModel->getResources($id);
        $publications = $equipeModel->getPublications($id);

        if (!$equipe) {
            $this->redirectWithError(BASE_PATH.'/admin/equipes','Équipe non trouvée.');
            return;
        }


        $view = new EquipeDetailsView($equipe, $members, $resources, $publications);
        $this->render($view);
    }
}