<?php

namespace App\Http\Services;
use Exception;
use Illuminate\Support\Facades\DB;


class ProjectServiceImpl implements ProjectService{
    /**
     * Get All Project
     * 
     * @return [array]
     */
    public function getProject()
    {
        try{
            // DB::enableQueryLog();

            $data = DB::table('project')
                ->join('users','users.id','=','project.id_pm')
                ->join('client','client.id','=','project.id_client')
                ->select(
                            'project.id as id_project',
                            'project.name as name_project',
                            'users.name as name_pm',
                            'project.total_effort',
                            'client.name as client',
                            'project.status'
                        )
                ->get();
            
            // dd(DB::getQueryLog());

            return $data;
        }catch(Exception $ex)
        {
            throw $ex;
        }
    }

    /**
     * Get All Client
     * 
     * @return [array]
     */
    public function getClient()
    {
        try{
            // DB::enableQueryLog();

            $data = DB::table('client')
                        ->select('name','id')
                        ->get();
            // dd(DB::getQueryLog());

            return $data;
        }catch(Exception $ex)
        {
            throw $ex;
        }
    }

    /**
     * Get all project manager
     * 
     * @return [array]
     */
    public function getPM()
    {
        try{
            // DB::enableQueryLog();

            $privillege_pm = config("web.config.user.pm");

            $data = DB::table('users')
            ->where('privillege',$privillege_pm)
            ->select('name','id')
            ->get();

            // dd(DB::getQueryLog());
            return $data;
        }catch(Exception $ex)
        {
            throw $ex;
        }
    }

    /**
     * Get all dev
     * 
     * @return [array]
     */
    public function getDev(){
        try{
            // DB::enableQueryLog();
            $privillege_pm = config("web.config.user.pm");
            $privillege_dev = config("web.config.user.dev");
            $data = DB::table('users')
            ->whereRaw("privillege IN(?,?)",[$privillege_pm,$privillege_dev])
            ->select('name','id')
            ->get();

            // dd(DB::getQueryLog());
            return $data;
        }catch(Exception $ex)
        {
            throw $ex;
        }
    }

    /**
     * Add new Project
     * 
     * @return boolean
     */
    public function addProject($input){
        try{
            $countID = DB::table('project')->where("id",$input["id"])->count();
            if($countID == 0){
                $ret = DB::table('project')->insert($input);
                return $ret;
            }
            else return ;
        }catch(Exception $ex)
        {
            throw $ex;
        }
    }

    public function addDevOnProject($id_project, $proj_member, $type){
        try{
            $data  = array();
            foreach($proj_member as $member)
            {
                $row = array();
                $row["id_project"] = $id_project;
                $row["type"] = $type;
                $row["id_dev"] = $member;
                $data[] = $row;
            }
            $ret =true;
            if(count($data) > 0) {
                $ret = DB::table('works_on')->insert($data);
            }
            return $ret;
            // $count = 0;
            // foreach($input["id_dev"] as $id_dev)
            // {
            //     $data[$count]["id_project"] = $input["id_project"];
            //     $data[$count]["type"] = $input["type"];
            //     $data[$count]["id_dev"] = $id_dev;
            //     $count++;
            // }
            // $ret = DB::table('works_on')->insert($data);
            
            // return $ret;
        }catch(Exception $ex)
        {
            throw $ex;
        }
    }

    /**
     * Check id project, true if existed else false
     * 
     * @return boolean
     */
    public function checkId($id){
        try{
            $ret = DB::table('project')->where('id',$id)->first();

            return !empty($ret);
            
        }catch(Exception $ex){
            throw $ex;
        }
    }

    /**
     * Delete project
     * 
     * @return boolean
     */
    public function delete($id){
        try{
            if($this->checkId($id)){
                // DB::enableQueryLog();

                $delWorksHour = DB::delete(
                    "DELETE wh
                    FROM project as p
                    JOIN works_on as wo ON p.id = wo.id_project
                    JOIN works_hour as wh ON wo.id = wh.id_works_on
                    WHERE p.id = ?",
                    [$id]
                );

                $delWorksOn = DB::delete(
                    "DELETE wo
                    FROM project as p
                    JOIN works_on as wo ON p.id = wo.id_project
                    WHERE p.id = ?",
                    [$id]
                );

                $delProject = DB::delete(
                    "DELETE p
                    FROM project as p
                    WHERE p.id = ?",
                    [$id]
                );
                // dd(DB::getQueryLog());
                return $delProject;
            }
            else{
                return false;
            }
        }catch(Exception $ex){
            throw $ex;
        }
    }

    
    /**
     * Get project belong to PM
     * @param id_pm
     * 
     * @return [array] id_project
     */
    public function getProjectByPM($id_pm){
        try{
            return DB::table('project')->where("id_pm",$id_pm)->select('id')->get();
        }catch(Exception $ex){
            throw $ex;
        }
    }
}