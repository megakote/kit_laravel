<?

namespace slowdream\kit_laravel;

use Illuminate\Support\Facades\Cache;

class Kit
{
    private $token;
    private $apiUrl = 'https://tk-kit.ru/API.1.1';

    public function __construct()
    {
        $this->token = config('kit.token');
    }


    private function sendRequest($func, $data = [])
    {
        $url = $this->apiUrl . '?token=' . $this->token . '&f=' . $func;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, count($data));
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_URL, $url);
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
          'data' => json_decode($responseBody, true)
        ];
    }


    public function getCityList()
    {
        // Закешируем на 600 минут список городов, если кеша нет, то сделаем запрос и результат поместим в кеш.
        $cityList = Cache::remember('CityList', 600, function () {
            return $this->sendRequest('get_city_list');
        });
        return $cityList;
    }


    public function isCity(string $city)
    {
        // Закешируем на 600 минут ответ сервера, если кеша нет, то сделаем запрос и результат поместим в кеш.
        $cityData = Cache::remember('Kit_' . $city, 600, function () use ($city) {
            $data = $this->sendRequest('is_city', ['city' => $city]);
            return $data['data'];
        });

        if ($cityData === [0]) {
            return false;
        }
        $data = explode(':', $cityData[0]);
        $vals = ['COUNTRY', 'REGION', 'TZONEID', 'ID', 'SR'];
        $cityData = [];
        foreach ($data as $key => $value) {
            $cityData[$vals[$key]] = $value;
        };

        return $cityData;

    }


    public function priceOrder(array $data, string $city_from, string $city_to)
    {
        if (!$city_from = $this->isCity($city_from)) {
            return ['error' => 'Не работаем с '. $city_from];
        }
        if (!$city_to = $this->isCity($city_to)) {
            return ['error' => 'Не работаем с '. $city_to];
        }

        $data['I_HAVE_DOC'] = ($data['I_HAVE_DOC'] == 'on') ? true : false;

        if (isset($data['DELIVERY']))
            $data['DELIVERY'] = ($data['DELIVERY'] == 'on') ? true : false;

        if (isset($data['PICKUP']))
            $data['PICKUP'] = ($data['PICKUP'] == 'on') ? true : false;

        $data['SLAND'] = $city_from['COUNTRY'];
        $data['SCODE'] = $city_from['ID'];
        $data['SZONE'] = $city_from['TZONEID'];
        $data['SREGIO'] = $city_from['REGION'];

        $data['RSLAND'] = $city_to['COUNTRY'];
        $data['RCODE'] = $city_to['ID'];
        $data['RZONE'] = $city_to['TZONEID'];
        $data['RREGIO'] = $city_to['REGION'];

        return $this->sendRequest('price_order', $data);
    }

}