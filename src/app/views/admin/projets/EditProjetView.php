<?php
namespace App\Views\Admin\Projets;
use App\Views\Admin\AdminLayout;
use Core\Session;
use Core\Components;

class EditProjetView extends AdminLayout
{
    private array $projet;

    public function __construct(array $projet)
    {
        parent::__construct();
        $this->projet = $projet;
    }

    public function renderContent()
    {
        ?>
        <main class="flex flex-col gap-6">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Modifier le projet</h2>
                <?= Components::Button([
                    'text' => '← Retour',
                    'variant' => 'outline',
                    'href' => $this->asset('admin/projets')
                ]) ?>
            </div>
            
            <?php if ($this->hasFlash('errors')): ?>
                <div class="alert-container">
                    <?php 
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
                    ?>
                </div>
            <?php endif; ?>

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

            <?= $this->renderProjetForm() ?>
        </main>
        <?php
    }

    private function renderProjetForm()
    {
        ob_start();
        ?>
        <form method="POST" action="<?= BASE_PATH."/admin/projets/update/{$this->projet['id']}" ?>" id="projetForm">
            <?= Components::Card([
                'title' => 'Informations du projet',
                'content' => $this->renderProjetSection()
            ]) ?>

            <div class="flex gap-4">
                <?= Components::Button([
                    'text' => 'Mettre à jour',
                    'type' => 'submit',
                    'variant' => 'default',
                    'class' => 'w-full'
                ]) ?>
                
                <?= Components::Button([
                    'text' => 'Annuler',
                    'type' => 'button',
                    'variant' => 'outline',
                    'class' => 'w-full',
                    'attrs' => [
                        'onclick' => "window.location.href='" . $this->asset('admin/projets') . "'"
                    ]
                ]) ?>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    private function renderProjetSection()
    {
        ob_start();
        ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <?= Components::Input([
                'type' => 'text',
                'name' => 'title',
                'label' => 'Titre',
                'placeholder' => 'Titre du projet',
                'value' => $this->old('title', $this->projet['title']),
                'error' => $this->hasError('title') ? $this->error('title') : '',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'text',
                'name' => 'theme',
                'label' => 'Thème',
                'placeholder' => 'Intelligence Artificielle, IoT...',
                'value' => $this->old('theme', $this->projet['theme']),
                'error' => $this->hasError('theme') ? $this->error('theme') : '',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'text',
                'name' => 'financement',
                'label' => 'Financement',
                'placeholder' => 'ANR, DGRSDT...',
                'value' => $this->old('financement', $this->projet['financement']),
                'error' => $this->hasError('financement') ? $this->error('financement') : '',
                'required' => true
            ]) ?>

            <?= Components::Select([
                'name' => 'status',
                'label' => 'Statut',
                'options' => [
                    'en cours' => 'En cours',
                    'termine' => 'Terminé',
                    'soumis' => 'Soumis'
                ],
                'value' => $this->old('status', $this->projet['status']),
                'error' => $this->hasError('status') ? $this->error('status') : '',
                'placeholder' => 'Sélectionner un statut',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'date',
                'name' => 'start_date',
                'label' => 'Date de début',
                'value' => $this->old('start_date', $this->projet['start_date']),
                'error' => $this->hasError('start_date') ? $this->error('start_date') : '',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'date',
                'name' => 'end_date',
                'label' => 'Date de fin',
                'value' => $this->old('end_date', $this->projet['end_date'] ?? ''),
                'error' => $this->hasError('end_date') ? $this->error('end_date') : ''
            ]) ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
?>

<style>
    .alert-container {
        margin-bottom: 1.5rem;
    }

    #projetForm .card {
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        #projetForm [style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>