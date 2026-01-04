<?php
namespace App\Controllers;
use Core\Controller;
use App\Views\UsersView;
use App\Models\UserModel;

class UsersController extends Controller
{
    public function index()
    {
        $filters = $this->request->query();
        $filters['domaine_recherche'] = !empty($filters['domaine_recherche'])
        ? ['operator' => 'LIKE', 'value' => $filters['domaine_recherche']]
        : '';
        $filters = array_filter($filters, fn($v) => $v !== '');


        $userModel = new UserModel();
        $users = $userModel->select(conditions: $filters);

        $view = new UsersView($users);
        $this->render($view);
    }
}