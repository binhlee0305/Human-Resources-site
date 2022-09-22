<?php

namespace App\Http\Services;

use App\Exceptions\QueryException;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Psr7\Request;

class EffortUsageServiceImpl implements EffortUsageService
{
    public function getFirstDayOfWeek($day)
    {
        //Get every single $day of current month, previous month and next month.
        $mondays = new \DatePeriod(
            Carbon::parse("first " . $day . " of previous month"),
            CarbonInterval::week(),
            Carbon::parse("first " . $day . " of next month + 1 month")
        );

        //format list mondays follow 'Y-m-d'
        $listMonday = array();
        foreach ($mondays as $monday) {
            array_push($listMonday, $monday->format('Y-m-d'));
        }
        return $listMonday;
    }

    public function getData()
    {
        //get monday 
        $data = $this->getFirstDayOfWeek("monday");

        // create query statement from every monday.
        $query =   "SELECT us.`id` as 'EmpCode', us.`name` as 'EmpName' ";
        array_push($data, Carbon::parse($data[count($data) - 1])->addDays(7)->format('Y-m-d'));
        for ($i = 0; $i < count($data) - 1; $i++) {
            $query .=  ", (SELECT ROUND(SUM(wh.hour)/40*100) FROM `users` subus 
                    INNER JOIN `works_on` wo ON  wo.`id_dev` = subus.`id`
                    INNER JOIN `works_hour` wh ON wo.`id` = wh.`id_works_on`
                    WHERE subus.id = us.`id` AND us.`username` = subus.`username`
                    AND subus.`join_date` <= wh.`week` AND (subus.`out_date` >= wh.`week` OR subus.`out_date` IS NULL)
                    AND wh.`week` >= '" . $data[$i] . "' AND wh.`week` < '" . $data[$i + 1] . "'
                    ) AS `" . Carbon::parse($data[$i])->format('d/m') . "` ";
        }
        $query .= "FROM `users` us 
                    WHERE `us`.`privillege` <> 1
                            GROUP BY us.`id`, us.`username`
                            ORDER BY us.`username` ASC";
    
        $renderQuery = DB::select(DB::raw($query));
        
        $empOutDate = DB::select('SELECT id, out_date  FROM users where out_date is not null');
        foreach ($empOutDate as $emp){
            $emp->out_date = Carbon::parse($emp->out_date)->format('d/m');
        }
        // So sánh EmpCode và id trong $empOutDate 
        // So sánh $emp->out_date < key có trong $render
        foreach ($renderQuery as $render){
            $keys = array_keys((array) $render);
            foreach ($empOutDate as $emp){
                if($render->EmpCode == $emp->id){
                    for($i = 2; $i < count($keys); $i++){
                        $compareDate = $this->compareDate($keys[$i],$emp->out_date);
                        if($compareDate == true){
                            $key = $keys[$i];
                            $render->$key = 'E';
                        }
                    }
                }
            }
        }
        
        if (!$renderQuery) {
            throw new QueryException();
        }
        return $renderQuery;
    }
    
    public function getFirstDayOfMonth($startDate, $endDate) {
       
        $mondays = new \DatePeriod(
            Carbon::create($startDate)->startOfWeek(),
            CarbonInterval::week(),
            Carbon::create($endDate)->addWeek(1)->startOfWeek(),
        );

        //format list mondays follow 'Y-m-d'
        $listMonday = array();
        foreach ($mondays as $monday) {
            array_push($listMonday, $monday->format('Y-m-d'));
        }
        return $listMonday;
    }

    public function getSearchEffortUsage($request)
    {
        $startDate = $request->input('fromDate');
        $endDate = $request->input('toDate');

        $data = $this->getFirstDayOfMonth($startDate,$endDate);

        $query =   "SELECT us.`id` as 'EmpCode', us.`name` as 'EmpName' ";
        array_push($data, Carbon::parse($data[count($data) - 1])->addDays(7)->format('Y-m-d'));
        for ($i = 0; $i < count($data) - 1; $i++) {
            $query .=  ", (SELECT ROUND(SUM(wh.hour)/40*100) FROM `users` subus 
                    INNER JOIN `works_on` wo ON  wo.`id_dev` = subus.`id`
                    INNER JOIN `works_hour` wh ON wo.`id` = wh.`id_works_on`
                    WHERE subus.id = us.`id` AND us.`username` = subus.`username`
                    AND subus.`join_date` <= wh.`week` AND (subus.`out_date` >= wh.`week` OR subus.`out_date` IS NULL)
                    AND wh.`week` >= '" . $data[$i] . "' AND wh.`week` < '" . $data[$i + 1] . "'
                    ) AS `" . Carbon::parse($data[$i])->format('d/m') . "` ";
        }
        $query .= "FROM `users` us 
                    WHERE `us`.`privillege` <> 1
                            GROUP BY us.`id`, us.`username`
                            ORDER BY us.`username` ASC";
        $renderQuery = DB::select(DB::raw($query));

        $empOutDate = DB::select('SELECT id, out_date  FROM users where out_date is not null');
        foreach ($empOutDate as $emp){
            $emp->out_date = Carbon::parse($emp->out_date)->format('d/m');
        }

        foreach ($renderQuery as $render){
            $keys = array_keys((array) $render);
            foreach ($empOutDate as $emp){
                if($render->EmpCode == $emp->id){
                    for($i = 2; $i < count($keys); $i++){
                        $compareDate = $this->compareDate($keys[$i],$emp->out_date);
                        if($compareDate == true){
                            $key = $keys[$i];
                            $render->$key = 'E';
                        }
                    }
                }
            }
        }
        if (!$renderQuery) {
            throw new QueryException();
        }
        return $renderQuery;
    }

    // So sánh ngày trong works_hour và out_date
    public function compareDate($dateRender, $dateOut)
    {
        $dateRender = explode('/', $dateRender);
        $dateOut = explode('/', $dateOut);

        if(intval($dateRender[1]) > intval($dateOut[1])){
            return true;
        }
        else if (intval($dateRender[1]) == intval($dateOut[1])){
            if (intval($dateRender[0]) > intval($dateOut[0])){
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }
}
