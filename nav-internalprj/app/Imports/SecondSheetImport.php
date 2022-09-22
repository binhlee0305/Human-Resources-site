<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Facades\DB;

set_time_limit(300);
class SecondSheetImport implements ToCollection, WithCalculatedFormulas
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $dataProject = $this->readDataEffort($collection);
        
        $dataProjectList = $this->getDataExcel($dataProject);

        //Insert data project
        $setProject = $this->setDataProject($dataProjectList);
        $removeProjectExisted = $this->removeProjectExisted($setProject);
        $getDataProject = $this->getDataProject($removeProjectExisted);
        //Insert data work_on
        $setWorkOn = $this->setWorkOn($dataProjectList);
        $removeWorkOnExisted = $this->removeWorkOnExisted($setWorkOn);
        $getWorkOn = $this->getWorkOn($removeWorkOnExisted);

        //Insert data work_hour
        $setWorkHour = $this->setWorkHour($dataProjectList, $removeWorkOnExisted);
        $getWorkHour = $this->getWorkHour($setWorkHour);
    }

    //Insert data project
    public function getDataProject($dataProjectList)
    {
        $insertProject = DB::table('project')->insert($dataProjectList);
        return $insertProject;
    }

    public function setDataProject($dataProjectList)
    {
        $dbUser = DB::select('SELECT * FROM users where username = "admin"');
        $dbClient = DB::select('SELECT * FROM client where name = "Other"');
        $timeProjectList = $this->getStartDateEndDate($dataProjectList);
        $dataProject = [];
        $count = 0;
        foreach ($dataProjectList as $project) {
            $listProject = [];
            $listProject['id'] = $project['idProject'];
            $listProject['name'] = $project['nameProject'];
            $listProject['start_date'] = $timeProjectList[$count]['start_date'];
            $listProject['end_date'] = $timeProjectList[$count]['end_date'];
            $listProject['total_effort'] = $project['Commitment']*8;
            $listProject['id_client'] = $dbClient[0]->id;
            $listProject['id_pm'] = $dbUser[0]->id;
            $count++;
            array_push($dataProject, $listProject);
        }

        return $dataProject;
    }
    /**Validation project */
    public function removeProjectExisted($dataProjectList)
    {
        $dataProjectExisted = DB::select(DB::raw('SELECT id,name FROM project'));
        
        $arrProject = [];
        for ($i = 0; $i < count($dataProjectList); $i++) {
            foreach ($dataProjectExisted as $keyProject) {
                if ($dataProjectList[$i]['id'] == $keyProject->id || $dataProjectList[$i]['name'] == $keyProject->name) {
                    array_push($arrProject, $i);
                }
            }
        }
        $projectExisted = [];
        foreach ($arrProject as $key => $value) {
            array_push($projectExisted, $dataProjectList[$value]);
            unset($dataProjectList[$value]);
        }

        $dataProjectList = array_values($dataProjectList);
        return $dataProjectList;
    }

    //Insert data work_on
    public function getWorkOn($dataProjectList)
    {
        $insertWorkOn = DB::table('works_on')->insert($dataProjectList);
        return $insertWorkOn;
    }

    public function setWorkOn($dataProjectList)
    {
        $newId = DB::table('works_on')->max('id');
        $dataWorkOn = [];
        foreach ($dataProjectList as $key => $value) {
            foreach ($value["employee"] as $emp) {
                $newId++;
                $listWorkOn = [];
                $listWorkOn['id'] = $newId;
                $listWorkOn['id_dev'] = $emp['idEmp'];
                $listWorkOn['type'] = $emp['typeEmp'];
                $listWorkOn['id_project'] = $value['idProject'];
                array_push($dataWorkOn, $listWorkOn);
            }
        }
        return $dataWorkOn;
    }
    /**Validation works_on */
    public function removeWorkOnExisted($setWorkOn)
    {
        $dataWorkOnExisted = DB::select(DB::raw('SELECT id_dev,id_project FROM works_on'));
        $arrWorkOn = [];
        for ($i = 0; $i < count($setWorkOn); $i++) {
            foreach ($dataWorkOnExisted as $keyWorkOn) {
                if ($setWorkOn[$i]['id_dev'] == $keyWorkOn->id_dev && $setWorkOn[$i]['id_project'] == $keyWorkOn->id_project) {
                    array_push($arrWorkOn, $i);
                }
            }
        }
        $workOnExisted = [];
        foreach ($arrWorkOn as $key => $value) {
            array_push($workOnExisted, $setWorkOn[$value]);
            unset($setWorkOn[$value]);
        }
        $setWorkOn = array_values($setWorkOn);
        return $setWorkOn;
    }
    //Insert data work_hour
    public function getWorkHour($dataProjectList)
    {
        $insertWorkHour = DB::table('works_hour')->insert($dataProjectList);
        return $insertWorkHour;
    }
    public function setWorkHour($dataProjectList, $dataWorkOn)
    {
        $dataWorkHour = [];

        if (count($dataWorkOn) > 0) {
            ////reset index theo thứ tự của dataWorkOn
            $dataWorkOnChange = array_values($dataWorkOn);

            ///lấy id_workon của từng nhân viên
            $arrayIDWO = [];
            foreach ($dataWorkOnChange as $id) {
                array_push($arrayIDWO, $id['id']);
            }

            ///TODO: lấy id_emp add vào chung với id_project 
            /// lấy id_project của từng dataWorkOn
            $idProjectAdded = [];
            for ($i = 0; $i < count($dataWorkOnChange); $i++) {
                array_push($idProjectAdded, $dataWorkOnChange[$i]["id_project"]);
            }

            // xóa id_project được add bị trùng
            if (count($idProjectAdded) == 1) {
                $idProjectAdded = $idProjectAdded;
            } else {
                $idProjectAddedChecked = array_unique($idProjectAdded);

                $idProjectAddedChecked = array_values($idProjectAddedChecked);
            }

            /// xóa dataProjectList bị trùng
            $dataProjectListChecked = [];
            for ($i = 0; $i < count($dataProjectList); $i++) {
                foreach ($idProjectAddedChecked as $keyAdd => $idProject) {
                    if ($dataProjectList[$i]["idProject"] == $idProject) {
                        array_push($dataProjectListChecked, $dataProjectList[$i]);
                    }
                }
            }
            $dataProjectListChecked = array_values($dataProjectListChecked);


            /////set data Work_hour//////////
            $count = 0;
            foreach ($dataProjectListChecked as $key => $value) {
                foreach ($value['employee'] as $employee => $emp) {
                    $count++;
                    foreach ($emp as $date => $effort) {
                        if ($date != "typeEmp" && $date != "idEmp" && $date != "nameEmp") {
                            if ($effort != 0) {
                                $listWorkHour = [];
                                $listWorkHour['id_works_on'] = $arrayIDWO[$count - 1];
                                $listWorkHour['week'] = $date;
                                $listWorkHour['hour'] = $effort;
                                array_push($dataWorkHour, $listWorkHour);
                            }
                        }
                    }
                }
            }
        }

        return $dataWorkHour;
    }

    public function getStartDateEndDate($dataProjectList)
    {
        $arrTimeListProject = $this->timeProject($dataProjectList);
        $arrayTimeProject = [];
        for ($i = 0; $i <= count($arrTimeListProject) - 1; $i++) {
            $date = [];
            //trường hợp project có 1 employee
            if (count($arrTimeListProject[$i]) == 1) {
                $startDate = $arrTimeListProject[$i][0][0];
                $endDate = $arrTimeListProject[$i][0][count($arrTimeListProject[$i][0]) - 1];
                $date['start_date'] = $startDate;
                $date['end_date'] = $endDate;
            } else {
                // trường hợp project có 2 employee trở lên
                $startOld = $arrTimeListProject[$i][0][0];
                $endOld = $arrTimeListProject[$i][0][count($arrTimeListProject[$i][0]) - 1];
                for ($k = 0; $k <= count($arrTimeListProject[$i]) - 2; $k++) {

                    $startNew = $arrTimeListProject[$i][$k + 1][0];

                    $startDate = $startOld;

                    $boolStart = $this->compareTimeProject($startOld, $startNew);
                    if ($boolStart == true) {
                        $startDate = $startNew;
                    }

                    $endNew = $arrTimeListProject[$i][$k + 1][count($arrTimeListProject[$i][$k + 1]) - 1];
                    $endDate = $endOld;

                    $boolEnd = $this->compareTimeProject($endOld, $endNew);
                    if ($boolEnd == false) {
                        $endDate = $endNew;
                    }
                    $date['start_date'] = $startDate;
                    $date['end_date'] = $endDate;
                }
            }
            array_push($arrayTimeProject, $date);
        }

        return $arrayTimeProject;
    }



    public function timeProject($dataProjectList)
    {
        
        $arrTimeListProject = array();
        foreach ($dataProjectList as $key => $value) {
            $arrTimeProject = array();
            foreach ($value['employee'] as $employee => $emp) {
                $arrTimeEmp = array();
                foreach ($emp as $date => $effort) {
                    if ($date != "typeEmp" && $date != "idEmp" && $date != "nameEmp") {
                        // dd($date,$effort);
                        if ($effort != 0) {
                            array_push($arrTimeEmp, $date);
                        }
                    }
                }
                array_push($arrTimeProject, $arrTimeEmp);
            }
            array_push($arrTimeListProject, $arrTimeProject);
        }
        return $arrTimeListProject;
    }

    public function compareTimeProject($date1, $date2)
    {
        $dateRender = explode('-', $date1);
        $dateOut = explode('-', $date2);
        $bool = true;
        if (intval($dateRender[1]) > intval($dateOut[1])) {
            $bool = true;
        } else if (intval($dateRender[1]) == intval($dateOut[1])) {
            if (intval($dateRender[2]) > intval($dateOut[2])) {
                $bool = true;
            } else {
                $bool = false;
            }
        } else {
            $bool = false;
        }
        return $bool;
    }

    public function listMonday()
    {
        $currentYear = Carbon::now()->year;
        $previousYear = $currentYear - 1;

        $startDate = Carbon::create($previousYear, 12, 31)->toDateTimeString();
        $endDate = Carbon::create($currentYear, 12, 31)->toDateTimeString();


        $mondays = new \DatePeriod(
            Carbon::create($startDate)->startOfWeek(),
            CarbonInterval::week(),
            Carbon::create($endDate)->addWeek(1)->startOfWeek(),
        );

        $listMonday = array();
        foreach ($mondays as $monday) {
            array_push($listMonday, $monday->format('y-m-d'));
        }
        return $listMonday;
    }

    public function readDataEffort($collection)
    {
        $listMonday = $this->listMonday();
        $dataProject = array();
        foreach ($collection as $key => $value) {
            $project = array();
            if ($key == 3) {
                for ($i = 0; $i < 10; $i++) {
                    if ($i >= 5 && $i <= 9) {;
                    } else {
                        array_push($project, $value[$i]);
                    }
                }
                $project = array_merge($project, $listMonday);
            } else {
                if ($key > 3) {
                    for ($i = 0; $i < count($value); $i++) {
                        if ($i >= 5 && $i <= 9) {;
                        } else {
                            if ($i > 9 ) {
                                array_push($project, ($value[$i] * 40));
                            } else {
                                array_push($project, $value[$i]);
                            }
                        }
                    }
                }
            }
            array_push($dataProject, $project);
            unset($dataProject[0], $dataProject[1], $dataProject[2], $dataProject[3]);
            array_values($dataProject);
        }
        return $dataProject;
    }

    public function getDataExcel($dataProject)
    {
        $listMonday = $this->listMonday();
        foreach ($dataProject as $key => $value) {
            if ($dataProject[$key][0] == "Make a copy from the lines above") {
                unset($dataProject[$key]);
            } else {
                if (is_null($dataProject[$key][0]) && is_null($dataProject[$key][1]) && $dataProject[$key][2] == "" && is_null($dataProject[$key][3])) {
                    unset($dataProject[$key]);
                }
            }
        }
        $projectList = [];
        $empProject = [];
        foreach ($dataProject as $key => $value) {
            if (is_null($value[0])) {
                $emp = [];
                $emp["idEmp"] = $value[1];
                $emp["nameEmp"] = $value[2];
                $emp["typeEmp"] = $value[4];
                $count = 5;
                foreach ($listMonday as $index => $day) {
                    $emp[$day] = $value[$count];
                    $count++;
                }
                array_push($empProject, $emp);
                $projectList[count($projectList) - 1]["employee"] = $empProject;
            } else {
                //
                $empProject = [];
                $project = [];
                $project["idProject"] = $value[0];
                $project["nameProject"] = $value[1];
                $project["Commitment"] = $value[4];
                array_push($projectList, $project);
            }
        }
        foreach ($projectList as $key =>$value){
            if(!isset($value['employee'])){
                unset($projectList[$key]);
            }
        }
        $projectList = array_values($projectList);

        return $projectList;
    }
}
