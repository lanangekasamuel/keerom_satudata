<?php 
class TemplateClass
{
    var $TAGS = array();
    var $THEME;
    var $CONTENT;
    private static $defined_tags;

    public function __construct()
    {
        if (!isset(self::$defined_tags)) {
            $defined_tags = array();
            $forbidden = '/(^im|DB|USER|PASS)/';
            $defined = get_defined_constants(true);
            $defined = $defined['user'];
            foreach ($defined as $k => $v) {
                if (preg_match($forbidden, $k)) continue;
                $defined_tags[$k] = $v;
            }

            // dump($defined_tags);die();
            $defined_vars = [];
            $forbidden = '/(ERROR|HOST|ROOT|PATH)/';
            foreach ($defined_tags as $k => $v) {
                if (preg_match($forbidden, $k)) continue;
                $defined_vars[$k] = $v;
            }
            $defined_vars['BASE_PATH'] = BASE_PATH;
            $defined_vars['THEME_URL'] = THEME_URL;
            $defined_tags['ENV_DEFINED_VARS'] = json_encode($defined_vars, JSON_FORCE_OBJECT);

            self::$defined_tags = $defined_tags;
        }
        $this->TAGS = self::$defined_tags;
    }

    function init($themename,$tags = array())
    {
        $this->defineTag($tags);
        $this->THEME = 'themes/'.$themename;
        $this->openTag ();
        $this->closeTag ();
    }
    
    function openTag ($tagBegin = '{'){
	    $this->tagBeginwal = $tagBegin;
    }
	
	 function getTheme (){
	    return $this->THEME; 
    }
    
    function closeTag ($tagEnd = '}') {
	    $this->closeTag = $tagEnd;  
    }
        
    function defineTag($tagname, $varname = null)
    {
        if (is_array ($tagname)) {
            foreach ($tagname as $key => $value) $this->TAGS[$key] = $value;
	    } else $this->TAGS[$tagname] = $varname;
    }
    
    function parse()
    {
        $this->CONTENT = file($this->THEME) OR DIE ('Tidak dapat meload template file : '.$this->THEME);
        
        $this->CONTENT = implode("", $this->CONTENT);
       
        foreach ($this->TAGS as $kunci=>$nilai){
            $regex = preg_quote('/'.$this->tagBeginwal.$kunci.$this->closeTag.'/');
            $this->CONTENT = preg_replace($regex, $nilai, $this->CONTENT);
        }
        
       return $this->CONTENT;
    }
	
	function printTpl()
	{
		echo $this->parse();
	}
}
