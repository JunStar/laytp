<?php

namespace plugin\core\controller;

use laytp\controller\Backend;
use think\facade\Db;

/**
 * 系统配置控制器
 * Class Conf
 * @package app\admin\controller
 */
class Conf extends Backend
{
    protected $model;
    protected $noNeedAuth = ['getGroupItem', 'getGroup'];

    public function _initialize()
    {
        $this->model = new \plugin\core\model\Conf();
    }

    /**
     * 获取分组列表
     */
    public function getGroup()
    {
        $config   = $this->model->where([
            ['group', '=', 'layTpSys'],
            ['key', '=', 'configGroup'],
        ])->value('value');
        $layTpSys = json_decode($config, true);
        unset($layTpSys['layTpSys']);
        return $this->success('获取成功', $layTpSys);
    }

    /**
     * 获取某个分组下所有的配置项
     */
    public function getGroupItem()
    {
        $group = $this->request->param('group');
        $items = $this->model->where('group', '=', $group)->order('id', 'asc')->select();
        return $this->success('获取成功', $items);
    }

    /**
     * 添加分组
     * @return \think\response\Json
     */
    public function addGroup()
    {
        $post     = filter_post_data($this->request->post());
        $config   = $this->model->where([
            ['group', '=', 'layTpSys'],
            ['key', '=', 'configGroup'],
        ])->value('value');
        $layTpSys = json_decode($config, true);
        foreach ($layTpSys as $k => $v) {
            if ($v['value'] === $post['group']) {
                return $this->error('分组的英文标识已经存在');
            }
            if ($v['name'] === $post['group_name']) {
                return $this->error('分组名称已经存在');
            }
        }
        $layTpSys[] = [
            'name'    => $post['group_name']
            , 'value' => $post['group']
            , 'icon'  => $post['icon'],
        ];
        $value      = json_encode($layTpSys, JSON_UNESCAPED_UNICODE);
        $update     = $this->model
            ->where('group', '=', 'layTpSys')
            ->where('key', '=', 'configGroup')
            ->update(['value' => $value]);
        if ($update !== false) {
            return $this->updateConfig();
        } else {
            return $this->error('操作失败');
        }
    }

    /**
     * 编辑分组
     */
    public function editGroup()
    {
        $group      = $this->request->param('group');
        $group_name = $this->request->param('group_name');
        $icon       = $this->request->param('icon');
        if (!$group) {
            return $this->error('group不能为空');
        }
        $config   = $this->model->where([
            ['group', '=', 'layTpSys'],
            ['key', '=', 'configGroup'],
        ])->value('value');
        $layTpSys = json_decode($config, true);
        foreach ($layTpSys as $k => $v) {
            if ($v['value'] === $group) {
                $layTpSys[$k]['name'] = $group_name;
                $layTpSys[$k]['icon'] = $icon;
            }
        }
        $value  = json_encode($layTpSys, JSON_UNESCAPED_UNICODE);
        $update = $this->model
            ->where('group', '=', 'layTpSys')
            ->where('key', '=', 'configGroup')
            ->update(['value' => $value]);
        if ($update !== false) {
            return $this->updateConfig();
        } else {
            return $this->error('操作失败');
        }
    }

    //删除分组
    public function delGroup()
    {
        $group    = $this->request->param('group');
        $config   = $this->model->where([
            ['group', '=', 'layTpSys'],
            ['key', '=', 'configGroup'],
        ])->value('value');
        $layTpSys = json_decode($config, true);
        if (in_array($group, ['basic', 'upload'])) {
            return $this->error("不允许删除此分组");
        }
        foreach ($layTpSys as $k => $v) {
            if ($v['value'] === $group) {
                unset($layTpSys[$k]);
                break;
            }
        }
        Db::startTrans();
        try {
            $value    = json_encode($layTpSys, JSON_UNESCAPED_UNICODE);
            $result[] = $this->model
                ->where('group', '=', 'layTpSys')
                ->where('key', '=', 'configGroup')
                ->update(['value' => $value]);
            $res      = $this->model->where([
                ['group', '=', $group],
            ])->delete();
            $result[] = $res === false ? false : true;
            if (check_res($result)) {
                DB::commit();
                return $this->updateConfig();
            } else {
                DB::rollback();
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 添加配置
     * @return array|\think\response\Json
     */
    public function addConfig()
    {
        $post = filter_post_data($this->request->post());
        //检测group,key是否存在
        $keyExist = $this->model->where([
            ['group', '=', $post['group']],
            ['key', '=', $post['key']],
        ])->find();
        if ($keyExist) {
            return $this->error('变量名' . $post['key'] . '已存在');
        }
        $keyExist = $this->model->where([
            ['group', '=', $post['group']],
            ['name', '=', $post['name']],
        ])->find();
        if ($keyExist) {
            return $this->error('配置名称' . $post['name'] . '已存在');
        }
        //处理content字段值
        if (in_array($post['type'], ['select_single', 'select_multi', 'checkbox', 'switch'])) {
            $content = array_filter(explode("\n", $post['content']));
            $return  = [];
            foreach ($content as $v) {
                $temp     = explode('=', $v);
                $return[] = ['value' => $temp[0], 'text' => $temp[1]];
            }
            $post['content'] = json_encode($return, JSON_UNESCAPED_UNICODE);
        } else if (in_array($post['type'], ['image_single', 'image_multi', 'file_single', 'file_multi'])) {
            $content = array_filter(explode("\n", $post['content']));
            $return  = [];
            foreach ($content as $v) {
                $temp             = explode('=', $v);
                $return[$temp[0]] = $temp[1];
            }
            $post['content'] = json_encode($return, JSON_UNESCAPED_UNICODE);
        } else {
            $post['content'] = '';
        }
        $update_res = $this->model->insert($post);
        if ($update_res) {
            return $this->updateConfig();
        } else if ($update_res === null) {
            return $this->error('操作失败');
        }
    }

    //编辑配置项
    public function editConfig()
    {
        $id     = $this->request->param('id', 0, 'intval');
        $config = $this->model->find($id);
        if (!$config) {
            return $this->error('id参数错误');
        }
        $getData = $this->request->param('get_data');
        if ($getData) {
            return $this->success('获取成功', $config);
        } else {
            $post = $this->request->param();
            //处理content字段值
            if (in_array($post['type'], ['select_single', 'select_multi', 'checkbox', 'switch'])) {
                $content = array_filter(explode("\n", $post['content']));
                $return  = [];
                foreach ($content as $v) {
                    $temp     = explode('=', $v);
                    $return[] = ['value' => $temp[0], 'text' => $temp[1]];
                }
                $post['content'] = json_encode($return, JSON_UNESCAPED_UNICODE);
            } else if (in_array($post['type'], ['image_single', 'image_multi', 'file_single', 'file_multi'])) {
                $content = array_filter(explode("\n", $post['content']));
                $return  = [];
                foreach ($content as $v) {
                    $temp             = explode('=', $v);
                    $return[$temp[0]] = $temp[1];
                }
                $post['content'] = json_encode($return, JSON_UNESCAPED_UNICODE);
            } else {
                $post['content'] = '';
            }

            $res = $config->save($post);
            if ($res !== false) {
                return $this->updateConfig();
            } else {
                return $this->error('操作失败');
            }
        }
    }

    //删除配置项
    public function delConfig()
    {
        $ids = array_filter(explode(',', $this->request->param('ids')));
        if (!$ids) {
            return $this->error('参数ids不能为空');
        }
        $config = $this->model->find($ids);
        if (!$config) {
            return $this->error('参数ids不存在');
        }
        if (in_array($config->group . '.' . $config->key, [
            'basic.siteName', 'basic.loginNeedCaptcha', 'basic.firstMenuNum',
            'upload.domain', 'upload.size', 'upload.mime', 'upload.type',
        ])) {
            return $this->error('不允许删除此配置');
        }

        if ($this->model->destroy($ids)) {
            return $this->updateConfig();
        } else {
            return $this->error('数据删除失败');
        }
    }

    //保存配置
    public function set()
    {
        $post  = $this->request->post();
        $group = $post['group'];
        unset($post['group']);
        foreach ($post as $k => $v) {
            if (is_array($v)) {
                $temp = [];
                foreach ($v['key'] as $arr_k => $arr_v) {
                    $temp[$arr_v] = $v['value'][$arr_k];
                }
                $post[$k] = json_encode($temp, JSON_UNESCAPED_UNICODE);
            }
        }
        $result = [];
        Db::startTrans();
        foreach ($post as $k => $v) {
            $update = $this->model
                ->where('group', '=', $group)
                ->where('key', '=', $k)
                ->update(['value' => $v]);
            if ($update !== false) {
                $result[] = true;
            }
        }
        if (check_res($result)) {
            Db::commit();
            return $this->updateConfig();
        } else {
            Db::rollback();
            return $this->error('操作失败');
        }
    }

    /**
     * 更新配置文件
     * @return array|\think\response\Json
     */
    public function updateConfig()
    {
        try {
            //写入配置文件
            $fileName     = root_path() . DS . 'config' . DS . 'laytp.php';
            $config       = $this->model->field('group,key,value,type')->select()->toArray();
            $resultConfig = [];
            foreach ($config as $k => $v) {
                if ($v['type'] == 'array') {
                    $resultConfig[$v['group']][$v['key']] = json_decode($v['value'], true);
                } else {
                    $resultConfig[$v['group']][$v['key']] = $v['value'];
                }
            }
            file_put_contents($fileName, "<?php\nreturn " . var_export($resultConfig, true) . ';');
            return $this->success('缓存更新成功');
        } catch (\Exception $e) {
            return $this->error('缓存更新失败:' . $e->getMessage());
        }
    }
}