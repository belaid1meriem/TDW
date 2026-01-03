<?php
namespace App\Controllers;
use Core\Session;
use Core\Validator;
use App\Models\User;
use App\Views\LoginView;
use Core\Controller;
use Core\Request;

class AuthController extends Controller {
    private User $userModel;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->userModel = new User();
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

        $user = $this->userModel->verify($identifier, $password);

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
        $this->userModel->updateRememberToken($user['id'], $token);
        
        // Set cookie for 30 days (persistent session)
        setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);

        if ($user['role'] === 'admin') {
            $this->redirectWithSuccess('admin', 'Welcome back, ' . $user['username'] . '!');
            return;
        }
        else {
            $this->redirectWithSuccess('/', 'Welcome back, ' . $user['username'] . '!');
            return;
        }
    }

    public function register(){
        // Validate input
        $validator = Validator::make($this->request->all(), [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $this->request->except(['password', 'password_confirmation']));
            $this->back();
            return;
        }

        // Create user
        $userId = $this->userModel->register([
            'username' => $this->request->input('username'),
            'email' => $this->request->input('email'),
            'password' => $this->request->input('password')
        ]);

        if (!$userId) {
            Session::flash('error', 'Registration failed. Please try again.');
            Session::flash('old', $this->request->except(['password', 'password_confirmation']));
            $this->back();
            return;
        }

        // Auto-login after registration
        $user = $this->userModel->find($userId);
        Session::set('user_id', $user['id']);
        Session::set('user_email', $user['email']);
        Session::set('user_username', $user['username']);
        Session::regenerate();

        $this->redirectWithSuccess('/', 'Registration successful! Welcome, ' . $user['username'] . '!');
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        $userId = Session::get('user_id');
        
        // Remove remember token
        if ($userId && isset($_COOKIE['remember_token'])) {
            $this->userModel->updateRememberToken($userId, null);
            setcookie('remember_token', '', time() - 3600, '/');
        }

        Session::destroy();
        $this->redirectWithSuccess('/login', 'You have been logged out.');
    }
}