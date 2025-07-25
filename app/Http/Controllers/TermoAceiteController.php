<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TermoAceiteController extends Controller
{
    public function show()
    {
        if (Auth::user()->termos_aceite_em) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.termo-aceite');
    }

    public function accept(Request $request)
    {
        $request->validate([
            'aceite' => 'required|accepted',
        ]);

        $user = Auth::user();
        $user->termos_aceite_em = now(); 
        $user->ip_aceite = $request->ip();
        $user->save();

        return redirect()->route('dashboard');
    }
}