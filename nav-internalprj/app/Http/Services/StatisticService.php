<?php 
namespace App\Http\Services;

use Illuminate\Http\Request;
interface StatisticService
{
    /**
     * Get new employee and % higher than last month
     * 
     * @return [int,int]
     */
    public function getNewEmployee();

    /**
     * Get Total employee and % higher than last month
     * 
     * @return [int,int]
     */
    public function getTotalEmployee();

    /**
     * Get Recent Projects
     * 
     * @return [int]
     */
    public function getTotalProject();

    /**
     * Get Total Clients
     * 
     * @return [int]
     */
    public function getTotalClient();
    
    /**
     * Get object for Resource Usage
     *
     * 
     * @return [array] 
     */
    public function getResourceUsage();

    public function getResourceUsageStartEnd($request);

    /**
     * Get object for Project Effort
     *
     * @return [array] 
     */
    public function getProjectEffort();

    /**
     * Get object for Employee Structure
     * 
     * @return [array[array]]
     */
    public function getEmployeeStructure();

    /**
     * Get Project Statistic 
     * 
     * @return [array[array]]
     */
    public function getProjStatistic();
    
    public function getFirstDayOfWeek($day);

    public function getFirstDayOfMonth($startDate, $endDate);
    
}