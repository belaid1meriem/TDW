<?php
namespace App\Views;
use Core\View;

abstract class AdminLayout extends View {
    public function render() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Dashboard</title>
            <link rel="stylesheet" href="css/base.css">
        </head>
        <body class="main-layout">
            <?php $this->renderHeader(); ?>
            <?php $this->renderContent(); ?>
            <?php $this->renderFooter(); ?>
        </body>
        </html>
        <?php
    }

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