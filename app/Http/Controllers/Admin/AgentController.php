<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Property;
use App\Feature;
use App\Comment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Hash;
use Toastr;


class AgentController extends Controller
{
    public function index()
    {
        $Agents=User::where('role_id', 2)->get();
        return view('admin.agents.index',compact('Agents'));
    }

    public function destroy(User $Agent)
    {
        $Agent = User::find($Agent->id);
        $Agent->delete();
        
        Toastr::success('message', 'Agent deleted successfully.');
        return back();
    }
    public function create()
    {
        return view('admin.agents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required',
            'username'  => 'required',
            'Email'     => 'required|email',
            'image'     => 'image|mimes:jpeg,jpg,png',
            'about'     => 'max:250'
        ]);

        $Agent=new User();
        $image = $request->file('image');
        $slug  = str_slug($request->name);

        if(isset($image)){
            $currentDate = Carbon::now()->toDateString();
            $imagename = $slug.'-agent-'.$currentDate.'.'.$image->getClientOriginalExtension();

            if(!Storage::disk('public')->exists('users')){
                Storage::disk('public')->makeDirectory('users');
            }
            if(Storage::disk('public')->exists('users/'.$Agent->image) && $Agent->image != 'default.png' ){
                Storage::disk('public')->delete('users/'.$Agent->image);
            }
            $userimage = Image::make($image)->stream();
            Storage::disk('public')->put('users/'.$imagename, $userimage);
        }

        $Agent->name = $request->name;
        $Agent->role_id=2;
        $Agent->username = $request->username;
        $Agent->Email = $request->Email;
        $Agent->image = $imagename;
        $Agent->about = $request->about;
        $Agent->password = bcrypt('123456');

        $Agent->save();
        return redirect()->route('admin.agents.index');
    }
}
