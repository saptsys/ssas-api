<?php

namespace App\Http\Controllers;

use App\Models\Firms;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FirmsController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {

  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function create()
  {

  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store(Request $request){

    $validator = Validator::make($request->all(), [
        'gstin' => ['required','string','regex:/^(0[1-9]|[1-2][0-9]|3[0-5])([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}([a-zA-Z0-9]){1}([a-zA-Z]){1}([0-9]){1}?$/'],
        // 'machineId' => 'required|string'
    ]);

    if($validator->fails()){
        return Response::json($validator->errors(),400);
    }

    $response = [];
    $gstin = $request->get("gstin");
    $machineId = $request->get("machineId","");

    $existing = DB::table("firms")
                ->where(function ($query) use ($gstin , $machineId) {
                    $query->where("gstin" , $gstin)
                    ->orWhere("machine_id" , $machineId);
                })->first();


    if($existing){
        $response = $existing;

        $startDate = new Carbon($existing->start_date);
        $endDate = new Carbon($existing->end_date);

        // ->where("start_date" ,"<=" , Carbon::now())
        // ->where("end_date" , ">=",Carbon::now());

        if($startDate->lessThanOrEqualTo(Carbon::now()) && $endDate->greaterThanOrEqualTo(Carbon::now())){
            $response->expired = false;
        }else{
            $response->expired = true;
        }

        $response->existing = true;
    }else{

        $firm = [];
        $firm['gstin'] = $gstin;
        $firm['machine_id'] = Str::uuid()->toString();
        $firm['start_date'] = Carbon::now();
        $firm['end_date'] = Carbon::now()->addDays(28);
        $firm['licence_type'] = "TRIAL";


        $lastInsertedId = DB::table("firms")->insertGetId($firm);

        $response = DB::table("firms")->find($lastInsertedId);
        $response->existing = false;
        $response->expired = false;

    }


    return Response::json($response);

  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($id)
  {

  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function edit($id)
  {

  }

  /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function update($id)
  {

  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy($id)
  {

  }

}


