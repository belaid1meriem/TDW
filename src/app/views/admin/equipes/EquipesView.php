<?php
namespace App\Views\Admin\Equipes;
use App\Views\Admin\AdminLayout;
use Core\Components;

class EquipesView extends AdminLayout
{
    private $equipes;

    public function __construct($equipes = [])
    {
        parent::__construct();
        $this->equipes = $equipes;
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
                <h2>Gestion des équipes</h2>
                <?= Components::Button([
                    'text' => 'Ajouter une équipe',
                    'variant' => 'primary',
                    'href' => $this->asset('admin/equipes/create')
                ]); ?>
            </div>
            
            <?= $this->renderEquipesList($this->equipes); ?> 
            

        </main>
        <?php
    }


    private function renderEquipesList($equipes)
    {
        ob_start();
        echo Components::Table([
            'columns' => $this->getEquipeTableColumns(),
            'data' => $equipes,
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
        $viewButton = Components::Button([
            'text' => 'Voir détails',
            'size' => 'sm',
            'variant' => 'secondary',
            'href' => BASE_PATH.'/admin/equipes/view/' . $equipe_id
        ]);
        
        
        ob_start();
        ?>
        <div class="flex gap-2">
            <?= $viewButton ?>
        </div>
        <?php   
        return ob_get_clean();
    }



}