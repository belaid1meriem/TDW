<?php
namespace App\Views\Components;
use Core\Components;


class UsersTable {

    public function renderUserList($users, $actions = true)
    {
        ob_start();
        echo Components::Table([
            'columns' => $this->getUserTableColumns($actions),
            'data' => $users,
            'striped' => true,
            'hoverable' => true
        ]);
        return ob_get_clean();
    }

    private function getUserTableColumns($actions = true)
    {
        $columns = [
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
        ];

        if ($actions) {
            $columns[] = $this->getActionsColumn();
        }
        
        return $columns;
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
                $photoPath = BASE_PATH . '/images/' . $photoFile;
                
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
            'key' => 'account_status',
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
    
        $editButton = Components::Button([
            'text' => 'Modifier',
            'size' => 'sm',
            'variant' => 'secondary',
            'href' => BASE_PATH . '/admin/users/edit/' . $userId
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
            <?= $editButton ?>
            <?= $deleteButton ?>
        </div>
        <?php   
        return ob_get_clean();
    }

    private function getDeleteConfirmation($userId, $username)
    {
        return sprintf(
            "if(confirm('Delete user %s?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'users/delete/%d';

                // Hidden input for _method=DELETE
                var method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                form.appendChild(method);

                document.body.appendChild(form);
                form.submit();
            }",
            htmlspecialchars($username, ENT_QUOTES),
            $userId
        );
    }

}