<?php 

/**
 * Fonts module class
 *
 * @package munkireport
 * @author tuxudo
 **/
class Fonts_controller extends Module_controller
{
    /*** Protect methods with auth! ****/
    function __construct()
    {
        // Store module path
        $this->module_path = dirname(__FILE__);
    }

    /**
     * Default method
     * @author tuxudo
     *
     **/
    function index()
    {
        echo "You've loaded the fonts module!";
    }

    /**
     * Get font names for widget
     *
     * @return void
     * @author tuxudo
     **/
     public function get_fonts()
     {
        $obj = new View();

        if (! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        
        $fonts = new Fonts_model();
        $obj->view('json', array('msg' => $fonts->get_fonts()));
     }

    /**
     * Get font vendors for widget
     *
     * @return void
     * @author tuxudo
     **/
     public function get_vendor()
     {
        $obj = new View();

        if (! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        
        $fonts = new Fonts_model();
        $obj->view('json', array('msg' => $fonts->get_vendor()));
     }

    /**
     * Get font types for widget
     *
     * @return void
     * @author tuxudo
     **/
     public function get_type()
     {
        $obj = new View();

        if (! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        
        $fonts = new Fonts_model();
        $obj->view('json', array('msg' => $fonts->get_type()));
     }

    /**
     * Get data for scrollbox widgets
     *
     * @return void  
     * @author tuxudo
     **/
    public function get_list($column)
    {
        // Remove non-column name characters
        $column = preg_replace("/[^A-Za-z0-9_\-]]/", '', $column);

        $fonts = new Fonts_model();
        $sql = "SELECT COUNT(CASE WHEN `".$column."` <> '' AND `".$column."` IS NOT NULL THEN 1 END) AS count, `".$column."` AS label
                FROM fonts
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                GROUP BY `".$column."`
                ORDER BY count DESC";

        $out = [];
        foreach ($fonts->query($sql) as $obj) {
            if ("$obj->count" !== "0") {
                $obj->label = $obj->label ? $obj->label : 'Unknown';
                $out[] = $obj;
            }
        }

        jsonView($out);
    }

    /**
     * Get data for button widget
     *
     * @return void
     * @author tuxudo
     **/
    public function get_button_widget($column)
    {
        // Remove non-column name characters
        $column = preg_replace("/[^A-Za-z0-9_\-]]/", '', $column);

        $sql = "SELECT COUNT(CASE WHEN `".$column."` = '1' THEN 1 END) AS 'yes',
                    COUNT(CASE WHEN `".$column."` = '0' THEN 1 END) AS 'no'
                    FROM fonts
                    LEFT JOIN reportdata USING (serial_number)
                    WHERE ".get_machine_group_filter('');

        $out = [];
        $queryobj = new Fonts_model();
        $result = $queryobj->query($sql);
        if ($result && count($result) > 0 && is_object($result[0])) {
            foreach((array)$result[0] as $label => $value){
                    $out[] = ['label' => $label, 'count' => $value];
            }
        }

        jsonView($out);
    }

    /**
     * Retrieve data in json format
     *
     **/
     public function get_data($serial_number = '')
     {
        $obj = new View();

        if (! $this->authorized()) {
            $obj->view('json', array('msg' => 'Not authorized'));
            return;
        }

        $queryobj = new Fonts_model();
        $fonts_tab = array();
        
        // Fix null safety issue to prevent crashes
        $records = $queryobj->retrieve_records($serial_number);
        if ($records) {
            foreach($records as $fontEntry) {
                $fonts_tab[] = $fontEntry->rs;
            }
        }

        $obj->view('json', array('msg' => $fonts_tab));
     }

} // END class Fonts_controller
