<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2026 51 Degrees Mobile Experts Limited, Davidson House,
 * Forbury Square, Reading, Berkshire, United Kingdom RG1 3EU.
 *
 * This Original Work is licensed under the European Union Public Licence
 * (EUPL) v.1.2 and is subject to its terms as set out below.
 *
 * If a copy of the EUPL was not distributed with this file, You can obtain
 * one at https://opensource.org/licenses/EUPL-1.2.
 *
 * The 'Compatible Licences' set out in the Appendix to the EUPL (as may be
 * amended by the European Commission) shall be deemed incompatible for
 * the purposes of the Work and the provisions of the compatibility
 * clause in Article 5 of the EUPL shall not apply.
 *
 * If using the Work as, or as part of, a network application, by
 * including the attribution notice(s) required under Article 5 of the EUPL
 * in the end user terms of the application under an appropriate heading,
 * such notice(s) shall fulfill the requirements of that article.
 * ********************************************************************* */

namespace fiftyone\pipeline\devicedetection\tests\classes;

// Class to track external processes for Linux only.
class Process
{
    private $pid;
    private $command;

    public function __construct($cl = false)
    {
        if ($cl) {
            $this->command = $cl;
        }
    }

    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function status()
    {
        $command = 'ps -p ' . $this->pid;
        exec($command, $op);

        return isset($op[1]);
    }

    public function start()
    {
        if ($this->command !== '') {
            $this->runCom();
        }
    }

    public function stop()
    {
        $command = 'kill ' . $this->pid;
        exec($command);

        return $this->status() === false;
    }

    private function runCom()
    {
        $command = 'nohup ' . $this->command . ' 1>/dev/null & echo $!';
        exec($command, $op);
        $this->pid = (int) $op[0];
    }
}
