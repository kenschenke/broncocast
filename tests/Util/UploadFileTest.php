<?php

namespace App\Tests\Util;

use App\Util\UploadFile;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UploadFileTest extends TestCase
{
    private $root;
    private $uploadFolder;

    protected function setUp()
    {
        $this->root = vfsStream::setup('home');
        $this->uploadFolder = $this->root->url() . '/uploads';
        mkdir($this->uploadFolder);
        chmod($this->uploadFolder, 0777);
    }

    protected function setUpFile($clientName, $error, $size, $mimeType, $guessExtension)
    {
        $file = $this->getMockBuilder('\Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $info = pathinfo($clientName);
        $clientExtension = isset($info['extension']) ? $info['extension'] : '';

        $file->method('getClientOriginalName')->will($this->returnValue($clientName));
        $file->method('getError')->will($this->returnValue($error));
        $file->method('getSize')->will($this->returnValue($size));
        $file->method('getMimeType')->will($this->returnValue($mimeType));
        $file->method('guessExtension')->will($this->returnValue($guessExtension));
        $file->method('getClientOriginalExtension')->will($this->returnValue($clientExtension));

        return $file;
    }

    public function testFileTooBigIniSize()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_INI_SIZE;
        $expectedMessages = [
            $filename . ' is too big: (max: ' . UploadFile::convertFromBytes($upl->getMaxSize()) . ').',
        ];

        $file = $this->setUpFile($filename, $error, 0, '', '');
        $this->assertFalse($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testFileTooBigFormSize()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_FORM_SIZE;
        $expectedMessages = [
            $filename . ' is too big: (max: ' . UploadFile::convertFromBytes($upl->getMaxSize()) . ').',
        ];

        $file = $this->setUpFile($filename, $error, 0, '', '');
        $this->assertFalse($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testFilePartiallyUploaded()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_PARTIAL;
        $expectedMessages = [
            $filename . ' was only partially uploaded.',
        ];

        $file = $this->setUpFile($filename, $error, 0, '', '');
        $this->assertFalse($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testFileNotUploaded()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_NO_FILE;
        $expectedMessages = [
            'No file submitted.',
        ];

        $file = $this->setUpFile($filename, $error, 0, '', '');
        $this->assertFalse($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testFileOtherError()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_CANT_WRITE;
        $expectedMessages = [
            "Sorry, there was a problem uploading " . $filename,
        ];

        $file = $this->setUpFile($filename, $error, 0, '', '');
        $this->assertFalse($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testFileTooBigDefaultSize()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_OK;
        $expectedMessages = [
            $filename . ' exceeds the maximum size for a file (' . UploadFile::convertFromBytes($upl->getMaxSize()) . ').',
        ];

        $file = $this->setUpFile($filename, $error, $upl->getMaxSize() + 1, '', '');
        $this->assertFalse($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Maximum size cannot exceed server limit for individual files: 2.0 MB
     */
    public function testSetMaxSizeBiggerThanServerSize()
    {
        $upl = new UploadFile();
        $serverMax = UploadFile::convertToBytes(ini_get('upload_max_filesize'));
        $upl->setMaxSize($serverMax + 1);
    }

    public function testFileTooBigCustomSize()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_OK;
        $maxSize = $upl->getMaxSize() - 5;  // smaller than default
        $expectedMessages = [
            $filename . ' exceeds the maximum size for a file (' . UploadFile::convertFromBytes($upl->getMaxSize()) . ').',
        ];

        $file = $this->setUpFile($filename, $error, $maxSize + 1, '', '');
        $upl->setMaxSize($maxSize);
        $this->assertFalse($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testEmptyFile()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_OK;
        $expectedMessages = [
            $filename . ' is empty.',
        ];

        $file = $this->setUpFile($filename, $error, 0, '', '');
        $this->assertFalse($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testNonNumericMaxSize()
    {
        $upl = new UploadFile($this->uploadFolder);

        $defaultMaxSize = $upl->getMaxSize();
        $maxSize = "Too Big";

        $upl->setMaxSize($maxSize);
        $this->assertEquals($defaultMaxSize, $upl->getMaxSize());
    }

    public function testZeroMaxSize()
    {
        $upl = new UploadFile($this->uploadFolder);

        $defaultMaxSize = $upl->getMaxSize();
        $maxSize = 0;

        $upl->setMaxSize($maxSize);
        $this->assertEquals($defaultMaxSize, $upl->getMaxSize());
    }

    public function testFileBiggerThanServerMaxSize()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_OK;
        $serverMax = UploadFile::convertToBytes(ini_get('upload_max_filesize'));
        $expectedMessages = [
            $filename . ' exceeds the maximum size for a file (' . UploadFile::convertFromBytes($serverMax) . ').',
        ];

        $file = $this->setUpFile($filename, $error, $serverMax + 1, '', '');
        $upl->setMaxSize($serverMax);
        $this->assertFalse($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testFileNotPermittedType()
    {
        $upl = new UploadFile();
        $upl->addPermittedType('image/jpeg');

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_OK;
        $expectedMessages = [
            $filename . ' is not a permitted type of file.',
        ];

        $file = $this->setUpFile($filename, $error, 1, 'application/dangerous', '');
        $this->assertFalse($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testNameWithSpaces()
    {
        $upl = new UploadFile();

        $filename = "Name With Spaces.txt";
        $newName = "Name_With_Spaces.txt";
        $error = UPLOAD_ERR_OK;
        $expectedMessages = [
            $filename . " was uploaded successfully, and was renamed $newName.",
        ];

        $file = $this->setUpFile($filename, $error, 1, '', 'txt');
        $file->expects($this->once())
            ->method('move')
            ->with($this->equalTo($this->uploadFolder."/"), $this->equalTo($newName))
            ->will($this->returnValue($file));

        $this->assertTrue($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testUntrustedFile()
    {
        $upl = new UploadFile();

        $filename = "testfile.js";
        $newName = "testfile.js.upload";
        $error = UPLOAD_ERR_OK;
        $expectedMessages = [
            $filename . " was uploaded successfully, and was renamed $newName.",
        ];

        $file = $this->setUpFile($filename, $error, 1, 'text/javascript', 'js');
        $file->expects($this->once())
            ->method('move')
            ->with($this->equalTo($this->uploadFolder."/"), $this->equalTo($newName))
            ->will($this->returnValue($file));

        $this->assertTrue($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testSuffix()
    {
        $upl = new UploadFile();

        $filename = "testfile.js";
        $newName = "testfile.js.fixed";
        $error = UPLOAD_ERR_OK;
        $suffix = 'fixed';
        $expectedMessages = [
            $filename . " was uploaded successfully, and was renamed $newName.",
        ];

        $file = $this->setUpFile($filename, $error, 1, 'text/javascript', 'js');
        $file->expects($this->once())
            ->method('move')
            ->with($this->equalTo($this->uploadFolder."/"), $this->equalTo($newName))
            ->will($this->returnValue($file));

        $upl->setDefaultSuffix($suffix);
        $this->assertTrue($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testDuplicateRenaming()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $newName = "testfile_1.txt";
        $error = UPLOAD_ERR_OK;
        $expectedMessages = [
            $filename . " was uploaded successfully, and was renamed $newName.",
        ];

        $duplicatefile = $this->uploadFolder . "/$filename";
        file_put_contents($duplicatefile, "File Contents");

        $file = $this->setUpFile($filename, $error, 1, '', 'txt');
        $file->expects($this->once())
            ->method('move')
            ->with($this->equalTo($this->uploadFolder."/"), $this->equalTo($newName))
            ->will($this->returnValue($file));

        $this->assertTrue($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testNoDuplicateRenaming()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_OK;
        $expectedMessages = [
            $filename . ' was uploaded successfully.',
        ];

        $duplicatefile = $this->uploadFolder . "/$filename";
        file_put_contents($duplicatefile, "File Contents");

        $file = $this->setUpFile($filename, $error, 1, '', 'txt');
        $file->expects($this->once())
            ->method('move')
            ->with($this->equalTo($this->uploadFolder."/"), $this->equalTo($filename))
            ->will($this->returnValue($file));

        $this->assertTrue($upl->upload($file, $this->uploadFolder, false));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testMultipleDuplicateRenaming()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $newName = "testfile_4.txt";
        $error = UPLOAD_ERR_OK;
        $expectedMessages = [
            $filename . " was uploaded successfully, and was renamed $newName.",
        ];

        $duplicatefile = $this->uploadFolder . "/$filename";
        file_put_contents($duplicatefile, "File Contents");
        for ($i = 1; $i <= 3; $i++) {
            $duplicatefile = $this->uploadFolder . "/testfile_$i.txt";
            file_put_contents($duplicatefile, "File Contents");
        }

        $file = $this->setUpFile($filename, $error, 1, '', 'txt');
        $file->expects($this->once())
            ->method('move')
            ->with($this->equalTo($this->uploadFolder."/"), $this->equalTo($newName))
            ->will($this->returnValue($file));

        $this->assertTrue($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testForceBaseName()
    {
        $upl = new UploadFile();

        $forceBaseName = "basename";
        $filename = "testfile.xls";
        $newName = "$forceBaseName.xls";
        $error = UPLOAD_ERR_OK;
        $expectedMessages = [
            $filename . " was uploaded successfully, and was renamed $newName.",
        ];

        $file = $this->setUpFile($filename, $error, 1, 'application/excel', 'xls');
        $file->expects($this->once())
            ->method('move')
            ->with($this->equalTo($this->uploadFolder."/"), $this->equalTo($newName))
            ->will($this->returnValue($file));

        $this->assertTrue($upl->upload($file, $this->uploadFolder, false, $forceBaseName));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    public function testMoveThrowsException()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_OK;
        $expectedMessages = [
            'Could not upload ' . $filename,
        ];

        $file = $this->setUpFile($filename, $error, 1, '', 'txt');
        $file->expects($this->once())
            ->method('move')
            ->with($this->equalTo($this->uploadFolder."/"), $this->equalTo($filename))
            ->will($this->throwException(new FileException('message')));

        $this->assertFalse($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage vfs://home/testfile must be a valid, writable folder.
     */
    public function testDestinationPathNotDirectory()
    {
        $testfile = $this->root->url() . '/testfile';
        file_put_contents($testfile, 'File contents');

        $upl = new UploadFile();

        $file = $this->setUpFile('', 0, 0, '', 'txt');

        $this->assertTrue($upl->upload($file, $testfile));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage vfs://home/uploads must be a valid, writable folder.
     */
    public function testDestinationPathNotWritable()
    {
        chmod($this->uploadFolder, 0555);
        $upl = new UploadFile();

        $file = $this->setUpFile('', 0, 0, '', 'txt');

        $this->assertTrue($upl->upload($file, $this->uploadFolder));
    }

    public function testSuccessfulUpload()
    {
        $upl = new UploadFile();

        $filename = "testfile.txt";
        $error = UPLOAD_ERR_OK;
        $expectedMessages = [
            $filename . ' was uploaded successfully.',
        ];

        $file = $this->setUpFile($filename, $error, 1, '', 'txt');
        $file->expects($this->once())
            ->method('move')
            ->with($this->equalTo($this->uploadFolder."/"), $this->equalTo($filename))
            ->will($this->returnValue($file));

        $this->assertTrue($upl->upload($file, $this->uploadFolder));
        $this->assertEquals($expectedMessages, $upl->getMessages());
    }
}
