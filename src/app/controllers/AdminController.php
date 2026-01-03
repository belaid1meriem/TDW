<?php
namespace App\Controllers;

use Core\Controller;
use App\Views\AdminView;

class AdminController extends Controller
{
    public function dashboard()
    {
        $view = new AdminView();
        $this->render($view);
    }
}