<?php

class SimpleClient implements SourceClientInterface
{
    const API_HOST = 'https://api.ssidorov.ru/';
    const API_TOKEN_PATH = 'auth';
    const API_DATA_PATH = 'posts';

    /**
     * @var array
     */
    private $_authDetails;
    /**
     * @var
     */
    private $_authToken;

    /**
     * SimpleClient constructor.
     * @param string $clientId
     * @param string $email
     * @param string $name
     */
    public function __construct(string $clientId, string $email, string $name)
    {
        $this->_authDetails = [
            'client_id' => $clientId,
            'email' => $email,
            'name' => $name,
        ];
    }

    /**
     * @return array
     */
    public function fetchAll(): array
    {
        $usefulData = [];
        $hasMoreData = true;
        try {
            $this->_setToken();
        } catch (\Exception $e) {
            //log this
            $hasMoreData = false;
        }

        $pageId = 1;

        while ($hasMoreData) {
            $data = $this->_fetchPage($pageId);
            if ($data['page'] === $pageId) {
                foreach ($data['posts'] as $post) {
                    $usefulData[$post['id']] = $this->_getUsefulData($post);
                }
                $pageId++;
            } else {
                $hasMoreData = false;
            }
        }
        return $usefulData;
    }

    /**
     * @throws Exception
     */
    private function _setToken()
    {
        $payload = json_encode($this->_authDetails);
        $url = self::API_HOST . self::API_TOKEN_PATH;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload))
        );
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);
        if (!isset($result['data']['sl_token'])) {
            throw new Exception('Token is not set');
        }
        $this->_authToken = $result['data']['sl_token'];
    }

    /**
     * @param int $pageId
     * @return array
     */
    private function _fetchPage(int $pageId): array
    {
        $queryData = ['sl_token' => $this->_authToken, 'page' => $pageId];
        $url = self::API_HOST . self::API_DATA_PATH . '?' . http_build_query($queryData);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);
        if (!isset($result['data'])) {
            return [];
        }
        return $result['data'];
    }

    /**
     * @param array $post
     * @return array
     */
    private function _getUsefulData(array $post): array
    {
        return [
            'month'   => $this->_extractMonth($post['created_time']),
            'week'    => $this->_extractWeek($post['created_time']),
            'user_id' => $this->_extractUserId($post['from_id']),
            'length'  => $this->_getContentLength($post['message']),
        ];
    }

    /**
     * @param string $date
     * @return string
     */
    private function _extractMonth(string $date) : string
    {
        return date('Y-n', strtotime($date));
    }

    /**
     * @param string $date
     * @return int
     */
    private function _extractWeek(string $date) : int
    {
        return (int)date('W', strtotime($date));
    }

    /**
     * @param string $user
     * @return int
     */
    private function _extractUserId(string $user) : int
    {
        return (int)explode('_', $user)[1];
    }

    /**
     * @param string $message
     * @return int
     */
    private function _getContentLength(string $message) : int
    {
        return strlen($message);
    }

}
