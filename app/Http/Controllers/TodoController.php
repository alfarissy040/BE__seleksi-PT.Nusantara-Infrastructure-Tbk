<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    public function index()
    {
        $todos = Auth::user()->todos;

        return response()->json([
            'todos' => $todos,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $todo = new Todo();
        $todo->title = $request->title;
        $todo->description = $request->description;
        $todo->completed = false;

        Auth::user()->todos()->save($todo);

        return response()->json([
            'message' => 'Todo created successfully',
            'todo' => $todo,
        ], 201);
    }

    public function show($id)
    {
        $todo = Auth::user()->todos()->find($id);

        if (!$todo) {
            return response()->json([
                'message' => 'Todo not found',
            ], 404);
        }

        return response()->json([
            'todo' => $todo,
        ]);
    }

    public function update(Request $request, $id)
    {
        $todo = Auth::user()->todos()->find($id);

        if (!$todo) {
            return response()->json([
                'message' => 'Todo not found',
            ], 404);
        }

        // Memastikan pengguna hanya dapat mengubah todo mereka sendiri
        if ($todo->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'nullable',
            'completed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $todo->title = $request->title;
        $todo->description = $request->description;
        $todo->completed = $request->completed ?? false;
        $todo->save();

        return response()->json([
            'message' => 'Todo updated successfully',
            'todo' => $todo,
        ]);
    }

    public function destroy($id)
    {
        $todo = Auth::user()->todos()->find($id);

        if (!$todo) {
            return response()->json([
                'message' => 'Todo not found',
            ], 404);
        }

        // Memastikan pengguna hanya dapat menghapus todo mereka sendiri
        if ($todo->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $todo->delete();

        return response()->json([
            'message' => 'Todo deleted successfully',
        ]);
    }
}
