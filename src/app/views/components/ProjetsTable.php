<?php
namespace App\Views\Components;
use Core\Components;

class ProjetsTable {

    public function renderProjetList($projets, $actions = true)
    {
        ob_start();
        echo Components::Table([
            'columns' => $this->getProjetTableColumns($actions),
            'data' => $projets,
            'striped' => true,
            'hoverable' => true
        ]);
        return ob_get_clean();
    }

    private function getProjetTableColumns($actions = true)
    {
        $columns = [
            $this->getIdColumn(),
            $this->getTitleColumn(),
            $this->getThemeColumn(),
            $this->getFinancementColumn(),
            $this->getStatusColumn(),
            $this->getStartDateColumn(),
            $this->getEndDateColumn(),
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

    private function getTitleColumn()
    {
        return [
            'key' => 'title',
            'label' => 'Titre'
        ];
    }

    private function getThemeColumn()
    {
        return [
            'key' => 'theme',
            'label' => 'Thème'
        ];
    }

    private function getFinancementColumn()
    {
        return [
            'key' => 'financement',
            'label' => 'Financement'
        ];
    }

    private function getStatusColumn()
    {
        return [
            'key' => 'status',
            'label' => 'Statut',
            'render' => function($value) {
                return $this->renderStatusBadge($value);
            }
        ];
    }

    private function renderStatusBadge($status)
    {
        $variants = [
            'en cours' => 'default',
            'termine' => 'success',
            'soumis' => 'secondary'
        ];

        $labels = [
            'en cours' => 'En cours',
            'termine' => 'Terminé',
            'soumis' => 'Soumis'
        ];
        
        return Components::Badge([
            'text' => $labels[$status] ?? ucfirst($status),
            'variant' => $variants[$status] ?? 'secondary'
        ]);
    }

    private function getStartDateColumn()
    {
        return [
            'key' => 'start_date',
            'label' => 'Date début'
        ];
    }

    private function getEndDateColumn()
    {
        return [
            'key' => 'end_date',
            'label' => 'Date fin'
        ];
    }

    private function getActionsColumn()
    {
        return [
            'key' => 'id',
            'label' => 'Actions',
            'render' => function($value, $row) {
                return $this->renderProjetActions($value, $row);
            }
        ];
    }

    private function renderProjetActions($projetId, $projet)
    {
        $viewButton = Components::Button([
            'text' => 'Voir',
            'size' => 'sm',
            'variant' => 'secondary',
            'href' => BASE_PATH . '/admin/projets/view/' . $projetId
        ]);
        
        $editButton = Components::Button([
            'text' => 'Modifier',
            'size' => 'sm',
            'variant' => 'secondary',
            'href' => BASE_PATH . '/admin/projets/edit/' . $projetId
        ]);
        
        $deleteButton = Components::Button([
            'text' => 'Supprimer',
            'size' => 'sm',
            'variant' => 'destructive',
            'attrs' => [
                'onclick' => $this->getDeleteConfirmation($projetId, $projet['title'])
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

    private function getDeleteConfirmation($projetId, $title)
    {
        return sprintf(
            "if(confirm('Supprimer le projet %s?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '%s/admin/projets/delete/%d';

                var method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                form.appendChild(method);

                document.body.appendChild(form);
                form.submit();
            }",
            htmlspecialchars($title, ENT_QUOTES),
            BASE_PATH,
            $projetId
        );
    }
}