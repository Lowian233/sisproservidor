<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\audit;
use App\TrainingPersonal;
use App\Personal;

class TrainingPersonalsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        if(Auth::user()->UsRol === "Programador"){
            $CapaPers = DB::table('training_personals')
                ->join('sedes', 'training_personals.FK_Sede', '=', 'sedes.ID_Sede')
                ->join('trainings', 'training_personals.FK_Capa', '=', 'trainings.ID_Capa')
                ->join('personals', 'training_personals.FK_Pers', '=', 'personals.ID_Pers')
                ->select('training_personals.ID_CapPers','training_personals.CapaPersDate','training_personals.CapaPersExpire','training_personals.CapaPersDelete','sedes.SedeName','trainings.CapaName','personals.PersFirstName','personals.PersLastName')
                ->get();
            return view('TrainingPersonals.index', compact('CapaPers'));
        }
        $CapaPers = DB::table('training_personals')
            ->join('sedes', 'training_personals.FK_Sede', '=', 'sedes.ID_Sede')
            ->join('trainings', 'training_personals.FK_Capa', '=', 'trainings.ID_Capa')
            ->join('personals', 'training_personals.FK_Pers', '=', 'personals.ID_Pers')
            ->select('training_personals.ID_CapPers','training_personals.CapaPersDate','training_personals.CapaPersExpire','training_personals.CapaPersDelete','sedes.SedeName','trainings.CapaName','personals.PersFirstName','personals.PersLastName')
            ->where('training_personals.CapaPersDelete', 0)
            ->get();
        return view('TrainingPersonals.index', compact('CapaPers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $Personals = DB::table('personals')
            ->select('ID_Pers', 'PersFirstName', 'PersLastName')
            ->get();
        $Trainings = DB::table('trainings')
            ->select('ID_Capa', 'CapaName')
            ->where('CapaDelete', 0)
            ->get();
        $Sedes = DB::table('sedes')
            ->select('ID_Sede', 'SedeName')
            ->where('SedeDelete', 0)
            ->get();
        return view('trainingPersonals.create', compact('Personals', 'Trainings', 'Sedes'));
    }

    /**
     * Show the form for creating a course for a specific person (from personal interno show).
     *
     * @param  string  $slug  PersSlug del personal
     * @return \Illuminate\Http\Response
     */
    public function createForPersonal($slug)
    {
        $Persona = Personal::where('PersSlug', $slug)->first();
        if (!$Persona) {
            abort(404);
        }
        $Personals = DB::table('personals')
            ->select('ID_Pers', 'PersFirstName', 'PersLastName')
            ->get();
        $Trainings = DB::table('trainings')
            ->select('ID_Capa', 'CapaName')
            ->where('CapaDelete', 0)
            ->get();
        $Sedes = DB::table('sedes')
            ->select('ID_Sede', 'SedeName')
            ->where('SedeDelete', 0)
            ->get();
        $sedePersona = DB::table('personals')
            ->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
            ->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
            ->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
            ->where('personals.ID_Pers', $Persona->ID_Pers)
            ->value('sedes.ID_Sede');
        $returnUrl = route('personalInterno.show', ['personalInterno' => $slug]);
        return view('trainingPersonals.create', compact('Personals', 'Trainings', 'Sedes', 'Persona', 'sedePersona', 'returnUrl'));
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
            'CapaPersDate' => 'required|date',
            'CapaPersExpire' => 'required|date',
            'FK_Pers' => 'required|exists:personals,ID_Pers',
            'FK_Capa' => 'required|exists:trainings,ID_Capa',
            'CapaPersPdf' => 'nullable|file|mimes:pdf|max:10240',
        ], ['CapaPersPdf.mimes' => 'El documento debe ser un archivo PDF.', 'CapaPersPdf.max' => 'El PDF no debe superar 10 MB.']);

        $CapaPers = new TrainingPersonal();
        $CapaPers->CapaPersDate = $request->input('CapaPersDate');
        $CapaPers->CapaPersExpire = $request->input('CapaPersExpire');
        $CapaPers->FK_Pers = $request->input('FK_Pers');
        $CapaPers->FK_Capa = $request->input('FK_Capa');
        $CapaPers->FK_Sede = $request->input('FK_Sede') ?: null;
        $CapaPers->CapaPersDelete = 0;
        $CapaPers->save();

        if ($request->hasFile('CapaPersPdf') && $request->file('CapaPersPdf')->isValid()) {
            $file = $request->file('CapaPersPdf');
            $nombre = $CapaPers->ID_CapPers . '.pdf';
            $destDir = storage_path('app/public/capacitaciones');
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $file->move($destDir, $nombre);
            $CapaPers->CapaPersPdf = 'capacitaciones/' . $nombre;
            $CapaPers->save();
        }

        $returnUrl = request('return_url');
        if ($returnUrl) {
            return redirect($returnUrl);
        }
        return redirect()->route('capacitacion-personal.index');
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
     * Download the PDF of a training/course.
     *
     * @param  int  $id  ID_CapPers
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf($id)
    {
        $CapaPers = TrainingPersonal::where('ID_CapPers', $id)->first();
        if (!$CapaPers || !$CapaPers->CapaPersPdf) {
            abort(404, 'No existe el documento.');
        }
        if (!Storage::disk('public')->exists($CapaPers->CapaPersPdf)) {
            abort(404, 'El archivo no se encuentra en el servidor.');
        }
        $nombre = ($CapaPers->training->CapaName ?? 'capacitacion') . '_' . $CapaPers->ID_CapPers . '.pdf';
        $filePath = storage_path('app/public/' . $CapaPers->CapaPersPdf);
        if (!file_exists($filePath)) {
            abort(404, 'El archivo no se encuentra en el servidor.');
        }
        return response()->download($filePath, $nombre, [
            'Content-Type' => 'application/pdf'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $CapaPer = TrainingPersonal::where('ID_CapPers', $id)->first();
        if (!$CapaPer) {
            abort(404);
        }
        $Personals = DB::table('personals')
            ->select('ID_Pers', 'PersFirstName', 'PersLastName')
            ->get();
        $Trainings = DB::table('trainings')
            ->select('ID_Capa', 'CapaName')
            ->where('CapaDelete', 0)
            ->get();
        $Sedes = DB::table('sedes')
            ->select('ID_Sede', 'SedeName')
            ->where('SedeDelete', 0)
            ->get();
        $returnUrl = request('return_url');
        return view('trainingPersonals.edit', compact('CapaPer','Personals','Trainings', 'Sedes', 'returnUrl'));
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
        $request->validate([
            'CapaPersDate' => 'required|date',
            'CapaPersExpire' => 'required|date',
            'FK_Pers' => 'required|exists:personals,ID_Pers',
            'FK_Capa' => 'required|exists:trainings,ID_Capa',
            'CapaPersPdf' => 'nullable|file|mimes:pdf|max:10240',
        ], ['CapaPersPdf.mimes' => 'El documento debe ser un archivo PDF.', 'CapaPersPdf.max' => 'El PDF no debe superar 10 MB.']);

        $CapaPers = TrainingPersonal::where('ID_CapPers', $id)->first();
        $CapaPers->CapaPersDate = $request->input('CapaPersDate');
        $CapaPers->CapaPersExpire = $request->input('CapaPersExpire');
        $CapaPers->FK_Pers = $request->input('FK_Pers');
        $CapaPers->FK_Capa = $request->input('FK_Capa');
        $CapaPers->FK_Sede = $request->input('FK_Sede');

        if ($request->hasFile('CapaPersPdf') && $request->file('CapaPersPdf')->isValid()) {
            $destDir = storage_path('app/public/capacitaciones');
            $nombre = $CapaPers->ID_CapPers . '.pdf';
            if ($CapaPers->CapaPersPdf && file_exists(storage_path('app/public/' . $CapaPers->CapaPersPdf))) {
                @unlink(storage_path('app/public/' . $CapaPers->CapaPersPdf));
            }
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $request->file('CapaPersPdf')->move($destDir, $nombre);
            $CapaPers->CapaPersPdf = 'capacitaciones/' . $nombre;
        }
        $CapaPers->save();

        $log = new audit();
        $log->AuditTabla="training_personals";
        $log->AuditType="Modificado";
        $log->AuditRegistro=$CapaPers->ID_CapPers;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=$request->except(['CapaPersPdf', '_token', '_method']);
        $log->save();

        $returnUrl = $request->input('return_url');
        if ($returnUrl) {
            return redirect($returnUrl);
        }
        return redirect()->route('capacitacion-personal.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $CapaPers = TrainingPersonal::where('ID_CapPers', $id)->first();
            if ($CapaPers->CapaPersDelete == 0) {
                $CapaPers->CapaPersDelete = 1;
            }
            else{
                $CapaPers->CapaPersDelete = 0;
            }
        $CapaPers->save();

        $log = new audit();
        $log->AuditTabla = "training_personals";
        $log->AuditType = "Eliminado";
        $log->AuditRegistro = $CapaPers->ID_CapPers;
        $log->AuditUser = Auth::user()->email;
        $log->Auditlog = $CapaPers->CapaPersDelete;
        $log->save();

        $returnUrl = request('return_url');
        if ($returnUrl) {
            return redirect($returnUrl);
        }
        return redirect()->route('capacitacion-personal.index');
    }
}