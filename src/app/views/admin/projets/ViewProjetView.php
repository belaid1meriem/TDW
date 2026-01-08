<?php
namespace App\Views\Admin\Projets;
use App\Views\Admin\AdminLayout;
use Core\Components;

class ViewProjetView extends AdminLayout
{
    private $projet;

    public function __construct($projet)
    {
        parent::__construct();
        $this->projet = $projet;
    }

    public function renderContent()
    {
        $projet = $this->projet;
        
        ?>
        <main class="flex flex-col gap-6">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Détails du projet</h2>
                <div class="flex gap-2">
                    <?= Components::Button([
                        'text' => 'Modifier',
                        'variant' => 'default',
                        'href' => $this->asset('admin/projets/edit/' . $projet['id'])
                    ]) ?>
                    <?= Components::Button([
                        'text' => '← Retour',
                        'variant' => 'outline',
                        'href' => $this->asset('admin/projets')
                    ]) ?>
                </div>
            </div>

            <?= $this->renderProjectCard() ?>
        </main>
        <?php
    }

    private function renderProjectCard()
    {
        $projet = $this->projet;
        
        ob_start();
        echo Components::Card([
            'title' => htmlspecialchars($projet['title']),
            'content' => $this->renderProjectContent()
        ]);
        return ob_get_clean();
    }

    private function renderProjectContent()
    {
        $projet = $this->projet;
        
        ob_start();
        ?>
        <div style="display: grid; gap: 1rem;">
            <?= $this->renderInfoRow('Statut', $projet['status']) ?>
            <?= $this->renderInfoRow('Thème', $projet['theme']) ?>
            <?= Components::Separator() ?>
            <?= $this->renderInfoRow('Financement', $projet['financement']) ?>
            <?= Components::Separator() ?>
            <?= $this->renderInfoRow('Date de début', $this->formatDate($projet['start_date'])) ?>
            <?= Components::Separator() ?>
            <?= $this->renderInfoRow('Date de fin', $projet['end_date'] ? $this->formatDate($projet['end_date']) : 'Non spécifiée') ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderInfoRow($label, $value)
    {
        ob_start();
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="font-medium"><?= htmlspecialchars($label) ?></span>
            <span class="text-muted"><?= htmlspecialchars($value) ?></span>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderStatusBadge($status)
    {
        ob_start();
        $variants = [
            'en cours' => 'default',
            'termine' => 'success',
            'soumis' => 'secondary'
        ];
        
        echo Components::Badge([
            'text' => ucfirst($status),
            'variant' => $variants[$status] ?? 'secondary'
        ]);
        return ob_get_clean();
    }

    private function formatDate($date)
    {
        if (!$date) return '';
        return date('d/m/Y', strtotime($date));
    }
}
?>

<style>
    .text-muted {
        color: hsl(var(--muted-foreground));
    }

    @media (max-width: 768px) {
        main > div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>