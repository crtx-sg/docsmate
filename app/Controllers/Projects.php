<?php namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\TeamModel;
use App\Models\DocumentModel;
use App\Models\ReviewModel;
use App\Models\ProductModel;
use App\Models\ProductsProjectsMappingModel;
use CodeIgniter\I18n\Time;
use TP\Tools\Pandoc;
use TP\Tools\PandocExtra;
use Mpdf;
use Mpdf\Config;
use DOMDocument;
use App\Controllers\GenerateDocuments;
use PhpOffice\PhpWord\Shared\ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;


// function myCustomErrorHandler(int $errNo, string $errMsg, string $file, int $line) {
// 	echo $errMsg;
// 	alert($errMsg);
// 	exit;
// 	return false;
// }
// set_error_handler('App\Controllers\myCustomErrorHandler');

class Projects extends BaseController
{
	public function index()
    {
        $data = [];
		$data['pageTitle'] = 'Projects';
		$data['addBtn'] = True;
		$data['addUrl'] = "/projects/add";

		$model = new ProjectModel();
		$view = $this->request->getVar('view');
		$productId = $this->request->getVar('product-id');

		$productModel = new ProductModel();
        $data['products'] = $productModel->getProducts();

		if($productId == ''){
			helper('Helpers\utils');
			$data['selectedProduct'] = getActiveProductId();
		} else if($productId != ''){
			$data['selectedProduct'] = $productId;
		}

		if($view == ''){
			$view = 'Active';			
		}

		$userId = session()->get('id');
		$documentModel = new DocumentModel();
		$userPermissions = $documentModel->getUserPermissions($userId);
		if($userPermissions){
			$data['isAllowedToDownload'] = "True";
		} else {
			$data['isAllowedToDownload'] = "False";
		}

		//$data['data'] = $model->where('status', $view)->orderBy('start-date', 'desc')->findAll();
		$condition["`docsgo-projects.status`"] = $view;

        if ($productId != "ALL") {
            $condition["`docsgo-products-projects-mapping.product-id`"] = $data['selectedProduct'];
        }
		$data['data'] = $model->getAll($condition);
		//print_r($data);exit;
		$data['view'] = $view;
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Projects/list',$data);
		echo view('templates/footer');
	}


	public function add($id = 0){

		helper(['form']);
		$model = new ProjectModel();
		$teamModel = new TeamModel();
		$productModel = new ProductModel();
		$productsProjectsMappingModel = new ProductsProjectsMappingModel();

		$data = [];
		$data['pageTitle'] = 'Projects';
		$data['addBtn'] = False;
		$data['backUrl'] = "/projects";
		$data['statusList'] = ['Active', 'Completed'];
		
		$data['teamMembers'] = $teamModel->getManagers();	
		$data['products'] = $productModel->getProducts();
		//print_r($data['products']);exit;

		if($id == 0){
			$data['action'] = "add";
			$data['formTitle'] = "Add Project";
		}else{
			$data['action'] = "add/".$id;
			
			$data['project'] = $model->where('project-id',$id)->first();
			$productProjectData = $productsProjectsMappingModel->where('project-id',$id)->first();
			if(isset($productProjectData) && $productProjectData != null){
				$data['project']['product-id'] = $productProjectData['product-id'];
			}

			$data['formTitle'] = $data['project']["name"];	
		}


		if ($this->request->getMethod() == 'post') {
			
			$rules = [
				'name' => 'required|min_length[3]|max_length[50]',
				'description' => 'max_length[500]',
				'version' => 'required|min_length[3]|max_length[10]',
				'start-date' => 'required',
				'status' => 'required',
				'product-id' => 'required'
			];	

			$newData = [
				'name' => $this->request->getVar('name'),
				'version' => $this->request->getVar('version'),
				'category' => $this->request->getVar('category'),
				'start-date' => $this->request->getVar('start-date'),
				'description' => trim($this->request->getVar('description')),
				'end-date' => $this->request->getVar('end-date'),
				'status' => $this->request->getVar('status'),
				'manager-id' => $this->request->getVar('manager-id'),
				'product-id' => $this->request->getVar('product-id')
			];

			$data['project'] = $newData;

			if (! $this->validate($rules)) {
				$data['validation'] = $this->validator;
			}else{
				if($id > 0){
					$newData['project-id'] = $id;
					$message = 'Project successfully updated.';
				}else{
					$message = 'Project successfully added.';
				}
				$model->save($newData);
				$productData['product-id'] = $this->request->getVar('product-id');
				if($id > 0){
					$lastid = $id;
				} else {
					$lastid = $model->insertID();
				}
				$productData['project-id'] = $lastid;
				
				$isMapped = $productsProjectsMappingModel->where('project-id',$productData['project-id'])->where('product-id',$productData['product-id'])->first();
				if(!(isset($isMapped)) && $isMapped == null){
					$productsProjectsMappingModel->save($productData);
				}
				$session = session();
				$session->setFlashdata('success', $message);
			}
		}
		
		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Projects/form', $data);
		echo view('templates/footer');
	}

	
	public function delete($id){
		if (session()->get('is-admin')){
			$model = new ProjectModel();
			$model->delete($id);
			$response = array('success' => "True");
			echo json_encode( $response );
		}else{
			$response = array('success' => "False");
			echo json_encode( $response );
		}
	}

	public function summary($id){
		helper(['form']);
		$model = new ProjectModel();
		$teamModel = new TeamModel();
		$data = [];
		$data['project'] = $model->where('project-id',$id)->first();
		$data['pageTitle'] = $data['project']["name"].' Project Summary';
		$data['addBtn'] = False;
		// $data['AddMoreBtn'] = true;
		// $data['AddMoreBtnText'] = "Preview";
		$data['backUrl'] = "/projects";
		
		$data['formTitle'] = $data['project']["name"];
		$data['managerName'] = $model->getName($data['project']["manager-id"]);	
		$data['reviewsCount'] = $model->getProjectReviewsCount($id);
		$data['docsCount'] = $model->getProjectDocumentsCount($id);
		$data['resourcesCount'] = $model->getProjectResourcesCount($id);
		$data['reviewsList'] = $model->getProjectOpenReviews($id);
		$data['risksList'] = $model->getProjectOpenRisks($id);
		$data['totalHrsSpent'] = $model->getProjectTotalHrsSpent($id);
		$data['developerWiseHrsSpent'] = $model->getProjectDeveloperWiseHrsSpent($id);
		//$data['gapsList'] = $model->getProjectGaps($id);

		$reviewModel = new ReviewModel();
        $reviews = $reviewModel->getProjectReviewRecords($id);
        
		if(isset($reviews) && $reviews != null){
			foreach($reviews as $row=>$val) {
				$chartData['label'][] = $row;
				$chartData['data'][] = (int) $val;
			}
		}

        $data['chart_data'] = json_encode($chartData);

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Projects/summary', $data);
		echo view('templates/footer');
	}

	public function downloadSummary()  {
		$pandoc = new Pandoc();
		$doc = new DOMDocument();
		ini_set("display_errors", "1");
		error_reporting(E_ALL);

		$params = $this->returnParams();
		$typeOfRequest = $params[0];
		//$type = $this->getActionType($params[0]);
		$project_id = $params[1];

		$model = new ProjectModel();
		$projectData = $model->getProjectData($project_id);
		//Findout the project data
		if(isset($projectData) && count($projectData) == 0) {
			echo "no data";
			return false;
		}
		$generateDocsCont = new GenerateDocuments();
		$docData = $generateDocsCont->getDocumentProperties();
		$documentTitle = $docData['title']; $documentIcon = $docData["image"]; $documentFooterMsg = $docData["footer"];
		$documentFooterVersion = "";
		if (strpos($docData["footer"], ';') !== false) {
			$footerData = (explode(";", $docData["footer"]));
				$documentFooterMsg = $footerData[0];
				$documentFooterVersion = $footerData[1];
		}
		$projectObject = array_keys($projectData);
		$count = 0;
		foreach ($projectObject as $id) {
			$jsonMain = $projectData;
			$fileName = preg_replace('/[^A-Za-z0-9\-]/', '_', $jsonMain[$id]['name']);
			$fileName = $fileName . ".pdf";	

			$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
			$fontDirs = $defaultConfig['fontDir'];

			$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
			$fontData = $defaultFontConfig['fontdata'];
			
			$mpdf = new \Mpdf\Mpdf([
				'fontDir' => array_merge($fontDirs, [
					__DIR__ . '/custom/font/directory',

				]),
				'fontdata' => $fontData + [
					'frutiger' => [
						'R' => "ARIAL.TTF",
						'B' => "ARIALBD 1.TTF",
						'I' => "ARIALI 1.TTF",
						'BI' => "ARIALBI 1.TTF",
						'useOTL' => 0xFF,
						'useKashida' => 75,
					]
				],
				'default_font' => 'frutiger',
				'anchor2Bookmark' => 1,
				'tempDir' => '/tmp',
			]);

			$mpdf->InsertIndex(true, false, "es_ES.utf8", "Spanish_Spain");
			$mpdf->SetAnchor2Bookmark(1);
			$mpdf->h2toc = array('H1' => 0, 'H2' => 1, 'H3' => 1);
			$stylesheet = $generateDocsCont->getMPDFInlineStyles();
			$mpdf->WriteHTML($stylesheet,1);

			//#-1 Adding Header section
			$documentIconImage = $documentIcon; 
			if($documentFooterMsg == ''){
				$documentFooterMsg = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			if($documentFooterVersion == ''){
				$documentFooterVersion = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}

			//#-1 Footer for all pages
			$mpdf->SetHTMLFooter('
			<div style="font-family: frutiger; font-size: 11pt;width:100%;">
					<div style="width:40%;float:left;text-align:left;">
							<span >' . $documentFooterMsg . '</span>
					</div>
					<div style="width:20%;float:left;text-align:center;">
							<span style="font-weight:bold;">Page {PAGENO} of {nb}</span>
					</div>
					<div style="width:40%;float:left;text-align:right;">
							<span >'. $documentFooterVersion .'</span>
					</div>
			</div>');
			

			$mpdf->WriteHTML('&nbsp;');

			//#-2: Adding image at first page header line
			$mpdf->WriteHTML('<div style="width: 100%; text-align: center; margin-top: 150px;">
								<img src="' . $documentIconImage . '" 
								style="width: 20%; margin: 0" />
							</div>');

			//$DocID = 'Doc ID: ' . $json['cp-line4'];

			//#-3 Adding Document title
			$mpdf->WriteHTML('<div style="position: absolute; left:0; right: 0; top: 360;text-align: center;font-family: frutiger; font-weight: bold; font-size: 16pt;">
			' . $jsonMain[$id]['name'] .' summary
			</div>');
			$managerName = $model->getName($jsonMain[$id]["manager-id"]);
			$jsonData = "|Name | Manager | Version | Start Date | End Date |\n| -----------|---------|-------------|----------|---------------|\n|".$jsonMain[$id]['name']."| ".$managerName."| ".$jsonMain[$id]['version']."| ".$jsonMain[$id]['start-date']."| ".$jsonMain[$id]['end-date'];
			//#-4 Adding Document ID
			$mpdf->WriteHTML('<div style="position: absolute; left:0; right: 0; top: 390;text-align: center;font-family: frutiger; font-weight: bold; font-size: 16pt;">
			' . $jsonMain[$id]['version'] . '
			</div>');
			$mpdf->WriteHTML('<br>');
			
			//#-5: Adding change history section
			//$mpdf->WriteHTML('<div style="position: absolute; left:80; right: 0; top: 450; bottom: 0;font-family: frutiger;font-weight: bold;">Change History</div>');
			$tableContent = $pandoc->convert($jsonData, "gfm", "html5");
			$tableContent = $this->addTableStylesToContent($tableContent, '90');
			$mpdf->WriteHTML('<div style="position: absolute; left:80; right: 0; top: 480; bottom: 0;font-family: frutiger;">
			' . $tableContent . '
			</div>');


			//#-6 Adding page layout
			$mpdf->AddPage(
				'', // L - landscape, P - portrait 
				'',
				'',
				'',
				'',
				20, // margin_left
				20, // margin right
				30, // margin top
				25, // margin bottom
				12, // margin header
				5
			);

			//#-8 Adding logo left corner section every page
			$mpdf->SetHTMLHeader('<div style="text-align:left;padding-bottom:30mm;"><img src="'.$documentIconImage.'" style="width: 20mm; height: 17mm; margin: 0;"/></div>','O',true);

			$reviewsCnt = $model->getProjectReviewsCount($project_id);
			$docsCnt = $model->getProjectDocumentsCount($project_id);
			$resourcesCnt = $model->getProjectResourcesCount($project_id);
			$totHrsSpent = $model->getProjectTotalHrsSpent($project_id);
			$mpdf->WriteHTML('<div style="position: absolute; left:0; right: 0; top: 100;text-align: center;font-family: frutiger; font-weight: bold; font-size: 16pt;">
			Overall Counts
			</div>');
			//$mpdf->WriteHTML('<br>');
			$countsData = "|Reviews Count | Documents Count | Resources Count | Total Hours Spent |\n| -----------|---------|-------------|----------|\n|".$reviewsCnt."| ".$docsCnt."| ".$resourcesCnt."| ".$totHrsSpent;
			$tableCntContent = $pandoc->convert($countsData, "gfm", "html5");
			$tableCntContent = $this->addTableStylesToContent($tableCntContent, '90');
			$mpdf->WriteHTML('<div style="position: absolute; left:80; right: 0; top: 130; bottom: 0;font-family: frutiger;">
			' . $tableCntContent . '
			</div>');
			
			$mpdf->WriteHTML('<br>');
			$mpdf->WriteHTML('<br>');

			$reviewsList = $model->getProjectOpenReviews($project_id);
			if(count($reviewsList)>0){
				$mpdf->WriteHTML('<div style="position: absolute; left:0; right: 0; top: 300;text-align: center;font-family: frutiger; font-weight: bold; font-size: 16pt;">
				Reviews List
				</div>');
				//$mpdf->WriteHTML('<br>');
				$reviewsData = "|# | Review Type | Review |\n| -----------|---------|----------|\n|";
				foreach($reviewsList as $key => $row){
					$reviewsRow[] = $key+1 ."| ".$row["category"]."| ".$row["context"]."|\n";
				}
				$reviewsRow = implode('',$reviewsRow);
				$reviewsData .=$reviewsRow;
				$tableReviewsList = $pandoc->convert($reviewsData, "gfm", "html5");
				$tableReviewsList = $this->addTableStylesToContent($tableReviewsList, '90');
				$mpdf->WriteHTML('<div style="position: absolute; left:80; right: 0; top: 340; bottom: 0;font-family: frutiger;">
				' . $tableReviewsList . '
				</div>');
			}


			$mpdf->WriteHTML('<br>');
			$mpdf->WriteHTML('<br>');


			$risksList = $model->getProjectOpenRisks($project_id);
			if(count($risksList)>0){
			//#-6 Adding page layout
			$mpdf->AddPage(
				'', // L - landscape, P - portrait 
				'',
				'',
				'',
				'',
				20, // margin_left
				20, // margin right
				30, // margin top
				25, // margin bottom
				12, // margin header
				5
			);
			
				$mpdf->WriteHTML('<div style="position: absolute; left:0; right: 0; top: 100;text-align: center;font-family: frutiger; font-weight: bold; font-size: 16pt;">
				Risks List
				</div>');
				//$mpdf->WriteHTML('<br>');
				$risksData = "|# | Risk Type | Risk |\n| -----------|---------|----------|\n|";
				foreach($risksList as $key => $row){
					$risksRow[] = $key+1 ."| ".$row["risk_type"]."| ".$row["risk"]."|\n";
				}
				$risksRow = implode('',$risksRow);
				$risksData .=$risksRow;
				$tableRisksList = $pandoc->convert($risksData, "gfm", "html5");
				$tableRisksList = $this->addTableStylesToContent($tableRisksList, '90');
				$mpdf->WriteHTML('<div style="position: absolute; left:80; right: 0; top: 130; bottom: 0;font-family: frutiger;">
				' . $tableRisksList . '
				</div>');
			}

			$mpdf->WriteHTML('<br>');
			$mpdf->WriteHTML('<br>');

			// $gapsList = $model->getProjectGaps($project_id);
			// if(count($gapsList)>0){
			// 	$mpdf->WriteHTML('<div style="position: absolute; left:0; right: 0; top: 500;text-align: center;font-family: frutiger; font-weight: bold; font-size: 16pt;">
			// 	Gaps
			// 	</div>');
			// 	//$mpdf->WriteHTML('<br>');
			// 	$gapsData = "|# | Requirement Type | Requirement |\n| -----------|---------|----------|\n|";
			// 	foreach($gapsList as $key => $row){
			// 		$gapsRow[] = $key+1 ."| ".$row["type"]."| ".$row["requirement"]."|\n";
			// 	}
			// 	$gapsRow = implode('',$gapsRow);
			// 	$gapsData .=$gapsRow;
			// 	$tableGapsList = $pandoc->convert($gapsData, "gfm", "html5");
			// 	$tableGapsList = $this->addTableStylesToContent($tableGapsList, '90');
			// 	$mpdf->WriteHTML('<div style="position: absolute; left:80; right: 0; top: 530; bottom: 0;font-family: frutiger;">
			// 	' . $tableGapsList . '
			// 	</div>');
			// }
			
			// $mpdf->WriteHTML('<br>');
			// $mpdf->WriteHTML('<br>');

			$developersHrsList = $model->getProjectDeveloperWiseHrsSpent($project_id);
			if(count($developersHrsList)>0){
				//#-6 Adding page layout
			$mpdf->AddPage(
				'', // L - landscape, P - portrait 
				'',
				'',
				'',
				'',
				20, // margin_left
				20, // margin right
				30, // margin top
				25, // margin bottom
				12, // margin header
				5
			);

				$mpdf->WriteHTML('<div style="position: absolute; left:0; right: 0; top: 200;text-align: center;font-family: frutiger; font-weight: bold; font-size: 16pt;">
				Developerwise Hours Spent
				</div>');
				//$mpdf->WriteHTML('<br>');
				$developersData = "|# | Name | Hours Spent |\n| -----------|---------|----------|\n|";
				foreach($developersHrsList as $key => $row){
					$developersRow[] = $key+1 ."| ".$row["name"]."| ".$row["timeSpent"]."|\n";
				}
				$developersRow = implode('',$developersRow);
				$developersData .=$developersRow;
				$tableDevelopersList = $pandoc->convert($developersData, "gfm", "html5");
				$tableDevelopersList = $this->addTableStylesToContent($tableDevelopersList, '90');
				$mpdf->WriteHTML('<div style="position: absolute; left:80; right: 0; top: 230; bottom: 0;font-family: frutiger;">
				' . $tableDevelopersList . '
				</div>');
			}
			
			try{
				// Saving the document as OOXML file...
				$count++;
			}
			catch (Error $e) {
				echo "Error caught: " . $e->getMessage();
				return false;
			}			
			try{
				if($typeOfRequest == 2){
					$rootDirName = $_SERVER['DOCUMENT_ROOT'];
					$directoryName = "Project_Summary_".$projectData[0]['project-id'];		
					if (!is_dir($directoryName)) {
						mkdir($directoryName, 0777);
					}
					ob_clean();
					if($typeOfRequest == 1){
						$mpdf->Output($fileName, 'D');
					}else{
						$dir = "PreviewDocx";
						if (!is_dir($dir)) {
							mkdir($dir, 0777);
						}
						$mpdf->Output($dir.'/'.$fileName);
						$response = array('success' => "True", "fileName"=>$dir.'/'.$fileName, "projectId" => $projectData[0]['project-id']);
						echo json_encode( $response );	
						return false;
					}
				}
			}
			catch (Error $e) {
				echo "Error caught: " . $e->getMessage();
				return false;
			}
				
		}
	}

	function addTableStylesToContent($rawContent, $tablewidth)
	{
		$fontFamily = 'frutiger, sans-serif';
		$fontSize = '10pt';
		$replaceContent = str_replace("<table>", '<table style="overflow: wrap; font-family:' . $fontFamily . '; font-size: ' . $fontSize . '; width: '.$tablewidth.'%; table-layout: fixed; word-wrap: break-word; padding: 50pt; border: 1pt #000000 solid; border-collapse: collapse;" border="1">', $rawContent);
		$replaceContent = str_replace("<th>", "<th style='font-weight: bold; padding-left:7pt; background-color:#d9d9d9;word-wrap: break-word'>", $replaceContent);
		$replaceContent = str_replace("<td>", "<td style='padding-left:7pt; font-size:10pt; word-wrap: break-word'>", $replaceContent);
		$replaceContent = str_replace("<br/>", " <br/> ", $replaceContent);
		return $replaceContent;
	}

	private function returnParams(){
		$uri = $this->request->uri;
		$id = $uri->getSegment(3);
		$type = $uri->getSegment(4);
		return [$id, $type];
	}
}
