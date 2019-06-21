<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaksi;
use App\Alat;   
use Yajra\Datatables\Datatables;
use DateTime;
class TransaksiController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return view('transaksi.index');
    }
    public function getData(){
        return Datatables::of(Transaksi::query())->addColumn('action', function ($t) {
            return '<a href="'.route('alat.edit',['id'=>$t->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a><a href="'.route('alat.delete',['id'=>$t->id]).'" class="btn btn-xs btn-danger"><i class="glyphicon glyphicon-delete"></i> Delete</a>';
        })
        ->make(true);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $alat = Alat::find($id);
        
        if(!$alat) return  redirect()->back();
        return view('transaksi.create',['alat'=>$alat]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'alat_id'=>'required',
            'tanggal_pinjam'=>'required|date|before:tanggal_rencana_kembali',
            'tanggal_rencana_kembali'=>'required|date|after:tanggal_pinjam',
            'nama_peminjam'=>'required',
            'no_ktp_sim'=>'required',
            'tipe_identitas'=>'required|in:SIM,KTP',
            'scan_identitas'=>'required|file|image|mimes:jpeg,png,gif,webp|max:2048',
        ]);
        
        $alat = Alat::find($request->alat_id);
        if(!$alat) return redirect()->back();

        if(Transaksi::where('alat_id',$request->alat_id)->count()){
            $alat = Alat::select('alats.*')->leftjoin('transaksis','transaksis.alat_id','alats.id')
            ->where('transaksis.tanggal_kembali','!=',null)
            ->where('alats.id',$request->alat_id)
            ->first($request->alat_id);

            if(!$alat) return redirect()->back();
        }

        $fdate = $request->tanggal_pinjam;
        $tdate = $request->tanggal_rencana_kembali;
        $datetime1 = new DateTime($fdate);
        $datetime2 = new DateTime($tdate);
        $interval = $datetime1->diff($datetime2);
        $days = $interval->format('%a');

        $file = $request->file('scan_identitas');
        $filename = 'scan_id-' . time() . '.' . $file->getClientOriginalExtension();
        $folder = $file->move('uploads', $filename);

        $transaksi = new Transaksi;
        $transaksi->alat_id = $request->alat_id;
        $transaksi->tanggal_pinjam = $request->tanggal_pinjam;
        $transaksi->tanggal_rencana_kembali = $request->tanggal_rencana_kembali;
        $transaksi->lama_hari = $days;
        $transaksi->total_biaya_sewa = $days*$alat->harga_sewa_perhari;
        $transaksi->scan_identitas = $filename;
        $transaksi->tipe_identitas = $request->tipe_identitas;
        $transaksi->nama_peminjam = $request->nama_peminjam;
        $transaksi->no_ktp_sim = $request->no_ktp_sim;
        $transaksi->save();

        return redirect()->route('transaksi.index');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // $alat = Alat::find($id);
        // if(!$alat) return redirect()->back();
        // return view('alat.edit',['alat'=>$alat]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $request->validate([
        //     'jenis_peralatan'=>'required',
        //     'tipe'=>'required',
        //     'no_reg'=>'required',
        //     'harga_sewa_perhari'=>'required|integer',
        // ]);
        // $alat = Alat::find($id);
        // if(!$alat) return redirect()->back();
        // $alat->update($request->all());
        // return redirect()->route('alat.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $alat = Alat::find($id);
        // if(!$alat) return redirect()->back();
        // $alat->delete();
        // return redirect()->route('alat.index');
    }
}