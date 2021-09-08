<?php
/**
 * 素材管理
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace gallery\api;

use app\components\Upload;
use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class IndexController extends BasicController
{
    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions           = parent::actions();
        $actions['create'] = [
            'class'       => 'yii\rest\CreateAction',
            'modelClass'  => M(),
            'checkAccess' => [$this, 'checkAccess'],
            'scenario'    => 'create',
        ];

        unset($actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'gallery':
                return $this->gallery_list();
                break;
            default:
                return $this->group_gallery_list();
                break;
        }
    }

    /**
     * 处理数据分页问题
     * @return [type] [description]
     */
    public function group_gallery_list()
    {

        //获取组ID
        $group_id = Yii::$app->request->get('group_id', 1);
        $type     = Yii::$app->request->get('type', 1);
        $group_id = intval($group_id);
        $type     = intval($type);
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where2      = [
            'is_deleted'  => 0,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
            'type'        => $type,
        ];

        if ($group_id > 0) {
            $where2['group_id'] = $group_id;
        }

        $where = [
            'is_deleted'  => 0,
            'parent_id'   => $group_id,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
            'type'        => $type,
        ];

        if (true) {
            $UID           = Yii::$app->user->identity->id;
            $where['UID']  = $UID;
            $where2['UID'] = $UID;
        }

        $query1 = M('gallery', 'GalleryGroup')::find()->where($where)->select('id,name as title_name,is_deleted as type,parent_id,path as url,AppID as cover,created_time');
        $query2 = M()::find()->where($where2)->select('id,title as title_name,type,group_id as parent_id,url,thumbnail as cover,created_time');
        $query  = (new \yii\db\Query())->from($query1->union($query2, true));
        $data   = new ActiveDataProvider(
            [
                'query'      => $query->orderBy(['type' => SORT_ASC, 'created_time' => SORT_DESC]),
                'pagination' => ['pageSize' => intval($pageSize), 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        //将所有返回内容中的本地地址代替字符串替换为域名
        $list = str2url($list);
        $data->setModels($list);
        return $data;

    }

    public function gallery_list()
    {
        //获取组ID
        $group_id = Yii::$app->request->get('group_id', 0);
        $type     = Yii::$app->request->get('type', 1);
        $group_id = intval($group_id);
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'is_deleted'  => 0,
            'type'        => $type,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
        ];

        if ($group_id) {
            $where['group_id'] = $group_id;
        }

        if (true) {
            $UID          = Yii::$app->user->identity->id;
            $where['UID'] = $UID;
        }

        $data = new ActiveDataProvider(
            [
                'query'      => M()::find()->where($where)->asArray(),
                'pagination' => ['pageSize' => intval($pageSize), 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        //将所有返回内容中的本地地址代替字符串替换为域名
        $list = str2url($list);
        $data->setModels($list);
        return $data;
    }

    /**
     * 获取素材详情
     * @return [type] [description]
     */
    public function actionView()
    {
        $id              = Yii::$app->request->get('id', false);
        $data            = M()::findOne($id);
        $data->url       = str2url($data->url);
        $data->thumbnail = str2url($data->thumbnail);
        return $data;
    }

    /**
     * 重写修改方法
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'setgroup':
                return $this->setGroup();
                break;
            default:
                return $this->update();
                break;
        }
    }

    public function update()
    {
        $id   = Yii::$app->request->get('id', false);
        $post = Yii::$app->request->post();

        if (isset($post['group_id'])) {
            $group_info = M('gallery', 'GalleryGroup')::find()->where(['id' => $post['group_id'], 'is_deleted' => 0])->one();
            if (empty($group_info)) {
                Error('分组不存在');
            }
        }

        $model = M()::findOne($id);
        if (empty($model)) {
            Error('素材不存在');
        }
        $model->setScenario('update');
        $model->setAttributes($post);
        if ($model->validate()) {
            if ($model->save()) {
                return $model;
            } else {
                Error('保存失败');
            }

        }
        return $model;
    }

    /**
     * 批量修改分组
     * @return [type] [description]
     */
    public function setGroup()
    {
        $group_id = Yii::$app->request->post('group_id', false);
        if (!$group_id) {
            Error('请选择分组');
        }
        $group_info = M('gallery', 'GalleryGroup')::find()->where(['id' => $group_id, 'is_deleted' => 0])->one();
        if (empty($group_info)) {
            Error('分组不存在');
        }

        $id = Yii::$app->request->get('id', false);
        if (!$id) {
            Error('ID缺失');
        }
        $id   = explode(',', $id);
        $data = [
            'group_id'     => $group_id,
            'updated_time' => time(),
        ];
        $result = M()::updateAll($data, ['in', 'id', $id]);
        if ($result) {
            return true;
        } else {
            Error('操作失败');
        }
    }

    /**
     * 删除素材
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->get('id', false);
        if (!$id) {
            Error('ID缺失');
        }
        $id   = explode(',', $id);
        $data = [
            'is_deleted'   => 1,
            'deleted_time' => time(),
        ];
        $result = M()::updateAll($data, ['in', 'id', $id]);
        if ($result) {
            return true;
        } else {
            Error('删除失败');
        }
    }

    /**
     * 上传
     * @return [type] [description]
     */
    public function upload()
    {
        $upload = new Upload();
        $type   = Yii::$app->request->post('type', 1);

        if ($type == 1) {
            $content = Yii::$app->request->post('content', false);

            if (empty($content)) {
                Error('图片不能为空');
            }

            $file              = $upload->image_base64($content);
            $data['size']      = $file['size'];
            $data['url']       = $upload::$upload_way == 0 ? URL_STRING . $file['url'] : $file['url'];
            $thumbnail         = $upload->image_compress($file['url']);
            $data['thumbnail'] = $upload::$upload_way == 0 ? URL_STRING . $thumbnail : $thumbnail;

        } elseif ($type == 2) {

            $content = $_FILES['content'];

            if (empty($content)) {
                Error('视频不能为空');
            }

            $data['size'] = $content['size'];

            $file         = $upload->video($content);
            $data['size'] = $file['size'];
            $data['url']  = $upload::$upload_way == 0 ? URL_STRING . $file['url'] : $file['url'];
            $cover        = Yii::$app->request->post('cover', false);
            if (strlen($cover) > 100) {
                $cover_url         = $upload->image_base64($cover);
                $data['thumbnail'] = $upload::$upload_way == 0 ? URL_STRING . $cover_url['url'] : $cover_url['url'];
            } else {
                $data['thumbnail'] = URL_STRING . '/static/images/gallery/video.png';
            }

        }
        $name          = explode('.', ltrim(strrchr($file['url'], '/'), '/'));
        $data['title'] = $name[0];

        return $data;

    }

    /**
     * 数据前置检查器
     * @param  [type]  $operation    [description]
     * @param  array   $params       [description]
     * @param  boolean $allowCaching [description]
     * @return [type]                [description]
     */
    public function checkAccess($operation, $params = array(), $allowCaching = true)
    {
        switch ($operation) {
            case 'create':
                $post = Yii::$app->request->post();
                if (isset($post['group_id']) && $post['group_id'] > 0) {
                    $group_info = M('gallery', 'GalleryGroup')::find()->where(['id' => $post['group_id'], 'is_deleted' => 0])->one();
                    if (empty($group_info)) {
                        Error('分组不存在');
                    }
                } else {
                    $post['group_id'] = 1;
                }
                $get_url             = $this->upload();
                $post['title']       = $post['title'] ?? $get_url['title'];
                $post['title']       = strlen($post['title']) > 32 ? substr($post['title'], 0, 32) : $post['title'];
                $post['size']        = $get_url['size'];
                $post['url']         = $get_url['url'];
                $post['thumbnail']   = $get_url['thumbnail'] ?? '';
                $post['UID']         = Yii::$app->user->identity->id;
                $post['merchant_id'] = 1;
                $post['AppID']       = Yii::$app->params['AppID'];
                Yii::$app->request->setBodyParams($post);
                break;
        }
    }
}
