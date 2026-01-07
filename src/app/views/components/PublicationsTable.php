<?php
namespace App\Views\Components;
use Core\Components;

class PublicationsTable {

    public function renderPublicationList($publications, $actions = true)
    {
        ob_start();
        echo Components::Table([
            'columns' => $this->getPublicationTableColumns($actions),
            'data' => $publications,
            'striped' => true,
            'hoverable' => true
        ]);
        return ob_get_clean();
    }

    private function getPublicationTableColumns($actions = true)
    {
        $columns = [
            $this->getIdColumn(),
            $this->getTitleColumn(),
            $this->getTypeColumn(),
            $this->getDoiColumn(),
            $this->getDomaineColumn(),
            $this->getPublicationDateColumn(),
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

    private function getTitleColumn()
    {
        return [
            'key' => 'title',
            'label' => 'Title',
            'render' => function($value, $row) {
                $maxLength = 50;
                $title = strlen($value) > $maxLength ? substr($value, 0, $maxLength) . '...' : $value;
                return '<span title="' . htmlspecialchars($value) . '">' . htmlspecialchars($title) . '</span>';
            }
        ];
    }

    private function getTypeColumn()
    {
        return [
            'key' => 'type',
            'label' => 'Type',
            'render' => function($value) {
                return $this->renderTypeBadge($value);
            }
        ];
    }

    private function renderTypeBadge($type)
    {
        $variants = [
            'article' => 'default',
            'conference' => 'secondary',
            'book' => 'outline',
            'thesis' => 'destructive',
            'report' => 'default'
        ];
        
        return Components::Badge([
            'text' => ucfirst($type),
            'variant' => $variants[$type] ?? 'secondary'
        ]);
    }

    private function getDoiColumn()
    {
        return [
            'key' => 'doi',
            'label' => 'DOI',
            'render' => function($value) {
                if (empty($value)) {
                    return '<span class="text-muted">N/A</span>';
                }
                $maxLength = 30;
                $doi = strlen($value) > $maxLength ? substr($value, 0, $maxLength) . '...' : $value;
                return '<a href="https://doi.org/' . htmlspecialchars($value) . '" target="_blank" class="text-primary hover:underline" title="' . htmlspecialchars($value) . '">' . htmlspecialchars($doi) . '</a>';
            }
        ];
    }

    private function getDomaineColumn()
    {
        return [
            'key' => 'domaine',
            'label' => 'Domaine',
            'render' => function($value) {
                if (empty($value)) {
                    return '<span class="text-muted">-</span>';
                }
                $maxLength = 40;
                $domaine = strlen($value) > $maxLength ? substr($value, 0, $maxLength) . '...' : $value;
                return '<span title="' . htmlspecialchars($value) . '">' . htmlspecialchars($domaine) . '</span>';
            }
        ];
    }

    private function getPublicationDateColumn()
    {
        return [
            'key' => 'publication_date',
            'label' => 'Publication Date',
            'render' => function($value) {
                if (empty($value)) {
                    return '<span class="text-muted">N/A</span>';
                }
                return date('d/m/Y', strtotime($value));
            }
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
            'pending' => ['variant' => 'secondary', 'text' => 'Pending'],
            'validated' => ['variant' => 'success', 'text' => 'Validated'],
            'rejected' => ['variant' => 'destructive', 'text' => 'Rejected']
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
                return $this->renderPublicationActions($value, $row);
            }
        ];
    }

    private function renderPublicationActions($publicationId, $publication)
    {
        $viewButton = Components::Button([
            'text' => 'View',
            'size' => 'sm',
            'variant' => 'outline',
            'href' => BASE_PATH . '/admin/publications/view/' . $publicationId
        ]);
        
        $editButton = Components::Button([
            'text' => 'Edit',
            'size' => 'sm',
            'variant' => 'secondary',
            'href' => BASE_PATH . '/admin/publications/edit/' . $publicationId
        ]);
        
        $deleteButton = Components::Button([
            'text' => 'Delete',
            'size' => 'sm',
            'variant' => 'destructive',
            'attrs' => [
                'onclick' => $this->getDeleteConfirmation($publicationId, $publication['title'])
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

    private function getDeleteConfirmation($publicationId, $title)
    {
        return sprintf(
            "if(confirm('Delete publication \"%s\"?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '%s/admin/publications/delete/%d';

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
            $publicationId
        );
    }
}