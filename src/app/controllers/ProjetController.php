<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\ProjetsModel;
use App\Views\User\ProjetsView;
use App\Views\User\ShowProjetView;
use Core\AutoCrud\VirtualModel;

class ProjetController extends Controller{

    private $projetModel;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->projetModel = new ProjetsModel();
    }


    public function index(){
        $filters = $this->request->query();
        $results = $this->projetModel->getAll($filters);

        $view = new ProjetsView($this->projetModel->vm(), $results);
        $this->render($view);
    }

    public function show($id){
        $record = $this->projetModel->getById($id);
        $pubs = $this->projetModel->getPublications($id);
        $pubsVm = VirtualModel::fromTable('publications');
        $view = new ShowProjetView($this->projetModel->vm(), $pubsVm, $record, $pubs);
        $this->render($view);
    }
}