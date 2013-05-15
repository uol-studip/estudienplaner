<?php

require_once dirname(__file__)."/qqUploader.php";

class DBFile extends SimpleORMap {

    public function __construct($id = null) {
        parent::__construct($id);
    }
    
    /**
     * things to do before this file is uploaded
     */
    public function beforeUpload($file_properties) {
        global $user;
        $this['filename'] = $file_properties['filename'];
        $this['filesize'] = $file_properties['filesize'];
        $this['autor_host'] = $GLOBALS['REMOTE_ADDR'];
        $this['author_name'] = get_fullname($user->id);
    }
    
    /**
     * Diese Funktion sollte an einer Stelle aufgerufen werden, wo die Datei schon 
     * de facto hochgeladen ist. Diese Funktion ordnet die Datei dann nur noch der 
     * Datenbank zu.
     * Ruft am Ende store() auf, damit die Datenbank die �nderungen �bernimmt. Alle 
     * Variablen sollten also vorher schon mal in die Datenbank geschrieben werden.
     */
    public function upload() {
        global $perm;
    	$allowedExtensions = array();
        $upload_type = $this->getUploadType();
        
        $sizeLimit = $upload_type['file_sizes'][$perm->get_perm()];
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload($GLOBALS['TMP_PATH']."/");
        // to pass data through iframe you will need to encode all html tags
        
        if ($result['success'] === true) {
            $this->beforeUpload($result);
        	$this->store();
            $result['id'] = $this->getId();
            //packe das Bild in das Dateisystem:
            if (!copy($result['path'], $this->getUploadPath())) {
                $this->delete();
                throw new Exception("Could not copy");
            }
        }
        //Datei im temp-Verzeichnis l�schen und fertig:
        if (file_exists($result['path'])) {
            unlink($result['path']);
        }
        unset($result['path']);
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        
    }
    
    protected function getUploadType() {
        return $GLOBALS['UPLOAD_TYPES']['default'];
    }
    
    protected function getUploadPath() {
        $directory = $GLOBALS['UPLOAD_PATH'].'/'.substr($this->getId(), 0, 2);
        if (!file_exists($directory)) {
            mkdir($directory);
        }
        return $directory.'/'.$this->getId();
    }
    
    /**
     * Diese Methode liefert die Datei aus und beendet alle anderen Ausgaben. So beware!
     * Es darf daf�r auch keine Ausgabe zuvor geschehen sein.
     */
    public function download($attachment = false) {
        //kein Rechtecheck
        header("Content-type: ".$this->getMimeType());
        header('Content-Disposition: '.($attachment ? "attachment" : "inline").'; filename="'.$this['filename'].'"');
        
        $chdate = $this->getLastDate();
        if ($chdate !== null && $_SERVER['HTTP_IF_MODIFIED_SINCE'] && $chdate < strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            //cache-control:
            header("HTTP/1.1 304 Not Modified");
            exit;
        }
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        header('Content-Length: ' . filesize($this->getUploadPath()));

        print file_get_contents($this->getUploadPath());
        page_close();
        exit;
    }
    
    /**
     * returns unix-timestamp of last time, this file was edited
     */
    public function getLastDate() {
        return $this["chdate"] ? $this["chdate"] : null;
    }

    public function delete() {
        @unlink($this->getUploadPath());
        parent::delete();
    }

    public function getMimeType() {
        
        switch (strtolower(getFileExtension ($this['filename']))) {
            case "txt":
                $content_type = "text/plain";
            break;
            case "css":
                $content_type = "text/css";
            break;
            case "gif":
                $content_type = "image/gif";
            break;
            case "jpeg":
            case "jpg":
            case "jpe":
                $content_type = "image/jpeg";
            break;
            case "bmp":
                $content_type = "image/x-ms-bmp";
            break;
            case "png":
                $content_type = "image/png";
            break;
            case "wav":
                $content_type = "audio/x-wav";
            break;
            case "ra":
                $content_type = "application/x-pn-realaudio";
            break;
            case "ram":
                $content_type = "application/x-pn-realaudio";
            break;
            case "mpeg":
            case "mpg":
            case "mpe":
                $content_type = "video/mpeg";
            break;
            case "qt":
            case "mov":
                $content_type = "video/quicktime";
            break;
            case "avi":
                $content_type = "video/x-msvideo";
            break;
            case "rtf":
                $content_type = "application/rtf";
            break;
            case "pdf":
                $content_type = "application/pdf";
            break;
            case "doc":
                $content_type = "application/msword";
            break;
            case "xls":
                $content_type = "application/ms-excel";
            break;
            case "ppt":
                $content_type = "application/ms-powerpoint";
            break;
            case "tgz":
            case "gz":
                $content_type = "application/x-gzip";
            break;
            case "bz2":
                $content_type = "application/x-bzip2";
            break;
            case "zip":
                $content_type = "application/zip";
            break;
            case "swf":
                $content_type = "application/x-shockwave-flash";
            break;
            case "csv":
                $content_type = "text/csv";
            break;
            case "ogg":
                $content_type = "application/ogg";
            break;
            case "ogv":
                $content_type = "video/ogg";
            break;
            case "mp4":
                $content_type = "video/mp4";
            break;
            case "webm":
                $content_type = "video/webm";
            break;
            case "oga":
                $content_type = "audio/ogg";
            break;
            case "mp3":
                $content_type = "audio/mp3";
            break;
            case "wav":
                $content_type = "audio/wave";
            break;
            default:
            $content_type = "application/octet-stream";
            break;
        }
        return $content_type;
    }
}
