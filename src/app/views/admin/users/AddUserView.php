<?php
namespace App\Views\Admin\Users;
use App\Views\Admin\AdminLayout;
use Core\Session;
use Core\Components;

class AddUserView extends AdminLayout
{
    public function renderContent()
    {
        ?>
        <main class="flex flex-col gap-6">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Ajouter un nouvel utilisateur</h2>
                <?= Components::Button([
                    'text' => '← Retour',
                    'variant' => 'outline',
                    'href' => $this->asset('admin/users')
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

            <?= $this->renderUserForm() ?>
        </main>
        <?php
    }

    private function renderUserForm()
    {
        ob_start();
        ?>
        <form method="POST" action=<?= $this->asset('admin/users/store') ?> enctype="multipart/form-data" id="userForm">
            <?= Components::Card([
                'title' => 'Informations de connexion',
                'content' => $this->renderLoginSection()
            ]) ?>

            <?= Components::Card([
                'title' => 'Informations personnelles',
                'content' => $this->renderPersonalSection()
            ]) ?>

            <?= Components::Card([
                'title' => 'Informations professionnelles',
                'content' => $this->renderProfessionalSection()
            ]) ?>

            <?= Components::Card([
                'title' => 'Informations académiques',
                'content' => $this->renderAcademicSection()
            ]) ?>

            <div class="flex gap-4">
                <?= Components::Button([
                    'text' => 'Créer l\'utilisateur',
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
                        'onclick' => "window.location.href='/admin/users'"
                    ]
                ]) ?>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    private function renderLoginSection()
    {
        ob_start();
        ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <?= Components::Input([
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email',
                'placeholder' => 'exemple@domaine.com',
                'value' => $this->old('email'),
                'error' => $this->hasError('email') ? $this->error('email') : '',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'text',
                'name' => 'username',
                'label' => 'Nom d\'utilisateur',
                'placeholder' => 'nomutilisateur',
                'value' => $this->old('username'),
                'error' => $this->hasError('username') ? $this->error('username') : '',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'password',
                'name' => 'password',
                'label' => 'Mot de passe',
                'placeholder' => '********',
                'error' => $this->hasError('password') ? $this->error('password') : '',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'password',
                'name' => 'password_confirmation',
                'label' => 'Confirmer le mot de passe',
                'placeholder' => '********',
                'error' => $this->hasError('password_confirmation') ? $this->error('password_confirmation') : '',
                'required' => true
            ]) ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderPersonalSection()
    {
        ob_start();
        ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <?= Components::Input([
                'type' => 'text',
                'name' => 'first_name',
                'label' => 'Prénom',
                'placeholder' => 'Jean',
                'value' => $this->old('first_name'),
                'error' => $this->hasError('first_name') ? $this->error('first_name') : '',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'text',
                'name' => 'last_name',
                'label' => 'Nom',
                'placeholder' => 'Dupont',
                'value' => $this->old('last_name'),
                'error' => $this->hasError('last_name') ? $this->error('last_name') : '',
                'required' => true
            ]) ?>

            <div style="grid-column: 1 / -1;">
                <?= Components::Input([
                    'type' => 'file',
                    'name' => 'photo',
                    'label' => 'Photo de profil',
                    'error' => $this->hasError('photo') ? $this->error('photo') : '',
                    'attrs' => [
                        'accept' => 'image/*'
                    ]
                ]) ?>
                <p class="text-sm text-muted" style="margin-top: 0.25rem;">
                    Formats acceptés: JPG, PNG, GIF (Max: 2MB)
                </p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderProfessionalSection()
    {
        ob_start();
        ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <?= Components::Select([
                'name' => 'role',
                'label' => 'Rôle',
                'options' => [
                    'admin' => 'Administrateur',
                    'enseignant' => 'Enseignant',
                    'chercheur' => 'Chercheur',
                    'doctorant' => 'Doctorant',
                    'etudiant' => 'Étudiant',
                    'invite' => 'Invité'
                ],
                'value' => $this->old('role'),
                'error' => $this->hasError('role') ? $this->error('role') : '',
                'placeholder' => 'Sélectionner un rôle',
                'required' => true
            ]) ?>

            <?= Components::Input([
                'type' => 'text',
                'name' => 'grade',
                'label' => 'Grade',
                'placeholder' => 'Professeur, Maître de conférences...',
                'value' => $this->old('grade'),
                'error' => $this->hasError('grade') ? $this->error('grade') : ''
            ]) ?>

            <?= Components::Input([
                'type' => 'text',
                'name' => 'poste',
                'label' => 'Poste',
                'placeholder' => 'Directeur de laboratoire, Chercheur...',
                'value' => $this->old('poste'),
                'error' => $this->hasError('poste') ? $this->error('poste') : ''
            ]) ?>

            <?= Components::Select([
                'name' => 'account_status',
                'label' => 'Statut',
                'options' => [
                    'actif' => 'Actif',
                    'suspendu' => 'Suspendu',
                ],
                'value' => $this->old('account_status', 'actif'),
                'error' => $this->hasError('account_status') ? $this->error('account_status') : '',
                'required' => true
            ]) ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderAcademicSection()
    {
        ob_start();
        ?>
        <div style="display: grid; gap: 1rem;">
            <?= Components::Textarea([
                'name' => 'domain_research',
                'label' => 'Domaine de recherche',
                'placeholder' => 'Intelligence Artificielle, Machine Learning, Réseaux de neurones...',
                'value' => $this->old('domain_research'),
                'error' => $this->hasError('domain_research') ? $this->error('domain_research') : '',
                'rows' => 3
            ]) ?>

            <?= Components::Textarea([
                'name' => 'bio',
                'label' => 'Biographie',
                'placeholder' => 'Présentation du profil, parcours académique, intérêts de recherche...',
                'value' => $this->old('bio'),
                'error' => $this->hasError('bio') ? $this->error('bio') : '',
                'rows' => 4
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

    #userForm .card {
        margin-bottom: 1.5rem;
    }

    .text-muted {
        color: hsl(var(--muted-foreground));
    }

    @media (max-width: 768px) {
        #userForm [style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>