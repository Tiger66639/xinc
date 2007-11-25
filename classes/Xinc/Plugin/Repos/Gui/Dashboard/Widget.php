<?php
/**
 * This interface represents a publishing mechanism to publish build results
 * 
 * @package Xinc.Plugin
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 David Ellis, One Degree Square
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

require_once 'Xinc/Gui/Widget/Interface.php';
require_once 'Xinc/Build/Iterator.php';
require_once 'Xinc/Project.php';
require_once 'Xinc/Build.php';

class Xinc_Plugin_Repos_Gui_Dashboard_Widget implements Xinc_Gui_Widget_Interface
{
    protected $_plugin;
    private $_extensions = array();
    public $projects = array();
    
    public $builds;
    
    public function __construct(Xinc_Plugin_Interface &$plugin)
    {
        $this->_plugin = $plugin;
        $this->builds = new Xinc_Build_Iterator();
    }
    
    public function handleEvent($eventId)
    {
        switch ($eventId) {
            case Xinc_Gui_Event::PAGE_LOAD: 
                    
                    $handler = Xinc_Gui_Handler::getInstance();
                    $statusDir = $handler->getStatusDir();
                    $dir = opendir($statusDir);
                    while ($file = readdir($dir)) {
                        $project = array();
                        $fullfile = $statusDir . DIRECTORY_SEPARATOR . $file;
                        
                        if (!in_array($file, array('.', '..')) && is_dir($fullfile)) {
                            $project['name']=$file;
                            $statusfile = $fullfile . DIRECTORY_SEPARATOR . 'build.ser';
                            //$xincProject = $fullfile . DIRECTORY_SEPARATOR . '.xinc';
                            
                            if (file_exists($statusfile)) {
                                //$ini = parse_ini_file($statusfile, true);
                                $object = unserialize(file_get_contents($statusfile));
                                //var_dump($object);
                                
                                //$project['build.status'] = $ini['build.status'];
                                //$project['build.label'] = isset($ini['build.label'])?$ini['build.label']:'';
                                //$project['build.time'] = $ini['build.time'];
                                $this->builds->add($object);
                            } else if (file_exists($xincProject)) {
                                $project['build.status'] = -10;
                                $project['build.time'] = 0;
                                $project['build.label'] = '';
                                $this->projects[]=$project;
                            }
                            
                            
                            
                        }
                    }
                    include 'view/overview.phtml';
                    
                break;
            default:
                break;
        }
    }
    public function registerMainMenu()
    {
        return true;
    }
    public function getTitle()
    {
        return 'Dashboard';
    }
    public function getPaths()
    {
        return array('/dashboard', '/dashboard/');
    }
    public function init()
    {
        
    }
    public function registerExtension($extension, $callback)
    {
        $this->_extensions[$extension] = $callback;
    }
    public function getExtensionPoints()
    {
        return array();
    }
}