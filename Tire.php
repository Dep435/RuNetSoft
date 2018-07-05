<?php

abstract class Tire {

    public $name;

    private $properties = [
        "brand" => "",
        "model" => "",
        "width" => "",
        "height" => "",
        "construction" => "",
        "diameter" => "",
        "load_index" => "",
        "speed_index" => "",
        "characterizing" => "",
        "runflat" => "",
        "tube" => "",
        "season" => "",
    ];

    protected $brands = ["BFGoodrich", "Continental", "Hankook", "Mitas", "Nokian", "Pirelli", "Toyo"];
    protected $runflats = ["HRS", "RFT", "ROF", "Run Flat", "RunFlat", "SSR", "ZPS", "ZP"];
    protected $tubes = ["TL/TT", "TL", "TT"];
    protected $seasons = ["Внедорожные", "Всесезонные", "Зимние (нешипованные)", "Зимние (шипованные)", "Летние"];

    abstract protected function prepare($name);

    abstract protected function parseTire($name);

    abstract public function writeTire($name, $properties, $table1, $table2);

    function __construct($name) {
        $this->name = $name;
    }

    function __get($name) {
        return $this->properties[$name];
    }

    function __set($name, $value) {
        $this->properties[$name] = $value;
    }

}

?>
