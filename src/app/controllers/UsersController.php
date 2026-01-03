<?php
namespace App\Controllers;
use Core\Controller;
use App\Views\UsersView;
class UsersController extends Controller
{
    public function index()
    {
        $view = new UsersView();
        $this->render($view);
    }
}