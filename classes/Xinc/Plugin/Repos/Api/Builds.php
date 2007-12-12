<?php
/**
 * Api to get information about builds
 * 
 * @package Xinc.Plugin
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 Arno Schneider, Barcelona
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *    This file is part of Xinc.
 *    Xinc is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU Lesser General Public License as published
 *    by the Free Software Foundation; either version 2.1 of the License, or    
 *    (at your option) any later version.
 *
 *    Xinc is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Lesser General Public License for more details.
 *
 *    You should have received a copy of the GNU Lesser General Public License
 *    along with Xinc, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require_once 'Xinc/Api/Module/Interface.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension.php';

class Xinc_Plugin_Repos_Api_Builds implements Xinc_Api_Module_Interface
{
    /**
     * Enter description here...
     *
     * @var Xinc_Plugin_Interface
     */
    protected $_plugin;
    
    public function __construct(Xinc_Plugin_Interface &$plugin)
    {
        $this->_plugin = $plugin;
        
    }
    public function getName()
    {
        return 'builds';
    }
    public function getMethods()
    {
        return array('get');
    }
    public function processCall($methodName, $params = array())
    {

        switch ($methodName){
            case 'get':
                return $this->_getBuilds($params);
                break;
        }
          
       
       
    }
    
    private function _getBuilds($params)
    {
        
        $project = isset($params['p']) ? $params['p'] : null;
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        $limit = isset($params['limit']) ? (int)$params['limit'] : null;
        $builds = $this->_getHistoryBuilds($project, $start, $limit);
        $responseObject = new Xinc_Api_Response_Object();
        $responseObject->set($builds);
        return $responseObject;
    }
   
   
    
    private function _getHistoryBuilds($projectName, $start, $limit=null)
    {
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $historyFile = $statusDir . DIRECTORY_SEPARATOR . $projectName . '.history';
        $project = new Xinc_Project();
        $project->setName($projectName);
        $buildHistoryArr = unserialize(file_get_contents($historyFile));
        $totalCount = count($buildHistoryArr);
        if ($limit==null) {
            $limit = $totalCount;
        }
        /**
         * turn it upside down so the latest builds appear first
         */
        $buildHistoryArr = array_reverse($buildHistoryArr, true);
        $buildHistoryArr = array_slice($buildHistoryArr, $start, $limit, true);
        
        $builds = array();
        
        foreach ($buildHistoryArr as $buildTimestamp => $buildFileName) {
            try {
                $buildObject = Xinc_Build::unserialize($project,
                                                       $buildTimestamp,
                                                       Xinc_Gui_Handler::getInstance()->getStatusDir());
                $builds[] = array('buildtime'=>$buildObject->getBuildTime(),
                'buildtimeRaw'=>$buildObject->getBuildTime(),
                                  'label'=>$buildObject->getLabel(),
                                  'status' => $buildObject->getStatus());
            } catch (Exception $e) {
                // TODO: Handle
                
            }
            
        }
        
        //$builds = array_reverse($builds);
        
        $object = new stdClass();
        $object->totalcount = $totalCount;
        $object->builds = $builds;
        //return new Xinc_Build_Iterator($builds);
        return $object;
    }
}