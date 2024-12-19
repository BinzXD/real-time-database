<?php

namespace App\Services;

use Kreait\Firebase;
use Kreait\Firebase\Factory;

class FirebaseService
{
    public static function connect()
    {
        $firebase = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
            ->withDatabaseUri(env("FIREBASE_DATABASE_URL"));

        return $firebase->createDatabase();
    }

    public static function create(string $setName, string $id, array $data)
    {
        $database = self::connect();
        $database->getReference("{$setName}/{$id}")->set($data);
    }

    public static function getAll()
    {
        $database = self::connect();
        return $database->getReference()->getValue(); 
    }

   
    public static function update($path, $data)
    {
        $database = self::connect();
        $database->getReference($path)->update($data);
    }

    public static function delete($path)
    {
        $database = self::connect();
        $database->getReference($path)->remove();
    }
}
