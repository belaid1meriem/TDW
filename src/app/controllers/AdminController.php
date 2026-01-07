<?php
namespace App\Controllers;

use Core\Controller;
use App\Views\Admin\AdminView;

class AdminController extends Controller
{
    public function dashboard()
    {
        $view = new AdminView();
        $this->render($view);
    }
}