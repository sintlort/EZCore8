<?php

namespace App\Http\Controllers\API;

use App\Helpers\MyDateTime;
use App\Http\Controllers\Controller;
use App\Models\mDermaga;
use App\Models\mDetailGolongan;
use App\Models\mDetailHarga;
use App\Models\mDetailJadwal;
use App\Models\mGolongan;
use App\Models\mHakKapal;
use App\Models\mHarga;
use App\Models\mJadwal;
use App\Models\mKapal;
use App\Models\mPelabuhan;
use App\Models\mPembelian;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ScheduleManagement extends Controller
{
    public function indexPelabuhan()
    {
        $pelabuhan = mPelabuhan::all();
        return response()->json($pelabuhan, 200);
    }

    public function indexGolongan(Request $request)
    {
        $golongan = mGolongan::get();
        return response()->json($golongan, 200);
    }

    public function indexGolonganSpeedboat(Request $request)
    {
        $golongan = mGolongan::whereIn('id', ['1', '2'])->get();
        return response()->json($golongan, 200);
    }

    public function searchSchedule(Request $request)
    {
        if ($request->golongan != "") {
            $detailGolongan = mDetailGolongan::where('id_golongan', $request->golongan)->pluck('id_kapal');
            $kapal = mKapal::whereIn('id', $detailGolongan)->where('tipe_kapal', 'feri')->pluck('id');
            $jadwal = mJadwal::whereIn('id_kapal', $kapal)->where('id_asal_pelabuhan', $request->asal_pelabuhan)->where('id_tujuan_pelabuhan', $request->tujuan_pelabuhan)->pluck('id');
            $day = MyDateTime::DateToDayConverter($request->date);
            $schedule = mDetailJadwal::with('DJJadwal')->whereIn('id_jadwal', $jadwal)->where('hari', strtolower($day))->where('status', 'aktif')->get();
            return response()->json($schedule, 200);
        } else {
            if ($request->tipe_kapal == 'feri') {
                $kapal = mKapal::where('tipe_kapal', 'feri')->pluck('id');
                $jadwal = mJadwal::whereIn('id_kapal', $kapal)->where('id_asal_pelabuhan', $request->asal_pelabuhan)
                    ->where('id_tujuan_pelabuhan', $request->tujuan_pelabuhan)->pluck('id');
                $day = MyDateTime::DateToDayConverter($request->date);
                $schedule = mDetailJadwal::with('DJJadwal', 'DJDermagaAsal', 'DJDermagaTujuan')->whereIn('id_jadwal', $jadwal)->where('hari', strtolower($day))->where('status', 'aktif')->get();
                foreach ($schedule as $index => $data) {
                    $schedule[$index]->nama_asal = $data->DJJadwal->JPelabuhanAsal->nama_pelabuhan;
                    $schedule[$index]->nama_tujuan = $data->DJJadwal->JPelabuhanTujuan->nama_pelabuhan;
                    $schedule[$index]->kode_pelabuhan_asal = $data->DJJadwal->JPelabuhanAsal->kode_pelabuhan;
                    $schedule[$index]->kode_pelabuhan_tujuan = $data->DJJadwal->JPelabuhanTujuan->kode_pelabuhan;
                    $schedule[$index]->status_asal = $data->DJJadwal->JPelabuhanAsal->status;
                    $schedule[$index]->status_tujuan = $data->DJJadwal->JPelabuhanTujuan->status;
                    $schedule[$index]->dermaga_asal = $data->DJDermagaAsal->nama_dermaga;
                    $schedule[$index]->dermaga_tujuan = $data->DJDermagaTujuan->nama_dermaga;
                    $schedule[$index]->estimasi_waktu = $data->DJJadwal->estimasi_waktu . ' Menit';
                    $schedule[$index]->tanggal = $request->date;
                    $schedule[$index]->hari = $day;
                    $schedule[$index]->harga = $data->DJJadwal->harga;
                    $schedule[$index]->nama_kapal = $data->DJJadwal->JKapal->nama_kapal;
                    $time = Carbon::createFromFormat("H:i:s", $data->DJJadwal->waktu_berangkat);
                    $schedule[$index]->waktu_berangkat_asal = $time->format('H:i');
                    $time = Carbon::createFromFormat("H:i:s", $data->DJJadwal->waktu_berangkat);
                    $time->addMinutes($data->DJJadwal->estimasi_waktu);
                    $schedule[$index]->waktu_berangkat_tujuan = $time->format('H:i');
                }
                return response()->json($schedule, 200);
            } else {
                $kapal = mKapal::where('tipe_kapal', 'speedboat')->pluck('id');
                $jadwal = mJadwal::whereIn('id_kapal', $kapal)->where('id_asal_pelabuhan', $request->asal_pelabuhan)
                    ->where('id_tujuan_pelabuhan', $request->tujuan_pelabuhan)->pluck('id');
                $jadwal = mJadwal::where('id_asal_pelabuhan', $request->asal_pelabuhan)
                    ->where('id_tujuan_pelabuhan', $request->tujuan_pelabuhan)->pluck('id');
                $day = MyDateTime::DateToDayConverter($request->date);
                $schedule = mDetailJadwal::with('DJJadwal', 'DJDermagaAsal', 'DJDermagaTujuan')->whereIn('id_jadwal', $jadwal)->where('hari', strtolower($day))->where('status', 'aktif')->get();
                foreach ($schedule as $index => $data) {
                    $schedule[$index]->nama_asal = $data->DJJadwal->JPelabuhanAsal->nama_pelabuhan;
                    $schedule[$index]->nama_tujuan = $data->DJJadwal->JPelabuhanTujuan->nama_pelabuhan;
                    $schedule[$index]->kode_pelabuhan_asal = $data->DJJadwal->JPelabuhanAsal->kode_pelabuhan;
                    $schedule[$index]->kode_pelabuhan_tujuan = $data->DJJadwal->JPelabuhanTujuan->kode_pelabuhan;
                    $schedule[$index]->status_asal = $data->DJJadwal->JPelabuhanAsal->status;
                    $schedule[$index]->status_tujuan = $data->DJJadwal->JPelabuhanTujuan->status;
                    $schedule[$index]->dermaga_asal = $data->DJDermagaAsal->nama_dermaga;
                    $schedule[$index]->dermaga_tujuan = $data->DJDermagaTujuan->nama_dermaga;
                    $schedule[$index]->estimasi_waktu = $data->DJJadwal->estimasi_waktu . ' Menit';
                    $schedule[$index]->tanggal = $request->date;
                    $schedule[$index]->hari = $day;
                    $schedule[$index]->harga = $data->DJJadwal->harga;
                    $schedule[$index]->nama_kapal = $data->DJJadwal->JKapal->nama_kapal;
                    $time = Carbon::createFromFormat("H:i:s", $data->DJJadwal->waktu_berangkat);
                    $schedule[$index]->waktu_berangkat_asal = $time->format('H:i');
                    $time = Carbon::createFromFormat("H:i:s", $data->DJJadwal->waktu_berangkat);
                    $time->addMinutes($data->DJJadwal->estimasi_waktu);
                    $schedule[$index]->waktu_berangkat_tujuan = $time->format('H:i');
                }
                return response()->json($schedule, 200);
            }
        }
    }

    public function searchTestv1(Request $request)
    {

        $idP1 = $request->asal_pelabuhan;
        $idP2 = $request->tujuan_pelabuhan;
        $idDP1 = mDermaga::where('id_pelabuhan', $idP1)->pluck('id');
        $idDP2 = mDermaga::where('id_pelabuhan', $idP2)->pluck('id');
        $idJadwal1 = mJadwal::whereIn('id_dermaga', $idDP1)->pluck('id');
        $idJadwal2 = mJadwal::whereIn('id_dermaga', $idDP2)->pluck('id');
        $tanggal = $request->date;
        $dataGolongan = $request->id_golongan;
        $type = $request->tipe_kapal;

        if ($type == 'feri') {
            $golongan = mGolongan::find($dataGolongan);
            $detailGolongan = mDetailGolongan::where('id_golongan', $golongan->id)->pluck('id');
            $harga = mHarga::where('id_pelabuhan_asal', $idP1)->where('id_pelabuhan_tujuan', $idP2)->whereIn('id_detail_golongan', $detailGolongan)->pluck('id');
            $data1 = mDetailJadwal::with('DJJadwalAsal', 'DJJadwalTujuan')->whereIn('id_jadwal_asal', $idJadwal1)->whereIn('id_jadwal_tujuan', $idJadwal2)->where('tanggal', $tanggal)->pluck('id');
            $idDetailHarga = mDetailHarga::whereIn('id_harga', $harga)->whereIn('id_detail_jadwal', $data1)->with('DHHarga', 'DHJadwal')->get();
            $detail = mDetailHarga::whereIn('id_harga', $harga)->whereIn('id_detail_jadwal', $data1)->with('DHHarga', 'DHJadwal')->get();
            $schedule = $detail;
            foreach ($schedule as $index => $data) {
                $terbayarkan = 0;
                if ($dataGolongan <= 2) {
                    $dataPembelian = mPembelian::where('id_jadwal', $data->id)->where('status', 'terkonfirmasi')->with('PDetailPembelian')->get();
                    foreach ($dataPembelian as $item) {
                        $terbayarkan = $terbayarkan + count($item->PDetailPembelian);
                    }
                } else {
                    $terbayarkan = mPembelian::where('id_jadwal', $data->id)->where('status', 'terkonfirmasi')->count();
                }
                $day = MyDateTime::DateToDayConverter($data->DHJadwal->tanggal);
                $schedule[$index]->nama_asal = $data->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->nama_pelabuhan;
                $schedule[$index]->nama_tujuan = $data->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->nama_pelabuhan;
                $schedule[$index]->kode_pelabuhan_asal = $data->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->kode_pelabuhan;
                $schedule[$index]->kode_pelabuhan_tujuan = $data->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->kode_pelabuhan;
                $schedule[$index]->status_asal = $data->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->status;
                $schedule[$index]->status_tujuan = $data->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->status;
                $schedule[$index]->dermaga_asal = $data->DHJadwal->DJJadwalAsal->JDermaga->nama_dermaga;
                $schedule[$index]->dermaga_tujuan = $data->DHJadwal->DJJadwalTujuan->JDermaga->nama_dermaga;
                $schedule[$index]->estimasi_waktu = $data->DHJadwal->estimasi_waktu . ' Menit';
                $schedule[$index]->tanggal = $data->DHJadwal->tanggal;
                $schedule[$index]->hari = $day;
                $schedule[$index]->harga = $data->DHHarga->harga;
                $schedule[$index]->nama_kapal = $data->DHJadwal->DJKapal->nama_kapal;
                $schedule[$index]->terbayarkan = $terbayarkan;
                $schedule[$index]->jumlah_tiket = $data->DHHarga->HDetailGolongan->jumlah;
                $time = Carbon::createFromFormat("H:i:s", $data->DHJadwal->DJJadwalAsal->waktu);
                $schedule[$index]->waktu_berangkat_asal = $time->format('H:i');
                $time->addMinutes($data->DHJadwal->estimasi_waktu);
                $schedule[$index]->waktu_berangkat_tujuan = $time->format('H:i');
            }
        } else {
            $golongan = mGolongan::find($dataGolongan);
            $detailGolongan = mDetailGolongan::where('id_golongan', $golongan->id)->pluck('id');
            $harga = mHarga::where('id_pelabuhan_asal', $idP1)->where('id_pelabuhan_tujuan', $idP2)->whereIn('id_detail_golongan', $detailGolongan)->pluck('id');
            $tipeKapal = mKapal::where('tipe_kapal', '!=', 'feri')->pluck('id');
            $data1 = mDetailJadwal::with('DJJadwalAsal', 'DJJadwalTujuan')->whereIn('id_kapal', $tipeKapal)->whereIn('id_jadwal_asal', $idJadwal1)->whereIn('id_jadwal_tujuan', $idJadwal2)->where('tanggal', $tanggal)->pluck('id');
            $idDetailHarga = mDetailHarga::whereIn('id_harga', $harga)->whereIn('id_detail_jadwal', $data1)->with('DHHarga', 'DHJadwal')->get();
            $detail = mDetailHarga::whereIn('id_harga', $harga)->whereIn('id_detail_jadwal', $data1)->with('DHHarga', 'DHJadwal')->get();
            $schedule = $detail;
            foreach ($schedule as $index => $data) {
                $terbayarkan = 0;
                if ($dataGolongan <= 2) {
                    $dataPembelian = mPembelian::where('id_jadwal', $data->id)->where('status', 'terkonfirmasi')->with('PDetailPembelian')->get();
                    foreach ($dataPembelian as $item) {
                        $terbayarkan = $terbayarkan + count($item->PDetailPembelian);
                    }
                } else {
                    $terbayarkan = mPembelian::where('id_jadwal', $data->id)->where('status', 'terkonfirmasi')->count();
                }
                $day = MyDateTime::DateToDayConverter($data->DHJadwal->tanggal);
                $schedule[$index]->nama_asal = $data->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->nama_pelabuhan;
                $schedule[$index]->nama_tujuan = $data->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->nama_pelabuhan;
                $schedule[$index]->kode_pelabuhan_asal = $data->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->kode_pelabuhan;
                $schedule[$index]->kode_pelabuhan_tujuan = $data->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->kode_pelabuhan;
                $schedule[$index]->status_asal = $data->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->status;
                $schedule[$index]->status_tujuan = $data->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->status;
                $schedule[$index]->dermaga_asal = $data->DHJadwal->DJJadwalAsal->JDermaga->nama_dermaga;
                $schedule[$index]->dermaga_tujuan = $data->DHJadwal->DJJadwalTujuan->JDermaga->nama_dermaga;
                $schedule[$index]->estimasi_waktu = $data->DHJadwal->estimasi_waktu . ' Menit';
                $schedule[$index]->tanggal = $data->DHJadwal->tanggal;
                $schedule[$index]->hari = $day;
                $schedule[$index]->harga = $data->DHHarga->harga;
                $schedule[$index]->nama_kapal = $data->DHJadwal->DJKapal->nama_kapal;
                $schedule[$index]->terbayarkan = $terbayarkan;
                $schedule[$index]->jumlah_tiket = $data->DHHarga->HDetailGolongan->jumlah;
                $time = Carbon::createFromFormat("H:i:s", $data->DHJadwal->DJJadwalAsal->waktu);
                $schedule[$index]->waktu_berangkat_asal = $time->format('H:i');
                $time->addMinutes($data->DHJadwal->estimasi_waktu);
                $schedule[$index]->waktu_berangkat_tujuan = $time->format('H:i');
            }
        }
        return response()->json($schedule, 200);
    }

    public function searchRevisi(Request $request)
    {

        $idP1 = $request->asal_pelabuhan;
        $idP2 = $request->tujuan_pelabuhan;
        $idDP1 = mDermaga::where('id_pelabuhan', $idP1)->pluck('id');
        $idDP2 = mDermaga::where('id_pelabuhan', $idP2)->pluck('id');
        $idJadwal1 = mJadwal::whereIn('id_dermaga', $idDP1)->pluck('id');
        $idJadwal2 = mJadwal::whereIn('id_dermaga', $idDP2)->pluck('id');
        $tanggal = $request->date;
        $dataGolongan = $request->id_golongan;
        $type = $request->tipe_kapal;

        if ($type == 'feri') {
            $schedule = mDetailHarga::with('DHHarga', 'DHJadwal')
                ->whereHas('DHHarga', function ($DHHarga) use ($dataGolongan) {
                    $DHHarga->whereHas('HDetailGolongan', function ($HDetailGolongan) use ($dataGolongan) {
                        $HDetailGolongan->whereHas('DHGolongan', function ($DHGolongan) use ($dataGolongan) {
                            $DHGolongan->where('id', $dataGolongan);
                        });
                    });
                })
                ->whereHas('DHJadwal', function ($DHJadwal) use ($idJadwal1) {
                    $DHJadwal->whereHas('DJJadwalAsal', function ($DJJadwalAsal) use ($idJadwal1) {
                        $DJJadwalAsal->whereHas('JDermaga', function ($JDermaga) use ($idJadwal1) {
                            $JDermaga->where('id_pelabuhan', $idJadwal1);
                        });
                    });
                })
                ->whereHas('DHJadwal', function ($DHJadwal) use ($idJadwal1) {
                    $DHJadwal->whereHas('DJJadwalTujuan', function ($DJJadwalTujuan) use ($idJadwal1) {
                        $DJJadwalTujuan->whereHas('JDermaga', function ($JDermaga) use ($idJadwal1) {
                            $JDermaga->where('id_pelabuhan', $idJadwal1);
                        });
                    });
                });
            if(count($schedule)>=1){
                foreach ($schedule as $index => $data){
                    $ticketCount = count($schedule->skip($index)->first()->DHPembelian->PDetailPembelian);
                    $ticketTotal = $schedule->skip($index)->first()->DHHarga->HDetailGolongan->jumlah;
                    $results = $ticketTotal - $ticketCount;
                    if($results > 0){
                        $schedule->skip($index)->first();
                        $day = MyDateTime::DateToDayConverter($schedule->DHJadwal->tanggal);
                        $schedule->nama_asal = $schedule->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->nama_pelabuhan;
                        $schedule->nama_tujuan = $schedule->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->nama_pelabuhan;
                        $schedule->kode_pelabuhan_asal = $schedule->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->kode_pelabuhan;
                        $schedule->kode_pelabuhan_tujuan = $schedule->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->kode_pelabuhan;
                        $schedule->status_asal = $schedule->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->status;
                        $schedule->status_tujuan = $schedule->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->status;
                        $schedule->dermaga_asal = $schedule->DHJadwal->DJJadwalAsal->JDermaga->nama_dermaga;
                        $schedule->dermaga_tujuan = $schedule->DHJadwal->DJJadwalTujuan->JDermaga->nama_dermaga;
                        $schedule->estimasi_waktu = $schedule->DHJadwal->estimasi_waktu . ' Menit';
                        $schedule->tanggal = $schedule->DHJadwal->tanggal;
                        $schedule->hari = $day;
                        $schedule->harga = $schedule->DHHarga->harga;
                        $schedule->nama_kapal = $schedule->DHJadwal->DJKapal->nama_kapal;
                        $time = Carbon::createFromFormat("H:i:s", $schedule->DHJadwal->DJJadwalAsal->waktu);
                        $schedule->waktu_berangkat_asal = $time->format('H:i');
                        $time->addMinutes($schedule->DHJadwal->estimasi_waktu);
                        $schedule->waktu_berangkat_tujuan = $time->format('H:i');
                        break;
                    }
                }
            }
        } else {
            $golongan = mGolongan::find($dataGolongan);
            $detailGolongan = mDetailGolongan::where('id_golongan', $golongan->id)->pluck('id');
            $harga = mHarga::where('id_pelabuhan_asal', $idP1)->where('id_pelabuhan_tujuan', $idP2)->whereIn('id_detail_golongan', $detailGolongan)->pluck('id');
            $tipeKapal = mKapal::where('tipe_kapal', '!=', 'feri')->pluck('id');
            $data1 = mDetailJadwal::with('DJJadwalAsal', 'DJJadwalTujuan')->whereIn('id_kapal', $tipeKapal)->whereIn('id_jadwal_asal', $idJadwal1)->whereIn('id_jadwal_tujuan', $idJadwal2)->where('tanggal', $tanggal)->pluck('id');
            $idDetailHarga = mDetailHarga::whereIn('id_harga', $harga)->whereIn('id_detail_jadwal', $data1)->with('DHHarga', 'DHJadwal')->get();
            $detail = mDetailHarga::whereIn('id_harga', $harga)->whereIn('id_detail_jadwal', $data1)->with('DHHarga', 'DHJadwal')->get();
            $schedule = $detail;
            foreach ($schedule as $index => $data) {
                $terbayarkan = 0;
                if ($dataGolongan <= 2) {
                    $dataPembelian = mPembelian::where('id_jadwal', $data->id)->where('status', 'terkonfirmasi')->with('PDetailPembelian')->get();
                    foreach ($dataPembelian as $item) {
                        $terbayarkan = $terbayarkan + count($item->PDetailPembelian);
                    }
                } else {
                    $terbayarkan = mPembelian::where('id_jadwal', $data->id)->where('status', 'terkonfirmasi')->count();
                }
                $day = MyDateTime::DateToDayConverter($data->DHJadwal->tanggal);
                $schedule[$index]->nama_asal = $data->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->nama_pelabuhan;
                $schedule[$index]->nama_tujuan = $data->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->nama_pelabuhan;
                $schedule[$index]->kode_pelabuhan_asal = $data->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->kode_pelabuhan;
                $schedule[$index]->kode_pelabuhan_tujuan = $data->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->kode_pelabuhan;
                $schedule[$index]->status_asal = $data->DHJadwal->DJJadwalAsal->JDermaga->DPelabuhan->status;
                $schedule[$index]->status_tujuan = $data->DHJadwal->DJJadwalTujuan->JDermaga->DPelabuhan->status;
                $schedule[$index]->dermaga_asal = $data->DHJadwal->DJJadwalAsal->JDermaga->nama_dermaga;
                $schedule[$index]->dermaga_tujuan = $data->DHJadwal->DJJadwalTujuan->JDermaga->nama_dermaga;
                $schedule[$index]->estimasi_waktu = $data->DHJadwal->estimasi_waktu . ' Menit';
                $schedule[$index]->tanggal = $data->DHJadwal->tanggal;
                $schedule[$index]->hari = $day;
                $schedule[$index]->harga = $data->DHHarga->harga;
                $schedule[$index]->nama_kapal = $data->DHJadwal->DJKapal->nama_kapal;
                $schedule[$index]->terbayarkan = $terbayarkan;
                $schedule[$index]->jumlah_tiket = $data->DHHarga->HDetailGolongan->jumlah;
                $time = Carbon::createFromFormat("H:i:s", $data->DHJadwal->DJJadwalAsal->waktu);
                $schedule[$index]->waktu_berangkat_asal = $time->format('H:i');
                $time->addMinutes($data->DHJadwal->estimasi_waktu);
                $schedule[$index]->waktu_berangkat_tujuan = $time->format('H:i');
            }
        }
        return response()->json($schedule, 200);
    }

    public function getKapal(Request $request)
    {
        $user = Auth::id();
        $getIdKapal = mHakKapal::where('id_user', $user)->where('hak_akses', "TAdmin")->first();
        $getKapal = mKapal::where('id', $getIdKapal->id_kapal)->first();
        if (!empty($getKapal)) {
            return response()->json(['error' => 'false', 'message' => 'data found', 'data' => $getKapal], 200);
        } else {
            return response()->json(['error' => 'true', 'message' => 'data not found', 'data' => $getKapal], 400);
        }
    }

    public function getJadwalKapal(Request $request)
    {
        $user = Auth::id();
        $getIdKapal = mHakKapal::where('id_user', $user)->where('hak_akses', "TAdmin")->first();
        $getKapal = mKapal::where('id', $getIdKapal->id_kapal)->first();
        if (!empty($getKapal)) {
            $getJadwal = mDetailJadwal::where('id_kapal', $getKapal->id)->with('DJJadwalAsal', 'DJJadwalTujuan')->get();
            foreach ($getJadwal as $index => $item) {
                $getJadwal[$index]->asal_pelabuhan = $item->DJJadwalAsal->JDermaga->DPelabuhan->nama_pelabuhan;
                $getJadwal[$index]->tujuan_pelabuhan = $item->DJJadwalTujuan->JDermaga->DPelabuhan->nama_pelabuhan;
                $getJadwal[$index]->kode_pelabuhan_asal = $item->DJJadwalAsal->JDermaga->DPelabuhan->kode_pelabuhan;
                $getJadwal[$index]->kode_pelabuhan_tujuan = $item->DJJadwalTujuan->JDermaga->DPelabuhan->kode_pelabuhan;
                $getJadwal[$index]->waktu_berangkat = $item->DJJadwalAsal->waktu;
                $getJadwal[$index]->waktu_sampai = $item->DJJadwalTujuan->waktu;
                $getJadwal[$index]->nama_dermaga_asal = $item->DJJadwalAsal->JDermaga->nama_dermaga;
                $getJadwal[$index]->nama_dermaga_tujuan = $item->DJJadwalTujuan->JDermaga->nama_dermaga;
                $getJadwal[$index]->status_pelabuhan_asal = $item->DJJadwalAsal->JDermaga->DPelabuhan->status_pelabuhan;
                $getJadwal[$index]->status_pelabuhan_tujuan = $item->DJJadwalTujuan->JDermaga->DPelabuhan->status_pelabuhan;
            }
            return response()->json(['error' => 'false', 'message' => 'data found', 'data' => $getJadwal], 200);
        } else {
            return response()->json(['error' => 'true', 'message' => 'data not found', 'data' => $getKapal], 400);
        }
    }

    public function getJadwalKapalFilter(Request $request)
    {
        $user = Auth::id();
        $getIdKapal = mHakKapal::where('id_user', $user)->where('hak_akses', "TAdmin")->first();
        if (!empty($getIdKapal)) {
            $getKapal = mKapal::where('id', $getIdKapal->id_kapal)->first();
            $getJadwal = mDetailJadwal::where('id_kapal', $getKapal->id)->with('DJJadwalAsal', 'DJJadwalTujuan', 'DJDetailHarga');
            $asal_pelabuhan = $request->asal_pelabuhan;
            $tujuan_pelabuhan = $request->tujuan_pelabuhan;
            $golongan = $request->golongan;
            if ($request->filled('asal_pelabuhan')) {
                $getJadwal->whereHas('DJJadwalAsal', function ($jadwal) use ($asal_pelabuhan) {
                    $jadwal->whereHas('JDermaga', function ($dermaga) use ($asal_pelabuhan) {
                        $dermaga->whereHas('DPelabuhan', function ($pelabuhan) use ($asal_pelabuhan) {
                            $pelabuhan->where('id', $asal_pelabuhan);
                        });
                    });
                });
            }
            if ($request->filled('tujuan_pelabuhan')) {
                $getJadwal->whereHas('DJJadwalTujuan', function ($jadwal) use ($tujuan_pelabuhan) {
                    $jadwal->whereHas('JDermaga', function ($dermaga) use ($tujuan_pelabuhan) {
                        $dermaga->whereHas('DPelabuhan', function ($pelabuhan) use ($tujuan_pelabuhan) {
                            $pelabuhan->where('id', $tujuan_pelabuhan);
                        });
                    });
                });
            }
            if ($request->filled('tanggal')) {
                $getJadwal->where('tanggal', $request->tanggal);
            }

            if ($request->filled('golongan')) {
                $getJadwal->whereHas('DJDetailHarga', function ($detailHarga) use ($golongan) {
                    $detailHarga->whereHas('DHHarga', function ($harga) use ($golongan) {
                        $harga->whereHas('HDetailGolongan', function ($detailGoolongan) use ($golongan) {
                            $detailGoolongan->where('id_golongan', $golongan);
                        });
                    });
                });
            }

            $getJadwal = $getJadwal->get();

            foreach ($getJadwal as $index => $item) {
                $getJadwal[$index]->asal_pelabuhan = $item->DJJadwalAsal->JDermaga->DPelabuhan->nama_pelabuhan;
                $getJadwal[$index]->tujuan_pelabuhan = $item->DJJadwalTujuan->JDermaga->DPelabuhan->nama_pelabuhan;
                $getJadwal[$index]->kode_pelabuhan_asal = $item->DJJadwalAsal->JDermaga->DPelabuhan->kode_pelabuhan;
                $getJadwal[$index]->kode_pelabuhan_tujuan = $item->DJJadwalTujuan->JDermaga->DPelabuhan->kode_pelabuhan;
                $getJadwal[$index]->waktu_berangkat = $item->DJJadwalAsal->waktu;
                $getJadwal[$index]->waktu_sampai = $item->DJJadwalTujuan->waktu;
                $getJadwal[$index]->nama_dermaga_asal = $item->DJJadwalAsal->JDermaga->nama_dermaga;
                $getJadwal[$index]->nama_dermaga_tujuan = $item->DJJadwalTujuan->JDermaga->nama_dermaga;
                $getJadwal[$index]->status_pelabuhan_asal = $item->DJJadwalAsal->JDermaga->DPelabuhan->status_pelabuhan;
                $getJadwal[$index]->status_pelabuhan_tujuan = $item->DJJadwalTujuan->JDermaga->DPelabuhan->status_pelabuhan;
            }
            return response()->json(['error' => 'false', 'message' => 'data found', 'data' => $getJadwal], 200);
        } else {
            return response()->json(['error' => 'true', 'message' => 'data not found', 'data' => ''], 400);
        }
    }
}
