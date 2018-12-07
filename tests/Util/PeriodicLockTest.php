<?php

namespace App\Tests\Util;

use App\Util\PeriodicLock;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class PeriodicLockTest extends TestCase
{
    private $root;
    private $lockDir;

    protected function setUp()
    {
        $this->root = vfsStream::setup('home');
        $this->lockDir = $this->root->url() . '/lockdir';
        mkdir($this->lockDir);
        chmod($this->lockDir, 0777);

        putenv("PERIODIC_LOCK_DIR={$this->lockDir}");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage PERIODIC_LOCK_DIR environment variable missing
     */
    public function testLockDirNotSet()
    {
        putenv('PERIODIC_LOCK_DIR=');
        new PeriodicLock();
    }

    public function testClearFailures()
    {
        $failureFiles = [];
        for ($i = 1; $i <= 3; $i++) {
            $filename = "{$this->lockDir}/FAILURE_$i";
            $fh = fopen($filename, "w");
            fwrite($fh, "Failure $i\n");
            fclose($fh);
            $failureFiles[] = $filename;
        }

        // add a couple subdirectories
        mkdir("{$this->lockDir}/FAILURE_5");
        mkdir("{$this->lockDir}/FAILURE_6");

        // create a notification file
        $filename = "{$this->lockDir}/FAIL_NOTIFY";
        $fh = fopen($filename, "w");
        fwrite($fh, "Failure notification file\n");
        fclose($fh);
        $failureFiles[] = $filename;

        $periodicLock = new PeriodicLock();
        $periodicLock->ClearFailures();

        foreach ($failureFiles as $filename) {
            $this->assertFalse(file_exists($filename));
        }
        $this->assertTrue(is_dir("{$this->lockDir}/FAILURE_5"));
        $this->assertTrue(is_dir("{$this->lockDir}/FAILURE_6"));
    }

    public function testIsLockedWithNoLockFile()
    {
        $periodicLock = new PeriodicLock();
        $this->assertFalse($periodicLock->IsLocked());
    }

    public function testIsLockedWithLockFileNotLocked()
    {
        $periodicLock = new PeriodicLock();

        $filename = "{$this->lockDir}/lockfile";
        $fh = fopen($filename, "w");
        fwrite($fh, "lock file\n");
        fclose($fh);

        $this->assertFalse($periodicLock->IsLocked());
    }

    public function testIsLockedWithLockFileLocked()
    {
        $periodicLock = new PeriodicLock();

        $filename = "{$this->lockDir}/lockfile";
        $fh = fopen($filename, "w");
        flock($fh, LOCK_EX | LOCK_NB);

        $this->assertTrue($periodicLock->IsLocked());

        flock($fh, LOCK_UN);
        fclose($fh);
    }

    public function testHaveFailuresBeenNotifiedNo()
    {
        $periodicLock = new PeriodicLock();
        $this->assertFalse($periodicLock->HaveFailuresBeenNotified());
    }

    public function testHaveFailuresBeenNotifiedYes()
    {
        $periodicLock = new PeriodicLock();
        $fh = fopen("{$this->lockDir}/FAIL_NOTIFY", 'w');
        fwrite($fh, "failure notify file\n");
        fclose($fh);
        $this->assertTrue($periodicLock->HaveFailuresBeenNotified());
    }

    public function testMarkFailure()
    {
        $maxFailures = 3;

        for ($i = 1; $i <= $maxFailures; $i++) {
            $fh = fopen("{$this->lockDir}/FAILURE_$i", 'w');
            fwrite($fh, "failure file $i\n");
            fclose($fh);
        }

        $periodicLock = new PeriodicLock();
        $periodicLock->MarkFailure();

        for ($i = 1; $i <= $maxFailures; $i++) {
            $this->assertTrue(file_exists("{$this->lockDir}/FAILURE_$i"));
        }
        $i = $maxFailures + 1;
        $this->assertTrue(file_exists("{$this->lockDir}/FAILURE_{$i}"));
    }

    public function testMarkFailuresAsNotified()
    {
        $periodicLock = new PeriodicLock();
        $periodicLock->MarkFailuresAsNotified();

        $this->assertTrue(file_exists("{$this->lockDir}/FAIL_NOTIFY"));
    }

    public function testLockAlreadyLocked()
    {
        $periodicLock = new PeriodicLock();
        $this->assertTrue($periodicLock->Lock());
        $this->assertFalse($periodicLock->Lock());
        $periodicLock->Unlock();
    }

    public function testLockFile()
    {
        $periodicLock = new PeriodicLock();
        $this->assertTrue($periodicLock->Lock());

        $filename = "{$this->lockDir}/lockfile";
        $this->assertTrue(file_exists($filename));
        $fh = fopen($filename, 'w');
        $this->assertTrue($fh !== false);
        $this->assertFalse(flock($fh, LOCK_EX | LOCK_NB));
        fclose($fh);
        $periodicLock->Unlock();
    }

    public function testTooManyFailuresNo()
    {
        for ($i = 1; $i <= 3; $i++) {
            $fh = fopen("{$this->lockDir}/FAILURE_$i", "w");
            fwrite($fh, "Failure file $i\n");
            fclose($fh);
        }

        $periodicLock = new PeriodicLock();
        $this->assertFalse($periodicLock->TooManyFailures());
    }

    public function testTooManyFailuresYes()
    {
        for ($i = 1; $i <= 6; $i++) {
            $fh = fopen("{$this->lockDir}/FAILURE_$i", "w");
            fwrite($fh, "Failure file $i\n");
            fclose($fh);
        }

        $periodicLock = new PeriodicLock();
        $this->assertTrue($periodicLock->TooManyFailures());
    }

    public function testUnlockNotLocked()
    {
        $periodicLock = new PeriodicLock();
        $this->assertFalse($periodicLock->Unlock());
    }

    public function testUnlockLocked()
    {
        $periodicLock = new PeriodicLock();
        $periodicLock->Lock();
        $this->assertTrue($periodicLock->Unlock());

        $fh = fopen("{$this->lockDir}/lockfile", "w");
        $this->assertTrue(flock($fh, LOCK_EX | LOCK_NB));
        flock($fh, LOCK_UN);
        fclose($fh);
    }
}
