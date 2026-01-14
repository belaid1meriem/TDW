<?php

namespace Core\AutoCrud\Views;

use Core\View;
use Core\Components;
use Core\AutoCrud\VirtualModel;

/**
 * ListView - Generic list view for any table
 * 
 * Automatically renders a table with columns, filters, pagination,
 * and action buttons based on VirtualModel metadata
 */
class ListView extends View
{
    protected VirtualModel $model;
    protected array $result;
    
    public function __construct(VirtualModel $model, array $result)
    {
        parent::__construct();
        $this->model = $model;
        $this->result = $result;
    }

    public function render()
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $this->escape($this->model->getTableLabel()) ?></title>
            <link rel="stylesheet" href="<?= $this->asset('css/base.css') ?>">
        </head>
        <body class="main-layout">
            <?php $this->renderHeader(); ?>
            
            <main class="flex flex-col gap-6">
                <?= $this->renderAlerts() ?>
                <?= $this->renderHeader() ?>
                <?= $this->renderFilters() ?>
                <?= $this->renderTable() ?>
                <?= $this->renderPagination() ?>
            </main>
            
            <?php $this->renderFooter(); ?>
        </body>
        </html>
        <?php
    }

    protected function renderHeader(): string
    {
        $createUrl = BASE_PATH . "/admin/{$this->model->table}/create";
        
        ob_start();
        ?>
        <header class="header">
            <div class="header-container">
                <a href="/" class="header-logo">LMCS</a>
                <nav class="header-nav">
                    <a href="">Accueil</a>
                    <a href="projets">Projets</a>
                    <a href="publications">Publications</a>
                    <a href="about">À propos</a>
                    <a href="contact">Contact</a>
                </nav>
            </div>
        </header>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2><?= $this->escape($this->model->getTableLabel()) ?></h2>
            
            <?= Components::Button([
                'text' => 'Add New',
                'variant' => 'default',
                'href' => $createUrl
            ]) ?>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function renderFooter(): void
    {
        
    }

    protected function renderAlerts(): string
    {
        ob_start();
        
        if ($this->hasFlash('success')) {
            echo Components::Alert([
                'variant' => 'success',
                'message' => $this->flash('success'),
                'dismissible' => true
            ]);
        }
        
        if ($this->hasFlash('error')) {
            echo Components::Alert([
                'variant' => 'destructive',
                'message' => $this->flash('error'),
                'dismissible' => true
            ]);
        }
        
        return ob_get_clean();
    }

    protected function renderFilters(): string
    {
        if (empty($this->model->filters)) {
            return '';
        }
        
        $fields = [];
        
        foreach ($this->model->filters as $column => $filterConfig) {
            $field = [
                'name' => $column,
                'label' => $this->model->getLabel($column),
                'value' => $_GET[$column] ?? ''
            ];
            
            switch ($filterConfig['type']) {
                case 'relation':
                    $field['type'] = 'select';
                    $field['options'] = $this->getForeignKeyOptions($filterConfig['relation']);
                    $field['placeholder'] = 'All';
                    break;
                    
                case 'enum':
                    $field['type'] = 'select';
                    $field['options'] = array_combine(
                        $filterConfig['values'], 
                        array_map('ucfirst', $filterConfig['values'])
                    );
                    $field['placeholder'] = 'All';
                    break;
                    
                case 'boolean':
                    $field['type'] = 'select';
                    $field['options'] = [
                        '1' => 'Yes',
                        '0' => 'No'
                    ];
                    $field['placeholder'] = 'All';
                    break;
                    
                case 'text':
                    $field['type'] = 'text';
                    $field['placeholder'] = 'Search...';
                    break;
                    
                case 'date_range':
                    $field['type'] = 'date';
                    $field['placeholder'] = 'YYYY-MM-DD';
                    break;
            }
            
            $fields[] = $field;
        }
        
        return Components::FilterForm([
            'action' => "",
            'method' => 'GET',
            'fields' => $fields,
            'title' => 'Filters'
        ]);
    }

    protected function renderTable(): string
    {
        return Components::Table([
            'columns' => $this->buildColumns(),
            'data' => $this->result['data'],
            'striped' => true,
            'hoverable' => true
        ]);
    }

    protected function buildColumns(): array
    {
        $columns = [];
        
        foreach ($this->model->columns as $col => $meta) {
            // Skip non-listable columns
            if (!$this->model->isListable($col)) {
                continue;
            }
            
            $columns[] = [
                'key' => $col,
                'label' => $this->model->getLabel($col),
                'render' => $this->getCellRenderer($col, $meta)
            ];
        }
        
        // Add actions column
        $columns[] = [
            'key' => $this->getPrimaryKeyColumn(),
            'label' => 'Actions',
            'render' => fn($id, $row) => $this->renderActions($id, $row)
        ];
        
        return $columns;
    }

    protected function getCellRenderer(string $col, array $meta): ?callable
    {
        // Foreign key - show label instead of ID
        if (isset($this->model->relations[$col])) {
            return function($value, $row) use ($col) {
                return $this->escape($row[$col . '__label'] ?? $value);
            };
        }
        
        // Enum - render as badge
        if (!empty($meta['enum_values'])) {
            return function($value) {
                return Components::Badge([
                    'text' => ucfirst($value),
                    'variant' => $this->getEnumVariant($value)
                ]);
            };
        }
        
        // Boolean/tinyint - render as badge
        if ($meta['type'] === 'tinyint') {
            return function($value) {
                return Components::Badge([
                    'text' => $value ? 'Yes' : 'No',
                    'variant' => $value ? 'success' : 'secondary'
                ]);
            };
        }
        
        // Date/datetime - format
        if (in_array($meta['type'], ['date', 'datetime', 'timestamp'])) {
            return function($value) use ($meta) {
                if (!$value) return '-';
                
                $format = $meta['type'] === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                
                return $date ? $date->format($format) : $value;
            };
        }
        
        // Text fields - truncate if too long
        if (in_array($meta['type'], ['varchar', 'text'])) {
            return function($value) {
                if (strlen($value) > 50) {
                    return $this->escape(substr($value, 0, 50)) . '...';
                }
                return $this->escape($value);
            };
        }
        
        // Default: just escape
        return fn($value) => $this->escape($value);
    }

    protected function renderActions($id, $row): string
    {
        $showUrl = BASE_PATH . "/admin/{$this->model->table}/{$id}";
        $editUrl = BASE_PATH . "/admin/{$this->model->table}/{$id}/edit";
        
        $showBtn = Components::Button([
            'text' => 'View',
            'size' => 'sm',
            'variant' => 'outline',
            'href' => $showUrl
        ]);
        
        $editBtn = Components::Button([
            'text' => 'Edit',
            'size' => 'sm',
            'variant' => 'secondary',
            'href' => $editUrl
        ]);
        
        $deleteBtn = Components::Button([
            'text' => 'Delete',
            'size' => 'sm',
            'variant' => 'destructive',
            'attrs' => [
                'onclick' => $this->getDeleteConfirmation($id)
            ]
        ]);
        
        return "<div class='flex gap-2'>{$showBtn} {$editBtn} {$deleteBtn}</div>";
    }

    protected function renderPagination(): string
    {
        $total = $this->result['total'];
        $page = $this->result['page'];
        $pages = $this->result['pages'];
        
        if ($pages <= 1) {
            return '';
        }
        
        ob_start();
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
            <div>
                Showing <?= ($page - 1) * $this->result['perPage'] + 1 ?> to 
                <?= min($page * $this->result['perPage'], $total) ?> of <?= $total ?> results
            </div>
            
            <div style="display: flex; gap: 0.5rem;">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <?php
                    $url = BASE_PATH . "/admin/{$this->model->table}?page={$i}";
                    foreach ($_GET as $key => $value) {
                        if ($key !== 'page') {
                            $url .= "&{$key}=" . urlencode($value);
                        }
                    }
                    ?>
                    <a href="<?= $url ?>" 
                       class="btn btn-sm <?= $i === $page ? 'btn-default' : 'btn-outline' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function getDeleteConfirmation($id): string
    {
        $url = BASE_PATH . "/admin/{$this->model->table}/{$id}";
        
        return "if(confirm('Are you sure you want to delete this record?')) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{$url}';
            
            var method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(method);
            
            document.body.appendChild(form);
            form.submit();
        }";
    }

    protected function getForeignKeyOptions(array $relation): array
    {
        $resolver = \Core\AutoCrud\ForeignKeyResolver::getInstance();
        return $resolver->getOptions($relation);
    }

    protected function getEnumVariant(string $value): string
    {
        $variantMap = [
            'active' => 'success',
            'actif' => 'success',
            'pending' => 'secondary',
            'inactive' => 'destructive',
            'inactif' => 'destructive',
            'suspended' => 'destructive',
            'suspendu' => 'destructive',
        ];
        
        return $variantMap[strtolower($value)] ?? 'default';
    }

    protected function getPrimaryKeyColumn(): string
    {
        $pk = $this->model->primaryKey;
        return is_array($pk) ? $pk[0] : $pk;
    }
}