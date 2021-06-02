## 定时任务组件
定期执行一些次要任务。比如:发送优惠券过期提醒，自动取消订单等。
需配合crontab(Linux),计划任务(windows)使用，建议执行间隔：3秒

### 主要方法

- doAllCrontab(): 执行所有的定时任务
- doOneCrontab($name): 执行某个定时任务
- scanCrontabList(): 遍历定时任务目录列表
- getCrontab($name): 获取具体的crontab对象

### 使用方法
```
//优惠券过期提醒定时任务
\Yii::$app->crontab->doOneCrontab('coupon_remind');
```

### 生成定时任务链接接口
```
POST api/leadmall/crontab 
```

### 执行定时任务链接接口
```
GET api/leadmall/crontab 
```
