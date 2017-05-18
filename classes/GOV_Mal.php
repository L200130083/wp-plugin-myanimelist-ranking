<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
class GOV_mal extends GOV_Class{
    function __construct(){
        parent::__construct();
        $this->cache_path = GOV_PG_MAL_DIR.DIRECTORY_SEPARATOR."cache/";
        $this->cache_duration = 86400 * get_option("gov_mal_max_age"); //1 day
    }
    static function activate() {
        // do not generate any output here
        add_option("gov_mal_template", '<span itemscope itemtype="http://schema.org/Product"> <b itemprop="name">{{MAL_title}}</b><br/> <span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"> Score : <span itemprop="ratingValue">{{MAL_score}}</span> (scored by <span itemprop="ratingCount">{{MAL_count}}</span> users) <meta itemprop="bestRating" content="10"> <meta itemprop="worstRating" content="1"> </span></span> <br/><span class="alt_text">{{MAL_alt_title}}</span>');
        register_setting('gov_mal_options_group', 'gov_mal_template', '');
        add_option("gov_mal_exclude", '<span itemscope itemtype="http://schema.org/Product">');
        register_setting('gov_mal_options_group', 'gov_mal_exclude', '');
        add_option("gov_mal_max_age", "30");
        register_setting('gov_mal_options_group', 'gov_mal_max_age', '');
    }
    static function uninstall(){
        // do not generate any output here
        delete_option("gov_mal_template");
        unregister_setting('gov_mal_options_group', 'gov_mal_template', '');
        delete_option("gov_mal_exclude");
        unregister_setting('gov_mal_options_group', 'gov_mal_exclude', '');
        delete_option("gov_mal_max_age");
        unregister_setting('gov_mal_options_group', 'gov_mal_max_age', '');
    }
    function assets(){
        wp_register_style('gov-assets', plugins_url('assets/GOV_Mal/style.css',GOV_PG_MAL_INIT_FILE));
        wp_enqueue_style('gov-assets');
    }
    function run(){
        add_action('admin_init', [$this, 'activate']);
        add_action("the_content", [$this, 'gov_mal_parse_post']);
        add_action('admin_menu', [$this, 'menu']);
        add_action('wp_enqueue_scripts', [$this, "assets"]);

        register_activation_hook(GOV_PG_MAL_INIT_FILE, array($this, 'activate'));
    }
    function do_uninstall(){
        register_uninstall_hook(GOV_PG_MAL_INIT_FILE, array($this, 'uninstall'));
    }
    function gov_mal_parse_post($content){
        if ( ! is_single()) return $content;
        if (strpos($content, "myanimelist.net/anime/") === FALSE) return $content;
        preg_match("/myanimelist\.net\/anime\/(\d+)/i", $content, $m);
        if (isset($m[1]) && is_numeric($m[1])){
            $MAL_ID = $m[1];
        }else{
            return $content;
        }
        $dont_run_triggers = get_option("gov_mal_exclude");
        $dont_run_triggers = explode(PHP_EOL, $dont_run_triggers);
        $dont_run_triggers = array_filter($dont_run_triggers, function($e){
            return trim($e) != "";
        });
        
        //check exclude
        foreach ($dont_run_triggers as $value) {

            if (strpos($content, trim($value)) !== FALSE){
                return $content;
            }
        }
        $MAL_result = $this->_get_from_mal($MAL_ID);
        return $MAL_result.$content;
        
    }
    private function _get_from_mal($id){
        
        if ($cache = $this->_get_cache_fgc($id)) return $cache;

        $h = @file_get_contents("http://myanimelist.net/anime/{$id}");
        if ( ! $h) return NULL;
        $alt = explode('<h2>Alternative Titles</h2><div class="spaceit_pad">', $h)[1];
        $alt = explode("<h2>Information</h2>", $alt)[0];
        $ext = explode(PHP_EOL, $alt);
        $ext = array_filter($ext, function($e){
            return (preg_replace("/[^A-Za-z0-9 ]/", "",trim(strip_tags($e))) != "");
        });
        $alt_title = implode("<br />", $ext);
        preg_match_all('/<span itemprop="ratingValue">(.*?)<\/span><sup>1<\/sup> \(scored by <span itemprop="ratingCount">(.*?)<\/span> users\)/i', $h, $m);
        if ( ! isset($m[1][0]) || ! isset($m[2][0])) return NULL;
        $score = $m[1][0];
        $vote = str_ireplace(",", "", $m[2][0]);

        //title FROM MAL
        //$title = explode("<title>", $h)[1];
        //$title = explode("</", $title)[0];
        //$title = trim(explode("-", $title)[0]);

        $tpl = get_option("gov_mal_template");
        $tpl = str_ireplace(["{{MAL_title}}", "{{MAL_score}}", "{{MAL_count}}", "{{MAL_alt_title}}"], [get_the_title(), $score, $vote, $alt_title], $tpl);
        return $this->_write_cache_fgc($id, $tpl);
    }
    private function _get_cache_fgc($id = NULL){
        if ( ! $id) return FALSE;
        $cache_path = $this->cache_path.DIRECTORY_SEPARATOR.MD5($id);
        if ( ! file_exists($cache_path) || is_dir($cache_path)){
            return FALSE;
        }
        $data = file_get_contents($cache_path);
        $data = unserialize($data);
        if ((time() - $data['time']) >  $this->cache_duration){
            FALSE;
        }
        return $data['data'];
    }
    private function _write_cache_fgc($id = NULL, $content = NULL){
        if ( ! $id || ! $content) return FALSE;
        $cache_path = $this->cache_path.DIRECTORY_SEPARATOR.MD5($id);
        $data['time'] = time();
        $data['data'] = $content;
        $data = serialize($data);
        file_put_contents($cache_path, $data, LOCK_EX);
        return $content;
    }
    private function flush_cache($dir = FALSE){
        $dir = $dir?:$this->cache_path;
        foreach(glob("{$dir}/*") as $file)
        {
            if(is_dir($file)) { 
                $this->flush_cache($file);
            } else {
                unlink($file);
            }
        }
    }
    function menu() {
        add_options_page('GMAL page', 'GMAL', 'manage_options', 'MY_GMAL', [$this, 'settings_page']);
    }    
    function settings_page(){
        
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) $this->flush_cache();
        $this->load_view('settings_page.php');
    }
}

