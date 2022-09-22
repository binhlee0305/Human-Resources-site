<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Imports\ExcelImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    //
    public function index(){
        $this->authorize('viewAny',User::class);

        try{
            $user = Auth::user();
            return view('import',compact('user'));
        }
        catch(Exception $ex){
            return abort(500);
        }
        catch (QueryException $exception) {
            return abort(404);
        }
    }

    public function Excel(Request $request)
    {
        $file = $request->data;
        
        $import = new ExcelImport();

        $import->onlySheets('Employees','Assignment');

        Excel::import($import, $file);
        
    }

}
