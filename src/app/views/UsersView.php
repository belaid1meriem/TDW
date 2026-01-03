<?php
namespace App\Views;
use App\Views\AdminLayout;

class UsersView extends AdminLayout
{
    public function renderContent()
    {
        ?>
        <main class="admin-content">
            <h2>Gestion des utilisateurs et rôles</h2>
            <p>Contenu de la gestion des utilisateurs et rôles à implémenter ici.</p>
        </main>
        <?php
    }
}