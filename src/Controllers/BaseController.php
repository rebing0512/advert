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
