<?php
declare(strict_types=1);

namespace App;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

class Database
{

    public static function connect(): Connection
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $connectionParams = [
            'dbname' => 'articles',
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        ];

        return DriverManager::getConnection($connectionParams);

    }

}