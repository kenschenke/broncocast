<?php

namespace App\Util;

use Symfony\Component\Console\Output\OutputInterface;

class PeriodicLock
{
    const FAIL_PREFIX = "FAILURE_";
    const FAIL_NOTIFY = "FAIL_NOTIFY";
    const MAX_FAILURES = 5;

    private $lockDir;
    private $lockFilename;
    private $hasLock;
    private $lockFh;

    public function __construct()
    {
        $this->hasLock = false;
        $this->lockDir = getenv('PERIODIC_LOCK_DIR');
        $this->lockFilename = "{$this->lockDir}/lockfile";
    }

    public function __destruct()
    {
        $this->Unlock();
    }

    public function ClearFailures()
    {
        $files = $this->GetFailureFiles();
        foreach ($files as $filename) {
            unlink($filename);
        }

        $filename = $this->MakeLockFilename(self::FAIL_NOTIFY);
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    private function GetFailureFiles()
    {
        $dir = opendir($this->lockDir);
        if ($dir === false) {
            return [];
        }

        $Failures = [];
        while (($entry = readdir($dir)) !== false) {
            $filename = "{$this->lockDir}/$entry";
            if (is_dir($filename)) {
                continue;
            }

            if (substr($entry, 0, strlen(self::FAIL_PREFIX)) === self::FAIL_PREFIX) {
                $Failures[] = $filename;
            }
        }

        closedir($dir);

        return $Failures;
    }

    public function IsLocked()
    {
        if (!file_exists($this->lockFilename)) {
            return false;
        }

        $fh = fopen($this->lockFilename, 'w');
        if ($fh === false) {
            return false;
        }

        $locked = flock($fh, LOCK_EX | LOCK_NB);

        if ($locked === false) {
            return true;
        }

        flock($fh, LOCK_UN);
        fclose($fh);

        return false;
    }

    public function HaveFailuresBeenNotified()
    {
        return file_exists($this->MakeLockFilename(self::FAIL_NOTIFY));
    }

    private function MakeLockFilename($name)
    {
        return getenv('PERIODIC_LOCK_DIR') . '/' . $name;
    }

    public function MarkFailure()
    {
        $files = $this->GetFailureFiles();

        $Highest = 0;
        foreach ($files as $filename) {
            $pos = strpos($filename, self::FAIL_PREFIX);
            if ($pos !== false) {
                $num = (int)substr($filename, $pos + strlen(self::FAIL_PREFIX));
                if ($num > $Highest) {
                    $Highest = $num;
                }
            }
        }

        $Highest++;
        $fh = fopen($this->MakeLockFilename(self::FAIL_PREFIX . $Highest), 'w');
        if ($fh !== false) {
            fclose($fh);
        }
    }

    public function MarkFailuresAsNotified()
    {
        $fh = fopen($this->MakeLockFilename(self::FAIL_NOTIFY), 'w');
        if ($fh !== false) {
            fclose($fh);
        }
    }

    public function Lock()
    {
        if ($this->hasLock) {
            return false;
        }

        $this->lockFh = fopen($this->lockFilename, 'w');
        if ($this->lockFh === false) {
            return false;
        }

        $locked = flock($this->lockFh, LOCK_EX | LOCK_NB);
        if ($locked === false) {
            fclose($this->lockFh);
            return false;
        }

        $this->hasLock = true;

        return true;
    }

    public function TooManyFailures()
    {
        $files = $this->GetFailureFiles();
        $failures = 0;
        foreach ($files as $filename) {
            $pos = strpos($filename, self::FAIL_PREFIX);
            if ($pos !== false) {
                $failures++;
            }

        }
        return $failures > self::MAX_FAILURES;
    }

    public function Unlock()
    {
        if (!$this->hasLock) {
            return false;
        }

        flock($this->lockFh, LOCK_UN);
        fclose($this->lockFh);

        $this->hasLock = false;

        return true;
    }
}
