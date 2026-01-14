<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\ProjetsModel;
use App\Views\User\ProjetsView;

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
}