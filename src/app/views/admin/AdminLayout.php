<?php
namespace App\Views\Admin;
use App\Views\BaseLayout;

abstract class AdminLayout extends BaseLayout {

    protected function renderHeader() {
        ?>
        <header class="header">
            <div class="header-container">
                <a href="/" class="header-logo">LMCS</a>
                <nav class="header-nav">
                    <a href="/">Accueil</a>
                    <a href="/about">À propos</a>
                    <a href="/contact">Contact</a>
                </nav>
            </div>
        </header>
        <?php
    }

    protected function renderFooter() {
        ?>
        <footer class="footer">
            <div class="footer-container">
                <p>&copy; 2025 LMCS. All rights reserved.</p>
            </div>
        </footer>
        <?php
    }

    abstract protected function renderContent();
}