<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TotalProductsByCompany;
use Illuminate\Http\Request;

class TotalProductsByCompanyController extends Controller
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
     * @param  \App\Models\TotalProductsByCompany  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(TotalProductsByCompany $obj)
    {
        $getData = $obj::select('*')
        ->with('Company')
        ->get();

        return response()->json(['data' => $getData], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TotalProductsByCompany  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(TotalProductsByCompany $obj)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TotalProductsByCompany  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TotalProductsByCompany $obj)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TotalProductsByCompany  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(TotalProductsByCompany $obj)
    {
        //
    }
}
