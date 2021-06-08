<?php

namespace collect\models\collect;

class CollectFactory
{
    public static function create($links, $param)
    {
        $successNum = 0;
        set_time_limit(0);
        if (is_string($links)) {
            $links = [$links];
        }
        foreach ($links as $link) {
            // 获取链接的域名
            $host = parse_url($link, PHP_URL_HOST);
            if (strpos($host, 'tmall') !== false) {
                $host = 'detail.tmall.com';
            }
            switch ($host) {
                case 'item.taobao.com':
                    $collect = new TaobaoCollect();
                    break;
                case 'detail.tmall.com':
                    $collect = new TmallCollect();
                    break;
                case 'item.jd.com':
                case 'i-item.jd.com':
                case 'item.m.jd.com':
                    $collect = new JdCollect();
                    break;
                case 'mobile.pinduoduo.com':
                case 'mobile.yangkeduo.com':
                case 'yangkeduo.com':
                    $collect = new PddCollect();
                    break;
                case 'detail.1688.com':
                case 'detail.m.1688.com':
                case 'm.1688.com':
                    $collect = new AlibabaData();
                    break;
                default:
                    continue 2;
                    break;
            }
            try {
                $collect->setLink($link)
                    ->setCats($param['cats'])
                    ->setCatsText($param['catsText'])
                    ->setIsSale($param['is_sale'])
                    ->setDownload($param['download']);
                $res = $collect->saveGoods();
                if (isset($res['status']) && $res['status'] == 0) {
                    $successNum++;
                }
            } catch (AuthException $exception) {
                Error($exception->getMessage());
            } catch (LimitException $exception) {
                Error($exception->getMessage());
            } catch (CommonException $exception) {
                Error($exception->getMessage());
            } catch (\Exception $e) {
                \Yii::error($e);
                continue;
            }
            //防止普通账号高并发
            sleep(1);
        }
        return $successNum;
    }
}