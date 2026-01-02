<?php

namespace Core;

abstract class Middleware {
    public abstract function handle(Request $request, \Closure $next);
}