<?php

class Apps {

    protected static $_oInstance;

    public static function getInstance(): Apps {

        if (null === self::$_oInstance) {
            self::$_oInstance = new self();
        }
        return self::$_oInstance;
    }

    public function dispatch() {

        $oRequest = Request::getInstance();
        $oView = View::getInstance();
        try {
            Controller::process($oRequest, $oView)->printOut();
        } catch (Exception $oE) {
            Controller::processException($oRequest, $oView, $oE)->printOut();
        }
    }

    /**
     * @return Apps
     * Start session
     */
    public static function start(): Apps {

        global $oLayout;
        require 'apps/configs/config.inc.php';
        $oApplication = Apps::getInstance();
        $oApplication->autoloader();
        Session::start();
        $oLayout = Layout::getInstance();

        return $oApplication;
    }

    /**
     * Autoloader
     */
    public function autoloader() {
        // Set include path
        $sPath = (string) get_include_path();
        $sPath .= (string) (PATH_SEPARATOR . LIBRARY_PATH );
        $sPath .= (string) (PATH_SEPARATOR . APPS_PATH );
        $sPath .= (string) (PATH_SEPARATOR . EXTENSIONS_PATH );
        $sPath .= (string) (PATH_SEPARATOR . APPS_PATH . '/models' );
        $sPath .= (string) (PATH_SEPARATOR . EXTENSIONS_PATH );
        $sPath .= (string) (PATH_SEPARATOR . LIBRARY_PATH . '/Model' );

        set_include_path($sPath);
        spl_autoload_register(array('Apps', 'loadClass'));
    }

    /**
     * @param $sClassName
     * Function to load class in the controller
     */
    public function loadClass($sClassName) {
        $sClassName = (string) str_replace('_', DIRECTORY_SEPARATOR, $sClassName);
        include_once($sClassName . '.php');
    }

    /**
     * Apps::getModel('Test');
     * @param string $sModelName
     * @return object Model_Abstract
     */
    public static function getModel(string $sModelName) {
        $tModelDirectory = explode('_', $sModelName);
        array_pop($tModelDirectory);
        if (count($tModelDirectory) > 1) {
            $sPath = (string) get_include_path();
            $sPath .= (string) (PATH_SEPARATOR . APPS_PATH .
                    '/models/' . str_replace('_', '/', $sModelName));

            $sPathToAdded = APPS_PATH . '/models/' . str_replace('_', '/', $sModelName);
            $tPaths = explode(PATH_SEPARATOR, get_include_path());
            if (!in_array($sPathToAdded, $tPaths)) {
                set_include_path($sPath);
            }
        }

        $sClassModel = $sModelName . 'Model';
        return new $sClassModel;
    }

    /**
     * @param $sManagerName
     */
    public static function getManager($sManagerName) {
        // TODO
    }

    /**
     * Apps::getResourceModel('Acl');
     * @param string $sModelName
     * @return object Model_Abstract
     */
    public static function usePlugin(string $sModelName) {
        $sClassModel = str_replace('/', '_', $sModelName);

        return new $sClassModel;
    }

    /**
     * @param $q
     * @return string
     * Function to encrypt some string using define key
     */
    public static function encryptIt($q): string {
        $cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
        $qEncoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), $q, MCRYPT_MODE_CBC, md5(md5($cryptKey))));

        return( $qEncoded );
    }

    /**
     * @param $q
     * @return string
     * After using encrypt function, data is needed to be decrypted
     */
    public static function decryptIt($q): string {
        $cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
        $qDecoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), base64_decode($q), MCRYPT_MODE_CBC, md5(md5($cryptKey))), "\0");
        return( $qDecoded );
    }

    /**
     * @param $date
     * @param string $format
     * @return false|string
     * Date format form the database
     */
    public static function convertDate($date, $format = 'Y-m-d') {

        return date($format, strtotime(str_replace('/', '-', $date)));
    }

    /**
     * @param bool $month_input
     * @return string|string[]
     */
    public static function month(bool $month_input = null) {
        $month = array(
            1 => "Janvier",
            2 => "Février",
            3 => "Mars",
            4 => "Avril",
            5 => "Mai",
            6 => "Juin",
            7 => "Juillet",
            8 => "Août",
            9 => "Septembre",
            10 => "Octobre",
            11 => "Novembre",
            12 => "Décembre"
        );
        if (!is_null($month_input)) {
            return $month[$month_input];
        }

        return $month;
    }

    public static function weekOfMonth($month, $year): array {

        $lastday = strftime("%W", mktime(0, 0, 0, $month + 1, 0, $year));
        $firstday = strftime("%W", mktime(0, 0, 0, $month, 1, $year));
        $weeks = array();
        for ($i = (int) $firstday; $i <= $lastday; $i++)
            $weeks[] = $i;

        return $weeks;
    }

    /**
     * @param string $logname
     * @param $_content
     * @param null $return
     * @return bool
     * Write error into the log file
     */
    public static function writeLog(string $logname, $_content, $return = null): bool {

        $content = str_replace(array('<br/>', '<br>'), "\r\n", $_content);
        $file = $logname . '.txt';
        $dir = ABSOLUTE_DIR . '/var/log/' . $file;

        if (file_exists($dir)) {
            $filePointer = fopen($dir, "a+");
        } else {
            $filePointer = fopen($dir, "w+");
        }
        fwrite($filePointer, $content);
        fclose($filePointer);

        if ($return) {

            return true;
        }
    }

    /**
     * @param $file
     * Print log in the file
     */
    public static function printLog($file) {
        $file = $file . '.txt';
        $dir = ABSOLUTE_DIR . '/var/log/' . $file;
        if (file_exists($dir)) {

            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $file . '"');
            readfile($dir);
        }
    }

    /**
     * @param $role
     * @return array
     * Get the menu properties
     */
    /*public static function getMenu($role): array
    {
        $oModel = Apps::getModel('Systemes_Menu');
        return $oModel->getMenu($role, IS_CONSO);

    }*/

    public static function getMenu() {

        return [];
    }

    /**
     * @param array $arr
     * @param null $element
     * @param int $index
     * @return array
     */
    public static function insertElementToArray($arr = array(), $element = null, $index = 0): array {

        if ($element == null) {
            return $arr;
        }
        $arrLength = count($arr);
        $j = $arrLength - 1;

        while ($j >= $index) {
            $arr[$j+1] = $arr[$j];
            $j--;
        }

        $arr[$index] = $element;

        return $arr;
    }

    /**
     * @param array $array
     * @param string $condition
     *
     * @return array
     */
    public static function sortArray(array $array, string $condition = 'id'): array {
        $newTableau = [];
        foreach($array as $key => $value){
            $newTableau[$value[$condition]][$key] = $value;
        }

        return $newTableau;
    }
	/**
     * Formatage de la taille d'un fichier
     * @param $bytes
     * @return string
     */
    public static function formatSizeUnits($bytes): string {

        if ($bytes >= 1073741824)  {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }  elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * @param $number
     * @return string
     * Format value in the TDB
     */
    public static function formatValue($number): string {

        return number_format($number, '2', '.', ' ');
    }

    /**
     * @param $number
     * @return string
     * Format money for easy reading value
     */
    public static function formatMonetaire($number): string {

        return number_format($number, '0', '.', ' ');
    }

    /**
     * @param $number
     * @return float
     * Round number half up
     */
    public static function roundUp($number): float {

        return round($number, '0', PHP_ROUND_HALF_UP);
    }

    /**
     * @param $sValue
     * @return string|string[]
     * To erase space from MySQL
     */
    public static function formatToNumber($sValue) {
        $nValue = str_replace(",", ".", $sValue);
        $nValue = str_replace(" ", "", $nValue);
        
        return $nValue;
    }
    
}
