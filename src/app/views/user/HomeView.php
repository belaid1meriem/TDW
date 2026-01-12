<?php
namespace App\Views\User;

use App\Views\BaseLayout;
use Core\Components;

class HomeView extends BaseLayout
{
    protected function renderContent()
    {
        $slideshow = $this->data['slideshow'] ?? [];
        $labInfo = $this->data['labInfo'] ?? [];
        $publications = $this->data['publications'] ?? [];
        $events = $this->data['events'] ?? ['data' => [], 'pages' => 0, 'page' => 1];
        $projects = $this->data['projects'] ?? [];
        $partners = $this->data['partners'] ?? [];
        $teams = $this->data['teams'] ?? [];
        $stats = $this->data['stats'] ?? [];
        
        ?>
        <style>
            /* Slideshow Styles */
            .slideshow {
                position: relative;
                width: 100%;
                max-width: 1200px;
                margin: 2rem auto;
                height: 400px;
                overflow: hidden;
                border-radius: var(--radius);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            
            .slide {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                opacity: 0;
                transition: opacity 1s ease-in-out;
                background-size: cover;
                background-position: center;
                display: flex;
                align-items: flex-end;
            }
            
            .slide.active {
                opacity: 1;
                z-index: 1;
            }
            
            .slide-content {
                background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
                padding: 2rem;
                width: 100%;
                color: white;
            }
            
            .slide-title {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
            }
            
            .slide-description {
                font-size: 0.875rem;
                margin-bottom: 1rem;
                opacity: 0.9;
            }
            
            .slide-indicators {
                position: absolute;
                bottom: 1rem;
                right: 1rem;
                display: flex;
                gap: 0.5rem;
                z-index: 2;
            }
            
            .indicator {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: rgba(255,255,255,0.5);
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .indicator.active {
                background: white;
                width: 30px;
                border-radius: 5px;
            }
            
            /* Stats Grid */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1.5rem;
                margin: 2rem 0;
            }
            
            .stat-card {
                text-align: center;
                padding: 1.5rem;
                background: hsl(var(--card));
                border-radius: var(--radius);
                border: 1px solid hsl(var(--border));
            }
            
            .stat-number {
                font-size: 2.5rem;
                font-weight: 700;
                color: hsl(var(--primary));
            }
            
            .stat-label {
                font-size: 0.875rem;
                color: hsl(var(--muted-foreground));
                margin-top: 0.5rem;
            }
            
            /* Section Styles */
            .section {
                margin: 3rem 0;
            }
            
            .section-title {
                font-size: 1.875rem;
                font-weight: 600;
                margin-bottom: 1.5rem;
                color: hsl(var(--foreground));
            }
            
            /* Card Grid */
            .card-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 1.5rem;
            }
            
            /* Partner Grid */
            .partner-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 2rem;
                align-items: center;
            }
            
            .partner-logo {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                border: 1px solid hsl(var(--border));
                border-radius: var(--radius);
                background: hsl(var(--card));
                transition: all 0.3s;
            }
            
            .partner-logo:hover {
                transform: translateY(-4px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            
            .partner-logo img {
                max-width: 100%;
                max-height: 60px;
                object-fit: contain;
            }
            
            /* Pagination */
            .pagination {
                display: flex;
                justify-content: center;
                gap: 0.5rem;
                margin-top: 2rem;
            }
        </style>
        
        <main>
            <!-- Slideshow Section -->
            <?= $this->renderSlideshow($slideshow) ?>

            
            <!-- Laboratory Presentation -->
            <?= $this->renderLabPresentation($labInfo, $teams) ?>
            
            <!-- Recent Publications -->
            <section class="section">
                <h2 class="section-title">Publications Récentes</h2>
                <?= $this->renderPublications($publications) ?>
                <div style="text-align: center; margin-top: 2rem;">
                    <?= Components::Button([
                        'text' => 'Voir toutes les publications',
                        'variant' => 'outline',
                        'href' => BASE_PATH . '/admin/publications'
                    ]) ?>
                </div>
            </section>
            
            <!-- Upcoming Events -->
            <section class="section">
                <h2 class="section-title">Événements à Venir</h2>
                <?= $this->renderEvents($events) ?>
            </section>
            
            <!-- Active Projects -->
            <section class="section">
                <h2 class="section-title">Projets en Cours</h2>
                <?= $this->renderProjects($projects) ?>
            </section>
            
            <!-- Partners -->
            <section class="section">
                <h2 class="section-title">Nos Partenaires</h2>
                <?= $this->renderPartners($partners) ?>
            </section>
        </main>
        
        <script>
            // Slideshow auto-advance
            let currentSlide = 0;
            const slides = document.querySelectorAll('.slide');
            const indicators = document.querySelectorAll('.indicator');
            
            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.classList.toggle('active', i === index);
                });
                indicators.forEach((indicator, i) => {
                    indicator.classList.toggle('active', i === index);
                });
            }
            
            function nextSlide() {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }
            
            // Auto-advance every 5 seconds
            setInterval(nextSlide, 5000);
            
            // Manual navigation
            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    currentSlide = index;
                    showSlide(currentSlide);
                });
            });
        </script>
        <?php
    }
    
    private function renderSlideshow($slides): string
    {
        if (empty($slides)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="slideshow">
            <?php foreach ($slides as $index => $slide): ?>
                <div class="slide <?= $index === 0 ? 'active' : '' ?>" 
                     style="background-image: url('<?= $this->asset('images/' . ($slide['image'] ?? 'default-slide.jpg')) ?>');">
                    <div class="slide-content">
                        <h3 class="slide-title"><?= $this->escape($slide['title']) ?></h3>
                        <p class="slide-description"><?= $this->escape(substr($slide['content'], 0, 150)) ?>...</p>
                        <?= Components::Button([
                            'text' => 'En savoir plus',
                            'variant' => 'secondary',
                            'size' => 'sm',
                            'href' => BASE_PATH . '/actualites/' . $slide['id']
                        ]) ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="slide-indicators">
                <?php foreach ($slides as $index => $slide): ?>
                    <div class="indicator <?= $index === 0 ? 'active' : '' ?>"></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    
    private function renderLabPresentation($labInfo, $teams): string
    {
        ob_start();
        ?>
        <section class="section">
            <h2 class="section-title">À Propos du Laboratoire</h2>
            <?= Components::Card([
                'content' => $this->renderLabContent($labInfo, $teams)
            ]) ?>
        </section>
        <?php
        return ob_get_clean();
    }
    
    private function renderLabContent($labInfo, $teams): string
    {
        ob_start();
        ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">
                    <?= $this->escape($labInfo['site_name'] ?? 'LMCS') ?>
                </h3>
                <p style="color: hsl(var(--muted-foreground)); line-height: 1.6;">
                    Le Laboratoire de Méthodes de Conception des Systèmes (LMCS) est un centre de recherche 
                    d'excellence en informatique, spécialisé dans l'intelligence artificielle, les systèmes 
                    distribués, et la cybersécurité. Nos équipes travaillent sur des projets innovants en 
                    collaboration avec des partenaires académiques et industriels internationaux.
                </p>
            </div>
            <div>
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Nos Équipes</h4>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <?php foreach ($teams as $team): ?>
                        <?= Components::Badge([
                            'text' => $this->escape($team['name']),
                            'variant' => 'outline'
                        ]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
  
    private function renderPublications($publications): string
    {
        ob_start();
        ?>
        <div class="card-grid">
            <?php foreach ($publications as $pub): ?>
                <?= Components::Card([
                    'title' => $this->escape($pub['title']),
                    'content' => $this->renderPublicationContent($pub),
                    'class' => 'clickable-card'
                ]) ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function renderPublicationContent($pub): string
    {
        ob_start();
        ?>
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <p style="font-size: 0.875rem; color: hsl(var(--muted-foreground));">
                <?= $this->escape(substr($pub['resume'] ?? '', 0, 100)) ?>...
            </p>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <?= Components::Badge([
                    'text' => $this->escape($pub['type']),
                    'variant' => 'secondary'
                ]) ?>
                <?php if ($pub['publication_date']): ?>
                    <span class="text-muted" style="font-size: 0.75rem;">
                        <?= date('Y', strtotime($pub['publication_date'])) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function renderEvents($eventsData): string
    {
        $events = $eventsData['data'] ?? [];
        
        ob_start();
        ?>
        <div class="card-grid">
            <?php if (empty($events)): ?>
                <p class="text-muted">Aucun événement à venir pour le moment.</p>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <?= Components::Card([
                        'title' => $this->escape($event['title']),
                        'content' => $this->renderEventContent($event),
                        'class' => 'clickable-card'
                    ]) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?= $this->renderEventsPagination($eventsData) ?>
        <?php
        return ob_get_clean();
    }
    
    private function renderEventContent($event): string
    {
        ob_start();
        ?>
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <p style="font-size: 0.875rem; color: hsl(var(--muted-foreground));">
                <?= $this->escape(substr($event['description'] ?? '', 0, 100)) ?>...
            </p>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <?= Components::Badge([
                    'text' => $this->escape($event['type']),
                    'variant' => 'outline'
                ]) ?>
                <span class="text-muted" style="font-size: 0.875rem;">
                    📅 <?= date('d/m/Y', strtotime($event['event_date'])) ?>
                </span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function renderEventsPagination($eventsData): string
    {
        $pages = $eventsData['pages'] ?? 0;
        $currentPage = $eventsData['page'] ?? 1;
        
        if ($pages <= 1) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="?events_page=<?= $i ?>" 
                   class="btn btn-sm <?= $i === $currentPage ? 'btn-default' : 'btn-outline' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function renderProjects($projects): string
    {
        ob_start();
        ?>
        <div class="card-grid">
            <?php foreach ($projects as $project): ?>
                <?= Components::Card([
                    'title' => $this->escape($project['title']),
                    'content' => $this->renderProjectContent($project),
                    'class' => 'clickable-card'
                ]) ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function renderProjectContent($project): string
    {
        ob_start();
        ?>
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <?= Components::Badge([
                    'text' => $this->escape($project['theme'] ?? 'N/A'),
                    'variant' => 'secondary'
                ]) ?>
                <?= Components::Badge([
                    'text' => $this->escape($project['status']),
                    'variant' => 'success'
                ]) ?>
            </div>
            <p style="font-size: 0.875rem; color: hsl(var(--muted-foreground));">
                <strong>Financement:</strong> <?= $this->escape($project['financement'] ?? 'N/A') ?>
            </p>
            <p style="font-size: 0.75rem; color: hsl(var(--muted-foreground));">
                <?= date('Y', strtotime($project['start_date'])) ?> - 
                <?= date('Y', strtotime($project['end_date'])) ?>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function renderPartners($partners): string
    {
        ob_start();
        ?>
        <div class="partner-grid">
            <?php foreach ($partners as $partner): ?>
                <a href="<?= $this->escape($partner['website_link'] ?? '#') ?>" 
                   target="_blank" 
                   class="partner-logo"
                   title="<?= $this->escape($partner['name']) ?>">
                    <?php if ($partner['logo']): ?>
                        <img src="<?= $this->asset('images/' . $partner['logo']) ?>" 
                             alt="<?= $this->escape($partner['name']) ?>">
                    <?php else: ?>
                        <span style="font-size: 0.875rem; font-weight: 600;">
                            <?= $this->escape($partner['name']) ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}