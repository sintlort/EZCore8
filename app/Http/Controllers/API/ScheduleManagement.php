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
            $idSchedule = array();
            foreach ($idDetailHarga as $det) {
                $jumlahPembelian = mPembelian::where('id_jadwal', $det->id)->count();
                $jumlahTiket = $det->DHHarga->HDetailGolongan->jumlah;
                if ($jumlahPembelian < $jumlahTiket) {
                    $idSchedule[] = $det->id;
                }
            }
            $detail = mDetailHarga::whereIn('id_harga', $harga)->whereIn('id_detail_jadwal', $data1)->whereIn('id', $idSchedule)->with('DHHarga', 'DHJadwal')->get();
            $schedule = $detail;
            foreach ($schedule as $index => $data) {
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
                $schedule[$index]->tersisa = mPembelian::where('id_jadwal', $data->id)->count() . " / " . $data->DHHarga->HDetailGolongan->jumlah;
                $time = Carbon::createFromFormat("H:i:s", $data->DHJadwal->DJJadwalAsal->waktu);
                $schedule[$index]->waktu_berangkat_asal = $time->format('H:i');
                $time->addMinutes($data->DHJadwal->estimasi_waktu);
                $schedule[$index]->waktu_berangkat_tujuan = $time->format('H:i');
            }
        } else {
            $golongan = mGolongan::find($dataGolongan);
            $detailGolongan = mDetailGolongan::where('id_golongan', $golongan->id)->pluck('id');
            $harga = mHarga::where('id_pelabuhan_asal', $idP1)->where('id_pelabuhan_tujuan', $idP2)->whereIn('id_detail_golongan', $detailGolongan)->pluck('id');
            $data1 = mDetailJadwal::with('DJJadwalAsal', 'DJJadwalTujuan')->whereIn('id_jadwal_asal', $idJadwal1)->whereIn('id_jadwal_tujuan', $idJadwal2)->where('tanggal', $tanggal)->pluck('id');
            $idDetailHarga = mDetailHarga::whereIn('id_harga', $harga)->whereIn('id_detail_jadwal', $data1)->with('DHHarga', 'DHJadwal')->get();
            $idSchedule = array();
            foreach ($idDetailHarga as $det) {
                $jumlahPembelian = mPembelian::where('id_jadwal', $det->id)->count();
                $jumlahTiket = $det->DHHarga->HDetailGolongan->jumlah;
                if ($jumlahPembelian < $jumlahTiket) {
                    $idSchedule[] = $det->id;
                }
            }
            $detail = mDetailHarga::whereIn('id_harga', $harga)->whereIn('id_detail_jadwal', $data1)->whereIn('id', $idSchedule)->with('DHHarga', 'DHJadwal')->get();
            $schedule = $detail;
            foreach ($schedule as $index => $data) {
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
                $schedule[$index]->tersisa = mPembelian::where('id_jadwal', $data->id)->count() . " / " . $data->DHHarga->HDetailGolongan->jumlah;
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
            $getJadwal = mDetailJadwal::where('id_kapal',$getKapal->id)->get();
            return response()->json(['error' => 'false', 'message' => 'data found', 'data' => $getJadwal], 200);
        } else {
            return response()->json(['error' => 'true', 'message' => 'data not found', 'data' => $getKapal], 400);
        }
    }
}
