<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date'
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'due_date' => $validated['due_date'] ?? null
        ]);

        return response()->json(['message' => 'Task created', 'data' => $task]);
    }

    public function index(Request $request)
    {
        $query = Task::query();

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $tasks = Cache::remember('tasks_' . md5($search ?? ''), 60, function () use ($query) {
            return $query->orderBy('created_at', 'desc')->get();
        });

        return response()->json($tasks);
    }

    public function update(Request $request, $id)
    {
        $task = Task::where('id', $id)->firstOrFail();

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date'
        ]);

        $task->update($validated);

        return response()->json(['message' => 'Task updated', 'data' => $task]);
    }

    public function destroy($id)
    {
        $task = Task::where('id', $id)->firstOrFail();
        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }

}
