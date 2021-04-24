<?php

namespace App\Http\Controllers;

use App\Models\Firms;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use stdClass;

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
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'gstin' => ['required'],
            'machineId' => 'required|string'
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        $response = [];
        $gstin = $request->get("gstin");
        $machineId = $request->get("machineId", "");
        $machineName = $request->get("machineName", "");

        $existing = DB::table("firms")
            ->where(function ($query) use ($gstin, $machineId) {
                $query->where("gstin", $gstin);
            })->first();


        if ($existing) {
            $existing->machine_name = $machineName;

            DB::table("firms")->where("id", $existing->id)->update([
                "last_synced" => Carbon::now(),
                "machine_name" => $existing->machine_name
            ]);

            if ($existing->machine_id) {
                if ($existing->machine_id != $machineId) {
                    $err = new stdClass();
                    $err->machineId = ["Machine is Not Registered With given GSTIN."];
                    return Response::json($err);
                }
            } else {
                $existing->machine_id = $machineId;

                DB::table("firms")->where("id", $existing->id)->update([
                    "machine_id" => $existing->machine_id,
                    "machine_name" => $existing->machine_name
                ]);
            }

            $response = $existing;

            $startDate = new Carbon($existing->start_date);
            $endDate = new Carbon($existing->end_date);

            // ->where("start_date" ,"<=" , Carbon::now())
            // ->where("end_date" , ">=",Carbon::now());

            if ($startDate->lessThanOrEqualTo(Carbon::now()) && $endDate->greaterThanOrEqualTo(Carbon::now())) {
                $response->expired = false;
            } else {
                $response->expired = true;
            }

            $response->existing = true;
        } else {

            $firm = [];
            $firm['gstin'] = $gstin;
            $firm['machine_id'] =  $machineId;
            $firm['machine_name'] =  $machineName;
            $firm['start_date'] = Carbon::now();
            $firm['end_date'] = Carbon::now()->addDays(28);
            $firm['licence_type'] = "TRIAL";
            $firm['last_synced'] = Carbon::now();

            $lastInsertedId = DB::table("firms")->insertGetId($firm);

            $response = DB::table("firms")->find($lastInsertedId);
            $response->existing = false;
            $response->expired = false;
        }


        return Response::json($response);
    }

    public function addExtraDays(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => ['required'],
            'machineId' => 'required|string'
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        $gstin = $request->get("gstin");
        $machineId = $request->get("machineId", "");

        $existing = DB::table("firms")
            ->where(function ($query) use ($gstin, $machineId) {
                $query->where("gstin", $gstin);
            })->first();
        if (!$existing) {
            abort(404);
        }

        DB::table("firms")->where("id", $existing->id)->update([
            "last_synced" => Carbon::now()
        ]);

        if ($existing->licence_type === "TRIAL") {
            abort(404);
        }

        $startDate = new Carbon($existing->start_date);
        $endDate = new Carbon($existing->end_date);

        // ->where("start_date" ,"<=" , Carbon::now())
        // ->where("end_date" , ">=",Carbon::now());

        if ($startDate->lessThanOrEqualTo(Carbon::now()) && $endDate->greaterThanOrEqualTo(Carbon::now())) {
            abort(404);
        } else {
            $existing->end_date = Carbon::now()->addDays(15);
            $existing->licence_type  = "TRIAL";

            DB::table("firms")->where("id", $existing->id)->update((array) $existing);
        }

        return Response::json((array) $existing);
    }
}
