<?php
/**
 * 后台控制器基类
 */

namespace laytp\controller;

use app\common\middleware\admin\Auth;
use app\common\service\admin\AuthServiceFacade;
use app\common\service\admin\User;
use laytp\BaseController;

class Backend extends BaseController
{
    use \laytp\traits\Backend;

    protected $noNeedLogin = ['selectPage'];//无需登录，也无需鉴权的方法名列表
    protected $noNeedAuth = [];//需要登录，但是无需鉴权的方法名列表
    protected $addon;//当前插件名
    protected $appName;//当前应用名
    protected $controller;//当前控制器名
    protected $action;//当前操作名
    protected $nowNode;//当前访问节点
    protected $roleIds;//当前登录者拥有的角色ID
    protected $ruleList;//当前登录者拥有的权限节点列表
    protected $menuIds;//当前登录者拥有的菜单列表
    protected $hasDel=1;//当前访问的模型是否有删除功能
    protected $hasSoftDel=0;//当前访问的模型是否有软删除功能
    protected $batchActionList=['edit','del'];//批量操作下拉展示的节点函数名
    protected $isShowSearchBtn = true;//是否展示筛选按钮
    protected $ref;//前端直接访问带[ref=菜单id]参数的链接地址就直接渲染菜单页，并且前端js把iframe的地址跳转到当前地址
    protected $orderRule=['id'=>'desc'];//默认排序规则

    /**
     * 后台用户服务
     * @var User
     */
    protected $userService = null;

    /**
     * 中间件
     * @var array
     */
    protected $middleware = [
        Auth::class
    ];

    /**
     * 基类初始化方法
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function initialize()
    {
        //将无需登录的方法名数组设置到权限服务中
        AuthServiceFacade::setNoNeedLogin($this->noNeedLogin);
        //将无需登录的方法名数组设置到权限服务中
        AuthServiceFacade::setNoNeedAuth($this->noNeedAuth);
        $this->_initialize();
    }

    //子类初始化
    protected function _initialize(){}

    /**
     * 生成查询条件
     * @return array
     */
    public function buildSearchParams(){
        $where = [];
        //传递了search_param字段，就说明是进行筛选搜索
        $searchParam = $this->request->param('search_param');
        if( $searchParam ){
            foreach($searchParam as $field=>$valueCondition){
                if($valueCondition['value'] != ''){
                    $condition = strtoupper($valueCondition['condition']);
                    switch( $condition ){
                        case '=':
                            $where[] = "`{$field}` = '{$valueCondition['value']}'";
                            break;
                        case 'FIND_IN_SET':
                            $values = explode(',', $valueCondition['value']);
                            $find_in_set = [];
                            foreach($values as $val){
                                $find_in_set[] = "find_in_set({$val},{$field})";
                            }
                            $where[] = '(' . implode(' OR ', $find_in_set) . ')';
                            break;
                        case 'LIKE':
                            $where[] = "$field LIKE '%{$valueCondition['value']}%'";
                            break;
                        case 'IN':
                            $where[] = "$field IN ({$valueCondition['value']})";
                            break;
                        case 'BETWEEN':
                            $arrBetween = explode(' - ', $valueCondition['value']);
                            $where[] = "($field BETWEEN '{$arrBetween[0]}' and '{$arrBetween[1]}')";
                            break;
                        case 'BETWEEN_STRTOTIME':
                            $arrBetween = explode(' - ', $valueCondition['value']);
                            $begin_time = strtotime($arrBetween[0]);
                            $end_time = strtotime($arrBetween[1]);
                            $where[] = "($field BETWEEN {$begin_time} and {$end_time})";
                            break;
                        case '>':
                            $where[] = "`{$field}` > {$valueCondition['value']}";
                            break;
                        case '>=':
                            $where[] = "`{$field}` >= {$valueCondition['value']}";
                            break;
                        case '<':
                            $where[] = "`{$field}` < {$valueCondition['value']}";
                            break;
                        case '<=':
                            $where[] = "`{$field}` <= {$valueCondition['value']}";
                            break;
                    }
                }
            }
        }
        $whereStr = implode(' AND ', $where);
        return $whereStr;
    }

    /**
     * 生成排序条件
     */
    public function buildOrder(){
        $order = $this->orderRule;
        //传递了order_param字段，就说明是进行筛选搜索
        $orderParam = $this->request->param('order_param');
        if($orderParam && $orderParam['field'] && $orderParam['type']){
            $order = array_merge([$orderParam['field']=>$orderParam['type']],$order);
        }
        return $order;
    }
}