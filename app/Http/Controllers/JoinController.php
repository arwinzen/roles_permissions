<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JoinController extends Controller
{
    public function create(){

        $organization = User::findOrFail(request('organization_id'));

        return view('join', [
            'organization' => $organization
        ]);
    }

    public function store(Request $request){
        // add record to intermediate table organization_user which links authenticated user and organization_id
        auth()->user()->organizations()->attach($request->input('organization_id'),
            ['role_id' => $request->input('role_id')]);

        return redirect()->route('home');
    }

    public function organization(){
        $organization = User::findOrFail(request('organization_id'));
        $role  = DB::table('organization_user')
            ->where('organization_id', $organization->id)
            ->where('user_id', auth()->user()->id)
            ->first();

        // save organization in session to display selected organization
        session([
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'organization_role_id' => $role->role_id
//            'organization_role_id' => $organization->pivot->role_id
        ]);

        return back();
    }
}
