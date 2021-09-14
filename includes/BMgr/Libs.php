<?php
namespace BMgr;
class Libs {
    protected $Directory = "lib/";
    protected $DepthLevel;
    protected $DefaultDepthLevel;
    protected $dirs = array();
    protected $files = array();
    protected $LIBS_ROOT = '';
    protected $ignoredDirs = array('.', '..', 'ignore', 'modules', 'fonts');
    protected $ignoredDirIndexes = array('assets', 'lib');
    public function __construct($libs_dir, $DepthLevel = 2)
    {
        $this->DefaultDepthLevel = $DepthLevel;
        $this->LIBS_ROOT = $libs_dir;
        return $this;
    }
    public function get ($Dir) {
        $this->DepthLevel = $this->DefaultDepthLevel;
        $dir = opendir($Dir);
         while (false !== ($f = readdir($dir)) && $this->DepthLevel):
             if (!in_array($f, $this->ignoredDirs)) {
                 if ( is_dir( "$Dir/$f" )) {
                     $this->dirs[] = "$Dir/$f";
                     $this->setDepthLevel($this->DepthLevel - 1);
                     $this->get("$Dir/$f");     
                 } else {
                     $break = explode('/', $Dir);
                     $s = '';
                     
                     foreach ($break as $b) if (!in_array($b, $this->ignoredDirIndexes))
                         $s .= "['". preg_replace("/[^a-z0-9]/i", "", $b) ."']";
                         
                     $ext = explode('.', $f); 
                     $ext = $ext[count($ext)-1];
                     $eval = $s . "['$ext'] = '".$Dir."/$f';";
                     eval('$this->files' . $eval);

                 }
             }
             
         endwhile; 
         $this->DepthLevel = $this->DefaultDepthLevel;
         return $this;
     }

    public function setDepthLevel($l) {
        $this->DepthLevel = $l;
        return $this;
    }
    public function getLibs() {
        return $this->get($this->LIBS_ROOT)->files;
    }
}

?>