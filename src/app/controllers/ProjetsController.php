<?php
namespace App\Controllers;
use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\ProjetModel;
use App\Views\Admin\Projets\ProjetsView;
use App\Views\Admin\Projets\AddProjetView;
use App\Views\Admin\Projets\EditProjetView;
use App\Views\Admin\Projets\ViewProjetView;

class ProjetsController extends Controller
{
    private ProjetModel $projetModel;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->projetModel = new ProjetModel();
    }

    public function index()
    {
        $projets = $this->projetModel->all();
        $statsByTheme = $this->projetModel->statsByTheme();
        $statsByEncadrant = $this->projetModel->statsByEncadrant();
        $statsByYear = $this->projetModel->statsByYear();


        $view = new ProjetsView($projets, $statsByTheme, $statsByEncadrant, $statsByYear);
        
        $this->render($view);
    }

    public function create()
    {
        $view = new AddProjetView();
        $this->render($view);
    }

    public function store()
    {
        $validator = Validator::make($this->request->all(), [
            'title' => 'required|max:255',
            'theme' => 'required|max:100',
            'financement' => 'required|max:255',
            'status' => 'required|in:en cours,termine,soumis',
            'start_date' => 'required|date',
            'end_date' => 'date',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }

        $projetData = [
            'title' => $this->request->input('title'),
            'theme' => $this->request->input('theme'),
            'financement' => $this->request->input('financement'),
            'status' => $this->request->input('status'),
            'start_date' => $this->request->input('start_date'),
            'end_date' => $this->request->input('end_date'),
        ];

        $projetId = $this->projetModel->create($projetData);

        if (!$projetId) {
            Session::flash('error', 'Failed to create projet. Please try again.');
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }

        $this->redirectWithSuccess(BASE_PATH . '/admin/projets', 'Projet created successfully!');
    }

    public function edit($id)
    {
        $projet = $this->projetModel->find($id);

        if (!$projet) {
            $this->redirectWithError(BASE_PATH . '/admin/projets', 'Projet not found.');
            return;
        }

        $view = new EditProjetView($projet);
        $this->render($view);
    }

    public function update($id)
    {
        $projet = $this->projetModel->find($id);

        if (!$projet) {
            $this->redirectWithError(BASE_PATH . '/admin/projets', 'Projet not found.');
            return;
        }

        $validator = Validator::make($this->request->all(), [
            'title' => 'required|max:255',
            'theme' => 'required|max:100',
            'financement' => 'required|max:255',
            'status' => 'required|in:en cours,termine,soumis',
            'start_date' => 'required|date',
            'end_date' => 'date',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }

        $projetData = [
            'title' => $this->request->input('title'),
            'theme' => $this->request->input('theme'),
            'financement' => $this->request->input('financement'),
            'status' => $this->request->input('status'),
            'start_date' => $this->request->input('start_date'),
            'end_date' => $this->request->input('end_date'),
        ];

        $result = $this->projetModel->updateById($id, $projetData);

        if ($result === false) {
            Session::flash('error', 'Failed to update projet. Please try again.');
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }

        $this->redirectWithSuccess(BASE_PATH . '/admin/projets', 'Projet updated successfully!');
    }

    public function view($id)
    {
        $projet = $this->projetModel->find($id);

        if (!$projet) {
            $this->redirectWithError(BASE_PATH . '/admin/projets', 'Projet not found.');
            return;
        }

        $view = new ViewProjetView($projet);
        $this->render($view);
    }

    public function delete($id)
    {
        $projet = $this->projetModel->find($id);

        if (!$projet) {
            Session::flash('error', 'Projet not found.');
            $this->redirect(BASE_PATH . '/admin/projets');
            return;
        }

        $result = $this->projetModel->deleteById($id);

        if ($result === false) {
            $this->redirectWithError(BASE_PATH . '/admin/projets', 'Failed to delete projet.');
            return;
        }

        $this->redirectWithSuccess(BASE_PATH . '/admin/projets', 'Projet deleted successfully!');
    }
}
?>