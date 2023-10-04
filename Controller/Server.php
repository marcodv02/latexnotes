<?php
class Background{
    private $view_path= "/";
    private $db;
    private $table= "";
    private $sql;
    function __construct(){
        $this->db= new mysqli(HOST, USER, PASSWORD, DATABASE);
    }
    function pong(){

    }
    function edit($filter, $data, $table){
        $table= ($table) ? $table : $this->table;

        $sql= "UPDATE $table SET ";

        foreach ($data as $column=>$value){
            $sql.= "`$column`='$value',";
        }
        $sql= rtrim($sql, ",")." WHERE $filter;";
        $this->sql= $sql;
        $this->db->query($sql);

        return mysqli_affected_rows($this->db);
    }
    function insert($data, $table= ""){
        $table= ($table) ? $table : $this->table;

        $sql= "INSERT INTO $table ";

        $keys= "(";
        $values= "(";
        foreach ($data as $key=>$val){
            $keys.= "$key,";
            $values.= "'$val',";
        }
        $keys= rtrim($keys, ",").")";
        $values= rtrim($values, ",").")";

        $sql.= "$keys VALUES $values;";
        $this->sql= $sql;
        return ($this->db->query($sql));
    }
    function show_all($filter, $table="", $keys=""){
        $table= ($table) ? $table : $this->table;
        $filter= ($filter) ? "WHERE $filter" : "";
        if ($keys=="")
            $keys="*";
        $sql= "SELECT $keys FROM $table $filter;";

        return $this->query($sql);
    }
    function query($sql){
        if ($res= $this->db->query($sql)){
            $ret= $res->fetch_all(MYSQLI_ASSOC);
        }else{
            $ret= False;
        }

        $this->sql= $sql;
        return $ret;
    }
    function just_query($sql){
        $this->sql= $sql;
        return $this->db->query($sql);
    }
    function num_select($filter, $table=""){
        return count($this->show_all($filter, $table));
    }
    function get_last_query(){
        return $this->sql;
    }
    function get_last_id(){
        return $this->db->insert_id;
    }
    function set_table($tab){
        $this->table= $tab;
    }
    function __destruct(){
        //$this->db->close();
    }
    function upload_dir(){
        return realpath(__DIR__."/../Asset/media/piatti/");
    }
    function no_accent($str){
        /**
         * Replace language-specific characters by ASCII-equivalents.
         * @param string $s
         * @return string
         */
        return iconv('UTF-8','ASCII//TRANSLIT',$str);
    }
}
class Foreground {
    private $serverName= SERVERNAME;
    private $view_path= "view/";
    private $element_path= "element/";
    private $api_path= "Api/";
    private $asset_path= "/Asset";
    private $lib_path= "/Libs";
    private $componenti= [];

    private $page= "index";
    function __construct(){
        if (substr($_SERVER['DOCUMENT_ROOT'], -1)!="/")
            $_SERVER['DOCUMENT_ROOT'].= "/";

        $strJsonFileContents = file_get_contents("conf.json");
        $array = json_decode($strJsonFileContents, true);
        if (array_key_exists("componenti", $array))
            $this->componenti= $array["componenti"];

        if (!ON_SERVER) {
            $this->view_path = SERVERNAME . "/" .$this->view_path;
            $this->api_path = SERVERNAME . "/" . $this->api_path;
            $this->element_path = SERVERNAME . "/" . $this->element_path;
            $this->asset_path = "/". SERVERNAME . $this->asset_path;
            $this->lib_path = "/". SERVERNAME . $this->lib_path;
        }
        if (isset($_GET["page"]) and $_GET["page"])
            $this->page= $_GET["page"];
        else
            $this->page= "index";
    }
    public function not_image(){
        echo $this->asset("img/notimg.png");
    }
    public function view($path){
        $mem= $_SERVER['DOCUMENT_ROOT'].$this->view_path.$path.".php";
        $path= realpath($mem);
        if ($path){
            if ((include $path)==FALSE)
                echo "<a title='$mem'>Error to include page</a>";
        } else
            echo "<a title='$mem'>Page doesn't exist</a>";
    }
    function api($path){
        $mem= $_SERVER['DOCUMENT_ROOT'].$this->api_path.$path.".php";
        $path= realpath($mem);
        $status= 0;
        $data= [$_GET, $_POST, $_SERVER];

        if ($path){
            $status= 1;
            $response= "Inclusione pagina $mem";

            if (!include $path)
                $response= "Errore nell'includere la pagina $mem";
        }else{
            $response= "Api non esistente: $mem";
        }
        header('Content-type: application/json');

        echo json_encode(["status" => $status, "response" => $response, "data" => $data]);
    }
    function element($path){
        $mem= $_SERVER['DOCUMENT_ROOT'].$this->element_path.$path.".php";
        $path= realpath($mem);
        if ($path){
            if ((include $path)==FALSE)
                echo "<a title='$mem'>Error to include element</a>";
        } else
            echo "<a title='$mem'>Element doesn't exist</a>";
    }
    function load_js(){
        if (array_key_exists($this->page, $this->componenti)){
            $libs= $this->componenti[$this->page]['libs'];
            if (is_array($libs))
                foreach ($libs as $lib=>$v)
                    $this->asset("vendor/$lib/$v/$lib.js");
        }
        $this->asset("page_script/".$this->page.".js");
    }
    function load_css(){
        if (array_key_exists($this->page, $this->componenti)){
            $libs= $this->componenti[$this->page]['libs'];
            if (is_array($libs))
                foreach ($libs as $lib=>$v)
                    $this->asset("vendor/$lib/$v/$lib.css");
        }
        $this->asset("page_style/".$this->page.".css");
    }
    function lang(){
        echo "it";
    }
    function title($name){
        echo "<title>$name</title>";
    }
    function asset($path, $data=[]){
        $format= "";
        foreach ($data as $point=>$value){
            $format.= $point."='".$value."' ";
        }

        if ($this->startsWith($path, "http")){
            if ($this->endsWith($path, ".js")){
                echo "<script src='$path' $format></script>";
            }
            if ($this->endsWith($path, ".css")){
                echo "<link href='$path' $format rel='stylesheet'/>";
            }
        }else{
            if ($this->endsWith($path, ".css")){
                echo "<link href='$this->asset_path/css/$path' rel='stylesheet'/>";
            }
            if ($this->endsWith($path, ".js")){
                echo "<script src='$this->asset_path/js/$path'></script>";
            }
            if ($this->endsWith($path, [".jpg", ".jpeg", ".png", ".svg", ".ico", ".mp4"])){
                return "$this->asset_path/media/$path";
            }
        }
        return 0;
    }
    function lib($path, $v="", $lib=""){
        $bf_name= basename($path);
        $f_name= explode(".", $bf_name)[0];
        if ($lib=="")
            $lib= $f_name;
        if ($v)
            $v= "$v/";
        if ($this->endsWith($bf_name, ".css")){
            echo "<link href='$this->lib_path/$lib/$v$path' rel='stylesheet'/>";
        }
        if ($this->endsWith($bf_name, ".js")){
            echo "<script src='$this->lib_path/$lib/$v$path'></script>";
        }
    }
    function meta(){

    }
    function ping(){
        var_dump("ping");
    }
    private function startsWith( $haystack, $needle ) {
        if (is_array($needle)){
            foreach ($needle as $need){
                $length = strlen($need);
                if (substr( $haystack, 0, $length )===$need)
                    return true;
            }
        }else{
            $length = strlen( $needle );
            return substr( $haystack, 0, $length ) === $needle;
        }
        return false;
    }

    private function endsWith( $haystack, $needle ) {
        if (is_array($needle)){
            foreach ($needle as $need){
                $length = strlen($need);
                if (substr( $haystack, -$length )===$need)
                    return true;
            }
        }else{
            $length = strlen( $needle );
            return substr( $haystack, -$length ) === $needle;
        }
        return false;
    }
}
