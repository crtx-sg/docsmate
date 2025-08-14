<?php namespace App\Controllers;

use App\Models\TestCasesModel;
use App\Models\TraceabilityOptionsModel;
use App\Models\ProductModel;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class TestCases extends BaseController
{
	public function index()
    {
		$data = [];
		$data['pageTitle'] = 'Test';
		$data['addBtn'] = True;
		$data['addUrl'] = "/test-cases/add";
		$data['AddMoreBtn'] = true;
		$data['AddMoreBtnText'] = "Sync";

		$productModel = new ProductModel();
        $data['products'] = $productModel->getProducts(); 

		$productId = $this->request->getVar('product-id');

		$model = new TestCasesModel();
		if($productId == ''){
			helper('Helpers\utils');
			$data['selectedProduct'] = getActiveProductId();
		} else if($productId != ''){
			$data['selectedProduct'] = $productId;
		}
		
		$status = $this->request->getVar('status');
		if($status == 'sync'){
			$this->syncTestCases();
		}
		$data['data'] = $model->where('product-id',$data['selectedProduct'])->orderBy('testcase', 'asc')->findAll();	
		

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('TestCases/list',$data);
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

	public function syncTestCases(){
		$model = new TestCasesModel();

		$testCases = $model->fetchTestLinkTestCases();

		if( $testCases ){
			$testCaseIdPrefix = $testCases->testCasePrefix; 
			foreach( $testCases->testCasesList as $testCase ){
				$description = "[$testCaseIdPrefix-$testCase->tc_external_id:$testCase->name] $testCase->summary";
				$whereCondition =  " WHERE testcase = '" . addslashes($testCase->name) . "'";
				$result = $model->getTestCaseRecord($whereCondition);
				if ($result) {
					// check whether the description is same or not, if not then update the description
					if ( $description != $result[0]['description'] ) {
						// update the test case description
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
						'testcase' => $testCase->name,
						'description' => $description,
						'update_date' => gmdate("Y-m-d H:i:s"),
					];
					$model->save($newData);
				}
			}
		} else {
			error_log("[DocsGo][TestCases.syncTestCases][INFO] test cases list is empty.");
			return;
		}
		
	}
	
	public function add(){

		$id = $this->returnParams();

		helper(['form']);
		$model = new TestCasesModel();
		$data = [];
		$data['pageTitle'] = 'Test';
		$data['addBtn'] = False;
		$data['backUrl'] = "/test-cases";

		$productModel = new ProductModel();
		if($id == ""){
			$data['products'] = $productModel->getProducts(); 
			$data['action'] = "add";
			$data['formTitle'] = "Add Test";

			$rules = [
				'product-id' => 'required',
				'testcase' => 'required|min_length[3]|max_length[100]',
				'description' => 'required|min_length[3]|max_length[500]'
			];

		}else{
			$data['action'] = "add/".$id;
			$data['formTitle'] = "Update Test";

			$rules = [
				'product-id' => 'required',
				'testcase' => 'required|min_length[3]|max_length[100]',
				'description' => 'required|min_length[3]|max_length[500]'
			];	

			$data['member'] = $model->where('id',$id)->first();	
			$data['products'] = $productModel->getProducts(); 	
		}
		

		if ($this->request->getMethod() == 'post') {
			$currentTime = gmdate("Y-m-d H:i:s");
			$newData = [
				'product-id' => $this->request->getVar('product-id'),
				'testcase' => $this->request->getVar('testcase'),
				'description' => $this->request->getVar('description'),
				'update_date' => $currentTime,
			];
			//print_r($newData);exit;
			$data['member'] = $newData;
			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{

				if($id > 0){
					$newData['id'] = $id;
					// date_default_timezone_set('Asia/Kolkata');
					// $timestamp = date("Y-m-d H:i:s");
					// $newData['update_date'] = $timestamp;
					$message = 'Test Cases successfully updated.';
				}else{
					$message = 'Test Cases successfully added.';
				}

				$model->save($newData);
				$session = session();
				$session->setFlashdata('success', $message);
			}
		}

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('TestCases/form', $data);
		echo view('templates/footer');
	}

	public function delete(){
		if (session()->get('is-admin')){
			//Delete all options wrt of id and type
			$id = $this->returnParams();
			$model = new TraceabilityOptionsModel;
			$check = array('requirement_id'=> $id, 'type'=> 'testcase');
			$model->where($check)->delete();

			$model1 = new TestCasesModel();
			$model1->delete($id);
			$response = array('success' => "True");
			echo json_encode( $response );
		}
		else{
			$response = array('success' => "False");
			echo json_encode( $response );
		}
	}

	public function import() {
		try{
			$filePath = getenv('app.basePath');
			$attachmentsDir = "uploads/testcases";
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
			$model = new TestCasesModel();
			foreach($sheet_data as $key => $val) {
				if($key != 0) {
						$list [] = [
							'product-id' =>  $productModel->getProductIdByName(trim($val[0])),
							'testcase'					=> $val[1],
							'description'				=> $val[2]
						];
				}
			}
			if(file_exists($filePath.$file_name))
				unlink($filePath.$file_name);
			if(count($list) > 0) {
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

	public function createTestCasesTemplate() {
		$fileName = 'testcasesTemplate.xlsx';
		
		$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
       	$sheet->setCellValue('A1', 'product_name');
        $sheet->setCellValue('B1', 'testcase');
        $sheet->setCellValue('C1', 'description');
		
		$spreadsheet
		->getActiveSheet()
		->getStyle('A1:C1')
		->getFill()
		->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
		->getStartColor()
		->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

		$spreadsheet->getActiveSheet()->getStyle('A1:C1')
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