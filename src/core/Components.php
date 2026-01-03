<?php

namespace Core;

/**
 * Common View Components Library
 * Minimalistic, generic components inspired by shadcn/ui design system
 */
class Components
{
    /**
     * Button Component
     * 
     * @param array $config [
     *   'text' => 'Click me',
     *   'variant' => 'default|destructive|outline|secondary|ghost|link',
     *   'size' => 'default|sm|lg|icon',
     *   'type' => 'button|submit|reset',
     *   'href' => 'url', // If provided, renders as <a>
     *   'class' => 'additional classes',
     *   'attrs' => ['data-id' => '123'],
     *   'disabled' => false
     * ]
     */
    public static function Button(array $config): string
    {
        $text = $config['text'] ?? 'Button';
        $variant = $config['variant'] ?? 'default';
        $size = $config['size'] ?? 'default';
        $type = $config['type'] ?? 'button';
        $href = $config['href'] ?? null;
        $disabled = $config['disabled'] ?? false;
        $class = $config['class'] ?? '';
        $attrs = $config['attrs'] ?? [];

        $baseClass = 'btn';
        $variantClass = "btn-{$variant}";
        $sizeClass = "btn-{$size}";
        $classes = trim("{$baseClass} {$variantClass} {$sizeClass} {$class}");

        $attrStr = self::buildAttributes($attrs);
        $disabledAttr = $disabled ? 'disabled' : '';

        if ($href) {
            $hrefAttr = 'href="' . htmlspecialchars($href) . '"';
            return "<a {$hrefAttr} class=\"{$classes}\" {$attrStr}>{$text}</a>";
        }

        return "<button type=\"{$type}\" class=\"{$classes}\" {$disabledAttr} {$attrStr}>{$text}</button>";
    }

    /**
     * Input Component
     * 
     * @param array $config [
     *   'type' => 'text|email|password|number|tel|url|search',
     *   'name' => 'input_name',
     *   'label' => 'Label text',
     *   'placeholder' => 'Enter value...',
     *   'value' => 'default value',
     *   'error' => 'Error message',
     *   'required' => true,
     *   'disabled' => false,
     *   'class' => 'additional classes',
     *   'attrs' => []
     * ]
     */
    public static function Input(array $config): string
    {
        $type = $config['type'] ?? 'text';
        $name = $config['name'] ?? '';
        $label = $config['label'] ?? '';
        $placeholder = $config['placeholder'] ?? '';
        $value = $config['value'] ?? '';
        $error = $config['error'] ?? '';
        $required = $config['required'] ?? false;
        $disabled = $config['disabled'] ?? false;
        $class = $config['class'] ?? '';
        $attrs = $config['attrs'] ?? [];

        $id = $attrs['id'] ?? "input-{$name}";
        $inputClass = trim("input {$class}" . ($error ? ' input-error' : ''));

        $attrStr = self::buildAttributes(array_merge($attrs, [
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'placeholder' => $placeholder,
            'value' => $value,
        ]));

        if ($required) $attrStr .= ' required';
        if ($disabled) $attrStr .= ' disabled';

        ob_start();
        ?>
        <div class="form-group">
            <?php if ($label): ?>
                <label for="<?= htmlspecialchars($id) ?>" class="form-label">
                    <?= htmlspecialchars($label) ?>
                    <?php if ($required): ?>
                        <span class="text-destructive">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            <input class="<?= $inputClass ?>" <?= $attrStr ?>>
            <?php if ($error): ?>
                <p class="form-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Textarea Component
     */
    public static function Textarea(array $config): string
    {
        $name = $config['name'] ?? '';
        $label = $config['label'] ?? '';
        $placeholder = $config['placeholder'] ?? '';
        $value = $config['value'] ?? '';
        $error = $config['error'] ?? '';
        $required = $config['required'] ?? false;
        $disabled = $config['disabled'] ?? false;
        $rows = $config['rows'] ?? 4;
        $class = $config['class'] ?? '';
        $attrs = $config['attrs'] ?? [];

        $id = $attrs['id'] ?? "textarea-{$name}";
        $textareaClass = trim("textarea {$class}" . ($error ? ' input-error' : ''));

        $attrStr = self::buildAttributes(array_merge($attrs, [
            'id' => $id,
            'name' => $name,
            'placeholder' => $placeholder,
            'rows' => $rows,
        ]));

        if ($required) $attrStr .= ' required';
        if ($disabled) $attrStr .= ' disabled';

        ob_start();
        ?>
        <div class="form-group">
            <?php if ($label): ?>
                <label for="<?= htmlspecialchars($id) ?>" class="form-label">
                    <?= htmlspecialchars($label) ?>
                    <?php if ($required): ?>
                        <span class="text-destructive">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            <textarea class="<?= $textareaClass ?>" <?= $attrStr ?>><?= htmlspecialchars($value) ?></textarea>
            <?php if ($error): ?>
                <p class="form-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Select Component
     */
    public static function Select(array $config): string
    {
        $name = $config['name'] ?? '';
        $label = $config['label'] ?? '';
        $options = $config['options'] ?? []; // ['value' => 'label']
        $value = $config['value'] ?? '';
        $error = $config['error'] ?? '';
        $required = $config['required'] ?? false;
        $disabled = $config['disabled'] ?? false;
        $placeholder = $config['placeholder'] ?? 'Select an option';
        $class = $config['class'] ?? '';
        $attrs = $config['attrs'] ?? [];

        $id = $attrs['id'] ?? "select-{$name}";
        $selectClass = trim("select {$class}" . ($error ? ' input-error' : ''));

        $attrStr = self::buildAttributes(array_merge($attrs, [
            'id' => $id,
            'name' => $name,
        ]));

        if ($required) $attrStr .= ' required';
        if ($disabled) $attrStr .= ' disabled';

        ob_start();
        ?>
        <div class="form-group">
            <?php if ($label): ?>
                <label for="<?= htmlspecialchars($id) ?>" class="form-label">
                    <?= htmlspecialchars($label) ?>
                    <?php if ($required): ?>
                        <span class="text-destructive">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            <select class="<?= $selectClass ?>" <?= $attrStr ?>>
                <?php if ($placeholder): ?>
                    <option value=""><?= htmlspecialchars($placeholder) ?></option>
                <?php endif; ?>
                <?php foreach ($options as $optValue => $optLabel): ?>
                    <option value="<?= htmlspecialchars($optValue) ?>" 
                            <?= $value == $optValue ? 'selected' : '' ?>>
                        <?= htmlspecialchars($optLabel) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($error): ?>
                <p class="form-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Checkbox Component
     */
    public static function Checkbox(array $config): string
    {
        $name = $config['name'] ?? '';
        $label = $config['label'] ?? '';
        $checked = $config['checked'] ?? false;
        $value = $config['value'] ?? '1';
        $disabled = $config['disabled'] ?? false;
        $class = $config['class'] ?? '';
        $attrs = $config['attrs'] ?? [];

        $id = $attrs['id'] ?? "checkbox-{$name}";

        $attrStr = self::buildAttributes(array_merge($attrs, [
            'id' => $id,
            'name' => $name,
            'type' => 'checkbox',
            'value' => $value,
        ]));

        if ($checked) $attrStr .= ' checked';
        if ($disabled) $attrStr .= ' disabled';

        ob_start();
        ?>
        <div class="checkbox-group">
            <input class="checkbox" <?= $attrStr ?>>
            <?php if ($label): ?>
                <label for="<?= htmlspecialchars($id) ?>" class="checkbox-label">
                    <?= htmlspecialchars($label) ?>
                </label>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Radio Component
     */
    public static function Radio(array $config): string
    {
        $name = $config['name'] ?? '';
        $label = $config['label'] ?? '';
        $value = $config['value'] ?? '';
        $checked = $config['checked'] ?? false;
        $disabled = $config['disabled'] ?? false;
        $attrs = $config['attrs'] ?? [];

        $id = $attrs['id'] ?? "radio-{$name}-{$value}";

        $attrStr = self::buildAttributes(array_merge($attrs, [
            'id' => $id,
            'name' => $name,
            'type' => 'radio',
            'value' => $value,
        ]));

        if ($checked) $attrStr .= ' checked';
        if ($disabled) $attrStr .= ' disabled';

        ob_start();
        ?>
        <div class="radio-group">
            <input class="radio" <?= $attrStr ?>>
            <?php if ($label): ?>
                <label for="<?= htmlspecialchars($id) ?>" class="radio-label">
                    <?= htmlspecialchars($label) ?>
                </label>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Card Component
     */
    public static function Card(array $config): string
    {
        $title = $config['title'] ?? '';
        $description = $config['description'] ?? '';
        $content = $config['content'] ?? '';
        $footer = $config['footer'] ?? '';
        $class = $config['class'] ?? '';

        $cardClass = trim("card {$class}");

        ob_start();
        ?>
        <div class="<?= $cardClass ?>">
            <?php if ($title || $description): ?>
                <div class="card-header">
                    <?php if ($title): ?>
                        <h3 class="card-title"><?= htmlspecialchars($title) ?></h3>
                    <?php endif; ?>
                    <?php if ($description): ?>
                        <p class="card-description"><?= htmlspecialchars($description) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($content): ?>
                <div class="card-content">
                    <?= $content ?>
                </div>
            <?php endif; ?>
            <?php if ($footer): ?>
                <div class="card-footer">
                    <?= $footer ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Table Component
     */
    public static function Table(array $config): string
    {
        $columns = $config['columns'] ?? []; // [['key' => 'name', 'label' => 'Name']]
        $data = $config['data'] ?? [];
        $class = $config['class'] ?? '';
        $striped = $config['striped'] ?? true;
        $hoverable = $config['hoverable'] ?? true;

        $tableClass = trim("table {$class}" . 
            ($striped ? ' table-striped' : '') . 
            ($hoverable ? ' table-hover' : '')
        );

        ob_start();
        ?>
        <div class="table-container">
            <table class="<?= $tableClass ?>">
                <thead>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <th><?= htmlspecialchars($column['label'] ?? $column['key']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="<?= count($columns) ?>" class="text-center text-muted">
                                No data available
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <?php foreach ($columns as $column): ?>
                                    <td>
                                        <?php
                                        $key = $column['key'];
                                        $value = $row[$key] ?? '';
                                        
                                        if (isset($column['render']) && is_callable($column['render'])) {
                                            echo $column['render']($value, $row);
                                        } else {
                                            echo htmlspecialchars($value);
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Badge Component
     */
    public static function Badge(array $config): string
    {
        $text = $config['text'] ?? '';
        $variant = $config['variant'] ?? 'default'; // default|secondary|destructive|outline|success
        $class = $config['class'] ?? '';

        $badgeClass = trim("badge badge-{$variant} {$class}");

        return "<span class=\"{$badgeClass}\">{$text}</span>";
    }

    /**
     * Alert Component
     */
    public static function Alert(array $config): string
    {
        $title = $config['title'] ?? '';
        $message = $config['message'] ?? '';
        $variant = $config['variant'] ?? 'default'; // default|destructive|success|warning
        $dismissible = $config['dismissible'] ?? false;
        $class = $config['class'] ?? '';

        $alertClass = trim("alert alert-{$variant} {$class}");

        ob_start();
        ?>
        <div class="<?= $alertClass ?>" role="alert">
            <?php if ($title): ?>
                <h5 class="alert-title"><?= htmlspecialchars($title) ?></h5>
            <?php endif; ?>
            <div class="alert-description">
                <?= htmlspecialchars($message) ?>
            </div>
            <?php if ($dismissible): ?>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                    ×
                </button>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Progress Component
     */
    public static function Progress(array $config): string
    {
        $value = $config['value'] ?? 0;
        $max = $config['max'] ?? 100;
        $class = $config['class'] ?? '';
        $showLabel = $config['showLabel'] ?? false;

        $percentage = ($value / $max) * 100;
        $progressClass = trim("progress {$class}");

        ob_start();
        ?>
        <div class="<?= $progressClass ?>">
            <div class="progress-bar" style="width: <?= $percentage ?>%"></div>
            <?php if ($showLabel): ?>
                <span class="progress-label"><?= round($percentage) ?>%</span>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Separator Component
     */
    public static function Separator(array $config = []): string
    {
        $orientation = $config['orientation'] ?? 'horizontal'; // horizontal|vertical
        $class = $config['class'] ?? '';

        $sepClass = trim("separator separator-{$orientation} {$class}");

        return "<div class=\"{$sepClass}\"></div>";
    }

    /**
     * Skeleton Component (Loading placeholder)
     */
    public static function Skeleton(array $config = []): string
    {
        $width = $config['width'] ?? '100%';
        $height = $config['height'] ?? '20px';
        $class = $config['class'] ?? '';

        $style = "width: {$width}; height: {$height};";
        $skeletonClass = trim("skeleton {$class}");

        return "<div class=\"{$skeletonClass}\" style=\"{$style}\"></div>";
    }

    /**
     * Avatar Component
     */
    public static function Avatar(array $config): string
    {
        $src = $config['src'] ?? '';
        $alt = $config['alt'] ?? '';
        $fallback = $config['fallback'] ?? substr($alt, 0, 2);
        $size = $config['size'] ?? 'default'; // sm|default|lg
        $class = $config['class'] ?? '';

        $avatarClass = trim("avatar avatar-{$size} {$class}");

        ob_start();
        ?>
        <div class="<?= $avatarClass ?>">
            <?php if ($src): ?>
                <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($alt) ?>" class="avatar-image">
            <?php else: ?>
                <span class="avatar-fallback"><?= htmlspecialchars($fallback) ?></span>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Build HTML attributes string from array
     */
    private static function buildAttributes(array $attrs): string
    {
        $parts = [];
        foreach ($attrs as $key => $value) {
            if (is_int($key)) {
                $parts[] = htmlspecialchars($value);
            } elseif ($value === true) {
                $parts[] = htmlspecialchars($key);
            } elseif ($value !== false && $value !== null) {
                $parts[] = htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
            }
        }
        return implode(' ', $parts);
    }
}