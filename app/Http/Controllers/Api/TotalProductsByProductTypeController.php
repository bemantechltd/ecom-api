<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TotalProductsByProductType;
use Illuminate\Http\Request;

class TotalProductsByProductTypeController extends Controller
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
     * @param  \App\Models\TotalProductsByProductType  $totalProductsByProductType
     * @return \Illuminate\Http\Response
     */
    public function show(TotalProductsByProductType $obj)
    {
        $getData = $obj::select('*')
        ->with('ProductType')
        ->get();

        return response()->json(['data' => $getData], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TotalProductsByProductType  $totalProductsByProductType
     * @return \Illuminate\Http\Response
     */
    public function edit(TotalProductsByProductType $totalProductsByProductType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TotalProductsByProductType  $totalProductsByProductType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TotalProductsByProductType $totalProductsByProductType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TotalProductsByProductType  $totalProductsByProductType
     * @return \Illuminate\Http\Response
     */
    public function destroy(TotalProductsByProductType $totalProductsByProductType)
    {
        //
    }
}
