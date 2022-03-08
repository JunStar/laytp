<?php

namespace laytp\controller;

use app\middleware\admin\ActionLog;
use app\middleware\admin\Auth;
use app\service\admin\AuthServiceFacade;
use laytp\BaseController;

/**
 * 后台应用admin控制器基类
 * @package laytp\controller
 */
class Backend extends BaseController
{
    use \laytp\traits\Backend;

    protected $noNeedLogin = [];//无需登录，也无需鉴权的方法名列表，支持*通配符定义所有方法
    protected $noNeedAuth = [];//需要登录，但是无需鉴权的方法名列表，支持*通配符定义所有方法
    protected $hasSoftDel = 0;//当前访问的模型是否有软删除功能
    protected $orderRule = ['id' => 'desc'];//默认排序规则

    /**
     * 中间件
     * @var array
     */
    protected $middleware = [
        Auth::class,
        ActionLog::class,
    ];

    /**
     * 基类初始化方法
     */
    protected function initialize()
    {
        //将无需登录的方法名数组设置到权限服务中
        AuthServiceFacade::setNoNeedLogin($this->noNeedLogin);
        //将无需鉴权的方法名数组设置到权限服务中
        AuthServiceFacade::setNoNeedAuth($this->noNeedAuth);
        $this->_initialize();
    }

    //子类初始化
    protected function _initialize()
    {
    }

    /**
     * 生成查询条件
     *  post方式传递了search_param参数，就说明是进行筛选搜索
     *  search_param格式：
     *      search_param : {
     *          数据库字段 : {
     *              value : 值,
     *              condition : 搜索条件
     *          }
     *      }
     * @return array
     */
    public function buildSearchParams()
    {
        $where       = [];
        $whereOr     = [];
        //传递了search_param就说明是进行搜索查询
        $searchParam = $this->request->param('search_param');
        if ($searchParam) {
            foreach ($searchParam as $field => $valueCondition) {
                if ($valueCondition['value'] !== '') {
                    $condition = trim(strtoupper($valueCondition['condition']));
                    switch ($condition) {
                        case '=':
                            $where[] = [$field, '=', $valueCondition['value']];
                            break;
                        case 'FIND_IN_SET':
                            $values      = explode(',', $valueCondition['value']);
                            foreach ($values as $val) {
                                $whereOr[] = [$field, 'find in set', $val];
                            }
                            if($whereOr){
                                $where[] = function($query) use($whereOr){
                                    $query->whereOr($whereOr);
                                };
                            }
                            break;
                        case 'LIKE':
                            $where[] = [$field, 'like', '%'.$valueCondition['value'].'%'];
                            break;
                        case 'IN':
                            $where[] = [$field, 'in', $valueCondition['value']];
                            break;
                        case 'BETWEEN':
                            $arrBetween = explode(' - ', $valueCondition['value']);
                            $where[]    = [$field, 'between', $arrBetween[0].','.$arrBetween[1]];
                            break;
                        case 'BETWEEN_STRTOTIME':
                            $arrBetween = explode(' - ', $valueCondition['value']);
                            $begin_time = strtotime($arrBetween[0]);
                            $end_time   = strtotime($arrBetween[1]);
                            $where[]    = [$field, 'between', $begin_time.','.$end_time];
                            break;
                        case '>':
                            $where[] = [$field, '>', $valueCondition['value']];
                            break;
                        case '>=':
                            $where[] = [$field, '>=', $valueCondition['value']];
                            break;
                        case '<':
                            $where[] = [$field, '<', $valueCondition['value']];
                            break;
                        case '<=':
                            $where[] = [$field, '<=', $valueCondition['value']];
                            break;
                        case '<>':
                        case '!=':
                            $where[] = [$field, '<>', $valueCondition['value']];
                            break;
                    }
                }
            }
        }
        return $where;
    }

    /**
     * 生成排序条件
     */
    public function buildOrder()
    {
        $order = $this->orderRule;
        //传递了order_param字段，就说明是进行排序搜索
        $orderParam = $this->request->param('order_param');
        if ($orderParam && $orderParam['field'] && in_array(strtolower($orderParam['type']), ['asc','desc'])) {
            if(isset($order[$orderParam['field']])){
                $order[$orderParam['field']] = $orderParam['type'];
            }else{
                $order = array_merge([$orderParam['field'] => $orderParam['type']], $order);
            }
        }
        return $order;
    }
}