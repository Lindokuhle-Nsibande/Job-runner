<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function index()
    {
        $jobs = DB::table('background_jobs')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('jobs.index', compact('jobs'));
    }

    public function destroy($id)
    {
        DB::table('background_jobs')->where('id', $id)->delete();
        return redirect()->route('jobs.index');
    }
}