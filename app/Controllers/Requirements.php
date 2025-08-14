<?php namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\ProductModel;
use App\Models\RequirementsModel;
use App\Models\SettingsModel;
use App\Models\TraceabilityMatrixModel;
use App\Models\TraceabilityOptionsModel;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Requirements extends BaseController
{
	public function index()
    {
		$data = [];
		$data['pageTitle'] = 'Requirements';
		$data['addBtn'] = True;
		$data['addUrl'] = "/requirements/add";
		$data['AddMoreBtn'] = true;
		$data['AddMoreBtnText'] = "Sync";

		// helper(['form']); 
		$productModel = new ProductModel();
        $data['products'] = $productModel->getProducts(); 
		
		$data['requirementCategory'] = $this->getRequirementCategoryEnums();
		$status = $this->request->getVar('status');
		//$projectId = $this->request->getVar('project-id');
		$productId = $this->request->getVar('product-id');
		$type = $this->request->getVar('type');
		$data['requirementSelected'] = $status;
		// if($productId == "" && $projectId != ""){
		// 	$productId = $productModel->getProductId($projectId);	
		// }
		if($status == 'All' || $status == ''){
			$status ='User Needs';
			$data['requirementSelected'] = 'User Needs';
		}
		
		if($productId == ''){
			helper('Helpers\utils');
			$data['selectedProduct'] = getActiveProductId();
			// $data['selectedProjectIds'] = $productModel->getProductProjectIds($data['selectedProduct']);
			// $data['selectedProject'] = $data['selectedProjectIds'];
		} else if($productId != ''){
			$data['selectedProduct'] = $productId;
			// $data['selectedProjectIds'] = $productModel->getProductProjectIds($data['selectedProduct']);
			// $data['selectedProject'] = $data['selectedProjectIds'];
		}

		//$data['projects'] = $productModel->getProductProjects($data['selectedProduct']);  
		
		$model = new RequirementsModel();
		if($type == 'sync'){
			$this->syncRequirements();
		}
		$data["data"] = $model->getRequirements($status, $data['selectedProduct']);
		session()->set('prevUrl', '');
		$data['isEditForm'] = false;

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Requirements/list',$data);
		echo view('templates/footer');
	}

	private function getRequirementCategoryEnums() {
		$settingsModel = new SettingsModel;
		$requirementCategory = $settingsModel->where("identifier","requirementsCategory")->first();
		if($requirementCategory["options"] != null){
			$requirementCategory = json_decode( $requirementCategory["options"], true );
		}else{
			$requirementCategory = [];
		}
		return $requirementCategory;
	}

	private function returnParams(){
		$uri = $this->request->uri;
		$id = $uri->getSegment(3);
		if($id != ""){
			$id = intval($id);
		}
		return $id;
	}

	public function syncRequirements(){
		$model = new RequirementsModel();

		$requirements = $model->fetchTestLinkRequirements();

		//add requiremts suties here to exclude
		$excludeRequirements = array("vms-tool-validation-requirements", "non-functional-requirements");

		if( $requirements ){
			try {
				foreach ($requirements->children() as $row) {
					$title = $row['title'];
					if ( in_array($title, $excludeRequirements) ){
						//exclude unwanted requirements
						$skip = 1;
					} 
					else {
						$type = "";
						if (strpos($title, "subsystem")) {
							$type = "Subsystem";
						} else {
							$type = "System";
						}
						foreach ($row->requirement as $requirement) {
							$description = "[$requirement->docid:$requirement->title] $requirement->description";
							$whereCondition =  " WHERE type = '". addslashes($type) ."' AND requirement = '". addslashes($requirement->title) ."'";
							$result = $model->getRequirementRecord($whereCondition);

							if( $result ){
								// check whether the description is same or not, if not then update the description
								if( $description != $result[0]['description']){
									// update the requirement description
									$updateData = [
										'id' => $result[0]['id'],
										'description' => $description,
										'update_date' => gmdate("Y-m-d H:i:s")
									];
									$model->save($updateData);
								} 
							} else {
								// insert a new record
								$newData = [
									'type' => $type,
									'requirement' => $requirement->title,
									'description' => $description,
									'update_date' => gmdate("Y-m-d H:i:s"),
								];
								$model->save($newData);
							}
						}
					}
				}
			} catch(Exception $e){
				error_log($e);
				return;
			}
			
		} else {
			error_log("[DocsGo][REQUIREMENTS][Requirements.syncRequirements][INFO] requirements list is empty.");
			return;
		}
	}
	
	public function add(){

		$id = $this->returnParams();

		helper(['form']);
		$model = new RequirementsModel();
		$data = [];
		$data['pageTitle'] = 'Requirements';
		$data['addBtn'] = False;
		$data['requirementCategory'] = $this->getRequirementCategoryEnums();

		//Handling the back page navigation url
		if(isset($_SERVER['HTTP_REFERER'])){
			$urlStr = $_SERVER['HTTP_REFERER'];
			if (strpos($urlStr, 'status')) {
				$urlAr = explode("status", $urlStr);
				$backUrl = '/requirements?status'.$urlAr[count($urlAr)-1];
				session()->set('prevUrl', $backUrl);
			}else{
				if(session()->get('prevUrl') == ''){
					session()->set('prevUrl', '/requirements');
				}
			}
		}else{
			session()->set('prevUrl', '/requirements');
		}
		if(strpos(session()->get('prevUrl'), '&type=sync')){
			$cur = session()->get('prevUrl');
			$urlparam = str_replace("&type=sync", "", $cur);
			session()->set('prevUrl', $urlparam);
		}
		$data['backUrl'] =  session()->get('prevUrl');
		
		$productModel = new ProductModel();
		if($id == ""){
        	$data['products'] = $productModel->getProducts(); 
			$data['action'] = "add";
			$data['formTitle'] = "Add Requirements";
			$data['isEditForm'] = false;
			$rules = [
				'type' => 'required',
				'product-id' => 'required',
				'requirement' => 'required|min_length[3]|max_length[100]',
				'description' => 'required|min_length[3]|max_length[2100]',
			];

		}else{
			$data['action'] = "add/".$id;
			$data['formTitle'] = "Update Requirements";
			$data['isEditForm'] = true;
			$rules = [
				'type' => 'required',
				'product-id' => 'required',
				'requirement' => 'required|min_length[3]|max_length[100]',
				'description' => 'required|min_length[3]|max_length[2100]',
			];	

			$data['member'] = $model->where('id',$id)->first();		
			$data['products'] = $productModel->getProducts(); 
			// $data['member']['product-id'] = $productModel->getProductId($data['member']['project-id']);
			// $data['projects'] = $productModel->getProductProjects($data['member']['product-id']);
		}
		

		if ($this->request->getMethod() == 'post') {
			$currentTime = gmdate("Y-m-d H:i:s");
			$newData = [
				'type' => $this->request->getVar('type'),
				'product-id' => $this->request->getVar('product-id'),
				'requirement' => $this->request->getVar('requirement'),
				'description' => $this->request->getVar('description'),
				'update_date' => $currentTime,
			];

			$data['member'] = $newData;
			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{

				if($id > 0){
					$newData['id'] = $id;
					$message = 'Requirements updated.';
				}else{
					$message = 'Requirements successfully added.';
				}

				$model->save($newData);
				$session = session();
				$session->setFlashdata('success', $message);
			}
		}
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Requirements/form', $data);
		echo view('templates/footer');
	}

	private function getProjects(){
        $projectModel = new ProjectModel();
        $data = $projectModel->findAll();	
		$projects = [];
		foreach($data as $project){
			$projects[$project['project-id']] = $project['name'];
		}
		return $projects;
	}


	public function delete(){
		if (session()->get('is-admin')){
			if ($this->request->getMethod() == 'post') {
                $id = $this->request->getVar('id');
                $type = $this->request->getVar('type');

				//If type is 'User Needs | Standards | Guidance delete all options and traceability two tables data
				if(($type == 'User Needs') || ($type == 'Standards') || ($type == 'Guidance')){
					// Track the traceability_id from options where type=$type&&requirement_id=$id
					$check1 = array('requirement_id'=> $id, 'type'=> $type);
					$model2 = new TraceabilityOptionsModel;
					$data = $model2->select('traceability_id')->where($check1)->findAll();
					if(isset($data) && count($data) > 0){
						foreach($data as $val){
							$traceabilityId = $val['traceability_id'];
							$model3 = new TraceabilityMatrixModel;
							$model3->delete($traceabilityId);
							$model2->where('traceability_id', $traceabilityId)->delete();
						}
					}
				}else{
					//Delete all options wrt of id and type
					$model2 = new TraceabilityOptionsModel;
					$check2 = array('requirement_id'=> $id, 'type'=> $type);
					$model2->where($check2)->delete();
				}
				$model1 = new RequirementsModel();
				$model1->delete($id);

				$response = array('success' => "True");
				echo json_encode( $response );
			}
		}else{
			$response = array('success' => "False");
			echo json_encode( $response );
		}
	}


	public function import() {
		try{
			$filePath = getenv('app.basePath');
			$attachmentsDir = "uploads/requirements";
			$fileLinks = $this->uploadFiles($attachmentsDir);
			if(count($fileLinks)){
					$file_name = $fileLinks['link'];
					$arr_file 	= explode('.', $file_name);
					$extension 	= end($arr_file);
			}
		
			if('csv' == $extension) {
				$reader 	= new \PhpOffice\PhpSpreadsheet\Reader\Csv();
			} else {
				$reader 	= new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			}
			$spreadsheet 	= $reader->load($filePath.$file_name);
			$sheet_data 	= $spreadsheet->getActiveSheet()->toArray();
			$list 			= [];
			$productModel = new ProductModel();
			foreach($sheet_data as $key => $val) {
				if($key != 0) {
						$list [] = [
							'product-id' =>  $productModel->getProductIdByName(trim($val[0])),
							'type'					=> $val[1],
							'requirement'			=> $val[2],
							'description'				=> $val[3]
						];
				}
			}
			if(file_exists($filePath.$file_name))
				unlink($filePath.$file_name);
			if(count($list) > 0) {
				$model = new RequirementsModel();
				$result 	= $model->bulkInsertion($list);
				$session = session();
				if($result) {
					$json = [
						'success_message' 	=> "All Entries are imported successfully."
					];
				} else {
					$json = [
						'error_message' 	=> "Something went wrong. Please try again."
					];
				}
			} else {
				$json = [
					'error_message' => "No new record is found."
				];
			}
		echo json_encode($json);
		}
		catch(Exception $e){
            error_log($e);
            return false;
        }
	}

	private function uploadFiles($attachmentsDir){
        $fileLinks = array();
        if($files = $this->request->getFiles())
        {
            if (!file_exists($attachmentsDir)) {
                mkdir($attachmentsDir, 0777, true);
            }

			$attachment = $files['attachments'];
			if ($attachment->isValid() && ! $attachment->hasMoved())
			{           
				$newName = $attachment->getRandomName();
				$attachment->move($attachmentsDir, $newName);
				$type = $attachment->getClientMimeType();
				$link = "/".$attachmentsDir."/".$newName;

				$object['link'] = $link;
				$object['type'] = $type;
			}
        }
        return $object;
    }

	public function createRequirementTemplate() {
		$fileName = 'requirementsTemplate.xlsx';
		
		$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
       	$sheet->setCellValue('A1', 'product_name');
        $sheet->setCellValue('B1', 'type');
        $sheet->setCellValue('C1', 'requirement');
        $sheet->setCellValue('D1', 'description');
		
		$spreadsheet
		->getActiveSheet()
		->getStyle('A1:D1')
		->getFill()
		->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
		->getStartColor()
		->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

		$spreadsheet->getActiveSheet()->getStyle('A1:D1')
->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
for ($i = 'A'; $i !=  $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
    $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
}
         
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$projectDocsRootDir = getenv('app.basePath');
		
		$outputFilePath = $projectDocsRootDir."/".$fileName;
		$writer->save($outputFilePath);

        header('Content-Type: application/vnd.ms-excel'); // generate excel file
        header('Content-Disposition: attachment;filename="'. $fileName.'"'); 
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');	// download file 
    }
}