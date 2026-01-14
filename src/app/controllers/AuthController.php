<?php
namespace App\Controllers;
use Core\Session;
use Core\Validator;
use App\Models\UsersModel;
use App\Views\LoginView;
use Core\Controller;
use Core\Request;
use App\Models\RoleModel;

class AuthController extends Controller {
    private UsersModel $UsersModel;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->UsersModel = new UsersModel();
    }


    public function showLogin() {
        $view = new LoginView();
        $this->render($view);
    }

    /**
     * Handle login
     */
    public function login()
    {
        // Validate input
        $validator = Validator::make($this->request->all(), [
            'identifier' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }

        // Verify credentials
        $identifier = $this->request->input('identifier');
        $password = $this->request->input('password');

        $user = $this->UsersModel->verifyPassword($identifier, $password);

        if (!$user) {
            Session::flash('error', 'Invalid credentials');
            Session::flash('old', $this->request->except('password'));
            $this->back();
            return;
        }

        // Set session
        Session::set('user_id', $user['id']);
        Session::set('user_email', $user['email']);
        Session::set('user_username', $user['username']);
        Session::regenerate();

        // Always set remember token for persistent session
        $token = bin2hex(random_bytes(32));
        $this->UsersModel->updateRememberToken($user['id'], $token);
        
        // Set cookie for 30 days (persistent session)
        setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);

        // Redirect based on role
        $roleModel = new RoleModel();
        $role = $roleModel->getRoleName($user['role_id']);
        
        if ($role === 'admin') {
            $this->redirectWithSuccess('admin', 'Welcome back, ' . $user['username'] . '!');
            return;
        }
        else {
            $this->redirectWithSuccess('/', 'Welcome back, ' . $user['username'] . '!');
            return;
        }
    }


    /**
     * Handle logout
     */
    public function logout()
    {
        $userId = Session::get('user_id');
        
        // Remove remember token
        if ($userId && isset($_COOKIE['remember_token'])) {
            $this->UsersModel->updateRememberToken($userId, null);
            setcookie('remember_token', '', time() - 3600, '/');
        }

        Session::destroy();
        $this->redirectWithSuccess('/login', 'You have been logged out.');
    }
}