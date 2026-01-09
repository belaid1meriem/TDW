<?php
namespace App\Views\Admin;
use App\Views\Admin\AdminLayout;
use Core\Components;

class AdminView extends AdminLayout
{
    public function renderContent()
    {
        ?>
        <main class="admin-content">
        <?php
        echo $this->renderCards();
        ?>
        </main>
        <?php
    }

    private function renderCards()
    {
        ob_start();
        $titles = [
            ["Gestion des utilisateurs et rôles", "admin/users"],
            ["Gestion des équipes", "admin/equipes"],
            ["Gestion des projets de recherche", "admin/projets"],
            ["Gestion des équipements et ressources", "admin/ressources"],
            ["Gestion des publications et base documentaire", "admin/publications"],
            ["Gestion des événements et communications", "admin/evenements"],
            ["Paramètres généraux de l’application", "admin/configurations"],
        ];
        foreach ($titles as $title) {
            echo Components::Card([
                'class' => 'admin-card',
                'content' => $this->renderCardContent($title[0]),
                'attrs' => ['onclick' => "window.location.href='{$title[1]}'"],
            ]);
        }

        return ob_get_clean();
    }

    private function renderCardContent($title): string
    {
        ob_start();
        ?>
        <h4><?= $title ?></h4>
        <?php
        return ob_get_clean();
    }

}
?>

<style>
    .admin-content {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        padding : 1rem;
    }

    
    @media (max-width: 900px) {
        .admin-content { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
        .admin-content { grid-template-columns: 1fr; }
    }
    .admin-card {
        display: flex;
        flex-direction: column;
        min-height: 8rem;
        align-items: center;
        justify-content: center;
        text-align: center;
        transition: transform 0.2s ease-in-out;
    }

    .admin-card:hover {
        cursor: pointer;
        transform: scale(1.05);
    }
</style>