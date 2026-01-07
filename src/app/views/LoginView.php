<?php

namespace App\Views;

use Core\View;
use Core\Components;

class LoginView extends View
{
    public function render()
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login</title>
            <link rel="stylesheet" href="css/base.css">
            <style>

                .auth-card {
                    width: 100%;
                    max-width: 400px;
                    gap: .5rem;
                }

                .auth-header {
                    text-align: center;
                    margin-bottom: 2rem;
                }

                .auth-header h1 {
                    font-size: 1.875rem;
                    font-weight: 600;
                    margin-bottom: 0.5rem;
                    color: hsl(var(--foreground));
                }

                .auth-header p {
                    font-size: 0.875rem;
                    color: hsl(var(--muted-foreground));
                }

                .auth-links {
                    margin-top: 1.5rem;
                    text-align: center;
                    font-size: 0.875rem;
                    color: hsl(var(--muted-foreground));
                }

                .auth-links p {
                    margin: 0.5rem 0;
                }

                .auth-links a {
                    color: hsl(var(--primary));
                    text-decoration: none;
                    font-weight: 500;
                }

                .auth-links a:hover {
                    text-decoration: underline;
                }

                .alert-container {
                    margin-bottom: 1.5rem;
                }
            </style>
        </head>
        <body>
            <div class="main-layout">
                <?php
                echo Components::Card([
                    'class' => 'auth-card',
                    'content' => $this->renderCardContent()
                ]);
                ?>
            </div>
        </body>
        </html>
        <?php
    }

    private function renderCardContent(): string
    {
        ob_start();
        ?>
        <div class="auth-header">
            <h1>Bienvenue</h1>
            <p>Connectez-vous à votre compte</p>
        </div>

        <?php if ($this->hasFlash('error')): ?>
            <div class="alert-container">
                <?php
                echo Components::Alert([
                    'variant' => 'destructive',
                    'message' => $this->flash('error'),
                    'dismissible' => true
                ]);
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

        <form method="POST" action="login">
            <?php
            echo Components::Input([
                'type' => 'text',
                'name' => 'identifier',
                'label' => 'Email ou Username',
                'placeholder' => 'Enter your email or username',
                'value' => $this->old('identifier'),
                'error' => $this->hasError('identifier') ? $this->error('identifier') : '',
                'required' => true
            ]);

            echo Components::Input([
                'type' => 'password',
                'name' => 'password',
                'label' => 'Password',
                'placeholder' => 'Enter your password',
                'error' => $this->hasError('password') ? $this->error('password') : '',
                'required' => true
            ]);

            echo Components::Button([
                'text' => 'Login',
                'type' => 'submit',
                'variant' => 'default',
                'class' => 'w-full mt-2'
            ]);
            ?>
        </form>

        <div class="auth-links">
            <p>Don't have an account? <a href="register">Register</a></p>
            <p><a href="/forgot-password">Forgot Password?</a></p>
        </div>
        <?php
        return ob_get_clean();
    }
}