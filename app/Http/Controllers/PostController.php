<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::where('user_id', auth()->id())
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        $resultado = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'titulo' => $post->titulo,
                'contenido' => $post->contenido,
                'autor' => $post->user->name ?? 'Usuario Público',
                'fecha' => $post->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json($resultado, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
    'titulo' => 'required|string|max:255',
    'contenido' => 'required|string',
        ]);

         $post = Post::create([
    'titulo' => $request->titulo,
    'contenido' => $request->contenido,
    'user_id' => auth()->id(),
]);

        return response()->json([
            'id' => $post->id,
            'titulo' => $post->titulo,
            'contenido' => $post->contenido,
            'autor' => $post->user->name ?? 'Usuario Público',
            'fecha' => $post->created_at->format('Y-m-d H:i:s'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'contenido' => 'sometimes|string',
        ]);

        $post->update($request->only(['titulo', 'contenido']));

        return response()->json([
            'message' => 'Post actualizado',
            'post' => $post
        ], 200);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post eliminado'], 200);
    }
}
