<?

namespace slowdream\kit_laravel;

class Kit
{
    private $token;
    private $apiUrl = 'https://tk-kit.ru/API.1.1';

    public function __construct()
    {
        //$this->token = config('kit.token');
    }


    private function sendRequest($func, $data = [])
    {
        $url = $this->apiUrl . '?token=' . $this->token . '&f=' . $func;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, count($data));
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_URL, $url);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headerCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $responseBody = substr($response, $header_size);
        curl_close($curl);


        return [
          'http_code' => $headerCode,
          'data' => json_decode($responseBody)
        ];
    }


    public function getCityList()
    {
        //return $this->sendRequest('get_city_list');
        return [
          "CITY" => [
            [
              "ID" => "565500100000",
              "OC" => "",
              "SP" => "",
              "SR" => "N",
              "TP" => "гор.",
              "NAME" => "Абдулино",
              "TZONE" => "N",
              "REGION" => "02",
              "COUNTRY" => "RU",
              "TZONEID" => "0000000212"
            ]
          ]
        ];
    }


    public function isCity($city)
    {
        $data = $this->sendRequest('is_city', ['city' => $city]);
        //return ($data == [0]) ? false : $data;
        return [
          "RU:66:0000006600:660000100000:Y"
        ];

    }


    public function priceOrder(array $data)
    {
        [
          'WEIGHT' => 10,
          // Вес груза в кг
          'VOLUME' => 1.3,
          // Объём груза в метрах кубических. Если переданы размеры (длина, ширины и высота), то объём считается исходя из размеров и данный параметр не учитывается.
          'LENGTH' => 0,
          //	Длина груза в сантиметрах (не обязательно)
          'WIDTH' => 0,
          // Ширина груза в сантиметрах (не обязательно)
          'HEIGHT' => 0,
          // Высота груза в сантиметрах (не обязательно)
          'SLAND' => "RU",
          //	Отправка из - Код страны (см. описание функции get_city_list)
          'SZONE' => "0000000212",
          // Отправка из - Транспортная зона (обязательно) (см. описание функции get_city_list поле TZONEID)
          'SREGIO' => '02',
          // Отправка из - Код региона (см. описание функции get_city_list)
          'RLAND' => "RU",
          'RZONE' => "0000000212",
          'RREGIO' => '02',
          'PRICE' => 1000,
          // Стоимость груза
          'I_HAVE_DOC' => true,
          // Есть документы подтверждающие стоимость груза
        ];

        //return $this->sendRequest('price_order', $data);
        return [
          "DAYS" => 1,
          "PRICE" => [
            "TOTAL" => "0.0",
            "PICKUP" => "0.0",
            "DELIVERY" => "0.0",
            "TRANSFER" => "0.0"
          ],
          "E_RATE" => [
            "KZT" => "5.0",
            "RUB" => 1
          ],
          "E_WAERS" => "RUB"
        ];
    }
}