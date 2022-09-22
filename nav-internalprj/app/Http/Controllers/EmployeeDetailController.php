<?php

namespace App\Http\Controllers;

use App\Http\Services\EmployeeDetailService;
use App\Models\Level;
use App\Models\Type;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;    
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Response;
use Exception;
use JavaScript;

class EmployeeDetailController extends Controller
{
    protected $employeeDetailService;

    public function __construct(EmployeeDetailService $employeeDetailService)
    {
        $this->employeeDetailService = $employeeDetailService;
    }

    public function index($id_employee, Request $request, User $employee)
    {
        $this->authorize('view',$employee);
        $user = Auth::user();

        try {
            $employee = User::find($id_employee);

            //get monday to create header table
            $listMonday = $this->employeeDetailService->getFirstDayOfWeek(
                config("web.config.day_week"), 
                config("web.config.num_month")
            );
            //get coppy list mondays format ('y-m-d')
            $arrayMondays = $listMonday;

            $level = Level::find($employee->id_level);
            $listLevel = Level::all();
            $getType = Type::find($employee->id_type);
            $listType = Type::all();
 
            $data = $this->employeeDetailService->getEffortEmployee($id_employee, $listMonday);

            JavaScript::put([
                'listMonday' => array_map(function ($e) {
                    return Carbon::parse($e)->format('d/m');
                }, $listMonday),
                'data' => $data,
                "arrayMondays" => $arrayMondays
            ]);

            //if user click submit -> retrive data from form with post method
            // and save data into database
            if ($request->isMethod('post')) {
                if($user->can('update',$employee)){
                    //retrive data from form submit
                    $employee_name = $request->input('employee-name');
                    $gender = $request->input('gender');
                    $join_date = $request->input('join-date');
                    $status = $request->input('status');
                    $id_level = $request->input('level');
                    $privillege = $request->input('privillege');
                    $type = $request->input('type');

                    // Check status employee
                    $out_date = ($status == "Disable") ? $this->employeeDetailService->getCurrentDate() : null;

                    // update new information project
                    $employee->name = $employee_name;
                    $employee->gender = $gender;
                    $employee->join_date = $join_date;
                    $employee->status = $status;
                    $employee->id_level = $id_level;
                    $employee->privillege = $privillege;
                    $employee->id_type = $type;
                    $employee->out_date = $out_date;
                    $employee->save();

                    return Redirect::to("employee/" . $id_employee);
                }else{
                    return abort(403);
                }
                
            }

            return view('employee_detail', [
                'user' => $user,
                'employee' => $employee,
                'level' => $level,
                'listLevel' => $listLevel,
                'type' => $getType,
                'listType' => $listType,
                'listMonday' => array_map(function ($e) {
                    return Carbon::parse($e)->format('d/m');
                }, $listMonday),
                'data' => $data,
            ]);
        } catch (Exception $ex) {
            return abort(500);
        } 
    }

    public function searchEffortUsage($id, Request $request){
        try {
            
            //get monday 
            $startDate = $request->input('fromDate');
            $endDate = $request->input('toDate');
            $mondays = $this->employeeDetailService->getFirstDayOfMonth($startDate,$endDate);
            $dataTable = $this->employeeDetailService->getEffortEmployee($id,$mondays);

            $mondays = array_map(function ($monday) {
                return Carbon::parse($monday)->format('d/m');
            }, $mondays);
            
            JavaScript::put([
                "dataTable" => $dataTable,
                "listMonday" => $mondays,
            ]);
            return response()->json(["dataTable" => $dataTable,"listMonday" => $mondays]);
        }
        catch(Exception $ex)
        {
            //response error
            return response($ex->getMessage(),400);
        }
    }

}
