<?php

namespace App\Utils;

use App\Models\User;
use App\Services\Config;

class Tuling
{
    public static function chat($user, $text)
    {
        if (Config::get('enable_tuling') == 'true') {
            if (substr($text, 0, 1) == '/') {
                $text = substr($text, 1);
            }
            $data = array();
            $data['key'] = Config::get('tuling_apikey');
            $data['userid'] = $user;
            $data['info'] = $text;
            
            $param = json_encode($data);
            
            
            $sock = new HTTPSocket;
            $sock->connect("www.tuling123.com", 80);
            $sock->set_method('POST');
            $sock->add_header('Content-Type', 'application/json');
            $sock->query('/openapi/api', $param);
            
            $result = $sock->fetch_body();
            $result_array = json_decode($result, true);

            $result_content = $result_array['text'];
            //文本类
            if ($result_array['code'] == '100000') {
                //Do nothing
            }
            //链接类
            elseif ($result_array['code'] == '200000') {
                $result_content = $result_content.PHP_EOL.
                '[打开页面]('.$result_array['url'].')';
            }
            //新闻类
            elseif ($result_array['code'] == '302000') {
                foreach ($result_array['list'] as $key) {
                    $result_content = $result_content.PHP_EOL.PHP_EOL.
                    '['.$key['article'].']('.$key['detailurl'].')';
                }
            }
            //菜谱类
            elseif ($result_array['code'] == '308000') {
                foreach ($result_array['list'] as $key) {
                    $result_content = $result_content.PHP_EOL.PHP_EOL.
                    '['.$key['name'].' '.$key['info'].']('.$key['detailurl'].')';
                }
            }

            return $result_content;
        }
    }
}
