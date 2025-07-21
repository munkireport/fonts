<?php

use CFPropertyList\CFPropertyList;

class Fonts_model extends \Model {

    function __construct($serial='')
    {
        parent::__construct('id', 'fonts'); // Primary key, tablename
        $this->rs['id'] = '';
        $this->rs['serial_number'] = $serial;
        $this->rs['name'] = '';
        $this->rs['enabled'] = 0; // True or False
        $this->rs['type_name'] = '';
        $this->rs['fullname'] = '';
        $this->rs['type_enabled'] = 0; // True or False
        $this->rs['valid'] = 0; // True or False
        $this->rs['duplicate'] = 0; // True or False
        $this->rs['path'] = '';
        $this->rs['type'] = '';
        $this->rs['family'] = '';
        $this->rs['style'] = '';
        $this->rs['version'] = '';
        $this->rs['embeddable'] = 0; // True or False
        $this->rs['outline'] = 0; // True or False
        $this->rs['unique_id'] = '';
        $this->rs['copyright'] = '';
        $this->rs['copy_protected'] = 0; // True or False
        $this->rs['description'] = '';
        $this->rs['vendor'] = '';
        $this->rs['designer'] = '';
        $this->rs['trademark'] = '';

        $this->serial_number = $serial;
    }

    // ------------------------------------------------------------------------

     /**
     * Get font names for widget
     *
     **/
     public function get_fonts()
     {
        $out = array();
        $sql = "SELECT COUNT(CASE WHEN type_name <> '' AND type_name IS NOT NULL THEN 1 END) AS count, type_name 
                FROM fonts
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                GROUP BY type_name
                ORDER BY count DESC";

        foreach ($this->query($sql) as $obj) {
            if ("$obj->count" !== "0") {
                $obj->type_name = $obj->type_name ? $obj->type_name : 'Unknown';
                $out[] = $obj;
            }
        }
        return $out;
     }

     /**
     * Get font vendors for widget
     *
     **/
     public function get_vendor()
     {
        $out = array();
        $sql = "SELECT COUNT(CASE WHEN vendor <> '' AND vendor IS NOT NULL THEN 1 END) AS count, vendor 
                FROM fonts
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                GROUP BY vendor
                ORDER BY count DESC";

        foreach ($this->query($sql) as $obj) {
            if ("$obj->count" !== "0") {
                $obj->vendor = $obj->vendor ? $obj->vendor : 'Unknown';
                $out[] = $obj;
            }
        }
        return $out;
     }

     /**
     * Get font types for widget
     *
     **/
     public function get_type()
     {
        $out = array();
        $sql = "SELECT COUNT(CASE WHEN type <> '' AND type IS NOT NULL THEN 1 END) AS count, type 
                FROM fonts
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                GROUP BY type
                ORDER BY count DESC";

        foreach ($this->query($sql) as $obj) {
            if ("$obj->count" !== "0") {
                $obj->type = $obj->type ? $obj->type : 'Unknown';
                $out[] = $obj;
            }
        }
        return $out;
     }

    /**
     * Process data sent by postflight
     *
     * @param string data
     * @author tuxudo
     **/
    function process($plist)
    {

        if ( ! $plist){
            throw new Exception("Error Processing Request: No property list found", 1);
        }

        // Delete previous set        
        $this->deleteWhere('serial_number=?', $this->serial_number);

        $parser = new CFPropertyList();
        $parser->parse($plist, CFPropertyList::FORMAT_XML);
        $myList = $parser->toArray();

        foreach ($myList as $font) {
            // Check if we have a name
            if( ! array_key_exists("name", $font)){
                continue;
            }

            // Format font type
            if (isset($font['type'])){
                $font['type'] = str_replace(array('opentype','truetype','postscript','bitmap'), array('OpenType','TrueType','PostScript','Bitmap'), $font['type']);
            }

            // Format Unique ID
            if (isset($font['unique_id'])){
                $font['unique_id'] = trim($font['unique_id'], '.');
            }

            // Format Typeface name
            if (isset($font['type_name'])){
                $font['type_name'] = trim($font['type_name'], '.');
            }

            // Format family
            if (isset($font['family'])){
                $font['family'] = trim($font['family'], '.');
            }

            foreach ($this->rs as $key => $value) {
                $this->rs[$key] = $value;
                if(array_key_exists($key, $font))
                {
                    $this->rs[$key] = $font[$key];
                }
            }

            // Save the font
            $this->id = '';
            $this->save();
        }
    }
}
