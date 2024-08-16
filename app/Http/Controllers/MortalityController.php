<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Mortality;
use App\Models\Item;
use App\Models\Shade;
use Carbon\Carbon;


class MortalityController extends Controller
{
    public function index(Request $req){


        $data = array(
            'title'     => 'Shade Mortality',
            'shade' => Shade::latest()->get(),
            'mortality' => Mortality::with('shade')->latest()->get(),

        );
        return view('admin.mortality.add_mortality')->with($data);
    }

    public function store(Request $req){

        if(check_empty($req->flock_id)){
            $mortality = Mortality::findOrFail(hashids_decode($req->flock_id));
            $msg      = 'Shade Mortality udpated successfully';
        }else{
            $mortality = new Mortality();
            $msg      = 'Shade Mortality added successfully';
        }

        $mortality->date              = $req->date;
        $mortality->shade_id          = hashids_decode($req->shade_id);
        $mortality->quantity              = $req->quantity;
        $mortality->save();

        return response()->json([
            'success'   => $msg,
            'redirect'    => route('admin.mortalitys.index')
        ]);

    }

    public function edit($id){

        $data = array(
            'title'     => 'Edit Shade Mortality',
            'shade' => Shade::latest()->get(),
            'mortality' => Mortality::latest()->get(),
            'edit_flock' => Mortality::with(['shade'])->findOrFail(hashids_decode($id)),
            'is_update'     => true
        );
        return view('admin.mortality.add_mortality')->with($data);
    }

    public function delete($id){
        Mortality::destroy(hashids_decode($id));
        return response()->json([
            'success'   => 'Mortality deleted successfully',
            'reload'    => true
        ]);
    }

}
