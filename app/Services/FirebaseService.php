<?php

namespace App\Services;

use Illuminate\Http\Request;
use Kreait\Firebase;
use Kreait\Firebase\Factory;

class FirebaseService
{
    public static function connect()
    {
        $firebase = (new Factory)
            ->withServiceAccount(base_path('storage/app/firebase/laravel-firebase-fec.json'))
            ->withDatabaseUri('https://alam-anaam-default-rtdb.firebaseio.com/');

        return $firebase->createDatabase();
    }
}
