<?php
/**
 * 喜马拉雅榜单相关接口
 * @author richiesong
 *
 */
class XmlyRank
{
    const APP_KEY = 'b617866c20482d133d5de66fceb37da3';
    
    const APP_SECRET = '4d8e605fa7ed546c4bcb33dee1381179';
    
    const CLIENT_OS_TYPE = '4';
    
    const SERVER_AUTH_STATIC_KEY = 'de5kio2f';
    
    const URI_RANK_LIST = '/ranks/index_list'; //根据rank_key获取某个榜单下的专辑列表
    const URI_RANK_ALBUMS = '/ranks/albums'; //根据rank_key获取某个榜单下的专辑列表
    
    const RANK_KEY_DAY = '1_57_ranking:track:scoreByTime:1:0'; //日榜
    const RANK_KEY_WEEK = '1_51_ranking:album:subscribed:7:5'; //周榜
    const RANK_KEY_MONTH = '1_21_ranking:album:played:30:0'; //月榜
   
    /**
     * 获取某个榜单下的专辑列表
     * @param unknown $rank_key
     * @param number $page
     * @param number $count
     */
    public function get_rank_albums($rank_key, $page = 1, $count = 100) {
        
        $data = [
            'rank_key' => $rank_key,
            'page' => $page,
            'count' => $count,
        ];
        
        $url = $this->get_req_url(self::URI_RANK_ALBUMS);
        $params = $this->get_req_params($data);
        
        $url = $url.'?'.http_build_query($params);
      
        $request = new HTTP_Request2($url, HTTP_Request2::METHOD_GET);
        
        $request = new HTTP_Request2($url, HTTP_Request2::METHOD_GET);
        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $content = $response->getBody();
                ee(json_decode($content));
            } else {
                echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                    $response->getReasonPhrase();
            }
        } catch (HTTP_Request2_Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
        
    }

    /**
     * 排行榜索引
     */
    public function get_rank_index_list() {
        
        $url = $this->get_req_url(self::URI_RANK_LIST);
        $params = $this->get_req_params(['rank_type'=>1]);
        
        $url = $url.'?'.http_build_query($params);
        
        $request = new HTTP_Request2($url, HTTP_Request2::METHOD_GET);
        
        try {
            $response = $request->send();
            $content = $response->getBody();
            ee(json_decode($content));
        } catch (HTTP_Request2_Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
        
    }
    
    /**
     * 获取请求地址
     * @param unknown $uri
     * @return string
     */
    private function get_req_url($uri) {
        return 'http://api.ximalaya.com/openapi-gateway-app'.$uri;
    }

    /**
     * 生成查询字符串
     * @param unknown $data
     */
    private function get_not_urlencode_query($data) {
        
        $res = [];
        foreach ($data as $key => $value) {
            $res[] = $key . '=' . $value;
        }
        $res = implode('&', $res);
        
        return $res;
        
    }
    
    private function get_nonce() {
        
        $microtime = microtime();
        $microtime = explode(' ', $microtime);
        $timestamp = $microtime[1] . substr($microtime[0], 2, 3);
        $nonce = $timestamp . mt_rand(1000, 9999);
        return $nonce;
    }
    
    /**
     * 获取请求参数
     * @param array $params
     */
    private function get_req_params(array $params) {
        
        unset($params['sign']);
        
        $params = array_merge($params, $this->get_common_params());
        ksort($params);
        
        $sigStrTmp = base64_encode($this->get_not_urlencode_query($params));
        $signKey = self::APP_SECRET . self::SERVER_AUTH_STATIC_KEY;
        $sigStrTmpBin = hash_hmac('sha1', $sigStrTmp, $signKey, true);
        $sig = md5($sigStrTmpBin);
        
        $params['sig'] = $sig;
        
        return  $params;
        
    }
    
    /**
     * 获取通用参数
     */
    private function get_common_params() {
        
        $params = [
            'app_key' => self::APP_KEY,
            'client_os_type' => self::CLIENT_OS_TYPE,
            'nonce' => $this->get_nonce(),
            'timestamp' => intval(microtime(1)*10000/10),
        ];
        
        return $params;
        
    }
    
}

$xmly = new XmlyRank();
// ee($xmly->get_rank_index_list());
ee($xmly->get_rank_albums(XmlyRank::RANK_KEY_WEEK));
