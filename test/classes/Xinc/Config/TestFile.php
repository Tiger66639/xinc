<?php
/**
 * Test Class for the Xinc Project Registry
 * 
 * @package Xinc
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
require_once 'Xinc/Config/File.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Xinc_Config_TestFile extends PHPUnit_Framework_TestCase
{

    public function testConstructOkFile()
    {
        $workingdir = getcwd();
        try {
            $configFile = Xinc_Config_File::load($workingdir .'/test/resources/testSystem.xml');
            $this->assertTrue(true, 'Should not throw any exception');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Should not catch any exception');
        }
    }

    
    public function testFileNotFoundException()
    {
        $workingdir = getcwd();
        
        try {
            $configFile = Xinc_Config_File::load($workingdir .'/test/resources/testSystemDoesNotExist.xml');
            $this->assertTrue(false, 'File Does not exist, should throw an exception');
        } catch (Xinc_Config_Exception_FileNotFound $notFound) {
            $this->assertTrue(true, 'Correct exception thrown');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Should have caught ' 
                                   . 'Xinc_Config_Exception_FileNotFound '
                                   . 'but caught: ' . get_class($e));
        }
    }
    public function testConstructException()
    {
       
        $workingdir = getcwd();
        try {
            $configFile = Xinc_Config_File::load($workingdir .'/test/resources/testSystemInvalid.xml');
            $this->assertTrue(false, 'It is invalid, should throw an exception');
            
        } catch (Xinc_Config_Exception_InvalidEntry $invalidEntry) {
            $this->assertTrue(true, 'Correct exception thrown');
            
        } catch (Exception $e) {
            $this->assertTrue(false, 'Should have caught ' 
                                   . 'Xinc_Config_Exception_InvalidEntry '
                                   . 'but caught: ' . get_class($e));
        }
       
        
        
    }
}