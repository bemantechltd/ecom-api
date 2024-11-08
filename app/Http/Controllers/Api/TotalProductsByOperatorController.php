<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TotalProductsByOperator;
use Illuminate\Http\Request;

class TotalProductsByOperatorController extends Controller
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
     * @param  \App\Models\TotalProductsByOperator  $totalProductsByOperator
     * @return \Illuminate\Http\Response
     */
    public function show(TotalProductsByOperator $obj)
    {
        $getData = $obj::select('*')
        ->with('User')
        ->orderBy('total_inserted','DESC')
        ->get();

        return response()->json(['data' => $getData], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TotalProductsByOperator  $totalProductsByOperator
     * @return \Illuminate\Http\Response
     */
    public function edit(TotalProductsByOperator $totalProductsByOperator)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TotalProductsByOperator  $totalProductsByOperator
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TotalProductsByOperator $totalProductsByOperator)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TotalProductsByOperator  $totalProductsByOperator
     * @return \Illuminate\Http\Response
     */
    public function destroy(TotalProductsByOperator $totalProductsByOperator)
    {
        //
    }
}
