<?php

namespace App\Http\Controllers\API;

use App\Helpers\MyDateTime;
use App\Http\Controllers\Controller;
use App\Models\mDetailHarga;
use App\Models\mDetailJadwal;
use App\Models\mDetailPembelian;
use App\Models\mHakKapal;
use App\Models\mKapal;
use App\Models\mMetodePembayaran;
use App\Models\mPembelian;
use Carbon\Carbon;
use Barryvdh\DomPDF\PDF;
use GuzzleHttp\Client;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TransactionManagement extends Controller
{
    public function metodePembayaran()
    {
        $metode = mMetodePembayaran::all();
        return response()->json($metode, 200);
    }

    public function imageUpload(Request $request)
    {

        $name = $request->file('image')->hashName();

        $path = $request->file('image')->store('public/images');

        $pembelian = mPembelian::where('id', $request->id_detail)->first();
        $pembelian2 = mPembelian::with('PDetailPembelian')->where('id', $request->id_detail)->first();
        $pembelian->bukti = $name;
        /*$pembelian->status = 'terkonfirmasi';

        $pdf = \PDF::loadView('ticket', compact('pembelian2'));
        $output = $pdf->output();
        $filename = time() . Str::random(5);
        $ticketname = $filename . '.pdf';
        Storage::disk('public')->put('/ticket_pdf/' . $ticketname, $output);
        $pembelian->file_tiket = $filename;*/
        $pembelian->save();

        return response()->json(['message' => 'success', 'transaction' => 'uploaded'], 200);
    }

    public function checkPDF($name)
    {
        return view('CHECK', compact('name'));
    }

    public function transactionCanceled(Request $request)
    {
        $user = Auth::user()->id;
        $data = mPembelian::find($request->id);
        $findTicket = mDetailPembelian::where('status', 'Used')->where('id_pembelian', $data->id)->get();
        if (count($findTicket) <= 0) {
            /**
             * Check status midtrans
             */
            $dataPembelian = mPembelian::with('PMetodePembayaran')->where('id', $request->id)->first();

            if (!empty($dataPembelian)) {
                $client = new Client();
                $response = $client->post(config('global.url_midtrans_base').$dataPembelian->id.'/status',
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => 'Basic '.base64_encode(config('global.server_key_midtrans')),
                            'Content-Type' => 'application/json',
                        ],
                    ]);
                $jsonResponse = json_decode($response);

                if($jsonResponse->status_code == 201){
                    switch ($jsonResponse->transaction_status){
                        case "pending":
                            $response = $client->post(config('global.url_midtrans_base').$dataPembelian->id.'/cancel',
                                [
                                    'headers' => [
                                        'Accept' => 'application/json',
                                        'Authorization' => 'Basic '.base64_encode(config('global.server_key_midtrans')),
                                        'Content-Type' => 'application/json',
                                    ],
                                ]);
                            $cancelResponse = json_decode($response);
                            if($cancelResponse->status_code != 200){
                                return response()->json(['message' => 'failed', 'transaction' => 'Status transaksi tidak dapat diperbaharui'], 200);
                            }
                            break;
                        case "capture":
                            return response()->json(['message' => 'failed', 'transaction' => 'Pembayaran telah dilakukan, tidak dapat dibatalkan'], 200);
                            break;
                        case "settlement":
                            return response()->json(['message' => 'failed', 'transaction' => 'Pembayaran telah dilakukan, tidak dapat dibatalkan'], 200);
                            break;
                        case "deny":
                            $data->status = 'dibatalkan';
                            $data->save();
                            return response()->json(['message' => 'failed', 'transaction' => 'Pembayaran telah ditolak'], 200);
                            break;
                        case "cancel":
                            $data->status = 'dibatalkan';
                            $data->save();
                            return response()->json(['message' => 'failed', 'transaction' => 'Pemesanan telah dibatalkan'], 200);
                            break;
                        case "expire":
                            $data->status = 'expired';
                            $data->save();
                            return response()->json(['message' => 'failed', 'transaction' => 'Masa pembayaran telah berakhir'], 200);
                            break;
                        case "failure":
                            $data->status = 'dibatalkan';
                            $data->save();
                            return response()->json(['message' => 'failed', 'transaction' => 'Gagal mendapatkan pembayaran, harap mengulangi pemesanan tiket kembali'], 200);
                            break;
                        default:
                            return response()->json(['message' => 'failed', 'transaction' => 'Tidak dapat menemukan transaksi'], 200);
                            break;
                    }
                } else {
                    return response()->json(['message' => 'failed', 'transaction' => 'Tidak dapat menemukan transaksi'], 200);
                }
            }

            $data->status = 'dibatalkan';
            $data->save();
            return response()->json(['message' => 'success', 'transaction' => 'canceled'], 200);
        } else {
            return response()->json(['message' => 'failed', 'transaction' => 'Tiket telah digunakan'], 200);
        }
    }

    public function transactionCommited(Request $request)
    {
        $getIDetail = mDetailHarga::with('DHHarga')->where('id', $request->id_detail)->first();
        $golongan = $getIDetail->DHHarga->HDetailGolongan->DGGolongan->golongan;

        if (!empty($getIDetail)) {
            if ($golongan == "Penumpang") {
                $pembelian = mPembelian::create([
                    'id_metode_pembayaran' => $request->id_metode_pembayaran,
                    'id_jadwal' => $request->id_detail,
                    'id_user' => Auth::user()->id,
                    'nomor_polisi' => $request->nomor_polisi,
                    'tanggal' => $request->tanggal,
                    'total_harga' => $request->jumlah_penumpang * $getIDetail->DHHarga->harga,
                    'status' => 'menunggu pembayaran',
                ]);
            } else {
                $pembelian = mPembelian::create([
                    'id_metode_pembayaran' => $request->id_metode_pembayaran,
                    'id_jadwal' => $request->id_detail,
                    'id_user' => Auth::user()->id,
                    'nomor_polisi' => $request->nomor_polisi,
                    'tanggal' => $request->tanggal,
                    'total_harga' => $getIDetail->DHHarga->harga,
                    'status' => 'menunggu pembayaran',
                ]);
            }

            $dataPembelian = mPembelian::with('PMetodePembayaran')->where('id', $pembelian->id)->first();

            if (!empty($dataPembelian)) {
                $client = new Client();
                $response = $client->post(config('global.url_midtrans'),
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => 'Basic '.base64_encode(config('global.server_key_midtrans')),
                            'Content-Type' => 'application/json',
                        ],
                        'body' => json_encode([
                            'payment_type' => 'bank_transfer',
                            'transaction_details'=> [
                                'order_id'=>$dataPembelian->id,
                                'gross_amount'=>$dataPembelian->total_harga,

                            ],
                            'bank_transfer'=>[
                                'bank'=> 'bca'
                            ]
                        ])
                    ]);
                $dataResponse = json_decode($response->getBody());
            }

            return response()->json(['message' => 'success', 'data' => $pembelian, 'midtrans_response'=>$dataResponse], 200);
        } else {
            return response()->json(['message' => 'failed', 'data' => null], 200);
        }
    }

    public function getTransactionData(Request $request)
    {
        $user = Auth::user()->id;
        $transaction = mPembelian::with('PDetailHarga', 'PMetodePembayaran')->where('id', $request->id_detail)->where('id_user', $user)->first();
        $day = MyDateTime::DateToDayConverter($transaction->PDetailHarga->DHJadwal->tanggal);
        $transaction->nama_asal = $transaction->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->nama_pelabuhan;
        $transaction->nama_tujuan = $transaction->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->nama_pelabuhan;
        $transaction->kode_pelabuhan_asal = $transaction->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->kode_pelabuhan;
        $transaction->kode_pelabuhan_tujuan = $transaction->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->kode_pelabuhan;
        $transaction->status_asal = $transaction->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->status;
        $transaction->status_tujuan = $transaction->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->status;
        $transaction->dermaga_asal = $transaction->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->nama_dermaga;
        $transaction->dermaga_tujuan = $transaction->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->nama_dermaga;
        $transaction->estimasi_waktu = $transaction->PDetailHarga->DHJadwal->estimasi_waktu . ' Menit';
        $transaction->tanggal = $transaction->PDetailHarga->DHJadwal->tanggal;
        $transaction->metode_pembayaran = $transaction->PMetodePembayaran->nama_metode;
        $transaction->nomor_rekening = $transaction->PMetodePembayaran->nomor_rekening;
        $transaction->hari = $day;
        $transaction->harga = $transaction->total_harga;
        $transaction->nama_kapal = $transaction->PDetailHarga->DHJadwal->DJKapal->nama_kapal;
        $time = Carbon::createFromFormat("H:i:s", $transaction->PDetailHarga->DHJadwal->DJJadwalAsal->waktu);
        $transaction->waktu_berangkat_asal = $time->format('H:i');
        $time->addMinutes($transaction->PDetailHarga->DHJadwal->estimasi_waktu);
        $transaction->waktu_berangkat_tujuan = $time->format('H:i');
        return response()->json($transaction, 200);
    }

    public function transactionCommitedForPenumpang(Request $request)
    {

        $maxPembelian = mDetailPembelian::max('kode_tiket');
        $detailPembelian = mDetailPembelian::create([
            'id_pembelian' => $request->id_detail_pemesanan,
            'no_id_card' => $request->telepon,
            'kode_tiket' => $maxPembelian + 1,
            'nama_pemegang_tiket' => $request->nama_pemegang_tiket,
            'status' => 'Not Used',
        ]);

        return response()->json(['message' => 'success', 'data' => $detailPembelian], 200);
    }

    public function getTransactionRecently()
    {
        $user = Auth::user()->id;
        $transaction = mPembelian::with('PDetailHarga', 'PMetodePembayaran')->where('id_user', $user)->orderBy("id", "desc")->get();
        foreach ($transaction as $index => $data) {
            $day = MyDateTime::DateToDayConverter($data->PDetailHarga->DHJadwal->tanggal);
            $transaction[$index]->nama_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->nama_pelabuhan;
            $transaction[$index]->nama_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->nama_pelabuhan;
            $transaction[$index]->kode_pelabuhan_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->kode_pelabuhan;
            $transaction[$index]->kode_pelabuhan_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->kode_pelabuhan;
            $transaction[$index]->status_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->status;
            $transaction[$index]->status_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->status;
            $transaction[$index]->dermaga_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->nama_dermaga;
            $transaction[$index]->dermaga_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->nama_dermaga;
            $transaction[$index]->estimasi_waktu = $data->PDetailHarga->DHJadwal->estimasi_waktu . ' Menit';
            $transaction[$index]->tanggal = $data->PDetailHarga->DHJadwal->tanggal;
            $transaction[$index]->status = $data->status;
            $transaction[$index]->metode_pembayaran = $data->PMetodePembayaran->nama_metode;
            $transaction[$index]->nomor_rekening = $data->PMetodePembayaran->nomor_rekening;
            $transaction[$index]->hari = $day;
            $transaction[$index]->harga = $data->total_harga;
            $transaction[$index]->nama_kapal = $data->PDetailHarga->DHJadwal->DJKapal->nama_kapal;
            $time = Carbon::createFromFormat("H:i:s", $data->PDetailHarga->DHJadwal->DJJadwalAsal->waktu);
            $transaction[$index]->waktu_berangkat_asal = $time->format('H:i');
            $time->addMinutes($data->PDetailHarga->DHJadwal->estimasi_waktu);
            $transaction[$index]->waktu_berangkat_tujuan = $time->format('H:i');
        }
        return response()->json($transaction, 200);
    }

    public function getTransactionHistory()
    {
        $user = Auth::user()->id;
        $transaction = mPembelian::with('PDetailHarga', 'PMetodePembayaran')->where('id_user', $user)->where('status', '!=', 'menunggu pembayaran')->get();
        foreach ($transaction as $index => $data) {
            $day = MyDateTime::DateToDayConverter($data->PDetailHarga->DHJadwal->tanggal);
            $transaction[$index]->nama_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->nama_pelabuhan;
            $transaction[$index]->nama_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->nama_pelabuhan;
            $transaction[$index]->kode_pelabuhan_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->kode_pelabuhan;
            $transaction[$index]->kode_pelabuhan_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->kode_pelabuhan;
            $transaction[$index]->status_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->status;
            $transaction[$index]->status_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->status;
            $transaction[$index]->dermaga_asal = $data->PDetailHarga->DHJadwal->DJJadwalAsal->JDermaga->nama_dermaga;
            $transaction[$index]->dermaga_tujuan = $data->PDetailHarga->DHJadwal->DJJadwalTujuan->JDermaga->nama_dermaga;
            $transaction[$index]->estimasi_waktu = $data->PDetailHarga->DHJadwal->estimasi_waktu . ' Menit';
            $transaction[$index]->tanggal = $data->PDetailHarga->DHJadwal->tanggal;
            $transaction[$index]->metode_pembayaran = $data->PMetodePembayaran->nama_metode;
            $transaction[$index]->nomor_rekening = $data->PMetodePembayaran->nomor_rekening;
            $transaction[$index]->hari = $day;
            $transaction[$index]->harga = $data->total_harga;
            $transaction[$index]->nama_kapal = $data->PDetailHarga->DHJadwal->DJKapal->nama_kapal;
            $time = Carbon::createFromFormat("H:i:s", $data->PDetailHarga->DHJadwal->DJJadwalAsal->waktu);
            $transaction[$index]->waktu_berangkat_asal = $time->format('H:i');
            $time->addMinutes($data->PDetailHarga->DHJadwal->estimasi_waktu);
            $transaction[$index]->waktu_berangkat_tujuan = $time->format('H:i');
        }

        return response()->json($transaction, 200);
    }

    public function getPenumpang(Request $request)
    {
        $user = Auth::user()->id;
        $transaction = mDetailPembelian::where('id_pembelian', $request->id)->get();
        return response()->json($transaction, 200);
    }

    public function checkTicket(Request $request)
    {
        $ticket_number = $request->ticket_number;

        $userID = Auth::user()->id;
        $idKapal = mHakKapal::where('id_user', $userID)->pluck('id_kapal');
        $dataJadwal = mDetailJadwal::whereIn('id_kapal', $idKapal)->pluck('id');
        $dataDetailHarga = mDetailHarga::whereIn('id_detail_jadwal', $dataJadwal)->pluck('id');
        $pembelianData = mPembelian::whereIn('id_jadwal', $dataDetailHarga)->where('tanggal', $request->tanggal)->pluck('id');
        $data = mDetailPembelian::where('kode_tiket', $ticket_number)->whereIn('id_pembelian', $pembelianData)->where('status', 'Not Used')->first();
        if (!empty($data)) {
            $data->status = "Used";
            $data->save();
            $data = mDetailPembelian::where('kode_tiket', $ticket_number)->whereIn('id_pembelian', $pembelianData)->where('status', 'Used')->first();
            return response()->json(["message" => 'success', 'data' => $data], 200);
        } else {
            return response()->json(["message" => "not found", 'data' => null], 200);
        }
    }

    public function getTicketData(Request $request)
    {
        $ticket_number = $request->ticket_number;
        $data = mDetailPembelian::with('DPPembelian')->where('kode_tiket', $ticket_number)->first();
        if (!empty($data)) {
            $data->tanggal = $data->DPPembelian->tanggal;
            return response()->json(['message' => 'success', 'data' => $data], 200);
        } else {
            return response()->json(['message' => 'not found', 'data' => null], 200);
        }
    }

    public function checkStatusMidtrans(Request $request){
        $dataPembelian = mPembelian::with('PMetodePembayaran')->where('id', $request->id)->first();

        if (!empty($dataPembelian)) {
            $client = new Client();
            $response = $client->post(config('global.url_midtrans_base').$dataPembelian->id.'/status',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Basic '.base64_encode(config('global.server_key_midtrans')),
                        'Content-Type' => 'application/json',
                    ],
                ]);
            return response()->json(['message'=>'success','data'=>json_decode($response)],200);
        }
        return response()->json(['message'=>'not found','data'=>''], 404);
    }
}
