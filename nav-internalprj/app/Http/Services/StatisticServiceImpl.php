<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\DB;

class StatisticServiceImpl implements StatisticService
{

    /**
     * Get new employee and % higher than last month
     * 
     * @return [array("number"=>int,"percent"=>int)]
     */
    public function getNewEmployee()
    {
        //New employee within a month
        $newEmp = DB::select(
            "SELECT
                COUNT(*) as num
            FROM
                users
            WHERE
                MONTH(CURRENT_DATE) = MONTH(join_date) AND YEAR(CURRENT_DATE) = YEAR(join_date) AND id LIKE 'C9%'"
        );

        $newEmp = $newEmp[0]->num;

        //Percent new employee compare to the previous month
        $newEmplastMonth = DB::select(
            "SELECT
                COUNT(*) as num
            FROM
                users
            WHERE
            MONTH(CURRENT_DATE) - 1 = MONTH(join_date) AND YEAR(CURRENT_DATE) = YEAR(join_date) AND id LIKE 'C9%'"

        );

        //increase_employee last month
        $newEmplastMonth = $newEmplastMonth[0]->num;
        //Handle division by zero
        if ($newEmplastMonth == 0) {
            if ($newEmp == 0)
                $percentEmp = 0;
            else
                $percentEmp = 100;
        } else {
            if ($newEmp == 0)
                $percentEmp = 0;
            else
                $percentEmp = ($newEmp / $newEmplastMonth - 1) * 100;
        }
        $percentEmp = (int)$percentEmp;

        //percent >= 0 => state = higher, percent < 0 => state = lower 
        if ($percentEmp >= 0) {
            $state = "higher";
        } else {
            $state = "lower";
        }

        return ["number" => $newEmp, "percent" => $percentEmp, "state" => $state];
    }

    /**
     * Get Total employee and % higher than last month
     * 
     * @return [array("number"=>int,"percent"=>int)]
     */
    public function getTotalEmployee()
    {
        //Total employee at the moment
        $totalEmp = DB::select(
            "SELECT
            COUNT(*) AS num
            FROM
                users
            WHERE
                id LIKE 'C9%'
            "
        );
        $totalEmp = $totalEmp[0]->num;

        //Total employee last month
        $totalEmplastMonth = DB::select(
            "SELECT
            COUNT(*) AS num
            FROM
                users
            WHERE
                id LIKE 'C9%' AND DATEDIFF(:date,join_date)> 0
            ",
            [
                ":date" => Carbon::now()->isoFormat("YYYY-MM-01")
            ]
        );
        $totalEmplastMonth = $totalEmplastMonth[0]->num;

        //Handle division by zero
        if ($totalEmplastMonth == 0) {
            if ($totalEmp == 0)
                $percentEmp = 0;
            else
                $percentEmp = 100;
        } else {
            if ($totalEmp == 0)
                $percentEmp = 0;
            else
                $percentEmp = ($totalEmp / $totalEmplastMonth - 1) * 100;
        }
        $percentEmp = (int)$percentEmp;

        //get state (higher|lower)
        if ($percentEmp >= 0) {
            $state = "higher";
        } else {
            $state = "lower";
        }

        return ["number" => $totalEmp, "percent" => $percentEmp, "state" => $state];
    }

    /**
     * Get Recent Projects
     * Condition: Projects have to unexpired
     * 
     * @return [int]
     */
    public function getTotalProject()
    {
        $project = Project::where('end_date', '>=', Carbon::now())->get()->count();

        return $project;
    }

    /**
     * Get Total Clients
     * 
     * @return [int]
     */
    public function getTotalClient()
    {
        $client = Client::all()->count();

        return $client;
    }

    /**
     * Get object for Resource Usage
     *
     * 
     * @return [array] 
     */

    //ham lay thu 2 dau tien cua thang truoc den thu 2 cuoi cung cua thang sau
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
    
    public function getResourceUsage()
    {
        try {
            $lockAccount = config("web.config.lock_account");

            $mondayOfWeek = $this->getFirstDayOfWeek("monday");

            array_push($mondayOfWeek,Carbon::parse($mondayOfWeek[count($mondayOfWeek) - 1])->addDays(7)->format('Y-m-d'));

            $renderQuery = [];
            for ($i = 0; $i < count($mondayOfWeek) - 1; $i++) {
                $query = "
                SELECT 
                ('" . $mondayOfWeek[$i] . "') AS date, 
                (SELECT IFNULL(COUNT(us.id),0) 
                    FROM  `users` us
                    WHERE us.`privillege` <> '" . $lockAccount . "' AND us.`join_date` <= '" . $mondayOfWeek[$i] . "' 
                    AND (us.`out_date` >= '" . $mondayOfWeek[$i] . "' OR us.`out_date` IS NULL)
                )AS `market1`,
                (SELECT IFNULL(SUM(wh1.hour) / 40,0)
                    FROM `works_hour` wh1
                    LEFT JOIN `works_on` wo
                    ON 
                        wh1.`id_works_on` = wo.`id`
                    WHERE
                        wh1.`week` = '" . $mondayOfWeek[$i] . "' AND wo.`type` IN('B', 'S', 'N') 
                )AS `market2`,
                    (SELECT
                        IFNULL(SUM(wh2.hour) / 40,0)
                    FROM 
                        `works_hour` wh2
                    LEFT JOIN `works_on` wo
                    ON
                        wh2.`id_works_on` = wo.`id`
                    WHERE
                        wh2.`week` = '" . $mondayOfWeek[$i] . "' AND wo.`type` IN('B', 'S') AND wo.`id_dev` LIKE 'C9%'
                )AS `market3`,
                    (SELECT
                            IFNULL(SUM(wh3.hour) / 40,0)
                        FROM
                            `works_hour` AS `wh3`
                        LEFT JOIN `works_on` AS `wo`
                        ON
                            wh3.`id_works_on` = wo.`id`
                        WHERE
                            wh3.`week` = '" . $mondayOfWeek[$i] . "' AND wo.`type` IN('B')
                    ) AS `market4`
                 ";
                 
                $data = DB::select(DB::raw($query));
                array_push($renderQuery, $data);
            }
            $dataTable = [];
            for ($j = 0; $j < count($renderQuery); $j++) {
                array_push($dataTable, $renderQuery[$j][0]);
            }

            return $dataTable;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function getFirstDayOfMonth($startDate, $endDate)
    {

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


    //lay getResource theo start date , end date
    public function getResourceUsageStartEnd($request)
    {
        try {
            $lockAccount = config("web.config.lock_account");

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $mondayOfMonth = $this->getFirstDayOfMonth($startDate, $endDate);

            array_push($mondayOfMonth, Carbon::parse($mondayOfMonth[count($mondayOfMonth) - 1])->addDays(7)->format('Y-m-d'));

            $renderQuery = [];
            for ($i = 0; $i < count($mondayOfMonth) - 1; $i++) {
                $query = "
                SELECT 
                ('" . $mondayOfMonth[$i] . "') AS date, 
                (SELECT IFNULL(COUNT(us.id),0) 
                    FROM  `users` us
                    WHERE us.`privillege` <> '" . $lockAccount . "' AND us.`join_date` <= '" . $mondayOfMonth[$i] . "' 
                    AND (us.`out_date` >= '" . $mondayOfMonth[$i] . "' OR us.`out_date` IS NULL)
                )AS `market1`,
                (SELECT IFNULL(SUM(wh1.hour) / 40,0)
                    FROM `works_hour` wh1
                    LEFT JOIN `works_on` wo
                    ON 
                        wh1.`id_works_on` = wo.`id`
                    WHERE
                        wh1.`week` = '" . $mondayOfMonth[$i] . "' AND wo.`type` IN('B', 'S', 'N')   
                )AS `market2`,
                    (SELECT
                        IFNULL(SUM(wh2.hour) / 40,0)
                    FROM 
                        `works_hour` wh2
                    LEFT JOIN `works_on` wo
                    ON
                        wh2.`id_works_on` = wo.`id`
                    WHERE
                        wh2.`week` = '" . $mondayOfMonth[$i] . "' AND wo.`type` IN('B', 'S') AND wo.`id_dev` LIKE 'C9%'
                )AS `market3`,
                    (SELECT
                            IFNULL(SUM(wh3.hour) / 40,0)
                        FROM
                            `works_hour` AS `wh3`
                        LEFT JOIN `works_on` AS `wo`
                        ON
                            wh3.`id_works_on` = wo.`id`
                        WHERE
                            wh3.`week` = '" . $mondayOfMonth[$i] . "' AND wo.`type` IN('B')
                    ) AS `market4`
                 ";
                
                $data = DB::select(DB::raw($query));
                array_push($renderQuery, $data);
            }
            $dataTable = [];

            for ($j = 0; $j < count($renderQuery); $j++) {
                array_push($dataTable, $renderQuery[$j][0]);
            }

            return $dataTable;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get object for Project Effort
     *
     * @return [array] 
     */
    public function getProjectEffort()
    {
        try {
            $data = DB::select(
                DB::raw(
                    "SELECT
                p.name AS name,
                p.total_effort/8 AS effort1,
                (
                SELECT
                    IFNULL(SUM(wh1.hour) / 8, 0)
                FROM
                    works_hour AS wh1
                INNER JOIN works_on AS wo1
                ON
                    wo1.id = wh1.id_works_on
                WHERE
                    wo1.id_project = p.id AND wo1.type IN('B', 'S')
                ) AS effort2,
                (
                    SELECT
                        IFNULL(SUM(wh2.hour) / 8,0)
                    FROM
                        works_hour AS wh2
                    INNER JOIN works_on AS wo2
                    ON
                        wo2.id = wh2.id_works_on
                    WHERE
                        wo2.id_project = p.id AND wo2.type IN('B')
                ) AS effort3
                FROM
                    project AS p
                ORDER BY
                    p.start_date
                DESC
                LIMIT 5"
                )

            );
            return $data;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get object for Employee Structure
     * 
     * @return [array[array]]
     */
    public function getEmployeeStructure()
    {
        try {

            //Get total employees who are male
            $male = DB::table('users')->where("gender", "male")->count();

            //Get total employees who are female
            $female = DB::table('users')->where("gender", "female")->count();

            return [['Male', $male], ['Female', $female]];
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get Project Statistic 
     * 
     * @return [array[array]]
     */
    public function getProjStatistic()
    {
        try {
            //project statistic
            $presale["num"] = Project::where('status', 'Pre-sale')->count();
            $active["num"] = Project::where('status', 'Active')->count();
            $pending["num"] = Project::where('status', 'Pending')->count();
            $closed["num"] = Project::where('status', 'Closed')->count();

            $total = $presale["num"] + $active["num"] + $pending["num"] + $closed["num"];

            if ($total == 0) {
                $presale["percent"] = 0;
                $active["percent"] = 0;
                $pending["percent"] = 0;
                $closed["percent"] = 0;
            } else {
                $presale["percent"] = (int)($presale["num"] / $total * 100);
                $active["percent"] = (int)($active["num"] / $total * 100);
                $pending["percent"] = (int)($pending["num"] / $total * 100);
                $closed["percent"] = 100 - ($presale["percent"] + $active["percent"] + $pending["percent"]);
            }
            //get top 3 client
            $client = DB::table('client')
                ->join('project', 'project.id_client', '=', 'client.id')
                ->select('client.name as name', DB::raw('COUNT(project.id) as num'))
                ->groupBy('client.id')
                ->orderByDesc('num')
                ->limit(3)
                ->get();

            $totalProject = Project::count();

            $sumTop3 = 0;
            $sumPercentTop3 = 0;

            foreach ($client as $cl) {
                if ($totalProject == 0)
                    $cl->percent = 0;
                else
                    $cl->percent = (int)($cl->num / $totalProject * 100);
                $sumPercentTop3 += $cl->percent;
                $sumTop3 += $cl->num;
            }

            //other client
            $clientOther = new \stdClass();
            $clientOther->name = "Other";
            $clientOther->num = $totalProject - $sumTop3;
            if ($totalProject == 0)
                $clientOther->percent = 0;
            else
                $clientOther->percent = 100 - $sumPercentTop3;

            return [
                "presale" => $presale,
                "active" => $active,
                "pending" => $pending,
                "closed" => $closed,
                "client" => $client,
                "otherClient" => $clientOther
            ];
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
