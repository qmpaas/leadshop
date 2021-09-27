<?php

namespace plugins\evaluate\models\collect;

use collect\models\collect\AuthException;
use collect\models\collect\CommonException;
use collect\models\collect\LimitException;

class CollectFactory
{
    public static function create($link, $param)
    {
        set_time_limit(0);
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
            default:
                Error('未匹配到商品平台,请检查');
                break;
        }
        try {
            $collect->begin = $param['begin'];
            $collect->end = $param['end'];
            $collect->num = $param['num'];
            $collect->showGoodsParam = $param['show_goods_param'];
            $collect->status = $param['status'];
            $collect->egoods = $param['egoods'];
            $collect->type = $param['type'];
            $collect->gallery = $param['gallery'];
            $collect->setLink($link);
            $collect->saveEvaluate();
            $collect->saveAll();
        } catch (AuthException $exception) {
            Error($exception->getMessage());
        } catch (LimitException $exception) {
            Error($exception->getMessage());
        } catch (CommonException $exception) {
            Error($exception->getMessage());
        } catch (\Exception $exception) {
            Error($exception->getMessage());
        }
        return count($collect->evaluates);
    }
}
