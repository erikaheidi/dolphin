<?php
/**
 * CLI Printing / Output Utilities
 */

namespace Dolphin\Core;

class CLIPrinter
{
    static $FG_BLACK = '0;30';
    static $FG_WHITE = '1;37';
    static $FG_RED = '0;31';
    static $FG_GREEN = '0;32';
    static $FG_BLUE = '1;34';
    static $FG_CYAN = '0;36';
    static $FG_MAGENTA = '0;35';

    static $BG_BLACK = '40';
    static $BG_RED = '41';
    static $BG_GREEN = '42';
    static $BG_BLUE = '44';
    static $BG_CYAN = '46';
    static $BG_WHITE = '47';
    static $BG_MAGENTA = '45';

    protected $palettes;

    public function __construct()
    {
        $this->palettes = [
            'default'     => [ self::$FG_WHITE ],
            'alt'         => [ self::$FG_BLACK, self::$BG_WHITE ],
            'error'       => [ self::$FG_RED ],
            'error_alt'   => [ self::$FG_WHITE, self::$BG_RED ],
            'success'     => [ self::$FG_GREEN ],
            'success_alt' => [ self::$FG_WHITE, self::$BG_GREEN ],
            'info'        => [ self::$FG_CYAN],
            'info_alt'    => [ self::$FG_WHITE, self::$BG_CYAN ],
            'unicorn'     => [ self::$FG_MAGENTA ],
            'unicorn_alt' => [ self::$FG_BLUE, self::$BG_MAGENTA ]
        ];
    }

    public function format($message, $style = "default")
    {
        $style_colors = $this->getPalette($style);

        $bg = '';
        if (isset($style_colors[1])) {
            $bg = ';' . $style_colors[1];
        }

        $output = sprintf("\e[%s%sm%s\e[0m", $style_colors[0], $bg, $message);

        return $output;
    }

    public function getPalette($style)
    {
        return isset($this->palettes[$style]) ? $this->palettes[$style] : "default";
    }

    public function out($message, $style = "default")
    {
        echo $this->format($message, $style);
    }

    public function error($message)
    {
        $this->newline();
        $this->out($message, "error");
        $this->newline();
    }

    public function info($message)
    {
        $this->newline();
        $this->out($message, "info");
        $this->newline();
    }

    public function success($message)
    {
        $this->newline();
        $this->out($message, "success");
        $this->newline();
    }

    public function newline()
    {
        echo "\n";
    }

    /**
     * Prints Dolphin Banner
     */
    public function printBanner()
    {
        $header = '
         ,gggggggggggg,                                                                    
        dP"""88""""""Y8b,               ,dPYb,             ,dPYb,                        
        Yb,  88       `8b,              IP\'`Yb             IP\'`Yb                        
         `"  88        `8b              I8  8I             I8  8I      gg                
             88         Y8              I8  8\'             I8  8\'      ""                
             88         d8   ,ggggg,    I8 dP  gg,gggg,    I8 dPgg,    gg    ,ggg,,ggg,  
             88        ,8P  dP"  "Y8ggg I8dP   I8P"  "Yb   I8dP" "8I   88   ,8" "8P" "8, 
             88       ,8P\' i8\'    ,8I   I8P    I8\'    ,8i  I8P    I8   88   I8   8I   8I 
             88______,dP\' ,d8,   ,d8\'  ,d8b,_ ,I8 _  ,d8\' ,d8     I8,_,88,_,dP   8I   Yb,
            888888888P"   P"Y8888P"    8P\'"Y88PI8 YY88888P88P     `Y88P""Y88P\'   8I   `Y8
                                               I8                                        
                                               I8                                        
                                               I8                                        
                                               I8                                        
                                               I8                                        
                                               I8                                        
        ';

        $this->out($header, "info");
        $this->out("\n");
    }

    /**
     * Prints Doplhin basic usage
     */
    public function printUsage()
    {
        $this->out("Usage: ./dolphin [command] [sub-command] [params]\n", "unicorn");
        $this->out("For help, use ./dolphin help\n", "info");
    }

    /**
     * @param array $table
     * @param int $min_col_size
     * @param bool $with_header
     */
    public function printTable(array $table, $min_col_size = 10, $with_header = true, $spacing = true)
    {
        $first = true;

        if ($spacing) {
            $this->newline();
        }

        foreach ($table as $index => $row) {

            $style = "default";
            if ($first && $with_header) {
                $style = "info_alt";
            }

            $this->printRow($table, $index, $style, $min_col_size);
            $first = false;
        }

        if ($spacing) {
            $this->newline();
        }
    }

    /**
     * @param array $table
     * @param int $row
     * @param string $style
     * @param int $min_col_size
     */
    public function printRow(array $table, $row, $style = "default", $min_col_size = 5)
    {

        foreach ($table[$row] as $column => $table_cell) {
            $col_size = $this->calculateColumnSize($column, $table, $min_col_size);

            $this->printCell($table_cell, $style, $col_size);
        }

        $this->out("\n");
    }

    /**
     * @param string $table_cell
     * @param string $style
     * @param int $col_size
     */
    protected function printCell($table_cell, $style = "default", $col_size = 5)
    {
        $table_cell = str_pad($table_cell, $col_size);
        $this->out($table_cell, $style);
    }

    /**
     * @param $column
     * @param array $table
     * @param int $min_col_size
     * @return int
     */
    protected function calculateColumnSize($column, array $table, $min_col_size = 5)
    {
        $size = $min_col_size;

        foreach ($table as $row) {
            $size = strlen($row[$column]) > $size ? strlen($row[$column]) + 2 : $size;
        }

        return $size;
    }
}