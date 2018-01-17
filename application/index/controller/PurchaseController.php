<?php
namespace app\index\controller;

use think\Session;
use think\Request;

use app\index\model\Project;
use app\index\model\Item;
use app\index\model\Purchase;
use app\index\model\Provider;
use app\index\model\Tender;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpWord\TemplateProcessor;

class PurchaseController extends \think\Controller {
	public function get_project() {
        check_login();
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();
        $page_id = 1;
        if($request->has('pageId')) {
            $page_id = intval($request->get('pageId'));
        }

        if($page_id == 0) {
            return json([
                'code' => 20062,
                'message' => '参数有误'
            ]);
        }

        $project = new Project();
        $perpage = 10;
        $total_id = ceil($project->where('project_user_id', $user_id)->where('project_state', 2)->count('project_id') / 10);
        $project_info = $project->field('project_id as projectId,project_name as projectName,project_code as projectCode')->order('project_id asc')->where('project_user_id', $user_id)->where('project_state', 2)->limit(($page_id - 1) * $perpage, $page_id * $perpage)->select();
        return json([
            'code' => 1131,
            'message' => '项目查询成功！',
            'page' => $page_id,
            'total' => $total_id,
            'content' => $project_info
        ]);
    }

    public function edit() {
        check_login();
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();
        $purchase_code = $request->post('purchaseCode');
        $purchase_project_id = $request->post('purchaseProjectId');
        $purchase_product_name = $request->post('purchaseProductName');
        $purchase_product_num = $request->post('purchaseProductNum');
        $purchase_product_type = $request->post('purchaseProductType');
        $purchase_type = $request->post('purchaseType');
        $purchase_dept = $request->post('purchaseDept');
        $purchase_budget = $request->post('purchaseBudget');
        $purchase_technology_parameter = $request->post('purchaseTechnologyParameter');
        $purchase_explain = $request->post('purchaseExplain');
        $purchase_technology_file = $request->post('purchaseTechnologyFile');
        
        $purchase_is_conform = $request->post('purchaseIsConform');
        if($purchase_is_conform == '是') {
            $purchase_is_conform = 1;
        }
        if($purchase_is_conform == '否') {
            $purchase_is_conform = 0;
        }
        
        $purchase_reject_reason = $request->post('purchaseRejectReason');
        $purchase_reject_content = $request->post('purchaseRejectContent');
        $purchase_payment = $request->post('purchasePayment');
        $purchase_quality = $request->post('purchaseQuality');
        $purchase_deadline = strtotime($request->post('purchaseDeadline'));
        $purchase_arrive_time = strtotime($request->post('purchaseArriveTime'));
        $purchase_place = $request->post('purchasePlace');
        $purchase_recommend = $request->post('purchaseRecommend');
        $purchase_order = $request->post('purchaseOrder');
        $purchase_order_time = strtotime($request->post('purchaseOrderTime'));
        $purchase_tip = $request->post('purchaseTip');
        
        $purchase = new Purchase();

        if($request->post('purchaseId') != '') {
            $purchase_id = intval($request->post('purchaseId'));
            $purchase_info = $purchase->field('purchase_id')->where('purchase_id', $purchase_id)->find();
            if($purchase_info == null) {
                return json([
                    'code' => 1153,
                    'message' => '请购不存在！'
                ]);
            }
            $purchase->save([
                'purchase_code' => $purchase_code,
                'purchase_project_id' => $purchase_project_id,
                'purchase_product_name' => $purchase_product_name,
                'purchase_product_num' => $purchase_product_num,
                'purchase_product_type' => $purchase_product_type,
                'purchase_type' => $purchase_type,
                'purchase_dept' => $purchase_dept,
                'purchase_budget' => $purchase_budget,
                'purchase_technology_parameter' => $purchase_technology_parameter,
                'purchase_explain' => $purchase_explain,
                'purchase_technology_file' => $purchase_technology_file,
                'purchase_is_conform' => $purchase_is_conform,
                'purchase_reject_reason' => $purchase_reject_reason,
                'purchase_reject_content' => $purchase_reject_content,
                'purchase_payment' => $purchase_payment,
                'purchase_quality' => $purchase_quality,
                'purchase_deadline' => $purchase_deadline,
                'purchase_arrive_time' => $purchase_arrive_time,
                'purchase_place' => $purchase_place,
                'purchase_recommend' => $purchase_recommend,
                'purchase_order' => $purchase_order,
                'purchase_order_time' => $purchase_order_time,
                'purchase_tip' => $purchase_tip
            ], ['purchase_id' => $purchase_id]);

            return json([
                'code' => 1152,
                'message' => '请购编辑成功！'
            ]);
        } else {
            $purchase->data([
                'purchase_code' => $this->check_code(),
                'purchase_user_id' => $user_id,
                'purchase_project_id' => $purchase_project_id,
                'purchase_product_name' => $purchase_product_name,
                'purchase_product_num' => $purchase_product_num,
                'purchase_product_type' => $purchase_product_type,
                'purchase_type' => $purchase_type,
                'purchase_dept' => $purchase_dept,
                'purchase_budget' => $purchase_budget,
                'purchase_technology_parameter' => $purchase_technology_parameter,
                'purchase_explain' => $purchase_explain,
                'purchase_technology_file' => $purchase_technology_file,
                'purchase_is_conform' => $purchase_is_conform,
                'purchase_reject_reason' => $purchase_reject_reason,
                'purchase_reject_content' => $purchase_reject_content,
                'purchase_payment' => $purchase_payment,
                'purchase_quality' => $purchase_quality,
                'purchase_deadline' => $purchase_deadline,
                'purchase_arrive_time' => $purchase_arrive_time,
                'purchase_place' => $purchase_place,
                'purchase_recommend' => $purchase_recommend,
                'purchase_order' => $purchase_order,
                'purchase_order_time' => $purchase_order_time,
                'purchase_tip' => $purchase_tip,
                'purchase_create_time' => time(),
                'purchase_state' => 1
            ]);
            $purchase->save();
            
            return json([
                'code' => 1151,
                'message' => '请购创建成功！'
            ]);
        }
    }

    public function get() {
        check_login();
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();
        $page_id = 1;
        if($request->has('pageId')) {
            $page_id = intval($request->get('pageId'));
        }

        if($page_id == 0) {
            return json([
                'code' => 20062,
                'message' => '参数有误'
            ]);
        }

        $purchase = new Purchase();
        $perpage = 10;
        $total_id = ceil($purchase->where('purchase_user_id', $user_id)->where('purchase_state', 1)->count('purchase_id') / 10);
        $purchase_info = array();
        $purchase_temp_list = $purchase->field('purchase_id,purchase_code,purchase_project_id,purchase_product_name,purchase_state')->order('purchase_id desc')->where('purchase_user_id', $user_id)->where('purchase_state', 1)->limit(($page_id - 1) * $perpage, $page_id * $perpage)->select();
        $project = new Project;

        foreach ($purchase_temp_list as $purchase_temp) {
            $project_info = $project->field('project_name')->where('project_id', $purchase_temp['purchase_project_id'])->find();
            $purchase_info[] = [
                'purchaseId' => $purchase_temp['purchase_id'],
                'purchaseCode' => $purchase_temp['purchase_code'],
                'purchaseProjectName' => $project_info['project_name'],
                'purchaseProductName' => $purchase_temp['purchase_product_name'],
                'purchaseState' => $purchase_temp['purchase_state']
            ];
        }
        return json([
            'code' => 1131,
            'message' => '项目查询成功！',
            'page' => $page_id,
            'total' => $total_id,
            'content' => $purchase_info
        ]);
    }

    public function get_detail() {
        check_login();
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();
        if($request->has('purchaseId')) {
            $purchase_id = intval($request->get('purchaseId'));
        } else {
            return json([
                'code' => 1044,
                'message' => '参数有误'
            ]);
        }

        if($purchase_id == 0) {
            return json([
                'code' => 1043,
                'message' => '参数有误'
            ]);
        }

        $purchase = new Purchase();
        $purchase_info = $purchase->field('purchase_code,purchase_project_id,purchase_product_name,purchase_product_num,purchase_product_type,purchase_type,purchase_dept,purchase_budget,purchase_technology_parameter,purchase_explain,purchase_technology_file,purchase_is_conform,purchase_reject_reason,purchase_reject_content,purchase_payment,purchase_quality,purchase_deadline,purchase_arrive_time,purchase_place,purchase_recommend,purchase_order,purchase_order_time,purchase_tip,purchase_create_time,purchase_state')->where('purchase_id', $purchase_id)->where('purchase_state', 1)->find();
        if($purchase_info == null) {
            return json([
                'code' => 1142,
                'message' => '请购不存在！'
            ]);
        }

        if(intval($purchase_info['purchase_is_conform']) == 1) {
            $purchase_info['purchase_is_conform'] = '是';
        }
        if(intval($purchase_info['purchase_is_conform']) == 0) {
            $purchase_info['purchase_is_conform'] = '否';
        }

        $project = new Project();
        $project_info = $project->field('project_id,project_code,project_name')->where('project_id', $purchase_info['purchase_project_id'])->find();
        if($project_info == null) {
            return json([
                'code' => 1143,
                'message' => '参数有误！'
            ]);
        }

        return json([
            'code' => 1141,
            'message' => '请购明细查询成功！',
            'content' => [
                'purchaseId' => $purchase_id,
                'purchaseCode' => $purchase_info['purchase_code'],
                'purchaseProjectId' => $project_info['project_id'],
                'purchaseProjectName' => $project_info['project_name'],
                'purchaseProjectCode' => $project_info['project_code'],
                'purchaseProductName' => $purchase_info['purchase_product_name'],
                'purchaseProductNum' => $purchase_info['purchase_product_num'],
                'purchaseProductType' => $purchase_info['purchase_product_type'],
                'purchaseType' => $purchase_info['purchase_type'],
                'purchaseDept' => $purchase_info['purchase_dept'],
                'purchaseBudget' => $purchase_info['purchase_budget'],
                'purchaseTechnologyParameter' => $purchase_info['purchase_technology_parameter'],
                'purchaseExplain' => $purchase_info['purchase_explain'],
                'purchaseTechnologyFile' => $purchase_info['purchase_technology_file'],
                'purchaseTechnologyFileArray' => list_to_file($purchase_info['purchase_technology_file']),
                'purchaseIsConform' => $purchase_info['purchase_is_conform'],
                'purchaseRejectReason' => $purchase_info['purchase_reject_reason'],
                'purchaseRejectContent' => $purchase_info['purchase_reject_content'],
                'purchasePayment' => $purchase_info['purchase_payment'],
                'purchaseQuality' => $purchase_info['purchase_quality'],
                'purchaseDeadline' => date('Y-m-d', $purchase_info['purchase_deadline']),
                'purchaseArriveTime' => date('Y-m-d', $purchase_info['purchase_arrive_time']),
                'purchasePlace' => $purchase_info['purchase_place'],
                'purchaseRecommend' => $purchase_info['purchase_recommend'],
                'purchaseOrder' => $purchase_info['purchase_order'],
                'purchaseOrderTime' => date('Y-m-d', $purchase_info['purchase_order_time']),
                'purchaseTip' => $purchase_info['purchase_tip']
            ],
            'purchaseCreateTime' => date('Y-m-d', $purchase_info['purchase_create_time']),
            'purchaseState' => $purchase_info['purchase_state']
        ]);
    }

    public function check_code($purchase_code = '') {
        check_login();
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();
        if($request->has('purchaseCode')) {
            $purchase_code = $request->get('purchaseCode');
        }

        $purchase = new Purchase();
        $recommend_code = array();
        $code_prefix = 'LK'.date('ym', time()).'-';
        
        if($purchase_code != '' && strlen($purchase_code) == 10) {
            $purchase_info = $purchase->field('purchase_id')->where('purchase_code', $purchase_code)->find();
            if($purchase_info == null) {
                return json([
                    'code' => 20061,
                    'message' => '请购编号审核成功',
                    'recommendCode' => array($purchase_code)
                ]);
            }
        }
        for($i = 0; $i < 100; $i++) {
            $code_postfix = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
            $purchase_list = $purchase->field('purchase_code')->where('purchase_code', 'like', $code_prefix.zero_full($i, 2).'%')->select();
            foreach ($purchase_list as $purchase_info) {
               $code_postfix[intval(substr($purchase_info['purchase_code'], 9))] = 1;
            }
            for($j = 0; $j < 10; $j++) {
                if($code_postfix[$j] == 0) {
                    $recommend_code[] = $code_prefix.zero_full($i, 2).$j;
                }
            }
            if(count($recommend_code) >= 3) {
                break;
            }
        }
        return json([
            'code' => 20062,
            'message' => '请购编号创建成功',
            'recommendCode' => $recommend_code
        ]);
    }
}