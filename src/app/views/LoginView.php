<?php

namespace App\Views;

use Core\View;

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
            <link rel="stylesheet" href="css/auth.css">
        </head>
        <body>
            <div class="auth-container">
                <div class="auth-card">
                    <h1>Login</h1>
                    
                    <?php if ($this->hasFlash('error')): ?>
                        <div class="alert alert-error">
                            <?= $this->escape($this->flash('error')) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->hasFlash('success')): ?>
                        <div class="alert alert-success">
                            <?= $this->escape($this->flash('success')) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login">
                        <div class="form-group">
                            <label for="identifier">Email or Username</label>
                            <input 
                                type="text" 
                                id="identifier" 
                                name="identifier" 
                                value="<?= $this->escape($this->old('identifier')) ?>"
                                required
                            >
                            <?php if ($this->hasError('identifier')): ?>
                                <span class="error-message"><?= $this->escape($this->error('identifier')) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                            >
                            <?php if ($this->hasError('password')): ?>
                                <span class="error-message"><?= $this->escape($this->error('password')) ?></span>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>

                    <div class="auth-links">
                        <p>Don't have an account? <a href="/register">Register</a></p>
                        <p><a href="/forgot-password">Forgot Password?</a></p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}