## 微信订阅消息

### 主要方法

- setUser($uid): 设置一个接收消息的用户。
- setPage('pages/index/index'): 设置小程序跳转页面
- send(BaseSubscribeMessage $message): 发送 接收一个 BaseSubscribeMessage 对象

### 目前有的订阅消息
- 付款成功通知
- 订单发货通知
- 售后审核通过通知
- 退款成功通知
- 优惠券到期提醒

### 使用示例
```
$data = [
    'amount' => '0.01',
    'payTime' => date('Y年m月d日 H:i', time()),
    'businessName' => '哈哈',
    'orderNo' => '123456'
];

\Yii::$app->subscribe->setUser(90)->setPage('index/index')->send(new OrderPayMessage($data))
```