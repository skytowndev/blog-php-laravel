<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    public function storeAvatar(REquest $request) {

        $request->validate([
            'avatar' => ['required','image','max:3000']//'required|image|max:3000'
        ]);

        //$request->file('avatar')->store('public/avatars');
        $imgData = Image::make($request->file('avatar'))->fit(240)->encode('jpg');
        $user = auth()->user();
        $filename = $user->id . '-' . uniqid() . '.jpg';
        Storage::put('public/avatars/' . $filename, $imgData);

        $oldAvatar = $user->avatar;

        $user->avatar = $filename;
        $user->save();

        if ($oldAvatar != "fallback-avatar.jpg") {
            Storage::delete(str_replace("/storage/", "public/", $oldAvatar));
        }

        return back()->with('success','Congrats on the new avatar');
    }

    public function showAvatrForm() {
        return view("avatar-form");
    }

    public function profile(User $user) {

        return view('profile-posts', ['username' => $user->username, 'avatar' => $user->avatar, 'posts' => $user->posts()->latest()->get(), 'postCount' => $user->posts()->count()]);
    }

    public function logout() {
        auth()->logout();
        return redirect('/')->with('success', 'You are now logged out.');;
    }


    public function showCorrectHomepage() {
        
        if (auth()->check()) {
            return view('homepage-feed');
        } else {

            //if (Cache::has('postCount')) {
            //    $postCount = Cache::get('postCount');
            //} else {
            //    sleep(5);
            //    $postCount = Post::count();
            //    Cache::put('postCount', $postCount, 20);
            //}

            $postCount = Cache::remember('postCount',20, function(){
                //sleep(5);
                return Post::count();
            });
            
            return view('homepage',['postCount' => $postCount]);
        }
    }

    public function loginApi(Request $request) {
        $incomingFields = $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required'
        ]);
    
        if (auth()->attempt($incomingFields)) {
            $user = User::where('email', $incomingFields['email'])->first();
            $token = $user->createToken('ourapptoken')->plainTextToken;
            return $token;
        }
        
        return 'sorry';

    }


    public function login(Request $request) {
        $incomingFields = $request->validate([
            'loginemail' => [
                    'required',
                    'email'
                ],
            'loginpassword' => [
                'required',
                'min:8'
                ]
        ]);

        if (auth()->attempt([
                'email' => $incomingFields['loginemail'],
                'password' => $incomingFields['loginpassword']
        ])) {

            $request->session()->regenerate();
            return redirect('/')->with('success', 'You have successfully logged in.');
        } 
            
        return redirect('/')->with('failure', 'Invalid login.');
    }




    public function register(Request $request) {
        $incomingFields = $request->validate([
            'username' =>['required','min:3','max:20', Rule::unique('users','username')],
            'email' => ['required','email', Rule::unique('users','email')],
            'password' => ['required','min:8','confirmed']
        ]);

        //$incomingFields['password'] = bcrypt($incomingFields['password']);
        $user = User::create($incomingFields);
        auth()->login($user);

        return redirect('/')->with('success', 'Thank you for creating an account.');
    }



}
