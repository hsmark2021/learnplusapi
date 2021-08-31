<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\Flight;


class FlightController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function getFlight()
    {
        return Flight::all();
        // foreach (Flight::all() as $flight) {
        //     echo $flight->name;
        // }
    }

    public function addFlight(Request $request)
    {
        $flight = new Flight;
        $flight->name = $request->name;
        $flight->save();
    }

    public function updateFlight($id,Request $request)
    {
        $flight = Flight::find($id);
        $flight->name = $request->name;
        $flight->save();
    }

    public function deleteFlight($id)
    {

        $flight = Flight::find($id);
        $flight->delete();
    }


}
