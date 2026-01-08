<?php
namespace App\Views\Admin\Equipes;
use App\Views\Admin\AdminLayout;
use Core\Components;
use App\Views\Components\UsersTable;
use App\Views\Components\RessourcesTable;
use App\Views\Components\PublicationsTable;

class EquipeDetailsView extends AdminLayout
{
    private $equipe;
    private $members;
    private $resources;
    private $publications;

    public function __construct($equipe, $members = [], $resources = [], $publications = [])
    {
        parent::__construct();
        $this->equipe = $equipe;
        $this->members = $members;
        $this->resources = $resources;
        $this->publications = $publications;
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
                <h2>Détails de l'équipe <?= $this->equipe['name'] ?></h2>
                <?= Components::Button([
                    'text' => '← Retour',
                    'variant' => 'outline',
                    'href' => $this->asset('admin/equipes')
                ]); ?>
            </div>

            <?= $this->renderEquipeDetails($this->equipe); ?>  
            
            <h3>Les Membres de l'equipe</h3>
            <?= $this->renderMembersList($this->members) ?>


            <h3>Les Ressources allouées</h3>
            <?= $this->renderResourcesList($this->resources) ?>


            <h3>Les Publications</h3>
            <?= $this->renderPublicationsList($this->publications) ?>

        </main>
        <?php
    }


    private function renderEquipeDetails($equipe)
    {
        ob_start();
        echo Components::Table([
            'columns' => $this->getEquipeTableColumns(),
            'data' => [$equipe],
            'striped' => true,
            'hoverable' => true
        ]);
        return ob_get_clean();
    }

    private function getEquipeTableColumns()
    {
        return [
            $this->getIdColumn(),
            $this->getNomColumn(),
            $this->getDescriptionColumn(),
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

    private function getNomColumn()
    {
        return [
            'key' => 'name',
            'label' => 'Nom de l\'équipe'
        ];
    }

    private function getDescriptionColumn()
    {
        return [
            'key' => 'description',
            'label' => 'Description'
        ];
    }

   

    private function getActionsColumn()
    {
        return [
            'key' => 'id',
            'label' => 'Actions',
            'render' => function($value, $row) {
                return $this->renderActions($value, $row);
            }
        ];
    }

    private function renderActions($equipe_id, $equipe)
    {
        $editButton = Components::Button([
            'text' => 'Modifier',
            'size' => 'sm',
            'variant' => 'secondary',
            'href' => '/equipes/view/' . $equipe_id // to change
        ]);
        $addMemberButton = Components::Button([
            'text' => 'Ajouter un membre',
            'size' => 'sm',
            'variant' => 'secondary',
            'href' => BASE_PATH.'/admin/equipes/addMember/' . $equipe_id // to change
        ]);
        
        
        ob_start();
        ?>
        <div class="flex gap-2">
            <?= $editButton ?>
            <?= $addMemberButton ?>
        </div>
        <?php   
        return ob_get_clean();
    }


    private function renderMembersList($members)
    {
        ob_start();
        $usersTable = new UsersTable();
        echo $usersTable->renderUserList($members, false);
        return ob_get_clean();
    }


    private function renderResourcesList($resources)
    {
        ob_start();

        $ressourcesTable = new RessourcesTable();
        echo $ressourcesTable->renderRessourceList($resources, false);
        return ob_get_clean();
    }

    private function renderPublicationsList($publications)
    {
        ob_start();

        $publicationsTable = new PublicationsTable();
        echo $publicationsTable->renderPublicationList($publications, false);
        return ob_get_clean();
    }
}