<?php

namespace Core\AutoCrud\Views;

use Core\View;
use Core\Components;
use Core\AutoCrud\VirtualModel;
use Core\AutoCrud\ForeignKeyResolver;

/**
 * FormView - Generic create/edit form for any table
 * 
 * Automatically renders form fields based on column types,
 * foreign keys, and validation rules
 */
class FormView extends View
{
    private VirtualModel $model;
    private string $mode; // 'create' or 'edit'
    private ForeignKeyResolver $fkResolver;
    
    public function __construct(VirtualModel $model, array $data = [], string $mode = 'create')
    {
        parent::__construct();
        $this->model = $model;
        $this->data = $data;
        $this->mode = $mode;
        $this->fkResolver = ForeignKeyResolver::getInstance();
    }

    public function render()
    {
        $title = $this->mode === 'create' 
            ? "Create {$this->model->getTableLabel()}" 
            : "Edit {$this->model->getTableLabel()}";
        
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $this->escape($title) ?></title>
            <link rel="stylesheet" href="<?= $this->asset('css/base.css') ?>">
        </head>
        <body class="main-layout">
            <main class="flex flex-col gap-6">
                <?= $this->renderHeader() ?>
                <?= $this->renderErrors() ?>
                <?= $this->renderForm() ?>
            </main>
        </body>
        </html>
        <?php
    }

    private function renderHeader(): string
    {
        $title = $this->mode === 'create' 
            ? "Create New " . $this->model->getTableLabel() 
            : "Edit " . $this->model->getTableLabel();
        
        $backUrl = BASE_PATH . "/admin/{$this->model->table}";
        
        ob_start();
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2><?= $this->escape($title) ?></h2>
            
            <?= Components::Button([
                'text' => '← Back',
                'variant' => 'outline',
                'href' => $backUrl
            ]) ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderErrors(): string
    {
        if (!$this->hasFlash('errors')) {
            return '';
        }
        
        ob_start();
        
        $errors = $this->flash('errors');
        foreach ($errors as $fieldErrors) {
            foreach ($fieldErrors as $error) {
                echo Components::Alert([
                    'variant' => 'destructive',
                    'message' => $error,
                    'dismissible' => true
                ]);
            }
        }
        
        return ob_get_clean();
    }

    private function renderForm(): string
    {
        $action = $this->mode === 'create'
            ? BASE_PATH . "/admin/{$this->model->table}"
            : BASE_PATH . "/admin/{$this->model->table}/{$this->getPrimaryKeyValue()}";
        
        ob_start();
        ?>
        <form method="POST" action="<?= $action ?>" enctype="multipart/form-data">

            
            <?= $this->renderFormSections() ?>
            
            <div class="flex gap-4 mt-4">
                <?= Components::Button([
                    'text' => $this->mode === 'create' ? 'Create' : 'Update',
                    'type' => 'submit',
                    'variant' => 'default',
                    'class' => 'w-full'
                ]) ?>
                
                <?= Components::Button([
                    'text' => 'Cancel',
                    'type' => 'button',
                    'variant' => 'outline',
                    'class' => 'w-full',
                    'attrs' => [
                        'onclick' => "window.location.href='" . BASE_PATH . "/admin/{$this->model->table}'"
                    ]
                ]) ?>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    private function renderFormSections(): string
    {
        // Group columns by section (could be enhanced with config)
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

    private function groupColumnsIntoSections(): array
    {
        $sections = ['Basic Information' => []];
        
        foreach ($this->model->columns as $col => $meta) {
            if (!$this->model->isEditable($col)) {
                continue;
            }
            
            // Could be enhanced to group by column naming patterns
            // e.g., address_*, contact_*, etc.
            $sections['Basic Information'][$col] = $meta;
        }
        
        return $sections;
    }

    private function renderSectionFields(array $columns): string
    {
        ob_start();
        ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <?php foreach ($columns as $col => $meta): ?>
                <div style="<?= $this->isWideField($col, $meta) ? 'grid-column: 1 / -1;' : '' ?>">
                    <?= $this->renderField($col, $meta) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderField(string $col, array $meta): string
    {
        // Foreign key → <select>
        if (isset($this->model->relations[$col])) {
            return $this->renderForeignKeyField($col, $meta);
        }
        
        // Enum → <select>
        if (!empty($meta['enum_values'])) {
            return $this->renderEnumField($col, $meta);
        }
        
        // Boolean/tinyint → checkbox
        if ($meta['type'] === 'tinyint' && in_array($col, ['active', 'enabled', 'published'])) {
            return $this->renderCheckboxField($col, $meta);
        }
        
        // Text/longtext → <textarea>
        if (in_array($meta['type'], ['text', 'mediumtext', 'longtext'])) {
            return $this->renderTextareaField($col, $meta);
        }
        
        // Default → <input>
        return $this->renderInputField($col, $meta);
    }

    private function renderInputField(string $col, array $meta): string
    {
        $type = $this->mapTypeToInputType($meta['type']);
        $value = $this->getFieldValue($col);
        
        return Components::Input([
            'type' => $type,
            'name' => $col,
            'label' => $this->model->getLabel($col),
            'placeholder' => $this->getPlaceholder($col, $meta),
            'value' => $value,
            'error' => $this->error($col),
            'required' => !$meta['nullable']
        ]);
    }

    private function renderTextareaField(string $col, array $meta): string
    {
        $value = $this->getFieldValue($col);
        
        return Components::Textarea([
            'name' => $col,
            'label' => $this->model->getLabel($col),
            'placeholder' => $this->getPlaceholder($col, $meta),
            'value' => $value,
            'error' => $this->error($col),
            'required' => !$meta['nullable'],
            'rows' => 4
        ]);
    }

    private function renderForeignKeyField(string $col, array $meta): string
    {
        $value = $this->getFieldValue($col);
        $relation = $this->model->relations[$col];
        $options = $this->fkResolver->getOptions($relation);
        
        return Components::Select([
            'name' => $col,
            'label' => $this->model->getLabel($col),
            'options' => $options,
            'value' => $value,
            'error' => $this->error($col),
            'placeholder' => 'Select...',
            'required' => !$meta['nullable']
        ]);
    }

    private function renderEnumField(string $col, array $meta): string
    {
        $value = $this->getFieldValue($col);
        $options = array_combine(
            $meta['enum_values'], 
            array_map('ucfirst', $meta['enum_values'])
        );
        
        return Components::Select([
            'name' => $col,
            'label' => $this->model->getLabel($col),
            'options' => $options,
            'value' => $value,
            'error' => $this->error($col),
            'placeholder' => 'Select...',
            'required' => !$meta['nullable']
        ]);
    }

    private function renderCheckboxField(string $col, array $meta): string
    {
        $value = $this->getFieldValue($col);
        
        return Components::Checkbox([
            'name' => $col,
            'label' => $this->model->getLabel($col),
            'checked' => (bool) $value,
            'value' => '1'
        ]);
    }

    private function mapTypeToInputType(string $dbType): string
    {
        $map = [
            'int' => 'number',
            'bigint' => 'number',
            'smallint' => 'number',
            'tinyint' => 'number',
            'decimal' => 'number',
            'float' => 'number',
            'double' => 'number',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'time' => 'time',
            'email' => 'email',
            'url' => 'url',
        ];
        
        return $map[$dbType] ?? 'text';
    }

    private function getFieldValue(string $col): mixed
    {
        // Priority: old input > existing data > empty
        if ($this->hasFlash('old')) {
            $old = $this->flash('old');
            if (isset($old[$col])) {
                return $old[$col];
            }
        }
        
        return $this->data[$col] ?? '';
    }

    private function getPlaceholder(string $col, array $meta): string
    {
        // Check for comment
        if (!empty($meta['comment'])) {
            return $meta['comment'];
        }
        
        // Generate from column name
        return 'Enter ' . strtolower($this->model->getLabel($col));
    }

    private function isWideField(string $col, array $meta): bool
    {
        // Text areas and long text fields should span full width
        if (in_array($meta['type'], ['text', 'mediumtext', 'longtext'])) {
            return true;
        }
        
        // Fields with "description", "content", "bio" in name
        if (preg_match('/(description|content|bio|note|comment)/i', $col)) {
            return true;
        }
        
        return false;
    }

    private function getPrimaryKeyValue(): mixed
    {
        $pk = is_array($this->model->primaryKey) 
            ? $this->model->primaryKey[0] 
            : $this->model->primaryKey;
        
        return $this->data[$pk] ?? null;
    }
}