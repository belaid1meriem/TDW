<?php
namespace App\Controllers;
use Core\Controller;
use App\Models\EquipeModel;
use App\Views\Admin\Equipes\EquipesView;
use App\Views\Admin\Equipes\EquipeDetailsView;
use App\Views\Admin\Equipes\AddMemberView;
use App\Models\UserModel;
use App\Views\Admin\Equipes\AddEquipeView;
use Core\Validator;
use Core\Session;

class EquipesController extends Controller
{
    private EquipeModel $equipeModel;
    private UserModel $userModel;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->equipeModel = new EquipeModel();
        $this->userModel = new UserModel();
    }

    public function addMember($equipeId)
    {
        $equipe = $this->equipeModel->find($equipeId);

        if (!$equipe) {
            $this->redirectWithError(BASE_PATH . '/admin/equipes', 'Equipe not found.');
            return;
        }

        $users = $this->userModel->all('last_name ASC, first_name ASC');
        $existingMemberIds = $this->equipeModel->getMemberIds($equipeId);

        $view = new AddMemberView($equipe, $users, $existingMemberIds);
        $this->render($view);
    }

    public function storeMember($equipeId)
    {
        $equipe = $this->equipeModel->find($equipeId);

        if (!$equipe) {
            $this->redirectWithError(BASE_PATH . '/admin/equipes', 'Equipe not found.');
            return;
        }

        $validator = Validator::make($this->request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }

        $userId = $this->request->input('user_id');

        if ($this->equipeModel->isMember($equipeId, $userId)) {
            Session::flash('error', 'This user is already a member of this equipe.');
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }

        $result = $this->equipeModel->addMember($equipeId, $userId);

        if (!$result) {
            Session::flash('error', 'Failed to add member. Please try again.');
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }

        $this->redirectWithSuccess(BASE_PATH . "/admin/equipes/view/{$equipeId}", 'Member added successfully!');
    }

    public function removeMember($equipeId, $userId)
    {
        $equipe = $this->equipeModel->find($equipeId);

        if (!$equipe) {
            $this->redirectWithError(BASE_PATH . '/admin/equipes', 'Equipe not found.');
            return;
        }

        if (!$this->equipeModel->isMember($equipeId, $userId)) {
            $this->redirectWithError(BASE_PATH . "/admin/equipes/view/{$equipeId}", 'User is not a member of this equipe.');
            return;
        }

        $result = $this->equipeModel->removeMember($equipeId, $userId);

        if (!$result) {
            $this->redirectWithError(BASE_PATH . "/admin/equipes/view/{$equipeId}", 'Failed to remove member. Please try again.');
            return;
        }

        $this->redirectWithSuccess(BASE_PATH . "/admin/equipes/view/{$equipeId}", 'Member removed successfully!');
    }


    public function index()
    {
        $equipes = $this->equipeModel->all();

        $view = new EquipesView($equipes);
        $this->render($view);
    }

    public function view($id)
    {
        $equipe = $this->equipeModel->find($id);
        $members = $this->equipeModel->getMembers($id);
        $resources = $this->equipeModel->getResources($id);
        $publications = $this->equipeModel->getPublications($id);

        if (!$equipe) {
            $this->redirectWithError(BASE_PATH.'/admin/equipes','Équipe non trouvée.');
            return;
        }


        $view = new EquipeDetailsView($equipe, $members, $resources, $publications);
        $this->render($view);
    }


    public function create(){
        $view = new AddEquipeView();
        $this->render($view);
    }

    public function store(){
        $validator = Validator::make($this->request->all(), [
            'name' => 'required',
            'description' => 'required'
        ]);


        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }

        $equipeData = [
            'name' => $this->request->input('name'),
            'description' => $this->request->input('description')
        ];

        $equipeId = $this->equipeModel->create($equipeData);

        if (!$equipeId) {
            Session::flash('error', 'Failed to create projet. Please try again.');
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }

        $this->redirectWithSuccess(BASE_PATH . '/admin/equipes', 'Projet created successfully!');
    }
}