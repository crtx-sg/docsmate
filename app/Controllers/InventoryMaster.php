<?php namespace App\Controllers;

use App\Models\InventoryMasterModel;
use CodeIgniter\I18n\Time;
use App\Models\TeamModel;
use App\Models\SettingsModel;
use App\Models\ProjectModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class InventoryMaster extends BaseController
{
	public function index()
    {
		$data = [];

		$data['pageTitle'] = 'Assets';
		$data['addBtn'] = true;
		$data['addUrl'] = "/inventory-master/add";
		$data['backUrl'] = '/inventory-master';

		$model = new InventoryMasterModel();
		$view = $this->request->getVar('view');
		if($view == '')
			$view = 'active';

		$data['data'] = $model->where('status', $view)->orderBy('id', 'desc')->findAll();	
		$data['today_date'] = gmdate("Y-m-d H:i:s");
		$data['statusList'] = ['active', 'in-active', 'not-found', 'cal-overdue'];
		$teamModel = new TeamModel();
		$data['teamMembers'] = $teamModel->getMembers();
		session()->set('prevUrl', '');

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('InventoryMaster/list',$data);
		echo view('templates/footer');
	}

	private function returnParams(){
		$uri = $this->request->uri;
		$id = $uri->getSegment(3);
		if($id != ""){
			$id = intval($id);
		}
		return $id;
	}

	private function getAssetsCategoryEnums() {
		$settingsModel = new SettingsModel;
		$assetsCategory = $settingsModel->where("identifier","assetsCategory")->first();
		if($assetsCategory["options"] != null){
			$dataList = json_decode( $assetsCategory["options"], true );
			$assetsCategory = [];
			foreach($dataList as $key=>$list){
				$assetsCategory[] = $dataList[$key];
			}
		}else{
			$assetsCategory = [];
		}
		return $assetsCategory;
	}

	public function add(){
		$id = $this->returnParams();
		helper(['form']);
		$model = new InventoryMasterModel();
		$data = [];
		$data['pageTitle'] = 'Assets';
		$data['addBtn'] = False;
		$dataList = [];
		$data['statusList'] = ['active', 'in-active', 'not-found', 'cal-overdue'];
		$date['today_date'] = gmdate("Y-m-d H:i:s");
		$teamModel = new TeamModel();
		$data['teamMembers'] = $teamModel->getMembers();
		$data['assetsCategory'] = $this->getAssetsCategoryEnums();
		//Handling the back page navigation url
		if(isset($_SERVER['HTTP_REFERER'])){
			$urlStr = $_SERVER['HTTP_REFERER'];
			if (strpos($urlStr, 'view')) {
				$urlAr = explode("view", $urlStr);
				$backUrl = '/inventory-master?view'.$urlAr[count($urlAr)-1];
				session()->set('prevUrl', $backUrl);
			}else{
				if(session()->get('prevUrl') == ''){
					session()->set('prevUrl', '/inventory-master');
				}
			}
		}else{
			session()->set('prevUrl', '/inventory-master');
		}
		$data['backUrl'] =  session()->get('prevUrl');

		$rules = [
			'type' => 'required|max_length[25]',
			'description' => 'required|max_length[1000]',
			'make' => 'required|max_length[25]',
			'model' => 'required|max_length[25]',
			'serial' => 'required|max_length[25]',
		];	

		if($id == ""){
			$data['action'] = "add";
			$data['formTitle'] = "Add Asset";
		}else{
			$data['action'] = "add/".$id;
			$data['formTitle'] = "Update Asset";
			$data['member'] = $model->where('id',$id)->first();	
		}

		if ($this->request->getMethod() == 'post') {
			/* Checking the 'cal_date', if its exceeds the current date then status should be 'cal-overdue' */
			$status = $this->request->getVar('status');
			// if((int)$this->request->getVar('cal_date')){
			// 	$cal_date = strtotime($this->request->getVar('cal_date')) + (330*60);
			// 	$today = strtotime(gmdate("Y-m-d")) + (330*60);
			// 	if($cal_date > $today){
			// 		$status = $data['statusList'][3];
			// 	}
			// }
			if($status == ''){
				$status = 'active';
			}
			$newData = [
				'item' => $this->request->getVar('item'),
				'model' => $this->request->getVar('model'),
				'type' => $this->request->getVar('type'),
				'description' => $this->request->getVar('description'),
				'make' => $this->request->getVar('make'),
				'model' => $this->request->getVar('model'),
				'serial' => $this->request->getVar('serial'),
				'entry_date' => $this->request->getVar('entry_date'),
				'retired_date' => $this->request->getVar('retired_date'),
				'cal_date' => $this->request->getVar('cal_date'),
				'cal_due' => $this->request->getVar('cal_due'),
				'location' => $this->request->getVar('location'),
				'invoice' => $this->request->getVar('invoice'),
				'invoice_date' => $this->request->getVar('invoice_date'),
				'vendor' => $this->request->getVar('vendor'),
				'status' => $status,
				'used_by' => $this->request->getVar('used_by'),
				'updated_by' => $this->request->getVar('updated_by'),
				'update_date' => gmdate("Y-m-d H:i:s")
			];
			$data['member'] = $newData;
			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{
				if($id > 0){
					$newData['id'] = $id;
					$newData['update_date'] = gmdate("Y-m-d H:i:s");
					$message = 'Assets successfully updated.';
				}else{
					$message = 'Assets successfully added.';
				}
				$model->save($newData);
				$session = session();
				$session->setFlashdata('success', $message);
			}
		}

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('InventoryMaster/form', $data);
		// echo view('templates/footer');
	}

	public function delete(){
		if (session()->get('is-admin')){
			$id = $this->returnParams();
			$model = new InventoryMasterModel();
			$model->delete($id);
			$response = array('success' => "True");
			echo json_encode( $response );
		}
		else{
			$response = array('success' => "False");
			echo json_encode( $response );
		}
	}

	public function createExcel() {
		$fileName = 'asset_'.date("Y-m-d_H:i:s").'.xlsx';
		//$fileName = 'asset.xlsx';
		$model = new InventoryMasterModel();
		$projectModel = new ProjectModel();
		$view = $this->request->getVar('view');
		if($view == '')
			$view = 'active';

		$inventoryData = $model->where('status', $view)->orderBy('id', 'desc')->findAll();	
		$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
       	$sheet->setCellValue('A1', 'Id');
        $sheet->setCellValue('B1', 'Item');
        $sheet->setCellValue('C1', 'Model');
        $sheet->setCellValue('D1', 'Type');
		$sheet->setCellValue('E1', 'Description');
        $sheet->setCellValue('F1', 'Serial');       
		$sheet->setCellValue('G1', 'Entry Date');  
		$sheet->setCellValue('H1', 'Retired Date');  
		$sheet->setCellValue('I1', 'Cal Date');  
		$sheet->setCellValue('J1', 'Cal Due');  
		$sheet->setCellValue('K1', 'Location');  
		$sheet->setCellValue('L1', 'Invoice');  
		$sheet->setCellValue('M1', 'Vendor');  
		$sheet->setCellValue('N1', 'Status');
		$sheet->setCellValue('O1', 'Used By');
		$sheet->setCellValue('P1', 'Updated By');
		$sheet->setCellValue('Q1', 'Update Date');

// 		$styleArray = array(
//             'borders' => array(
//                 'outline' => array(
//                     'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
//                     'color' => array('argb' => '00000000'),
//                 ),
//             ),
//             'fill' => array(
//                 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                 'startColor' => array('argb' => 'FF4F81BD')
//             )
//         );
// $spreadsheet->getActiveSheet()->applyFromArray($styleArray);
		$spreadsheet
		->getActiveSheet()
		->getStyle('A1:Q1')
		->getFill()
		->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
		->getStartColor()
		->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);
	//$spreadsheet->getActiveSheet()->freezePane('A1');

		$spreadsheet->getActiveSheet()->getStyle('A1:R1')
->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
for ($i = 'A'; $i !=  $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
    $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
}
        $rows = 2;
        foreach ($inventoryData as $val){
            $sheet->setCellValue('A' . $rows, $val['id']);
            $sheet->setCellValue('B' . $rows, $val['item']);
            $sheet->setCellValue('C' . $rows, $val['model']);
            $sheet->setCellValue('D' . $rows, $val['type']);
	    	$sheet->setCellValue('E' . $rows, $val['description']);
            $sheet->setCellValue('F' . $rows, $val['serial']);
			$sheet->setCellValue('G' . $rows, $val['entry_date']);
			$sheet->setCellValue('H' . $rows, $val['retired_date']);
			$sheet->setCellValue('I' . $rows, $val['cal_date']);
			$sheet->setCellValue('J' . $rows, $val['cal_due']);
			$sheet->setCellValue('K' . $rows, $val['location']);
			$sheet->setCellValue('L' . $rows, $val['invoice']);
			$sheet->setCellValue('M' . $rows, $val['vendor']);
			$sheet->setCellValue('N' . $rows, $val['status']);
			$sheet->setCellValue('O' . $rows, (isset($val['used_by']) && $val['used_by'] != "")?$projectModel->getName($val['used_by']):"");
			$sheet->setCellValue('P' . $rows, (isset($val['updated_by']) && $val['updated_by'] != "")?$projectModel->getName($val['updated_by']):"");
			$sheet->setCellValue('Q' . $rows, $val['update_date']);
            $rows++;
        } 
        $writer = new Xlsx($spreadsheet);
		$projectDocsRootDir = getenv('app.basePath');
		$directoryName = "Asset_Details";
		if (!is_dir($directoryName)) {
			mkdir($directoryName, 0777);
		}
		$outputFilePath = $projectDocsRootDir . "/" . $directoryName . "/" .$fileName;
		$writer->save($outputFilePath);

        header('Content-Type: application/vnd.ms-excel'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName.'"'); 
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');	// download file 
    }    
}