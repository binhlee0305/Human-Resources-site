<?php

namespace App\Http\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeServiceImpl implements EmployeeService{
    /**
     * Get All Employee
     * 
     * @return [array]
     */
    public function getEmployee()
    {
        try{
            // DB::enableQueryLog();

            // $data = DB::table('users')
            //     ->join('level','level.id','=','users.id_level')
            //     ->orderBy('name')
            //     ->select(
            //         'users.id as id_user',
            //         'level.level as level',
            //         'users.name as name',
            //         'users.privillege',
            //         'users.status'
            //     )
            //     ->get();

            $data = DB::select(
                "SELECT
                u.id AS id_user,
                l.level AS level,
                t.type AS type,
                u.name AS name,
                (
                SELECT
                    SUM(HOUR)
                FROM
                    works_on AS wo
                INNER JOIN works_hour AS wh
                ON
                    wo.id = wh.id_works_on
                WHERE
                    MONTH(CURRENT_DATE) = MONTH(wh.week) AND YEAR(CURRENT_DATE) = YEAR(wh.week) AND wo.id_dev = u.id 
                ) AS resource, u.status
                FROM
                    users AS u
                INNER JOIN level AS l
                ON
                    l.id = u.id_level
                INNER JOIN types AS t
                ON
                    t.id = u.id_type
                WHERE u.privillege <> 1"
            );
            // dd($data);
            // dd(DB::getQueryLog());

            return $data;
        }catch(Exception $ex)
        {
            throw $ex;
        }
    }


    /**
     * Get all level
     * 
     * @return [array]
     */
    public function getLevel(){
        try{
            //query db
            $data = DB::table('level')->get();

            return $data;
        }catch(Exception $ex)
        {
            throw $ex;
        }
    }

    /**
     * Get all type
     * 
     * @return [array]
     */
    public function getType(){
        try{
            //query db
            $data = DB::table('types')->get();

            return $data;
        }catch(Exception $ex)
        {
            throw $ex;
        }
    }

    /**
     * Add employee
     * @param [array]
     * 
     * @return bool
     */
    public function add($input){
        try{
            $countID = DB::table('users')->where("id",$input["id"])->count();
            if($countID == 0){
                $input["password"]=Hash::make($input["password"]);
                $bool = DB::table('users')->insert($input);
                return $bool;
            }
            else{
                return ;
            }
        }catch(Exception $ex){
            throw $ex;
        }
    }

    /**
     * Check Username
     * @param username
     * 
     * @return bool
     */
    public function checkUser($username){
        try{
            $count = DB::table('users')->where("username",$username)->count();
            if($count == 0)
                return true;
            else 
                return false;
        }catch(Exception $ex){
            throw $ex;
        }
    }
    /**
     * Check id
     * @param id
     * 
     * @return bool
     */
    public function checkId($id){
        try{
            $ret = DB::table('users')->where('id',$id)->first();

            return !empty($ret);
        }catch(Exception $ex){
            throw $ex;
        }
    }

    /**
     * Check PM
     * @param id 
     * 
     * @return bool
     */
    public function checkPM($id){
        try{
            $ret = DB::table('users')->whereRaw("id = ? AND privillege = 2",$id)->first();
            return $ret;
        }catch(Exception $ex){
            throw $ex;
        }
    }

    /**
     * Delete user
     * @param id
     * 
     * @return bool
     */
    public function delete($id){
        try{

            if($this->checkId($id)){

                // DB::enableQueryLog();

                $delWorksHour = DB::delete(
                    "DELETE wh
                    FROM users as us
                    JOIN works_on as wo ON us.id = wo.id_dev
                    JOIN works_hour as wh ON wo.id = wh.id_works_on
                    WHERE us.id = ?",
                    [$id]
                );

                $delWorksOn = DB::delete(
                    "DELETE wo
                    FROM users as us
                    JOIN works_on as wo ON us.id = wo.id_dev
                    WHERE us.id = ?",
                    [$id]
                );

                $delUser = DB::delete(
                    "DELETE us
                    FROM users as us
                    WHERE us.id = ?",
                    [$id]
                );
                // dd(DB::getQueryLog());
                return $delUser;
            }
            else{
                return false;
            }
        }catch(Exception $ex){
            throw $ex;
        }
    }

    public function importEmployee($dataNewEmp){
        $boolInsert = DB::table('users')->insert($dataNewEmp);
        return $boolInsert;
    }

    public function setDefaultNewEmp($dataNewEmp){
        $defaultdata =[];
        foreach($dataNewEmp as $emp){
            $emp['password'] = $this->getRandomPwd();
            $emp['username'] = $this->getUsernameNewEmp($emp['name']);
            $emp['status'] = config("web.config.default_user.status");
            $emp['id_level'] = config("web.config.default_user.id_level");
            $emp['privillege'] = config("web.config.default_user.privillege");
            $emp['id_type'] = config("web.config.default_user.id_type");
            array_push($defaultdata, $emp);
        }
        return $defaultdata;
    }

    public function removeEmpExisted($dataNewEmp){
        $ret = DB::select(DB::raw('SELECT id FROM users'));
        $arrIndexDel = [];
        for($i=0; $i<count($dataNewEmp); $i++){
            foreach($ret as $emp){
                if($dataNewEmp[$i]['id']==$emp->id){
                    array_push($arrIndexDel,$i);
                }
            }
        }
        $empExisted =[];
        foreach($arrIndexDel as $key=>$val){
            array_push($empExisted,$dataNewEmp[$val]);
            unset($dataNewEmp[$val]);
        }
        $dataEmp = ["dataNewEmp"=>$dataNewEmp, "dataExisted"=>$empExisted];
        return $dataEmp;
    }

    public function getUsernameNewEmp($name){
        $arrFullname = explode(' ', $name);
        $length = count($arrFullname);
        $username = $this->stripunicode(strtolower($arrFullname[$length-1])).".";
        for( $i = 0; $i < count($arrFullname)-1 ; $i++ ) {
            $username.= $this->stripunicode(strtolower($arrFullname[$i][0]));
        }
        return $username;
    }

    function getRandomPwd() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $pass= Hash::make(implode($pass));
        return $pass; 
    }

    function stripunicode($str){ 
        if(!$str) return false;
        $unicode = array('a'=>'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
                         'd'=>'đ',
                         'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
                         'i'=>'í|ì|ỉ|ĩ|ị',
                         'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
                         'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
                         'y'=>'ý|ỳ|ỷ|ỹ|ỵ');
        foreach($unicode as $khongdau=>$codau) {
            $arr=explode("|",$codau);$str = str_replace($arr,$khongdau,$str);
        }
    return $str;
    }
}