<?php
namespace App\Views\Admin\Users;
use App\Views\Admin\AdminLayout;
use Core\Components;

class UsersView extends AdminLayout
{
    private $users;

    public function __construct($users = [])
    {
        parent::__construct();
        $this->users = $users;
    }

    public function renderContent()
    {
        ?>
        <main class="flex flex-col gap-6">
            <h2>Gestion des utilisateurs et rôles</h2>
            <?= $this->renderFilterForm(); ?>
            <?= $this->renderUserList($this->users); ?>     
        </main>
        <?php
    }

    private function renderFilterForm()
    {
        echo Components::FilterForm([
            'action' => '',
            'method' => 'GET',
            'fields' => [
                [
                    'name' => 'role',
                    'label' => 'Role',
                    'type' => 'select',
                    'options' => [
                        'admin' => 'Admin',
                        'chercheur' => 'Chercheur',
                        'enseignant' => 'Enseignant',
                    ],
                    'placeholder' => 'Tous',
                    'value' => $_GET['role'] ?? ''
                ],
                [
                    'name' => 'status',
                    'label' => 'Statut',
                    'type' => 'select',
                    'options' => [
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                    ],
                    'placeholder' => 'Tous',
                    'value' => $_GET['status'] ?? ''
                ],
                [
                    'name' => 'nbr_publications',
                    'label' => 'Nombre de publications',
                    'type' => 'number',
                    'placeholder' => '3',
                    'value' => $_GET['nbr_publications'] ?? ''
                ],
                [
                    'name' => 'domaine_recherche',
                    'label' => 'Domaine de recherche',
                    'type' => 'text',
                    'placeholder' => 'Machine Learning, AI...',
                    'value' => $_GET['domaine_recherche'] ?? ''
                ],
            ]
        ]);
    }

    private function renderUserList($users)
    {
        ob_start();
        echo Components::Table([
            'columns' => $this->getUserTableColumns(),
            'data' => $users,
            'striped' => true,
            'hoverable' => true
        ]);
        return ob_get_clean();
    }

    private function getUserTableColumns()
    {
        return [
            $this->getIdColumn(),
            $this->getPhotoColumn(),
            $this->getUsernameColumn(),
            $this->getEmailColumn(),
            $this->getFirstNameColumn(),
            $this->getLastNameColumn(),
            $this->getRoleColumn(),
            $this->getGradeColumn(),
            $this->getPositionColumn(),
            $this->getStatusColumn(),
            $this->getActionsColumn(),
        ];
    }

    private function getIdColumn()
    {
        return [
            'key' => 'id',
            'label' => '#'
        ];
    }

    private function getPhotoColumn()
    {
        return [
            'key' => 'photo',
            'label' => 'Photo',
            'render' => function($value, $row) {
                // Use user's photo or default
                $photoFile = $value ?: 'default.jpg';
                $photoPath = $this->asset('images/' . $photoFile);
                
                return sprintf(
                    '<img src="%s" alt="%s" style="width: 40px; height: 40px; border-radius: 50%%; object-fit: cover;">',
                    htmlspecialchars($photoPath),
                    htmlspecialchars($row['first_name'])
                );
            }
        ];
    }

    private function getUsernameColumn()
    {
        return [
            'key' => 'username',
            'label' => 'Username'
        ];
    }

    private function getEmailColumn()
    {
        return [
            'key' => 'email',
            'label' => 'Email'
        ];
    }

    private function getFirstNameColumn()
    {
        return [
            'key' => 'first_name',
            'label' => 'First Name'
        ];
    }

    private function getLastNameColumn()
    {
        return [
            'key' => 'last_name',
            'label' => 'Last Name'
        ];
    }

    private function getRoleColumn()
    {
        return [
            'key' => 'role',
            'label' => 'Role',
            'render' => function($value) {
                return $this->renderRoleBadge($value);
            }
        ];
    }

    private function renderRoleBadge($role)
    {
        $variants = [
            'admin' => 'destructive',
            'chercheur' => 'default',
            'enseignant' => 'secondary',
            'doctorant' => 'outline'
        ];
        
        return Components::Badge([
            'text' => ucfirst($role),
            'variant' => $variants[$role] ?? 'secondary'
        ]);
    }

    private function getGradeColumn()
    {
        return [
            'key' => 'grade',
            'label' => 'Grade'
        ];
    }

    private function getPositionColumn()
    {
        return [
            'key' => 'poste',
            'label' => 'Position'
        ];
    }

    private function getStatusColumn()
    {
        return [
            'key' => 'status',
            'label' => 'Status',
            'render' => function($value) {
                return $this->renderStatusBadge($value);
            }
        ];
    }

    private function renderStatusBadge($status)
    {
        $variant = $status === 'actif' ? 'success' : 'destructive';
        
        return Components::Badge([
            'text' => ucfirst($status),
            'variant' => $variant
        ]);
    }

    private function getActionsColumn()
    {
        return [
            'key' => 'id',
            'label' => 'Actions',
            'render' => function($value, $row) {
                return $this->renderUserActions($value, $row);
            }
        ];
    }

    private function renderUserActions($userId, $user)
    {
        $viewButton = Components::Button([
            'text' => 'Activer',
            'size' => 'sm',
            'variant' => 'secondary',
            'href' => '/users/view/' . $userId
        ]);
        
        $editButton = Components::Button([
            'text' => 'Modifier',
            'size' => 'sm',
            'variant' => 'secondary',
            'href' => '/users/edit/' . $userId
        ]);
        
        $deleteButton = Components::Button([
            'text' => 'Supprimer',
            'size' => 'sm',
            'variant' => 'destructive',
            'attrs' => [
                'onclick' => $this->getDeleteConfirmation($userId, $user['username'])
            ]
        ]);
        
        ob_start();
        ?>
        <div class="flex gap-2">
            <?= $viewButton ?>
            <?= $editButton ?>
            <?= $deleteButton ?>
        </div>
        <?php   
        return ob_get_clean();
    }

    private function getDeleteConfirmation($userId, $username)
    {
        return sprintf(
            "if(confirm('Delete user %s?')) deleteUser(%d)",
            htmlspecialchars($username),
            $userId
        );
    }

}