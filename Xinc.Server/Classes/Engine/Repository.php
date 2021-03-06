<?php
/**
 * Xinc - Continuous Integration.
 * Repository to manage all registered Engines
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Server
 * @author    Arno Schneider <username@example.org>
 * @copyright 2007 Arno Schneider, Barcelona
 * @license   http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *            This file is part of Xinc.
 *            Xinc is free software; you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation; either version 2.1 of
 *            the License, or (at your option) any later version.
 *
 *            Xinc is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public
 *            License along with Xinc, write to the Free Software Foundation,
 *            Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link      http://code.google.com/p/xinc/
 */

namespace Xinc\Server\Engine;

use Xinc\Core\Singleton;

class Repository extends Singleton
{
    private $defaultEngine;

    private $engines = array();

    /**
     * Register a engine with the repository so that
     * builds can use it
     *
     * @param Xinc\Server\Engine\EngineInterface $engine
     * @param boolean $default
     *
     * @return boolean
     */
    public function registerEngine(EngineInterface $engine, $default = false)
    {
        $engineClass = get_class($engine);

        if (isset($this->engines[$engine->getName()]) || isset($this->engines[$engineClass])) {
            \Xinc\Core\Logger::getInstance()->error(
                'cannot load engine ' . $engineClass . ' already registered'
            );

            return false;
        }
        $this->engines[$engine->getName()] = $engine;
        $this->engines[$engineClass] = $engine;

        if ($default) {
            $this->defaultEngine = $engine;
        }

        return true;
    }

    /**
     * Returns Plugin Iterator
     *
     * @return Xinc\Core\Iterator
     */
    public function getEngines()
    {
        return new Iterator($this->engines);
    }

    /**
     * returns the specified engine
     *
     * @param string $name
     *
     * @return Xinc_Engine_Interface
     *
     * @throws Xinc\Server\Engine\Exception\NotFoundException
     */
    public function getEngine($name)
    {
        if (empty($name) && isset($this->defaultEngine)) {
            return $this->defaultEngine;
        }
        if (isset($this->engines[$name])) {
            return $this->engines[$name];
        } else {
            throw new Exception\NotFoundException($name);
        }
    }
}
