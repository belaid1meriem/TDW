<?php

namespace Core;

class Config {
    public static function database(){
        return [
            'host'=>'localhost',
            'dbname'=>'test',
            'username'=>'admin',
            'password'=>'admin'
        ];
    }

}