<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddEmployeeRequest;
use App\Http\Services\EmployeeService;
use App\Http\Services\ProjectService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;  
use App\Models\User;

class EmployeeController extends Controller
{
    private $employeeService;
    private $projectService;

    public function __construct(EmployeeService $employeeService, ProjectService $projectService){
        parent::__construct();
        $this->projectService = $projectService;
        $this->employeeService = $employeeService;
    }

    public function index()
    {
        $this->authorize('viewAny',User::class);
        $user = Auth::user();

        try{
            //get list employee
            $employee = $this->employeeService->getEmployee();
            
            //get level
            $level = $this->employeeService->getLevel();

            //get type
            $type = $this->employeeService->getType();

            return view('employee',[
                "employee" => $employee,
                "level" => $level,
                'type' => $type,
                "user" => $user
            ]);
        }
        catch(Exception $ex){
            return abort(500);
        }
    }

    public function add(AddEmployeeRequest $request){
        $this->authorize('create',User::class);

        try{
            $input = array_diff_key($request->all(),["_token"=>0]);
            $bool = $this->employeeService->add($input);
            //return json
            if($bool)
                return response("Success");
            else
                return response("Faild");
        }catch(Exception $ex){
            return response($ex->getMessage(),500);
        }catch(QueryException $ex){
            return response($ex->getMessage(),500);
        }
    }

    public function importFileEmp(Request $request){
        try{
            $dataNewEmp = $request->all();
            // Check id employee already exists
            $dataNewEmp = $this->employeeService->removeEmpExisted($dataNewEmp);
            if(empty($dataNewEmp)){
                return response()->json(["status"=>"Existed"]);
            }
            // //set default values for new employee
            $defaulData = $this->employeeService->setDefaultNewEmp($dataNewEmp["dataNewEmp"]);
            //Insert new Employee 
            $boolImport = $this->employeeService->importEmployee($defaulData);
            //return json
            if($boolImport == true)
                return response()->json(["status"=>"Success", "EmpExisted"=>$dataNewEmp["dataExisted"]]);
            else
                return response()->json(["status"=>"Faild"]);
        }catch(Exception $ex){
            return response($ex->getMessage(),500);
        }catch(QueryException $ex){
            return response($ex->getMessage(),500);
        }
    }

    public function checkUser($username){
        $this->authorize('create',User::class);
        try{
            $check = $this->employeeService->checkUSer($username);
            if($check){
                return response("Valid");
            }
            else{
                return response("Invalid");
            }
        }
        catch(Exception $ex){
            return response($ex->getMessage(),500);
        }catch(QueryException $ex){
            return response($ex->getMessage(),500);
        }
    }

    public function delete($id, User $employee){
        $this->authorize('delete', $employee);

        try{            

            //delete projects if user is PM
            if($this->employeeService->checkPM($id)){
                $projects = $this->projectService->getProjectByPM($id);

                foreach($projects as $p){
                    $this->projectService->delete($p->id);
                }
                
            }

            //delete dev
            $bool = $this->employeeService->delete($id);

            if($bool){
                return true;
            }else{
                return response("Delete failure",500);
            }
        }catch(Exception $ex){
            return response($ex->getMessage(),500);
        }
    }
}
