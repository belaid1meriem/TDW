<?php

namespace Core;

class Config {
    public static function database(){
        return [
            'host'=>'localhost',
            'port'=>'3307',
            'dbname'=>'tdw',
            'username'=>'root',
            'password'=>''
        ];
    }

}