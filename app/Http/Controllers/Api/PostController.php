<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['user.company', 'likes', 'comments.user'])
            ->published()
            ->latest();

        // Feed personalizado basado en conexiones
        if ($request->has('feed') && $request->feed === 'connections') {
            $user = $request->user();
            $connectionIds = $user->connections()->pluck('users.id')->toArray();
            $connectionIds[] = $user->id; // Incluir posts propios
            
            $query->whereIn('user_id', $connectionIds);
        }

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        $posts = $query->paginate(10);

        // Agregar información de si el usuario actual ha dado like
        $posts->getCollection()->transform(function ($post) use ($request) {
            $post->user_has_liked = $request->user() 
                ? $post->likes()->where('user_id', $request->user()->id)->exists()
                : false;
            return $post;
        });

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'type' => 'in:text,image,video,link,job,event',
            'media' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        $post = $request->user()->posts()->create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type ?? 'text',
            'media' => $request->media,
            'metadata' => $request->metadata,
            'published_at' => now(),
        ]);

        return response()->json([
            'message' => 'Post creado exitosamente',
            'post' => $post->load('user.company'),
        ], 201);
    }

    public function show(Post $post)
    {
        return response()->json([
            'post' => $post->load(['user.company', 'comments.user', 'likes']),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'sometimes|string',
            'type' => 'in:text,image,video,link,job,event',
            'media' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        $post->update($request->only([
            'title', 'content', 'type', 'media', 'metadata'
        ]));

        return response()->json([
            'message' => 'Post actualizado exitosamente',
            'post' => $post->load('user.company'),
        ]);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json([
            'message' => 'Post eliminado exitosamente',
        ]);
    }

    public function like(Request $request, Post $post)
    {
        $user = $request->user();
        
        $existingLike = PostLike::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $post->decrement('likes_count');
            $liked = false;
        } else {
            PostLike::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
            $post->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'message' => $liked ? 'Post liked' : 'Post unliked',
            'liked' => $liked,
            'likes_count' => $post->fresh()->likes_count,
        ]);
    }
}