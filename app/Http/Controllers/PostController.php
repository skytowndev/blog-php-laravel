<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\SendNewPostEmail;


class PostController extends Controller
{

    public function search($term) {
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
    }


    public function update(Post $post, Request $request) {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);
        return back()->with('success', 'Post successfully updated.');


    }


    public function showEditForm(Post $post) {

        return view('edit-post', ['post' => $post]);
    }

    
    public function deleteApi(Post $post) {
        $post->delete();
        return 'post deleted';
    }


    public function delete(Post $post) {
        //if (auth()->user()->cannot('delete', $post)) {
        //    return 'You cannot do that';
       // }

        $post->delete();
        return redirect('/profile/' . auth()->user()->username)->with('success', 'Post successfully deleted');
    }


    public function showSinglePost(Post $post){
        //$post['body'] = Str::markdown($post->body);
        $post['body'] = strip_tags(Str::markdown($post->body),'<p><ul><ol><li><strong><em><h1><h2><h3><br>');
        return view('single-post',['post' => $post]);
    }


    public function showCreateForm(){

        return view('create-post');
    }
    

    public function storeNewPostApi(Request $request) {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $post = Post::create($incomingFields);

        return $post->id;
    }

    public function storeNewPost(Request $request) {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $post = Post::create($incomingFields);

        //Mail::to('test@google.com')->send(new NewPostEmail());
        //Mail::to(auth()->user()->email)->send(new NewPostEmail(['name' => auth()->user()->username, 'title' => $post->title]));

        dispatch(new SendNewPostEmail([
            'sendTo' => auth()->user()->email,
            'name' => auth()->user()->username,
            'title' => $post->title
        ]));     

        return redirect("/post/{$post->id}")->with('success', 'New Post successfully created.');
    }
}
