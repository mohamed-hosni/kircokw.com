<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::getPosts();
        return view('admin.pages.posts.index', ['posts' => $posts->toArray()]);
    }

    public function show(Request $request)
    {
        $post = Post::with('gallaryImages')->find($request->post_id);
        return view('admin.pages.posts.post', ['post' => $post]);
    }

    public function create(Request $request)
    {
        return Post::createPost($request);
    }

    public function showCreate()
    {
        return view('admin.pages.posts.upsert');
    }

    public function edit(Request $request)
    {
        return Post::updatePost($request);
    }

    public function showEdit(Request $request)
    {
        $post = Post::with('gallaryImages')->find($request->post_id);        
        return view('admin.pages.posts.upsert', ["post" => $post]);
    }

    public function delete(Request $request)
    {
        return Post::deletePost($request);
    }
}
