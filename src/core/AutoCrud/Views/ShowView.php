<?php

namespace Core\AutoCrud\Views;

use Core\View;
use Core\Components;
use Core\AutoCrud\VirtualModel;
use Core\AutoCrud\ForeignKeyResolver;

/**
 * ShowView - Generic detail view for any record
 * 
 * Displays all field values in a clean, readable format
 */
class ShowView extends View
{
    protected VirtualModel $model;
    protected array $record;
    protected ForeignKeyResolver $fkResolver;
    
    public function __construct(VirtualModel $model, array $record)
    {
        parent::__construct();
        $this->model = $model;
        $this->record = $record;
        $this->fkResolver = ForeignKeyResolver::getInstance();
    }

    public function render()
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $this->escape($this->model->getTableLabel()) ?> Details</title>
            <link rel="stylesheet" href="<?= $this->asset('css/base.css') ?>">
        </head>
        <body class="main-layout">
            <main class="flex flex-col gap-6">
                <?= $this->renderHeader() ?>
                <?= $this->renderDetails() ?>
                
            </main>
        </body>
        </html>
        <?php
    }

    protected function renderHeader(): string
    {
        $pkValue = $this->getPrimaryKeyValue();
        $backUrl = BASE_PATH . "/admin/{$this->model->table}";
        $editUrl = BASE_PATH . "/admin/{$this->model->table}/{$pkValue}/edit";
        
        ob_start();
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2><?= $this->escape($this->model->getTableLabel()) ?> Details</h2>
            
            <div class="flex gap-2">
                <?= Components::Button([
                    'text' => 'Edit',
                    'variant' => 'default',
                    'href' => $editUrl
                ]) ?>
                
                <?= Components::Button([
                    'text' => '← Back',
                    'variant' => 'outline',
                    'href' => $backUrl
                ]) ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function renderDetails(): string
    {
        $sections = $this->groupColumnsIntoSections();
        
        ob_start();
        
        foreach ($sections as $sectionName => $columns) {
            echo Components::Card([
                'title' => $sectionName,
                'content' => $this->renderSectionFields($columns)
            ]);
        }
        
        return ob_get_clean();
    }

    protected function groupColumnsIntoSections(): array
    {
        $sections = ['Details' => []];
        
        foreach ($this->model->columns as $col => $meta) {
            // Skip hidden columns
            if (in_array($col, $this->model->hiddenColumns)) {
                continue;
            }
            
            $sections['Details'][$col] = $meta;
        }
        
        return $sections;
    }

    protected function renderSectionFields(array $columns): string
    {
        ob_start();
        ?>
        <div style="display: grid; gap: 1rem;">
            <?php foreach ($columns as $col => $meta): ?>
                <?= $this->renderField($col, $meta) ?>
                <?php if (!$this->isLastColumn($col, $columns)): ?>
                    <?= Components::Separator() ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function renderField(string $col, array $meta): string
    {
        $label = $this->model->getLabel($col);
        $value = $this->formatValue($col, $meta);
        
        ob_start();
        ?>
        <div style="display: flex; justify-content: space-between; align-items: start; gap: 2rem;">
            <span class="font-medium" style="min-width: 200px;"><?= $this->escape($label) ?></span>
            <span class="text-muted" style="flex: 1; text-align: right;"><?= $value ?></span>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function formatValue(string $col, array $meta): string
    {
        $value = $this->record[$col] ?? null;
        
        // Null values
        if ($value === null || $value === '') {
            return '<span class="text-muted">N/A</span>';
        }
        
        // Foreign keys - resolve to label
        if (isset($this->model->relations[$col])) {
            $relation = $this->model->relations[$col];
            $label = $this->fkResolver->resolve($relation['table'], $value);
            return $this->escape($label ?? $value);
        }
        
        // Enums - render as badge
        if (!empty($meta['enum_values'])) {
            return Components::Badge([
                'text' => ucfirst($value),
                'variant' => $this->getEnumVariant($value)
            ]);
        }
        
        // Boolean/tinyint
        if ($meta['type'] === 'tinyint') {
            return Components::Badge([
                'text' => $value ? 'Yes' : 'No',
                'variant' => $value ? 'success' : 'secondary'
            ]);
        }
        
        // Dates
        if (in_array($meta['type'], ['date', 'datetime', 'timestamp'])) {
            $format = $meta['type'] === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
            
            if ($date) {
                return $this->escape($date->format($format));
            }
            
            return $this->escape($value);
        }
        
        // Text fields - preserve line breaks
        if (in_array($meta['type'], ['text', 'mediumtext', 'longtext'])) {
            return nl2br($this->escape($value));
        }
        
        // Default
        return $this->escape($value);
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

    protected function getPrimaryKeyValue(): mixed
    {
        $pk = is_array($this->model->primaryKey) 
            ? $this->model->primaryKey[0] 
            : $this->model->primaryKey;
        
        return $this->record[$pk] ?? null;
    }

    protected function isLastColumn(string $col, array $columns): bool
    {
        $keys = array_keys($columns);
        return $col === end($keys);
    }
}