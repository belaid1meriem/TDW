<?php
namespace App\Views\Admin\Users;
use App\Views\Admin\AdminLayout;
use Core\Components;
use App\Views\Components\UsersTable;

class UsersView extends AdminLayout
{
    private $users;
    private $usersTable;
    
    public function __construct($users = [])
    {
        parent::__construct();
        $this->users = $users;
        $this->usersTable = new UsersTable();
    }

    public function renderContent()
    {
        ?>
        <main class="flex flex-col gap-6">
            <?php if ($this->hasFlash('success')): ?>
                <div class="alert-container">
                    <?php
                    echo Components::Alert([
                        'variant' => 'success',
                        'message' => $this->flash('success'),
                        'dismissible' => true
                    ]);
                    ?>
                </div>
            <?php endif; ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Gestion des utilisateurs et rôles</h2>
                <?= Components::Button([
                    'text' => 'Ajouter un utilisateur',
                    'variant' => 'primary',
                    'href' => 'users/create'
                ]); ?>
            </div>
            
            <?= $this->renderFilterForm(); ?>
            <?= $this->usersTable->renderUserList($this->users); ?>     
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
                    'name' => 'account_status',
                    'label' => 'Statut',
                    'type' => 'select',
                    'options' => [
                        'actif' => 'Actif',
                        'suspendu' => 'Suspendu',
                    ],
                    'placeholder' => 'Tous',
                    'value' => $_GET['account_status'] ?? ''
                ],
                [
                    'name' => 'nbr_publications',
                    'label' => 'Nombre de publications',
                    'type' => 'number',
                    'placeholder' => '3',
                    'value' => $_GET['nbr_publications'] ?? ''
                ],
                [
                    'name' => 'domain_research',
                    'label' => 'Domaine de recherche',
                    'type' => 'text',
                    'placeholder' => 'Machine Learning, AI...',
                    'value' => $_GET['domain_research'] ?? ''
                ],
            ]
        ]);
    }



}