<?php
namespace App\Http\Services;

interface EmployeeService{
    /**
     * Get all Employee
     * 
     * @return [array]
     */
    public function getEmployee();

    /**
     * Get all level
     * 
     * @return [array]
     */
    public function getLevel();

    /**
     * Get all type
     * 
     * @return [array]
     */
    public function getType();

    /**
     * Add employee
     * @param [array]
     * 
     * @return bool
     */
    public function add($input);

    /**
     * Check Username
     * @param username
     * 
     * @return bool
     */
    public function checkUser($username);

    /**
     * Check id
     * @param id
     * 
     * @return bool
     */
    public function checkId($id);

    /**
     * Delete user
     * @param id
     * 
     * @return bool
     */
    public function delete($id);

    /**
     * Check PM
     * @param id 
     * 
     * @return bool
     */
    public function checkPM($id);

    /**
     * Check PM
     * @param dataNewEmp
     * 
     * @return bool
     */
    public function importEmployee($dataNewEmp);

    /**
     * Check PM
     * @param dataNewEmp 
     * 
     * @return dataNewEmp
     */
    public function setDefaultNewEmp($dataNewEmp);

    /**
     * Check PM
     * @param dataNewEmp 
     * 
     * @return dataNewEmp
     */
    public function removeEmpExisted($dataNewEmp);

}