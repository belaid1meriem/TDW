<?php
abstract class Controller{
    function render(View $view){
        $view->render();
    }
}