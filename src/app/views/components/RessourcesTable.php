<?php
namespace App\Views\Components;
use Core\Components;

class RessourcesTable {

    public function renderRessourceList($ressources, $actions = true)
    {
        ob_start();
        echo Components::Table([
            'columns' => $this->getRessourceTableColumns($actions),
            'data' => $ressources,
            'striped' => true,
            'hoverable' => true
        ]);
        return ob_get_clean();
    }

    private function getRessourceTableColumns($actions = true)
    {
        $columns = [
            $this->getIdColumn(),
            $this->getNameColumn(),
            $this->getTypeColumn(),
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

    private function getNameColumn()
    {
        return [
            'key' => 'name',
            'label' => 'Name',
        ];
    }

    private function getTypeColumn()
    {
        return [
            'key' => 'type',
            'label' => 'Type'
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
        $config = [
            'libre' => ['variant' => 'success', 'text' => 'Libre'],
            'reserve' => ['variant' => 'secondary', 'text' => 'Réservé'],
            'maintenance' => ['variant' => 'destructive', 'text' => 'En Maintenance']
        ];
        
        $statusConfig = $config[$status] ?? ['variant' => 'secondary', 'text' => ucfirst($status)];
        
        return Components::Badge([
            'text' => $statusConfig['text'],
            'variant' => $statusConfig['variant']
        ]);
    }

    private function getActionsColumn()
    {
        return [
            'key' => 'id',
            'label' => 'Actions',
            'render' => function($value, $row) {
                return $this->renderRessourceActions($value, $row);
            }
        ];
    }

    private function renderRessourceActions($ressourceId, $ressource)
    {
        $viewButton = Components::Button([
            'text' => 'View',
            'size' => 'sm',
            'variant' => 'outline',
            'href' => BASE_PATH . '/admin/ressources/view/' . $ressourceId
        ]);
        
        $editButton = Components::Button([
            'text' => 'Edit',
            'size' => 'sm',
            'variant' => 'secondary',
            'href' => BASE_PATH . '/admin/ressources/edit/' . $ressourceId
        ]);
        
        // Conditional Reserve/Release button based on status
        $statusButton = $this->renderStatusButton($ressourceId, $ressource['status']);
        
        $deleteButton = Components::Button([
            'text' => 'Delete',
            'size' => 'sm',
            'variant' => 'destructive',
            'attrs' => [
                'onclick' => $this->getDeleteConfirmation($ressourceId, $ressource['name'])
            ]
        ]);
        
        ob_start();
        ?>
        <div class="flex gap-2">
            <?= $viewButton ?>
            <?= $editButton ?>
            <?= $statusButton ?>
            <?= $deleteButton ?>
        </div>
        <?php   
        return ob_get_clean();
    }

    private function renderStatusButton($ressourceId, $status)
    {
        if ($status === 'libre') {
            return Components::Button([
                'text' => 'Reserve',
                'size' => 'sm',
                'variant' => 'default',
                'attrs' => [
                    'onclick' => $this->getStatusChangeConfirmation($ressourceId, 'reserve', 'Reserve this resource?')
                ]
            ]);
        } elseif ($status === 'reserve') {
            return Components::Button([
                'text' => 'Release',
                'size' => 'sm',
                'variant' => 'default',
                'attrs' => [
                    'onclick' => $this->getStatusChangeConfirmation($ressourceId, 'libre', 'Release this resource?')
                ]
            ]);
        }
        
        return '';
    }

    private function getStatusChangeConfirmation($ressourceId, $newStatus, $message)
    {
        return sprintf(
            "if(confirm('%s')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '%s/admin/ressources/status/%d';

                var statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = '%s';
                form.appendChild(statusInput);

                document.body.appendChild(form);
                form.submit();
            }",
            htmlspecialchars($message, ENT_QUOTES),
            BASE_PATH,
            $ressourceId,
            htmlspecialchars($newStatus, ENT_QUOTES)
        );
    }

    private function getDeleteConfirmation($ressourceId, $name)
    {
        return sprintf(
            "if(confirm('Delete resource \"%s\"?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '%s/admin/ressources/delete/%d';

                var method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                form.appendChild(method);

                document.body.appendChild(form);
                form.submit();
            }",
            htmlspecialchars($name, ENT_QUOTES),
            BASE_PATH,
            $ressourceId
        );
    }
}