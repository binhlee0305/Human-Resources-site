<?php
namespace App\Http\Services;

interface ProjectService{
    /**
     * Get all Project
     * 
     * @return [array]
     */
    public function getProject();

    /**
     * Get all Client
     * 
     * @return [array]
     */
    public function getClient();

    /**
     * Get all project manager
     * 
     * @return [array]
     */
    public function getPM();

    /**
     * Get all dev
     * 
     * @return [array]
     */
    public function getDev();

    /**
     * Add new Project
     * 
     * @return boolean
     */
    public function addProject($input);

    /**
     * Add Dev Work on Project
     * 
     * @return boolean
     */
    public function addDevOnProject($id_project, $proj_member, $type);

    /**
     * Check id project, true if existed else false
     * @param id project
     * @return boolean
     */
    public function checkId($id);

    /**
     * Delete project
     * @param id project
     * @return boolean
     */
    public function delete($id);

    /**
     * Get project belong to PM
     * @param id_pm
     * 
     * @return [array] id_project
     */
    public function getProjectByPM($id_pm);
}