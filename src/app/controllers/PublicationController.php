<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\PublicationsModel;
use App\Views\User\PublicationsView;
use App\Views\User\ShowPublicationView;

class PublicationController extends Controller
{
    private $publicationModel;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->publicationModel = new PublicationsModel();
    }

    public function index()
    {
        $filters = $this->request->query();
        $results = $this->publicationModel->getAll($filters);

        $view = new PublicationsView($this->publicationModel->vm(), $results);
        $this->render($view);
    }

    public function show($id)
    {
        $record = $this->publicationModel->getById($id);

        $view = new ShowPublicationView($this->publicationModel->vm(), $record);
        $this->render($view);
    }
}
