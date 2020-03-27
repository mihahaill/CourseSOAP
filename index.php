<?php
/*
 * Class of SOAPClient
 */

class Client {

    public const USD = 10;
    public const EUR = 11;

    public $soapClient;

    public function __construct() {
        //create SoapClient from WSDL URI
        $this->$soapClient = new SoapClient("http://cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL", array('trace' => 1));
    }

    /*
     * @cursDate date for needed curs
     * @valuteIndex number in list of curses
     * return array with valute info
     */

    public function GetCursOnDate($cursDate, $valuteIndex) {
        //required params for request
        $param = array(
            'On_date' => $cursDate
        );

        try {
            //call function from wsdl
            $info = $this->$soapClient->GetCursOnDate($param);
            $data = Service::xmlParser($info);
            return $data->diffgram->ValuteData->ValuteCursOnDate[$valuteIndex];
        } catch (SoapFault $fault) {
            print("alert('Произошла ошибка:" . $fault->faultcode . "-" . $fault->faultstring);
        }
    }

}

/*
 * Store service functions and work with Client model
 */

class Service {

    public $client;

    public function __construct() {
        $this->client = new Client();
    }

    /*
     * @xml string Response from Client function
     * return array
     */

    public static function xmlParser($xml) {
        $xml = str_replace(array("diffgr:", "msdata:"), '', $xml->GetCursOnDateResult->any);
        $xml = "<package>" . $xml . "</package>";
        $data = simplexml_load_string($xml);
        return $data;
    }

    /*
     * Get curs on date
     * @cursDate date for needed curs
     * @valuteIndex number in list of curses
     * return array with valute info
     */

    public function GetCursOnDate($cursDate, $valuteIndex) {
        return $this->client->GetCursOnDate($cursDate, $valuteIndex);
    }

}
?>

<html>
    <head>
        <title>Курсы валют</title>
    </head>
    <body>
        <?php
        $yesterday = new DateTime();
        $today = $yesterday->format('Y-m-d');
        $yesterday->modify("-1 day");
        $yesterday = $yesterday->format('Y-m-d');

        $service = new Service();

        $cursFromUSD = $service->GetCursOnDate($today, Client::USD);
        $cursToUSD = $service->GetCursOnDate($yesterday, Client::USD);
        $cursFromEUR = $service->GetCursOnDate($today, Client::EUR);
        $cursToEUR = $service->GetCursOnDate($yesterday, Client::EUR);
        ?>
        <table border="1">
            <thead>
            <td><?php echo "Валюта" ?></td>
            <td><?php echo $cursToUSD->Vname ?></td>
            <td><?php echo $cursToEUR->Vname ?></td>
        </thead>
        <tr>
            <td><?php echo "Сегодня" ?></td>
            <td><?php echo $cursToUSD->Vcurs ?></td>
            <td><?php echo $cursToEUR->Vcurs ?></td>
        </tr>
        <tr>
            <td><?php echo "Динамика" ?></td>
            <td><?php
                $cursFromUSD = floatval($cursFromUSD->Vcurs);
                $cursToUSD = floatval($cursToUSD->Vcurs);
                if ($cursFromUSD < $cursToUSD) {
                    echo "&#8593"; //стрелка вниз
                } else if ($cursFromUSD > $cursToUSD) {
                    echo "&#8595"; //стрелка вверх
                } else {
                    echo "не изменился";
                }
                ?></td>
            <td><?php
                $cursFromEUR = floatval($cursFromEUR->Vcurs);
                $cursToEUR = floatval($cursToEUR->Vcurs);
                if ($cursFromEUR < $cursToEUR) {
                    echo "&#8593"; //стрелка вниз
                } else if ($cursFromEUR > $cursToEUR) {
                    echo "&#8595"; //стрелка вверх
                } else {
                    echo "не изменился";
                }
                ?></td>
        </tr>
    </table>
</body>
</html>