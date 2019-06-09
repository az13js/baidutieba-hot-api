<?php
class Example
{
    private $curl = null;
    private $curlError = '';
    private $userAgent = ''; // 在此定义浏览器的UA
    private $cookie = ''; // 填写cookie

    public function handle()
    {
        $last_tid = 0;
        for ($page = 1; $page <= 50; $page++) {
            // 执行获取数据操作
            $result = $this->curlGet('https://tieba.baidu.com/f/index/feedlist?' . http_build_query([
                'is_new' => 1,
                'tag_id' => 'all',
                'limit' => 20,
                'offset' => ($page - 1) * 20,
                'last_tid' => $last_tid,
                '_' => time() * 1000
            ]));

            // 获取失败检测
            if (empty($result)) {
                echo 'Error, curl get data but return empty.' . PHP_EOL;
                echo 'Error info: ' . $this->curlError . PHP_EOL;
                echo 'Page: ' . $page . PHP_EOL;
                echo 'Time: ' . date('Y-m-d H:i:s') . PHP_EOL;
                echo 'Now return 0 and exit.' . PHP_EOL;
                return 0;
            }
            $resultHash = hash('sha256', $result);

            // 保存原始数据
            file_put_contents($resultHash, $result);
            echo 'Save result as ' . $resultHash . PHP_EOL;

            // JSON 解码和 JSON 解码检测
            $resultArray = json_decode($result, true);
            if (empty($resultArray)) {
                echo 'Json decode fail. Result file is' . PHP_EOL;
                echo $resultHash . PHP_EOL;
                echo 'Now return 0 and exit.' . PHP_EOL;
                return 0;
            }

            // 需要的参数进行检测
            if (!isset($resultArray['data'])) {
                echo 'Error, json decode data no attribute "data"' . PHP_EOL;
                echo 'File: ' . $resultHash . PHP_EOL;
                return 0;
            }
            // 读取数据并保存到CSV文件
            $as = $this->process($resultArray['data']['html']);
            foreach ($as as &$a) {
                if (false === mb_stripos($a, 'title feed-item-link')) {
                    continue;
                }
                $a = str_replace('href="', 'href="https://tieba.baidu.com', $a);
                file_put_contents('URL.txt', $a . PHP_EOL, FILE_APPEND);
            }

            $last_tid = $resultArray['data']['last_tid'];
            if (!$resultArray['data']['has_more']) {
                echo 'No more data, end' . PHP_EOL;
                break;
            }
        }
    }

    public function curlGet($url)
    {
        if (is_null($this->curl)) {
            $h = curl_init();
            curl_setopt($h, CURLOPT_AUTOREFERER, true);
            curl_setopt($h, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($h, CURLOPT_FORBID_REUSE, false);
            curl_setopt($h, CURLOPT_FRESH_CONNECT, false);
            curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($h, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($h, CURLOPT_DNS_CACHE_TIMEOUT, 60 * 5);
            curl_setopt($h, CURLOPT_MAXCONNECTS, 10);
            curl_setopt($h, CURLOPT_MAXREDIRS, 20);
            curl_setopt($h, CURLOPT_TIMEOUT, 60 * 2);
            curl_setopt($h, CURLOPT_REFERER, 'https://tieba.baidu.com/index.html');
            curl_setopt($h, CURLOPT_USERAGENT, $this->userAgent);
            curl_setopt($h, CURLOPT_HTTPHEADER, ['Content-type: text/html; charset=UTF-8']);
            curl_setopt($h, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($h, CURLOPT_COOKIE, $this->cookie);
            $this->curl = $h;
        } else {
            $h = $this->curl;
        }
        curl_setopt($h, CURLOPT_URL, $url);
        $data = curl_exec($h);
        if (false === $data) {
            $this->curlError = curl_error($h);
        }
        return $data;
    }

    private function process($html, $pgx = '#<a[\S|\s]*?>[\S|\s]*?</a>#i')
    {
        $result = [];
        preg_match_all($pgx, $html, $result);
        $strings = [];
        foreach ($result as $v1) {
            if (is_array($v1)) {
                foreach ($v1 as $v2) {
                    if (is_array($v2)) {
                        foreach ($v2 as $v3) {
                            $strings[] = $v3;
                        }
                    } else {
                        $strings[] = $v2;
                    }
                }
            } else {
                $strings[] = $v1;
            }
        }
        return $strings;
    }
}

(new Example())->handle();
