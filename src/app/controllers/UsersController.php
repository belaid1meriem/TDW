<?php
namespace App\Controllers;
use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\UserModel;
use App\Views\Admin\Users\UsersView;
use App\Views\Admin\Users\AddUserView;
use App\Views\Admin\Users\EditUserView;
use App\Views\Admin\Users\ViewUserView;

class UsersController extends Controller
{
    private UserModel $userModel;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $filters = $this->request->query();
        $filters['domaine_recherche'] = !empty($filters['domaine_recherche'])
            ? ['operator' => 'LIKE', 'value' => $filters['domaine_recherche']]
            : '';
        $filters = array_filter($filters, fn($v) => $v !== '');

        $users = $this->userModel->select(conditions: $filters);

        $view = new UsersView($users);
        $this->render($view);
    }

    public function create()
    {
        $view = new AddUserView();
        $this->render($view);
    }

    public function store()
    {
        // Validate input
        $validator = Validator::make($this->request->all(), [
            'email' => 'required|email|unique:users,email',
            'username' => 'required|min:3|max:50|unique:users,username',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'role' => 'required|in:admin,enseignant,chercheur,doctorant',
            'status' => 'required|in:actif,suspendu,inactif',
            'grade' => 'max:50',
            'poste' => 'max:100'
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $this->request->except(['password', 'password_confirmation']));
            $this->back();
            return;
        }

        // Handle photo upload
        $photoPath = null;
        if ($this->request->hasFile('photo')) {
            try {
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                $photoPath = $this->uploadFile('photo', 'public/images/', $allowedTypes);
                // Store only the filename in database
                $photoPath = basename($photoPath);
            } catch (\Exception $e) {
                Session::flash('error', 'Photo upload failed: ' . $e->getMessage());
                Session::flash('old', $this->request->except(['password', 'password_confirmation']));
                $this->back();
                return;
            }
        }

        // Prepare user data
        $userData = [
            'email' => $this->request->input('email'),
            'username' => $this->request->input('username'),
            'password' => password_hash($this->request->input('password'), PASSWORD_BCRYPT),
            'first_name' => $this->request->input('first_name'),
            'last_name' => $this->request->input('last_name'),
            'role' => $this->request->input('role'),
            'status' => $this->request->input('status', 'actif'),
            'grade' => $this->request->input('grade'),
            'poste' => $this->request->input('poste'),
            'photo' => $photoPath,
            'domaine_recherche' => $this->request->input('domaine_recherche'),
            'bio' => $this->request->input('bio'),
            'documents_personnels' => $this->request->input('documents_personnels')
        ];

        // Create user
        $userId = $this->userModel->create($userData);

        if (!$userId) {
            Session::flash('error', 'Failed to create user. Please try again.');
            Session::flash('old', $this->request->except(['password', 'password_confirmation']));
            $this->back();
            return;
        }

        $this->redirectWithSuccess('admin/users', 'User created successfully!');
    }

    public function edit($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            $this->redirectWithError('/admin/users', 'User not found.');
            return;
        }

        $view = new EditUserView($user);
        $this->render($view);
    }

    public function update($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            $this->redirectWithError('/admin/users', 'User not found.');
            return;
        }

        // Validate input
        $rules = [
            'email' => 'required|email',
            'username' => 'required|min:3|max:50',
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'role' => 'required|in:admin,enseignant,chercheur,doctorant',
            'status' => 'required|in:actif,suspendu,inactif',
            'grade' => 'max:50',
            'poste' => 'max:100'
        ];

        // Only validate password if provided
        if ($this->request->input('password')) {
            $rules['password'] = 'min:8';
            $rules['password_confirmation'] = 'same:password';
        }

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $this->request->except(['password', 'password_confirmation']));
            $this->back();
            return;
        }

        // Handle photo upload
        $photoPath = $user['photo'];
        if ($this->request->hasFile('photo')) {
            try {
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                $newPhotoPath = $this->uploadFile('photo', 'public/images/', $allowedTypes);
                
                // Delete old photo if exists
                if ($photoPath && file_exists("public/images/{$photoPath}")) {
                    unlink("public/images/{$photoPath}");
                }
                
                $photoPath = basename($newPhotoPath);
            } catch (\Exception $e) {
                Session::flash('error', 'Photo upload failed: ' . $e->getMessage());
                Session::flash('old', $this->request->except(['password', 'password_confirmation']));
                $this->back();
                return;
            }
        }

        // Prepare update data
        $userData = [
            'email' => $this->request->input('email'),
            'username' => $this->request->input('username'),
            'first_name' => $this->request->input('first_name'),
            'last_name' => $this->request->input('last_name'),
            'role' => $this->request->input('role'),
            'status' => $this->request->input('status'),
            'grade' => $this->request->input('grade'),
            'poste' => $this->request->input('poste'),
            'photo' => $photoPath,
            'domaine_recherche' => $this->request->input('domaine_recherche'),
            'bio' => $this->request->input('bio'),
            'documents_personnels' => $this->request->input('documents_personnels')
        ];

        // Update password only if provided
        if ($this->request->input('password')) {
            $userData['password'] = password_hash($this->request->input('password'), PASSWORD_BCRYPT);
        }

        // Update user
        $result = $this->userModel->updateById($id, $userData);

        if ($result === false) {
            Session::flash('error', 'Failed to update user. Please try again.');
            Session::flash('old', $this->request->except(['password', 'password_confirmation']));
            $this->back();
            return;
        }

        $this->redirectWithSuccess('/admin/users', 'User updated successfully!');
    }

    public function delete($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            Session::flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        // Delete user photo if exists
        if ($user['photo'] && file_exists("public/images/{$user['photo']}")) {
            unlink("public/images/{$user['photo']}");
        }

        // Delete user
        $result = $this->userModel->deleteById($id);

        if ($result === false) {
            $this->redirectWithError('/admin/users', 'Failed to delete user.');
            return;
        }

        $this->redirectWithSuccess('/admin/users', 'User deleted successfully!');
    }

    public function view($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            $this->redirectWithError('/admin/users', 'User not found.');
            return;
        }

        $view = new ViewUserView($user);
        $this->render($view);
    }
}
?>