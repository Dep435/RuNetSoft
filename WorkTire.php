<?php

spl_autoload_register(function ($class_name) {
     include_once($class_name . ".php");
});

class WorkTire extends Tire {

    /*protected $brands = ["BFGoodrich", "Continental", "Hankook", "Mitas", "Nokian", "Pirelli", "Toyo"];
    protected $runflats = ["HRS", "RFT", "ROF", "Run Flat", "RunFlat", "SSR", "ZP", "ZPS"];
    protected $tubes = ["TL", "TL/TT", "TT"];
    protected $seasons = ["Внедорожные", "Всесезонные", "Зимние (нешипованные)", "Зимние (шипованные)", "Летние"];
*/
    protected $mustDel = [9, 10, 13, 32, 160];

    protected function prepare($name) {
        for($i = 0; $i < count($mustDel); $i++) {
            $mustDelChr[] = chr($mustDel[$i]);
        }
        $name = str_replace($mustDelChr, " ", $name);
        $name_temp = "";
        while($name != $name_temp) {
          $name_temp = $name;
          $name = str_replace("  ", " ", $name_temp);
        }
        return trim($name);
    }

    protected function subParse($name, $chars) {
        $i = 0;
        $t = true;
        while($i < strlen($name) && $t) {
            if(($chars == "0" &&
                ($name[$i] < "0" || $name[$i] > "9")) ||
            ($chars == "A" &&
                ($name[$i] < "A" ||
                ($name[$i] > "Z" && $name[$i] < "a") ||
                $name[$i] > "z")) ||
            (($chars == "0A" || $chars == "A0") &&
                ($name[$i] < "0" ||
                ($name[$i] > "9" && $name[$i] < "A") ||
                ($name[$i] > "Z" && $name[$i] < "a") ||
                $name[$i] > "z"))
            ) {
                $i++;
            } else {
                $t = false;
            }
        }
        if($i > 0) {
            $name = substr($name, $i);
        }

        $i = 0;
        $t = true;
        while($i < strlen($name) && $t) {
            if(($chars == "0" && $name[$i] >= "0" && $name[$i] <= "9") ||
                ($chars == "A" && (($name[$i] >= "A" && $name[$i] <= "Z") ||
                    ($name[$i] >= "a" && $name[$i] <= "z"))) ||
                (($chars == "0A" || $chars == "A0") &&
                    (($name[$i] >= "0" && $name[$i] <= "9") ||
                        (($name[$i] >= "A" && $name[$i] <= "Z") ||
                            ($name[$i] >= "a" && $name[$i] <= "z"))
                    )
                )
            ) {
                $i++;
            } else {
                $t = false;
            }
        }
        if($i < strlen($name)) {
            $res["value"] = substr($name, 0, $i);
            $res["subname"] = trim(substr($name, $i));
        } else {
            $res["value"] = "";
            $res["subname"] = $name;
        }
        return $res;
    }

    protected function matchParse($name, $list) {
        $i = 0;
        while($i < count($list) && stripos($name, $list[$i]) === false) {
            $i++;
        }
        if($i < count($list)) {
            $res["value"] = $list[$i];
            $res["subname"] = trim(str_replace($list[$i], "", $name));
        } else {
            $res["value"] = "";
            $res["subname"] = $name;
        }
        return $res;
    }

    protected function parseTire($name) {
        $temp = $this->matchParse($name, $this->brands);
        $properties["brand"] = $temp["value"];

        $name = $temp["subname"];
        $t = true;
        $i = 0;
        while($t && $i < strlen($name)) {
          $j = stripos($name, "/", $i);
          if($j !== false) {
              if($name[$j - 1] >= "0" && $name[$j - 1] <= "9" && $name[$j + 1] >= "0" && $name[$j + 1] <= "9") {
                  $t = false;
              } else {
                  $i = $j + 1;
              }
          }
        }
        if($i < strlen($name)) {
            $i = $j - 1;
            while($name[$i] >= "0" && $name[$i] <= "9") {
                $i--;
            }
            $properties["model"] = trim(substr($name, 0, $i + 1));

            $temp = $this->subParse(substr($name, $i), "0");
            $properties["width"] = $temp["value"];

            $name = $temp["subname"];
            $temp = $this->subParse($name, "0");
            $properties["height"] = $temp["value"];

            $name = $temp["subname"];
            $temp = $this->subParse($name, "A");
            $properties["construction"] = $temp["value"];

            $name = $temp["subname"];
            $temp = $this->subParse($name, "0");
            $properties["diameter"] = $temp["value"];

            $name = $temp["subname"];
            $temp = $this->subParse($name, "0");
            $properties["load_index"] = $temp["value"];

            $name = $temp["subname"];
            $temp = $this->subParse($name, "A");
            $properties["speed_index"] = $temp["value"];

            $name = $temp["subname"];
            $temp = $this->matchParse($name, $this->tubes);
            $properties["tube"] = $temp["value"];

            $name = $temp["subname"];
            $temp = $this->matchParse($name, $this->seasons);
            $properties["season"] = $temp["value"];

            $name = $temp["subname"];
            $temp = $this->matchParse($name, $this->runflats);
            $properties["runflat"] = $temp["value"];

            $properties["characterizing"] = $this->prepare($temp["subname"]);
        }
        return $properties;
    }

    function __construct($name) {
        $this->name = $name;
        $this->properties = $this->parseTire($this->prepare($name));
    }

    public function writeTire($name, $properties, $table1, $table2) {
        $r = mysql_query("INSERT " . $table1 . " VALUES(null, '" . $name . "', 0)") or die("Query failed: " . mysql_error());

        $r = mysql_query("SELECT id FROM " . $table1 . " WHERE title = '" . $name . "'") or die("Query failed: " . mysql_error());
        if($s = mysql_fetch_array($r, MYSQL_NUM)) {
            $properties["tire_id"] = $s[0];
        }

        $query = "INSERT " . $table2 . " VALUES (null";
        $r = mysql_query("SHOW COLUMNS FROM " . $table2) or die("Query failed: " . mysql_error());
        $s = mysql_fetch_array($r, MYSQL_NUM);
        $i = 1;
        $flag = 1;
        while($s = mysql_fetch_array($r, MYSQL_NUM)) {
            $query .= ", '" .$properties[$s[0]]. "'";
            if(($i <= 9 || $i == 13) && $properties[$s[0]] == "") {
                $flag = 2;
            }
            $i++;
        }
        $query .= ")";
        $r = mysql_query($query) or die("Query failed: " . mysql_error());
        $r = mysql_query("UPDATE " . $table1 . " SET flag = " . $flag . " WHERE id = " . $properties["tire_id"]) or die("Query failed: " . mysql_error());
    }
}

?>
