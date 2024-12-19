<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

class StoreFirebaseController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'status' => 'required|in:backlog,todo,inprogress,done',
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $set = 'secret-' . Str::uuid()->toString();
        $id = Str::uuid()->toString();

        $data = [
            'name' => $validated['name'],
            'status' => $validated['status'],
            'start' => $validated['start'],
            'end' => $validated['end'],
        ];

        FirebaseService::create($set, $id, $data);

        return response()->api($data, 200);
    }


    public function list()
    {
        $data = FirebaseService::getAll();
        if(!$data){
            return response()->json('Data not found');
        }
        return response()->json($data);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:100',
            'content' => 'required|string',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'content' => $validated['content'],
            'created_at' => now()->toDateTimeString(),
        ];

        $title = 'secret';
        FirebaseService::update($title, $data);

        return response()->json('Data has been updated');
    }
}
