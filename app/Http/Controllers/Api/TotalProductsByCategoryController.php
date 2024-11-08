<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TotalProductsByCategory;
use Illuminate\Http\Request;

class TotalProductsByCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TotalProductsByCategory  $totalProductsByCategory
     * @return \Illuminate\Http\Response
     */
    public function show(TotalProductsByCategory $obj)
    {
        $getData = $obj::select('*')
        ->whereNotNull('category_id')
        ->with('Category')
        ->get();

        return response()->json(['data' => $getData], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TotalProductsByCategory  $totalProductsByCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(TotalProductsByCategory $totalProductsByCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TotalProductsByCategory  $totalProductsByCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TotalProductsByCategory $totalProductsByCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TotalProductsByCategory  $totalProductsByCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(TotalProductsByCategory $totalProductsByCategory)
    {
        //
    }
}
