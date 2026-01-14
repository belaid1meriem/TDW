<?php

namespace App\Views;

use Core\Components;
use Core\AutoCrud\VirtualModel;

class Table
{
    public const ACTIONS_ADMIN     = 'admin';
    public const ACTIONS_VIEW_ONLY = 'view';
    public const ACTIONS_NONE      = 'none';

    protected VirtualModel $model;
    protected array $result;
    protected string $actionsMode;

    public function __construct(
        VirtualModel $model,
        array $result,
        string $actionsMode = self::ACTIONS_VIEW_ONLY
    ) {
        $this->model = $model;
        $this->result = $result;
        $this->actionsMode = $actionsMode;
    }

    public function render(): void
    {
        echo $this->renderTable();
    }

    protected function renderTable(): string
    {
        return Components::Table([
            'columns'    => $this->buildColumns(),
            'data'       => $this->result,
            'striped'    => true,
            'hoverable'  => true
        ]);
    }

    protected function buildColumns(): array
    {
        $columns = [];

        foreach ($this->model->columns as $col => $meta) {
            if (!$this->model->isListable($col)) {
                continue;
            }

            $columns[] = [
                'key'    => $col,
                'label'  => $this->model->getLabel($col),
                'render' => $this->getCellRenderer($col, $meta)
            ];
        }

        if ($this->actionsMode !== self::ACTIONS_NONE) {
            $columns[] = [
                'key'    => $this->getPrimaryKeyColumn(),
                'label'  => 'Actions',
                'render' => fn($id, $row) => $this->renderActions($id)
            ];
        }

        return $columns;
    }

    protected function getCellRenderer(string $col, array $meta): ?callable
    {
        if (isset($this->model->relations[$col])) {
            return fn($value, $row) => $row[$col . '__label'] ?? $value;
        }

        if (!empty($meta['enum_values'])) {
            return fn($value) => Components::Badge([
                'text'    => ucfirst($value),
                'variant' => $this->getEnumVariant($value)
            ]);
        }

        if ($meta['type'] === 'tinyint') {
            return fn($value) => Components::Badge([
                'text'    => $value ? 'Yes' : 'No',
                'variant' => $value ? 'success' : 'secondary'
            ]);
        }

        if (in_array($meta['type'], ['date', 'datetime', 'timestamp'])) {
            return function ($value) use ($meta) {
                if (!$value) {
                    return '-';
                }

                $format = $meta['type'] === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);

                return $date ? $date->format($format) : $value;
            };
        }

        if (in_array($meta['type'], ['varchar', 'text'])) {
            return fn($value) =>
                strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
        }

        return fn($value) => $value;
    }

    protected function renderActions($id): string
    {
        // Determine URL base
        $base = $this->actionsMode === self::ACTIONS_ADMIN 
            ? BASE_PATH . "/admin/{$this->model->table}" 
            : BASE_PATH . "/{$this->model->table}";

        $showUrl = "{$base}/{$id}";
        $editUrl = "{$base}/{$id}/edit";

        $buttons = [];

        // View button is always present
        $buttons[] = Components::Button([
            'text'    => 'View',
            'size'    => 'sm',
            'variant' => 'outline',
            'href'    => $showUrl
        ]);

        // Admin-only actions
        if ($this->actionsMode === self::ACTIONS_ADMIN) {
            $buttons[] = Components::Button([
                'text'    => 'Edit',
                'size'    => 'sm',
                'variant' => 'secondary',
                'href'    => $editUrl
            ]);

            $buttons[] = Components::Button([
                'text'    => 'Delete',
                'size'    => 'sm',
                'variant' => 'destructive',
                'attrs'   => [
                    'onclick' => $this->getDeleteConfirmation($id)
                ]
            ]);
        }

        return "<div class='flex gap-2'>" . implode(' ', $buttons) . "</div>";
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

    protected function getEnumVariant(string $value): string
    {
        $variantMap = [
            'active'     => 'success',
            'actif'      => 'success',
            'pending'    => 'secondary',
            'inactive'   => 'destructive',
            'inactif'    => 'destructive',
            'suspended'  => 'destructive',
            'suspendu'   => 'destructive',
        ];

        return $variantMap[strtolower($value)] ?? 'default';
    }

    protected function getPrimaryKeyColumn(): string
    {
        $pk = $this->model->primaryKey;
        return is_array($pk) ? $pk[0] : $pk;
    }
}
