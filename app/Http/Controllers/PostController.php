<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\Log;
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::where('user_id', Auth::id())->get();
        return view('posts.index', compact('posts'));
    }

    public function store(Request $request)
    {
        Log::info('Incoming post store request', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        $user = Auth::user();

        if (!$user) {
            Log::error('No authenticated user found.');
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        // Only allow users with type 'startup' to create posts
        if ($user->type !== 'startup') {
            Log::warning('Unauthorized user type tried to create post', [
                'user_id' => $user->id,
                'user_type' => $user->type,
            ]);
            return response()->json(['error' => "You can't access this page"], 403);
        }

        $request->validate([
            'title' => 'required|string',
            'image' => 'nullable|image|max:2048'
        ]);

        $totalPosts = Post::where('user_id', $user->id)->count();
        Log::info('Total posts by user', ['count' => $totalPosts]);

        // Upload image if present
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
            Log::info('Image uploaded', ['path' => $imagePath]);
        }

        if ($totalPosts < 2) {
            $post = Post::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'image' => $imagePath,
            ]);
            Log::info('Post created (free)', ['post_id' => $post->id]);
            return response()->json(['message' => 'Post created (free)'], 201);
        }

        $package = $user->packages()->where('status', 'active')->latest()->first();
        Log::info('Package fetched for user', ['package' => $package]);

        if ($package && $package->pivot->remaining_posts > 0) {
            $post = Post::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'image' => $imagePath,
            ]);
            Log::info('Post created using package', ['post_id' => $post->id]);

            $package->pivot->decrement('remaining_posts');
            Log::info('Remaining posts decremented', ['package_id' => $package->id]);

            return response()->json(['message' => 'Post created (from package)'], 201);
        }

        Log::warning('User exceeded free post limit and has no active package', ['user_id' => $user->id]);
        return response()->json(['error' => 'You need a subscription to add more posts.'], 403);
    }

    public function fetchPosts()
    {
        // Get all posts with user data
        $posts = Post::with('user')->latest()->get();

        return response()->json($posts);
    }
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }


}
