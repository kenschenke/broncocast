<?php

namespace App\Util;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFile
{
    protected $destination;
    protected $messages = [];
    protected $maxSize = 2097152; // 2M
    protected $permittedTypes = [];  // empty means everything is allowed
    protected $newName;
    protected $notTrusted = ['bin', 'cgi', 'exe', 'js', 'pl', 'php', 'py', 'sh'];
    protected $suffix = '.upload';
    protected $renameDuplicates;
    protected $forceBaseName;

    public function addPermittedType($type)
    {
        $this->permittedTypes[] = $type;
    }

    public function setMaxSize($bytes)
    {
        $serverMax = self::convertToBytes(ini_get('upload_max_filesize'));
        if ($bytes > $serverMax) {
            throw new \Exception('Maximum size cannot exceed server limit for individual files: ' .
                self::convertFromBytes($serverMax));
        }
        if (is_numeric($bytes) && $bytes > 0) {
            $this->maxSize = $bytes;
        }
    }

    public static function convertToBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = (int)(substr($val, 0, strlen($val)-1));
        if (in_array($last, ['g', 'm', 'k'])) {
            switch ($last) {
                case 'g':
                    $val *= 1024;

                case 'm':
                    $val *= 1024;

                case 'k':
                    $val *= 1024;
            }
        }

        return $val;
    }

    public static function convertFromBytes($bytes)
    {
        $bytes /= 1024;
        if ($bytes > 1024) {
            return number_format($bytes / 1024, 1) . ' MB';
        } else {
            return number_format($bytes, 1) . ' KB';
        }
    }

    public function getLocalName()
    {
        return $this->newName;
    }

    public function setDefaultSuffix($suffix = null)
    {
        if (!is_null($suffix)) {
            if (strpos($suffix, '.') === 0 || $suffix == '') {
                $this->suffix = $suffix;
            } else {
                $this->suffix = ".$suffix";
            }
        }
    }

    public function upload(UploadedFile $file, $uploadFolder,
                           $renameDuplicates = true, $forceBaseName = '')
    {
        // Make sure the upload folder is valid.
        if (!is_dir($uploadFolder) || !is_writable($uploadFolder)) {
            throw new \Exception("$uploadFolder must be a valid, writable folder.");
        }

        // Make sure the upload folder ends in a slash.
        if ($uploadFolder[strlen($uploadFolder) - 1] != '/') {
            $uploadFolder .= '/';
        }

        $this->destination = $uploadFolder;

        $this->renameDuplicates = $renameDuplicates;

        $this->forceBaseName = $forceBaseName;

        if ($this->checkFile($file)) {
            return $this->moveFile($file);
        }

        return false;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getMaxSize()
    {
        return $this->maxSize;
    }

    protected function checkFile(UploadedFile $file)
    {
        if ($file->getError() != UPLOAD_ERR_OK) {
            $this->getErrorMessage($file);
            return false;
        }

        if (!$this->checkSize($file)) {
            return false;
        }

        if (!$this->checkType($file)) {
            return false;
        }

        $this->checkName($file);

        return true;
    }

    protected function getErrorMessage(UploadedFile $file)
    {
        switch ($file->getError()) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->messages[] = $file->getClientOriginalName() . ' is too big: (max: ' .
                    self::convertFromBytes($this->maxSize) . ').';
                break;

            case UPLOAD_ERR_PARTIAL:
                $this->messages[] = $file->getClientOriginalName() . ' was only partially uploaded.';
                break;

            case UPLOAD_ERR_NO_FILE:
                $this->messages[] = 'No file submitted.';
                break;

            default:
                $this->messages[] = 'Sorry, there was a problem uploading ' . $file->getClientOriginalName();
                break;
        }
    }

    protected function checkSize(UploadedFile $file)
    {
        if ($file->getSize() == 0) {
            $this->messages[] = $file->getClientOriginalName() . ' is empty.';
            return false;
        } elseif ($file->getSize() > $this->maxSize) {
            $this->messages[] = $file->getClientOriginalName() . ' exceeds the maximum size for a file (' .
                self::convertFromBytes($this->maxSize) . ').';
            return false;
        } else {
            return true;
        }
    }

    protected function checkType(UploadedFile $file)
    {
        if (!empty($this->permittedTypes)) {
            if (in_array($file->getMimeType(), $this->permittedTypes)) {
                return true;
            } else {
                $this->messages[] = $file->getClientOriginalName() . ' is not a permitted type of file.';
                return false;
            }
        }

        return true;
    }

    protected function checkName(UploadedFile $file)
    {
        $this->newName = null;
        $nospaces = str_replace(' ', '_', $file->getClientOriginalName());
        if ($nospaces != $file->getClientOriginalName()) {
            $this->newName = $nospaces;
        }
        $extension = $file->guessExtension();
        if (is_null($extension) || $extension === 'bin') {
            $extension = $file->getClientOriginalExtension();
        }
        if (!empty($this->suffix)) {
            if (in_array($extension, $this->notTrusted) || empty($extension)) {
                $this->newName = $nospaces . $this->suffix;
            }
        }

        if ($this->renameDuplicates) {
            $name = isset($this->newName) ? $this->newName : $file->getClientOriginalName();
            $nameparts = pathinfo($name);
            $existing = scandir($this->destination);
            if (in_array($name, $existing)) {
                $i = 1;
                do {
                    $this->newName = $nameparts['filename'] . '_' . $i++;
                    if (!empty($extension)) {
                        $this->newName .= ".$extension";
                    }
                    if (in_array($extension, $this->notTrusted)) {
                        $this->newName .= $this->suffix;
                    }
                } while(in_array($this->newName, $existing));
            }
        }

        if ($this->forceBaseName) {
            $this->newName = $this->forceBaseName;
            if (!empty($extension)) {
                $this->newName .= ".$extension";
            }
        }
    }

    protected function moveFile(UploadedFile $file)
    {
        $filename = is_null($this->newName) ? $file->getClientOriginalName() : $this->newName;
        try {
            $file->move($this->destination, $filename);
            $result = $file->getClientOriginalName() . " was uploaded successfully";
            if (!is_null($this->newName)) {
                $result .= ', and was renamed ' . $this->newName;
            }
            $result .= '.';
            $this->messages[] = $result;
        } catch (FileException $e) {
            $this->messages[] = 'Could not upload ' . $file->getClientOriginalName();
            return false;
        }

        return true;
    }
}
