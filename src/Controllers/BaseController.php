<?php
namespace MBCore\MAdvert\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use MBCore\MAdvert\Models\AdvertFormValidation;

class BaseController extends Controller
{

    protected $app_id = null;
    protected $group_id = null;

    /**
     * BaseController constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $app_id = config('mbcore_mcore.default.app_id', config('mbcore_avdert.default.app_id', 1));
        $group_id = config('mbcore_mcore.default.group_id', config('mbcore_avdert.default.group_id', 1));
        $this->app_id = $request->get("app_id", $app_id);
        $this->group_id = $request->get("group_id", $group_id);
    }

    /**
     * @param $result
     * @param int $code
     * @param int $httpCode
     * @param null $msg
     * @return \Illuminate\Http\JsonResponse
     */
    protected function ret($result, int $code = 1, int $httpCode = 200, $msg = null): \Illuminate\Http\JsonResponse
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers:Origin, X-Requested-With,  Content-Type, Cookie, Accept, appid, channel, mbcore-access-token, mbcore-auth-token');
        header('Access-Control-Allow-Methods:GET, POST, PATCH, PUT, OPTIONS');
        header('Access-Control-Allow-Credentials:false');
        header('Access-Control-Max-Age:3600');
        header('Content-Type:text/html;charset=utf-8');
        header('Content-type:application/json');
        return response()->json([
            'code' => $code,
            'result' => $result,
            'msg' => $msg ?: ($result['msg'] ?? null),
        ], $httpCode, [], 271);
        exit;
    }

    /**
     * @param $msg
     * @param int $code
     * @param int $httpCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function retMsgString($msg, int $code = 0, int $httpCode = 200): \Illuminate\Http\JsonResponse
    {
        return $this->ret($msg, $code, $httpCode);
    }

    /**
     * DataUpdate
     *
     * @param $data
     * @param $post
     * @param null $table
     * @param null $request
     * @return array
     */
    public function DataUpdate($data, $post, $table = null, $request = null): array
    {
        if ($table) {
            $validator = $this->FormVerify($table, $request);
            if ($validator->fails()) {
                return [
                    'code'=>201,
                    'msg'=>$validator->errors()->first()
                ];
            }
        }
        \DB::beginTransaction();
        try {
            $data->update($post);
            \DB::commit();
            return [
                'code'=>200,
                'msg'=>'success'
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            return [
                'code'=>201,
                'msg'=>$e->getMessage()
            ];
        }
    }

    /**
     * DataCreate
     *
     * @param $query
     * @param $post
     * @param null $table
     * @param null $request
     * @return array
     */
    public function DataCreate($query, $post,$table = null, $request = null): array
    {
        if ($table) {
            $validator = $this->FormVerify($table, $request);
            if ($validator->fails()) {
                return [
                    'code'=>201,
                    'msg'=>$validator->errors()->first()
                ];
            }
        }
        \DB::beginTransaction();
        try {
            $data = $query->create($post);
            \DB::commit();
            return [
                'code'=>200,
                'data'=>$data,
                'msg'=>'success'
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            return [
                'code'=>201,
                'msg'=>$e->getMessage()
            ];
        }
    }

    /**
     * 移除数据（软删除）
     *
     * @param $data
     * @return array
     */
    public function DataRemove($data): array
    {
        \DB::beginTransaction();
        try {
            $data->delete();
            \DB::commit();
            return [
                'code'=>200,
                'msg'=>'success'
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            return [
                'code'=>201,
                'msg'=>$e->getMessage()
            ];
        }
    }

    /**
     * FormVerify
     *
     * @param $table
     * @param $request
     * @return mixed
     */
    public function FormVerify($table,$request)
    {
        $rules = array();
        $messages = array();
        $where = [
            'table'=>$table,
            'rule'=>'required'
        ];
        $data = AdvertFormValidation::query()->where($where)->oldest('sort')->get();
        if($data){
            foreach ($data as $val) {
                $rules[$val['column']] = $val['rule'];
                $messages[$val['column'].'.'.$val['rule']] = $val['message'];
            }
        }
        return Validator::make($request->all(),$rules, $messages);
    }

    /**
     * @param $tel
     * @param bool $onlyMob
     * @return array
     *
     * 电话号验证
     */
    public function TelVerify($tel, bool $onlyMob = false): array
    {
//    preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#',$tel);
        $isMob = "/^1[3-9]{1}[0-9]{9}$/";
        $isTel="/^([0-9]{3,4}-)?[0-9]{7,8}$/";
        $special = '/^(4|8)00(-\d{3,4}){2}$/';//'/^(4|8)00(\d{4,8})$/';
        $data3 = substr($tel, 0,3);
        $data2 = substr($tel, 0,2);
        $msg = 'success';
        $msg_zh = '成功';
        $code = 1;
        if($onlyMob){# 只验证手机号，不验证座机和400|800的号码
            if (preg_match($isMob, $tel)) {
                if($data2 == 14){
                    if(!in_array($data3,[147,145])){
                        # 只开放 147,145
                        $msg = $data3.' is not open!';
                        $msg_zh = $data3.' 号段暂未开放！';
                        $code = 0;
                        return [
                            'code' => $code,
                            'msg'=>$msg,
                            'msg_zh' => $msg_zh
                        ];
                    }
                } else if($data2 == 16){
                    if(!in_array($data3,[165,166,167])){
                        # 只开放 165,166,167
                        $msg = $data3.' is not open';
                        $msg_zh = $data3.' 号段暂未开放！';
                        $code = 0;
                        return [
                            'code' => $code,
                            'msg'=>$msg,
                            'msg_zh' => $msg_zh
                        ];
                    }
                }else if($data2 == 17){
                    if(in_array($data3,[179,174])){
                        # 未开放 179,174
                        $msg = $data3.' is not open';
                        $msg_zh = $data3.' 号段暂未开放！';
                        $code = 0;
                        return [
                            'code' => $code,
                            'msg'=>$msg,
                            'msg_zh' => $msg_zh
                        ];
                    }
                }else if($data2 == 19){
                    if(!in_array($data3,[199,198,191,193,195])){
                        # 只开放 199,198
                        $msg = $data3.' is not open';
                        $msg_zh = $data3.' 号段暂未开放！';
                        $code = 0;
                        return [
                            'code' => $code,
                            'msg'=>$msg,
                            'msg_zh' => $msg_zh
                        ];
                    }
                }
            } else {
                $msg = 'Invalid mobile phone number';
                $msg_zh = '手机号不合法！';
                $code = 0;
                return [
                    'code' => $code,
                    'msg' => $msg,
                    'msg_zh' => $msg_zh
                ];
            }
            return [
                'code' => $code,
                'msg' => $msg,
                'msg_zh' => $msg_zh
            ];
        }else {# 手机、座机、以及400|800号码的验证
            if (preg_match($isMob, $tel)) {
                if($data2 == 14){
                    if(!in_array($data3,[147,145])){
                        # 只开放 147,145
                        $msg = $data3.' is not open!';
                        $msg_zh = $data3.' 号段暂未开放！';
                        $code = 0;
                        return [
                            'code' => $code,
                            'msg'=>$msg,
                            'msg_zh' => $msg_zh
                        ];
                    }
                }else if($data2 == 16){
                    if(!in_array($data3,[165,166,167])){
                        # 只开放 165,166,167
                        $msg = $data3.' is not open';
                        $msg_zh = $data3.' 号段暂未开放！';
                        $code = 0;
                        return [
                            'code' => $code,
                            'msg'=>$msg,
                            'msg_zh' => $msg_zh
                        ];
                    }
                }else if($data2 == 17){
                    if(in_array($data3,[179,174])){
                        # 未开放 179,174
                        $msg = $data3.' is not open';
                        $msg_zh = $data3.' 号段暂未开放！';
                        $code = 0;
                        return [
                            'code' => $code,
                            'msg'=>$msg,
                            'msg_zh' => $msg_zh
                        ];
                    }
                }else if($data2 == 19){
                    if(!in_array($data3,[199,198,191,193,195])){
                        # 只开放 199,198
                        $msg = $data3.' is not open';
                        $msg_zh = $data3.' 号段暂未开放！';
                        $code = 0;
                        return [
                            'code' => $code,
                            'msg'=>$msg,
                            'msg_zh' => $msg_zh
                        ];
                    }
                }
            } else if (preg_match($special, $tel)) {
                return [
                    'code' => $code,
                    'msg' => $msg,
                    'msg_zh' => $msg_zh
                ];
            } else if (preg_match($isTel, $tel)){
                return [
                    'code' => $code,
                    'msg' => $msg,
                    'msg_zh' => $msg_zh
                ];
            } else {
                $msg = 'Invalid mobile phone number,If it is a fixed telephone, it must be like (010-87876787 or 400-000-0000)!';
                $msg_zh = '手机号不合法,如果是固定电话, 必须类似以下号码 (010-87876787 或者 400-000-0000)！';
                $code = 0;

                return [
                    'code' => $code,
                    'msg' => $msg,
                    'msg_zh' => $msg_zh
                ];
            }
            return [
                'code' => $code,
                'msg' => $msg,
                'msg_zh' => $msg_zh
            ];
        }
    }
    /**
     * @return mixed|null
     * @throws Exception
     *
     * 多维数组自定义多条件排序
     */
    public function ArrayByManyFieldSort(){
        # 获取函数的参数的数组
        $args = func_get_args();
        if(empty($args)){
            return null;
        }
        $arr = array_shift($args);
        if(!is_array($arr)){
            return $this->retMsgString([
                'msg'=>'第一个参数不为数组'
            ],0);
        }
        foreach($args as $key => $field){
            if(is_string($field)){
                $temp = array();
                foreach($arr as $index=> $val){
                    $temp[$index] = $val[$field];
                }
                $args[$key] = $temp;
            }
        }
        $args[] = &$arr;#引用值
        call_user_func_array('array_multisort',$args);
        return array_pop($args);
    }
}
