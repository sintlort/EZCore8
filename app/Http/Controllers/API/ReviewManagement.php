<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\mReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewManagement extends Controller
{
    public function getReview(Request $request)
    {
        $data = mReview::where('id_pembelian', $request->id_pembelian)->first();
        if (empty($data)) {
            $dataReview = mReview::create([
                'id_user'=>Auth::user()->id,
                'id_pembelian' => $request->id_pembelian,
                'review' => '',
                'score' => 0,
            ]);
            return response()->json(['message' => 'success', 'review' => $dataReview], 200);
        } else {
            return response()->json(['message' => 'success', 'review' => $data], 200);
        }
    }

    public function postReview(Request $request)
    {
        $data = mReview::where('id_pembelian', $request->id_pembelian)->first();
        if (empty($data)) {
            if($request->isi_review != '' && $request->score != 0){
                $dataReview = mReview::create([
                    'id_user'=>Auth::user()->id,
                    'id_pembelian' => $request->id_pembelian,
                    'review' => $request->isi_review,
                    'score' => $request->score,
                ]);
                return response()->json(['message' => 'success', 'review' => $dataReview], 200);
            } else {
                return response()->json(['message' => 'failed', 'review' => ''], 400);
            }
        } else {
            if($request->isi_review != '' && $request->score != 0){
                $data->review = $request->isi_review;
                $data->score = $request->score;
                $data->save();
                return response()->json(['message' => 'success', 'review' => $data], 200);
            } else {
                return response()->json(['message' => 'failed', 'review' => ''], 400);
            }
        }
    }

    public function hapusReview(Request $request){
        $data = mReview::where('id_pembelian', $request->id_pembelian)->first();
        if(!empty($data)){
            $data->delete();
            return response()->json(['message'=>'success'],200);
        } else {
            return response()->json(['message'=>'data not found'], 200);
        }
    }
}
