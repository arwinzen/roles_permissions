<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
        auth()->user()->organizations()->attach($request->input('organization_id'));

        return redirect()->route('home');
    }

    public function organization(){
        $organization = User::findOrFail(request('organization_id'));

        // save organization in session to display selected organization
        session(['organization_id' => $organization->id, 'organization_name' => $organization->name]);

        return back();
    }
}
