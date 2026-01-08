<?php
namespace App\Views\Admin\Equipes;
use App\Views\Admin\AdminLayout;
use Core\Components;

class AddEquipeView extends AdminLayout
{
    public function renderContent()
    {
        ?>
        <main class="flex flex-col gap-6">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Ajouter une nouvelle equipe</h2>
                <?= Components::Button([
                    'text' => '← Retour',
                    'variant' => 'outline',
                    'href' => $this->asset('admin/equipes')
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

            <?= $this->renderEquipeForm() ?>
        </main>
        <?php
    }

    private function renderEquipeForm()
    {
        ob_start();
        ?>
        <form method="POST" action="<?= $this->asset('admin/equipes/store') ?>" id="EquipeForm">
            <?= Components::Card([
                'title' => 'Informations de l\'equipe ',
                'content' => $this->renderEquipeSection()
            ]) ?>

            <div class="flex gap-4">
                <?= Components::Button([
                    'text' => 'Créer l\'equipe',
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
                        'onclick' => "window.location.href='" . $this->asset('admin/equipes') . "'"
                    ]
                ]) ?>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    private function renderEquipeSection()
    {
        ob_start();
        ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <?= Components::Input([
                'type' => 'text',
                'name' => 'name',
                'label' => 'Nom',
                'placeholder' => 'Nom de l\'equipe',
                'value' => $this->old('name'),
                'error' => $this->hasError('name') ? $this->error('name') : '',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'text',
                'name' => 'description',
                'label' => 'Description',
                'placeholder' => 'Intelligence Artificielle, IoT...',
                'value' => $this->old('description'),
                'error' => $this->hasError('description') ? $this->error('description') : '',
                'required' => true
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

    #EquipeForm .card {
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        #EquipeForm [style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>