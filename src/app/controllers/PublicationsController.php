<?php
namespace App\Controllers;
use Core\Controller;
use App\Models\PublicationModel;
use App\Views\Admin\Publications\PublicationsView;


class PublicationsController extends Controller
{
    private PublicationModel $publicationModel;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->publicationModel = new PublicationModel();
    }

    public function index()
    {
        $validatedPublications = $this->publicationModel->getValidatedPublications();
        $pendingPublications = $this->publicationModel->getPendingPublications();
        $rejectedPublications = $this->publicationModel->getRejectedPublications();


        $view = new PublicationsView($validatedPublications, $pendingPublications, $rejectedPublications);
        
        $this->render($view);
    }


    public function validate($id)
    {
        $this->publicationModel->updatePublicationStatus($id, 'validated');
        $this->redirectWithError(BASE_PATH . '/admin/publications', 'Publication validated successfully.');
    }

    public function reject($id)
    {
        $this->publicationModel->updatePublicationStatus($id, 'rejected');
        $this->redirectWithSuccess(BASE_PATH . '/admin/publications', 'Publication rejected successfully.');
    }
}