<?php
/**
 * phpillow tool
 *
 * This file is part of phpillow.
 *
 * phpillow is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * phpillow is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with phpillow; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

/**
 * Basic tool handling in- end exports of CouchDB dumps.
 *
 * API and format should be compatible with couchdb-python [1].
 *
 * [1] http://code.google.com/p/couchdb-python/
 *
 * @package Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */
class phpillowTool
{
    /**
     * Construct tool
     *
     * Construct tool from database DSN (Data-Source-Name, the URL defining the
     * databases location) and an optional set of options.
     * 
     * @param mixed $dsn 
     * @param array $options 
     * @return void
     */
    public function __construct( $dsn, array $options = array() )
    {
        $this->dsn     = $dsn;
        $this->options = $options;
    }

    /**
     * Print version
     *
     * Print version of the tool, if the version flag has been set.
     * 
     * @return bool
     */
    protected function printVersion()
    {
        if ( !isset( $this->options['version'] ) )
        {
            return false;
        }

        $version = '$Revision$';
        if ( preg_match( '(\\$Revision:\\s+(?P<revision>\\d+)\\s*\\$)', $version, $match ) )
        {
            $version = 'svn-' . $match['revision'];
        }

        echo "PHPillow backup tool - version: ", $version, "\n";
        return true;
    }

    /**
     * Execute dump command
     *
     * Returns a proper status code indicating successful execution of the
     * command.
     *
     * @return int
     */
    public function dump()
    {
        if ( $this->printVersion() )
        {
            return 0;
        }
    }

    /**
     * Execute load command
     *
     * Returns a proper status code indicating successful execution of the
     * command.
     *
     * @return int
     */
    public function load()
    {
        if ( $this->printVersion() )
        {
            return 0;
        }
    }
}

