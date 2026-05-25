<?php

namespace App\Http\Controllers;

use App\Models\FinishedGood;
use Illuminate\Http\Request;

class FinishedGoodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        return view('pages.finished-goods.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FinishedGood $finishedGood)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FinishedGood $finishedGood)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FinishedGood $finishedGood)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FinishedGood $finishedGood)
    {
        //
    }
}
