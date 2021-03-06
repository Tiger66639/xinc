<?php
/**
 * Xinc - Continuous Integration.
 * Queue that is holding all the builds
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Build
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
 * @link      http://xincplus.sourceforge.net
 */

require_once 'Xinc/Build/Queue/Interface.php';
require_once 'Xinc/Build/Iterator.php';


class Xinc_Build_Queue implements Xinc_Build_Queue_Interface
{
    /**
     * @var Xinc_Build_Iterator
     */
    private $builds;

    private $lastBuild;

    /**
     * @var array
     */
    private $queue = array();

    /**
     * constructor for build queue
     *
     */
    public function __construct()
    {
        $this->builds = new Xinc_Build_Iterator();
    }

    /**
     * adds a build to the queue
     *
     * @param Xinc_Build_Interface $build
     */
    public function addBuild(Xinc_Build_Interface $build)
    {
        $this->builds->add($build);
    }

    /**
     * Adds a number of builds to the queue
     *
     * @param Xinc_Build_Iterator $builds
     */
    public function addBuilds(Xinc_Build_Iterator $builds)
    {
        while ($builds->hasNext()) {
            $this->builds->add($builds->next());
        }
    }

    private function _handleBuildConfig(Xinc_Build_Interface $build)
    {
        $timezone = $build->getConfigDirective('timezone');
        if ($timezone !== null) {
            Xinc_Timezone::set($timezone);
        } else {
            Xinc_Timezone::reset();
        }
    }

    /**
     * Returns the next build time of all the builds scheduled
     * in this queue
     *
     * @return integer unixtimestamp
     */
    public function getNextBuildTime()
    {
        $nextBuildTime = null;
        /**
         * Xinc_Build_Interface
         */
        $build = null;
        while ($this->builds->hasNext()) {
            $build = $this->builds->next();
            $this->_handleBuildConfig($build);
            $buildTime = $build->getNextBuildTime();
            if ($buildTime <= $nextBuildTime || $nextBuildTime === null) {
                if ($build->getStatus() != Xinc_Build_Interface::STOPPED) {
                    if ($buildTime !== null) {
                        $this->addBuild2Queue($build);
                    }
                    $nextBuildTime = $buildTime;
                }
            }
        }
        $this->builds->rewind();
        return $nextBuildTime;
    }

    public function addBuild2Queue($build)
    {
        if (!$build->isQueued()) {
            $this->queue[] = $build;
            $build->enqueue();
        }
        usort($this->queue, array(&$this, 'sortQueue'));
    }

    /**
     * Sorts the builds in the queue by buildtime
     *
     * @param Xinc_Build_Interface $a
     * @param Xinc_Build_Interface $b
     *
     * @return integer
     */
    public function sortQueue($a, $b)
    {
        $this->_handleBuildConfig($a);
        $buildTimeA = $a->getNextBuildTime();

        $this->_handleBuildConfig($b);
        $buildTimeB = $b->getNextBuildTime();

        if ($buildTimeA == $buildTimeB) return 0;
        return $buildTimeA<$buildTimeB ? -1:1;
    }

    /**
     * Removes the next scheduled build from the queue
     * and returns it
     *
     * @return Xinc_Build_Interface
     */
    public function getNextBuild()
    {
        usort($this->queue, array(&$this, 'sortQueue'));
        if (isset($this->queue[0])) {
            $this->_handleBuildConfig($this->queue[0]);
            if ($this->queue[0]->getNextBuildTime() <= time()) {
                $build = array_shift($this->queue);
                $build->dequeue();
                return $build;
            }
        }
        return null;
    }
}
