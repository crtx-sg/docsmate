<?php namespace App\Controllers;

use App\Models\SettingsModel;
use CodeIgniter\I18n\Time;
use TP\Tools\Pandoc;
use TP\Tools\PandocExtra;
use Mpdf;
use Mpdf\Config;
use DOMDocument;
use App\Models\ProjectModel;
use App\Models\DocumentModel;
use PhpOffice\PhpWord\Shared\ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

function myCustomErrorHandler(int $errNo, string $errMsg, string $file, int $line) {
	echo $errMsg;
	alert($errMsg);
	exit;
	return false;
}
set_error_handler('App\Controllers\myCustomErrorHandler');

class GenerateDocuments extends BaseController
{
	
	public function index(){
		$this->downloadDocuments();
	}

	public function downloadDocuments()  {
		$pandoc = new Pandoc();
		$doc = new DOMDocument();
		ini_set("display_errors", "1");
		error_reporting(E_ALL);

		$params = $this->returnParams();
		$typeOfRequest = $params[0];
		$type = $this->getActionType($params[0]);
		$project_document_id = $params[1];

		$model = new DocumentModel();
		$documentData = $model->getDocumentsData($type, $project_document_id); 
		//Findout number of documents count
		if(isset($documentData) && count($documentData) == 0) {
			echo "no data";
			return false;
		}

		$docData = $this->getDocumentProperties();
		$documentTitle = $docData['title']; $documentIcon = $docData["image"]; $documentFooterMsg = $docData["footer"]; $documentPageNos = $docData["pageNos"];
		$documentFooterVersion = "";
		if (strpos($docData["footer"], ';') !== false) {
			$footerData = (explode(";", $docData["footer"]));
				$documentFooterMsg = $footerData[0];
				$documentFooterVersion = $footerData[1];
		}
		$documentObject = array_keys($documentData);
		$count = 0;
		foreach ($documentObject as $id) {
			$jsonMain = $documentData;
			$fileName = preg_replace('/[^A-Za-z0-9\-]/', '_', $jsonMain[$id]['file-name']);
			$fileName = $fileName . ".pdf";	

			$jsonObj = json_decode($jsonMain[$id]['json-object'], true);
			$documentType = array_keys($jsonObj);
			$json = $jsonObj[$documentType[0]];

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
			$mpdf->SetTitle($documentTitle);
			$mpdf->SetAnchor2Bookmark(1);
			$mpdf->h2toc = array('H1' => 0, 'H2' => 1, 'H3' => 1);
			$stylesheet = $this->getMPDFInlineStyles();
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
			if($documentPageNos == "Enable"){
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
			}else{
				$mpdf->SetHTMLFooter('
			<div style="font-family: frutiger; font-size: 11pt;width:100%;">
					<div style="width:50%;float:left;text-align:left;">
							<span >' . $documentFooterMsg . '</span>
					</div>
					<div style="width:50%;float:left;text-align:right;">
							<span >'. $documentFooterVersion .'</span>
					</div>
			</div>');
			}
			

			$mpdf->WriteHTML('&nbsp;');

			//#-2: Adding image at first page header line
			$mpdf->WriteHTML('<div style="width: 100%; text-align: center; margin-top: 150px;">
								<img src="' . $documentIconImage . '" 
								style="width: 20%; margin: 0" />
							</div>');

			$DocID = 'Doc ID: ' . $json['cp-line4'];

			//#-3 Adding Document title
			$mpdf->WriteHTML('<div style="position: absolute; left:0; right: 0; top: 360;text-align: center;font-family: frutiger; font-weight: bold; font-size: 16pt;">
			' . $json['cp-line3'] . '
			</div>');
			
			//#-4 Adding Document ID
			$mpdf->WriteHTML('<div style="position: absolute; left:0; right: 0; top: 390;text-align: center;font-family: frutiger; font-weight: bold; font-size: 16pt;">
			' . $DocID . '
			</div>');

			$mpdf->WriteHTML('<br>');
			//$mpdf->shrink_tables_to_fit=0;
			//#-4.1 Adding Note on First Page
			$mpdf->WriteHTML('<div style="position: absolute; left:0; right: 0; top: 410;text-align: center;font-family: frutiger; font-weight: bold; font-size: 12pt;">
			' . $json['cp-approval-matrix'] . '
			</div>');

			//#-5: Adding change history section
			$mpdf->WriteHTML('<div style="position: absolute; left:80; right: 0; top: 430; bottom: 0;font-family: frutiger;font-weight: bold;">Change History</div>');
			$tableContent = $pandoc->convert($json['cp-change-history'], "gfm", "html5");
			$tableContent = $this->addTableStylesToContent($tableContent, '90');
			$mpdf->WriteHTML('<div style="position: absolute; left:80; right: 0; top: 450; bottom: 0;font-family: frutiger;">
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

			//#-7 Adding TOC
			$mainWord = $json['cp-line3']; $searchWord = "MeetingMinutes,ReleaseReview";  
			if(!(strpos($mainWord, $searchWord) !== false)){
			$mpdf->TOCpagebreakByArray(array(
				'tocfont' => 'frutiger',
				'tocfontsize' => '10',
				'tocindent' => '0',
				'TOCusePaging' => true,
				'TOCuseLinking' => true,
				'toc_orientation' => 'P',
				'toc_mgl' => '',
				'toc_mgr' => '',
				'toc_mgt' => '',
				'toc_mgb' => '',
				'toc_mgh' => '',
				'toc_mgf' => '',
				'toc_ohname' => '',
				'toc_ehname' => '',
				'toc_ofname' => '',
				'toc_efname' => '',
				'toc_ohvalue' => 0,
				'toc_ehvalue' => 0,
				'toc_ofvalue' => 0,
				'toc_efvalue' => 0,
				'toc_preHTML' => '<h4 style="text-align:center;font-family:frutiger;padding-bottom:0.3in">TABLE OF CONTENTS</h4>',
				'toc_postHTML' => '',
				'toc_bookmarkText' => '',
				'resetpagenum' => '',
				'pagenumstyle' => '',
				'suppress' => '',
				'orientation' => '',
				'mgl' => '',
				'mgr' => '',
				'mgt' => '',
				'mgb' => '',
				'mgh' => '',
				'mgf' => '',
				'ohname' => '',
				'ehname' => '',
				'ofname' => '',
				'efname' => '',
				'ohvalue' => 0,
				'ehvalue' => 0,
				'ofvalue' => 0,
				'efvalue' => 0,
				'toc_id' => 0,
				'pagesel' => '',
				'toc_pagesel' => '',
				'sheetsize' => '',
				'toc_sheetsize' => '',
			));
		}

			//#-8 Adding logo left corner section every page
			$mpdf->SetHTMLHeader('<div style="text-align:left;padding-bottom:30mm;"><img src="'.$documentIconImage.'" style="width: 20mm; height: 17mm; margin: 0;"/></div>','O',true);

			//#-9 Adding sections
			try{
				for ($i = 0; $i < count($json['sections']); $i++) {
					$index = (string)$i+1;
					$sectionCount = $index.'.0';
					//Adjusting the space between index number and heading content, some pixels are getting differ compare to single digits and double digits, so added space to fix the issue
					if($sectionCount > 9)
							$mpdf->WriteHTML('<div style="font-family: frutiger; font-weight: bold; font-size: 12pt;"><h1 style="font-size: 12pt;">'.$sectionCount.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($json['sections'][$i]['title']).'</h1></div>');
						else
							$mpdf->WriteHTML('<div style="font-family: frutiger; font-weight: bold; font-size: 12pt;"><h1 style="font-size: 12pt;">'.$sectionCount.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($json['sections'][$i]['title']).'</h1></div>');						

							$contentSection = '<b></b>';
					$org = $json['sections'][$i]['content'];
					$contentSection = $pandoc->convert($org, "gfm", "html5");
					//Adding the sub,sub-sub indexing values
					$contentSection = $this->handleSubIndexNumbers($contentSection, $index);
					$contentSection = $this->handleSunHeadingAlignments($contentSection);
					$contentSection = $this->formatUnorderedLists($contentSection);
					if (strpos($contentSection, '<table>') !== false) {
						if(strtolower($json['sections'][$i]['title']) == 'traceability matrix'){
							$contentSection = str_replace('<br/>', '; ', $contentSection);
						}
						// add a newline before table which comes immediately after the section header
						if( substr($contentSection,0,7) == '<table>'){
							$contentSection = str_replace('<table>', '<br/><table>', $contentSection);
						}						
						$contentSection = str_replace('</table>', '</table><br/>', $contentSection);
						$contentSection = str_replace('</table><br/><strong>', '</table><strong>', $contentSection);
						$tableContentFormatted = $this->addTableStylesToContent($contentSection, '100');
						if(strtolower($json['sections'][$i]['title']) == 'risk assessment'){
							$tableContentFormatted = str_replace('<strong>', '<br/><strong>', $tableContentFormatted);
							$tableContentFormatted = str_replace('</strong>', '</strong><br/>', $tableContentFormatted);
							#Dirty fix to resolve nested table issue in the pdf report
							$tableContentFormatted = str_replace('>Hazard-analysis', 'rowspan="2">Hazard-analysis', $tableContentFormatted);
							$tableContentFormatted = str_replace('<br/><strong>OTS Software Hazard Mitigation', "</tr><tr><td style='border-top: none;'><br/><strong>OTS Software Hazard Mitigation", $tableContentFormatted);
							$tableContentFormatted = str_replace("'><br/><strong>OTS Software Hazard Analysis", ";border-bottom: none;'><br/><strong>OTS Software Hazard Analysis", $tableContentFormatted);
							$tableContentFormatted = str_replace("border-collapse: collapse;", "border-collapse: collapse; page-break-inside: avoid;", $tableContentFormatted);
							$tableContentFormatted = $this->addWordWrap($tableContentFormatted);
							// libxml_use_internal_errors(true); //To restrict the DocType errors from the content 
							// $doc->loadHTML(mb_convert_encoding($tableContentFormatted, 'HTML-ENTITIES', 'UTF-8'));
							// $html= $this->checkLargeTables($doc, 'risk');
							$mpdf->WriteHTML($tableContentFormatted);
						}else{
							if(strtolower($json['sections'][$i]['title']) == 'traceability matrix'){
								libxml_use_internal_errors(true); //To restrict the DocType errors from the content 
								$doc->loadHTML(mb_convert_encoding($tableContentFormatted, 'HTML-ENTITIES', 'UTF-8'));
								$html= $this->checkLargeTables($doc, 'trace');

								$mpdf->WriteHTML($html);
							}else{
							$mpdf->WriteHTML('<div style="font-family: frutiger; font-weight: normal; font-size: 11pt;margin-left: 0.55in;">
							'.$tableContentFormatted.'
							</div>');
							}

							// libxml_use_internal_errors(true); //To restrict the DocType errors from the content 
							// $doc->loadHTML(mb_convert_encoding($tableContentFormatted, 'HTML-ENTITIES', 'UTF-8'));
						    // $html= $this->checkLargeTables($doc, 'trace');

							// $mpdf->WriteHTML($html);
						}
					} else {
						$mpdf->WriteHTML('<div style="font-family: frutiger; font-weight: normal; font-size: 11pt;margin-left: 0.55in;">
						'.$contentSection.'
						</div>');
					}
					$mpdf->WriteHTML('<br>');
				}
			}
			catch (Error $e) {
				echo "Error caught: " . $e->getMessage();
				return false;
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
				if ($typeOfRequest == 2) {
					$directoryName = "Project_Documents_".$documentData[0]['project-id'];		
					if (!is_dir($directoryName)) {
						mkdir($directoryName, 0777);
					}
					$mpdf->Output($directoryName . '/' . $fileName);
					if (count($documentObject) == $count) {
						$zip_file = $directoryName.'.zip';
						$rootPath = realpath($directoryName);
						// / Initiate a new instance of ZipArchive  
						$zip = new ZipArchive();  
						$res = $zip->open($zip_file, ZipArchive::CREATE);
						if ($zip->open($zip_file, ZipArchive::CREATE)) {
							$files = new RecursiveIteratorIterator(
								new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY
							);
							foreach ($files as $name => $file) {
								if (!$file->isDir()) {
									$filePath = $file->getRealPath();
									$relativePath = substr($filePath, strlen($rootPath) + 1);
									$zip->addFile($filePath, $relativePath);
									$filesToDelete[] = $filePath;
								}
							}
							$zip->close();
							header('Content-Description: File Transfer');
							header('Content-Type: '.mime_content_type($zip_file).'');
							header("Content-Disposition: attachment; filename=\"".basename($zip_file)."\";");
							header('X-Sendfile: '.$zip_file);
							header('Content-Transfer-Encoding: binary');
							header("Cache-Control: no-cache");
							header('Content-Length: ' . filesize($zip_file));
							//removing the document files
							/*
							if (is_dir($directoryName)) {
								foreach ($filesToDelete as $file) {
									unlink($file);
								}
								rmdir($directoryName);
							}
							*/
							readfile($zip_file);
						}else{
							echo "unable to create zip file";
						}
					}
				}else{
					$rootDirName = $_SERVER['DOCUMENT_ROOT'];
					$directoryName = "Project_Documents_".$documentData[0]['project-id'];		
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
						$response = array('success' => "True", "fileName"=>$dir.'/'.$fileName, "projectId" => $documentData[0]['project-id']);
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

	public function handleSubIndexNumbers($data, $index){
		preg_match_all( '|<h[^>]+>(.*)</h[^>]+>|iU', $data, $matches);
		//if any h2, h3 matches found
		if(count($matches[0]) >= 1){
			$h3TagPosition = 0;	
			$h3TagIncrement = 0;
			$h3TagLoopIncrement = 0;
			$h3ParentIndex = 0;
			$currentIndex = 0;
			foreach ($matches[0] as $x => $val) {
				$key = $x;
				//findout h3 is exists or not....
				$h3Exist = strpos($val, '</h3>');
				$orgVal = $val;
				if($h3Exist){
					if($h3TagPosition == 0){
						$h3TagLoopIncrement = 0;
						$x = $currentIndex;
						if($currentIndex == 0){
							$x = $x;
						}
						$h3ParentIndex = $x;
						$h3TagPosition = $h3ParentIndex;
					}
					$incKey = $index.".".$h3ParentIndex.".".($h3TagLoopIncrement+1)." ";
					$h3TagIncrement = $h3TagIncrement+1;
					$h3TagLoopIncrement = $h3TagLoopIncrement +1;
					$val = str_replace(">", ">".$incKey, $val);
					//update the index numbering string into original string
					$data = $this->updateSubIndexNumbers($orgVal, $incKey, $data, 'h3');
				}else{
					$orgVal = $val;
					$h3TagPosition = 0;
					$h3TagLoopIncrement = 0;
					$currentIndex = ($x+1-$h3TagIncrement); 
					$incKey = $index.".".($x+1-$h3TagIncrement)." ";
					$data = $this->updateSubIndexNumbers($orgVal, $incKey, $data, 'h2');
				}
			}

		}
		return $data;
	}

	public function updateSubIndexNumbers($orgVal, $incKey, $data, $type) {
		$orgValString = preg_replace("/\s+/",' ',$orgVal);  
		preg_match_all( '|<'.$type.'(.*)>(.*)</'.$type.'>|iU', $orgValString, $orgValMatch);
		if(!empty($orgValMatch)) {
			$changeHeader = $orgValMatch[2][0];
			//Adjusting the space between index number and heading content, some pixels are getting differ compare to single digits and double digits, so added space to fix the issue
			$indexCount = strlen($incKey)-1;
			if($incKey < 10){
				if($indexCount == 3){
					$incKey = $incKey.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}elseif($indexCount == 4){
					$incKey = $incKey.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}elseif($indexCount == 5){
					$incKey = $incKey.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';						
				}elseif($indexCount == 6){
					$incKey = $incKey.'&nbsp;&nbsp;&nbsp;';
				}else{
					$incKey = $incKey.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
			}else{
				if($indexCount == 4){
					$incKey = $incKey.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}elseif($indexCount == 5){
					$incKey = $incKey.'&nbsp;&nbsp;&nbsp;';						
				}elseif($indexCount == 6){
					$incKey = $incKey.'&nbsp;&nbsp;';
				}elseif($indexCount == 7){
					$incKey = $incKey.'&nbsp;';	
				}else{
					$incKey = $incKey.'&nbsp;&nbsp;&nbsp;&nbsp;';
				}
			}
			$newValue = '">'.$incKey.$changeHeader;
			if (!(ctype_upper($changeHeader))) { 
				$newValue = '">'.$incKey.ucwords($changeHeader);
			}
			//may be same heading name will be present in content also, so to avoid the number appending at that content we checked with tag(>,</) characters
			$data  = str_replace('">'.$changeHeader.'</', $newValue.'</', $data);
		}
		return $data;
	}

	public function handleSunHeadingAlignments($contentSection){
		//Adjusting the sub heading content display position, applying the font to code block
		$contentSection = str_replace("<h2 ", '<h2 style="position: absolute; margin-left: -40pt; font-weight: normal;" ', $contentSection);
		$contentSection = str_replace("<h3 ", '<h3 style="position: absolute; margin-left: -40pt; font-weight: normal;" ', $contentSection);
		$contentSection = str_replace("<code>", '<code style="font-family: frutiger;">', $contentSection);
		return $contentSection;
	}

	public function addWordWrap($tableContentFormatted) {
		$tableContentFormatted = str_replace(',"', ', "', $tableContentFormatted);
		$tableContentFormatted = str_replace('https://', ' https://', $tableContentFormatted);
		$tableContentFormatted = str_replace('},{', '}, {', $tableContentFormatted);
		$tableContentFormatted = str_replace('":"', '": "', $tableContentFormatted);
		return $tableContentFormatted;
	}

	public function checkLargeTables(&$doc, $from)
	{
		//new code to split table large cells
		foreach ($doc->getElementsByTagName('table') as $table) {
			// iterate over each row in the table
			$trs = $table->getElementsByTagName('tr');
			$cloneArr = [];
			foreach ($trs as $tr) {
				$cloned = 0;
				foreach ($tr->getElementsByTagName('td') as $td) { // get the columns in this row
					if (strlen($td->textContent) > 3000) {
						$longValue = $td->nodeValue;
						if($from == "trace")
							$breaktill = strpos($td->nodeValue, ';', 1400);
						else
							$breaktill = strpos($td->nodeValue, '.', 1400);
						if ($cloned == 0) {
							$cloneNode = $tr->cloneNode(TRUE);
							$cloned = 1;
							$cloneArr[] = ["node" => $cloneNode, 'row' => $tr, 'breaktill' => $breaktill];
						}
						$td->textContent = substr($longValue, 0, $breaktill) . '. (cont.)';
						$td->setAttribute("style:", "white-space: nowrap; word-break: break-all; font-size: 12pt !important");
						//$td->setAttribute("style:", "padding-left:7pt; word-break: break-word; font-size: 10pt !important");
						$td->setAttribute("width", "84%");
					}else{
						if($from == "trace")
							$td->setAttribute("width", "15%");
					}
				}
			}
			//here insert new nodes
			foreach ($cloneArr as $cloneData) {
				$this->insertNewNodes($cloneData, $table, $from);    //this will be recursive function to split row multiple times if needed
			}
		}
		return @$doc->saveHTML();
	}
	
	public function insertNewNodes(&$cloneData, &$table, $from, $start = 800)
	{
		//processing cloneNodes
		$cloned = 0;
		if($from == "risk")
		   $delimiter = '.';
		else
			$delimiter = ';';
		foreach ($cloneData['node']->getElementsByTagName('td') as $td) {
			$longValue = $td->textContent;
			if (strlen($longValue) > $start) {
				$breaktill = strpos($longValue, $delimiter, $start); //starting point after first fullstop
				if (strlen($longValue) > ($breaktill + 800)) {
					$endPoint = $breaktill + 800;
					$end = strpos($longValue, $delimiter, $endPoint) - $breaktill; //end point till last sentence
				} else {
					$end = 800;
				}
				if (strlen($longValue) > $end + $breaktill && $cloned == 0) {
					$cloned = 1;
					$newNode = [];
					$newNode['node'] = $cloneData['node']->cloneNode(TRUE);
					$newNode['row'] = $cloneData['node'];
				}
				$td->textContent = '(cont\'d)' . substr($longValue, $breaktill + 1, $end);
			} else {
				$td->textContent = '';
			}
		}
		try {
			$cloneData['row']->parentNode->insertBefore($cloneData['node'], $cloneData['row']->nextSibling);
		} catch (\Exception $e) {
			$table->appendChild($cloneData['node']);
		}
		if ($cloned == 1) {
			$this->insertNewNodes($newNode, $table, $from, $start + 800);
		}
	}

	public function getMPDFInlineStyles() {
		$stylesheet = '
			body{
				font-family: frutiger !important;
				font-size: 11pt  !important;
			}
			table{
				border-spacing: 0 10pt !important; layout: fixed !important; font-family:frutiger !important; font-size: 10pt !important; table-layout: fixed !important; word-wrap: break-word !important; padding: 50px !important; border: 1px #000000 solid !important; border-collapse: collapse !important;
			}
			th{
				font-weight: bold !important; padding-left:7pt !important;background-color:#d9d9d9 !important; font-size: 10pt !important; text-align:left !important; font-family: frutiger !important;
			}
			td{
				padding-left:7pt !important; font-size: 10pt !important; font-family: frutiger !important;
			}
			table td {
				word-wrap:break-word;
				white-space: normal;
			}
			h1{
				font-size: 12pt !important; font-family: frutiger !important;
			}
			h2{
				font-size: 11pt !important; font-family: frutiger !important;
			}
			h3{
				font-size: 10pt !important; font-family: frutiger !important;
			}
			div.mpdf_toc {
				font-family: frutiger !important;
				font-size: 11pt;
			}
			a.mpdf_toc_a  {
				text-decoration: none;
				color: black;
			}
			
			/* Whole line level 0 */
			div.mpdf_toc_level_0 {
				line-height: 2.0;
				margin-left: 0;
				padding-right: 2em;
			}
			
			/* padding-right should match e.g <dottab outdent="2em" /> 0 is default */
			/* Title level 0 - may be inside <a> */
			span.mpdf_toc_t_level_0 {
				font-weight: bold;
			}
			
			/* Page no. level 0 - may be inside <a> */
			span.mpdf_toc_p_level_0 {}
			
			/* Whole line level 1 */
			div.mpdf_toc_level_1 {
				margin-left: 0;
				text-indent: -2em;
				padding-right: 2em;
			}
			
			/* padding-right should match <dottab outdent="2em" /> 2em is default */
			/* Title level 1 */
			span.mpdf_toc_t_level_1 {
				font-style: normal;
				font-weight: normal;
			}
			
			/* Page no. level 1 - may be inside <a> */
			span.mpdf_toc_p_level_1  {}
			
			/* Whole line level 2 */
			div.mpdf_toc_level_2 {
				margin-left: 4em;
				text-indent: -2em;
				padding-right: 2em;
			}';
		return $stylesheet;
	}

	public function startPDFDocxConvertion() {
		try {
			$params = $this->returnParams();
			$projectId = $params[0];
			$fileName = $params[1];
			$paths = new \Config\Paths();
			$projectDocsRootDir = $paths->publicDirectory;
			$writableDir = $paths->writableDirectory;
			$directoryName = "Project_Word_Documents_".$projectId;
			$outputFilePath = $projectDocsRootDir . "/" . $directoryName . "/" . str_replace('.pdf', '.docx', $fileName);
			#create folder for saving the docx files		
			if (!is_dir($directoryName)) {
				mkdir($directoryName, 0777);
			} else {
				// remove old docx file if it exists
				if(file_exists($outputFilePath)){
					exec("rm " . $outputFilePath);
				}
			}

			$logFileName = "log-" . gmdate("Y-m-d") . ".log";
			$command ="cd $projectDocsRootDir/pdf-to-docx-converter; node export-pdf-to-docx.js " . $projectDocsRootDir . "/Project_Documents_" . $projectId . "/" . $fileName
			. " " . $outputFilePath . " >>" . " $writableDir/logs/" . $logFileName . " 2>&1; cd $projectDocsRootDir";

			file_put_contents($writableDir."/logs/" . $logFileName, "\r\nExporting pdf to docx: ". $fileName . "\r\n", FILE_APPEND);
			
			// file_put_contents($writableDir."/logs/" . $logFileName, "\r\nCommand: ". $command . "\r\n", FILE_APPEND);

			exec($command);
			
			$response = array('success' => "True", "status"=>"Converted pdf file to docx successfully", "fileName" => $fileName, "fileDownloadUrl" => $directoryName . "/" . str_replace('.pdf', '.docx', $fileName));
			echo json_encode( $response );
		} catch (Exception $e) {
			// echo "Error on converting pdf to docx file " . $e->getMessage();
			$response = array('success' => "False", "status"=>"failed to convert pdf to docx file", "fileName" => $fileName);
			echo json_encode( $response );
		}

		}

	public function updateDownloadUrl() {
		$id = $this->request->getVar('id');
		$projectId = $this->request->getVar('project-id');
		$path = $this->request->getVar('path');
		$fileName = $this->request->getVar('name');
		$isDBUpdate = $this->request->getVar('isDBUpdate');
		$lastItem = $this->request->getVar('lastItem');
		$fileNames = $this->request->getVar('fileNames');

		// if($isDBUpdate == "true"){
		// 	$model = new DocumentModel();
		// 	$documentData = $model->updateDownloadUrl($projectId, $id, $path); 
		// }
		$directoryName = "Project_Word_Documents_".$projectId;		

		//Goto zip folder download //Write zip code:
		if($lastItem == $fileName){
			// wait 30s for completion of all the reports conversion
			$now = time(); 
        	while ($now + 30 > time()){};
			$zip = new ZipArchive();
			$downloadFilename = $directoryName.".zip";
			if ($zip->open($downloadFilename, ZipArchive::CREATE)!==TRUE) {
				exit("cannot open <$downloadFilename>\n");
			}
			$dir = $directoryName.'/';
			$isAllFilesExists = true;
			$documentModel = new DocumentModel();
			if($isDBUpdate == "false"){
				foreach($fileNames as $outputFile){
					if(!file_exists($outputFile["download-path"])){
						$isAllFilesExists = false;
						$documentData = $documentModel->updateDownloadUrl($outputFile["project-id"], $outputFile["id"], NULL); 
					} else {
						$documentData = $documentModel->updateDownloadUrl($outputFile["project-id"], $outputFile["id"], $outputFile["download-path"]); 
					}
				}
			} else {
				foreach($fileNames as $file => $id) {
					$file = str_replace(".pdf", ".docx", $file);
					if(!file_exists($directoryName . "/" . $file)){
						$isAllFilesExists = false;
						$documentData = $documentModel->updateDownloadUrl($projectId, $id, NULL); 
					} else {
						$documentData = $documentModel->updateDownloadUrl($projectId, $id, $directoryName . "/" . $file); 
					}
				}
			}
			if($isAllFilesExists){
				if (is_dir($dir)){
					if ($dh = opendir($dir)){
						while (($file = readdir($dh)) !== false){
							if (is_file($dir.$file)) {
								if($file != '' && $file != '.' && $file != '..'){
									$zip->addFile($dir.$file);
								}
							}
						}
						closedir($dh);
					}
				}
				$zip->close();
				$response = array('success' => "True", "status"=>'Download-zip-file', "downloadFile"=>$downloadFilename);
				echo json_encode( $response );
			} else {
				$response = array('success' => "False", "status"=>'Some docx files are missing in download directory');
				echo json_encode( $response );
			}			
		}else{
			$response = array('success' => "True", "status"=>'single-Download-zip', "name"=>$fileName, 'lastName' => $lastItem, "path"=>$path, "proId"=>$projectId, "id"=>$id);
			echo json_encode( $response );	
		}
	}

	public function getWordDocumentFileList() {
		//First check all approved files download path is available or not
		$projectId = $this->returnProjectID();
		$model = new ProjectModel();
		$pathList = $model->select('download-path')->where('project-id', $projectId)->first();
		$pathList = json_decode($pathList['download-path'], true);

		if($pathList == "" || $pathList == null || $pathList == 'null'){
			//JSON not available, Goto fresh download
			//check if any documents are in approved state for the requested project
			$docModel = new DocumentModel();
			$dataCount = $docModel->getApprovedFilesCount($projectId);
			if($dataCount == 0 || $dataCount == null){
				$response = array('success' => "False", "description"=>'No downloads available');
				echo json_encode( $response );
			}else{
				$response = array('success' => "False", "description"=>'Download path is not available');
				echo json_encode( $response );	
			}
		}else{
			//JSON is available, check all document's update-date is lowerthan the zipfile timestamp
			$current_date =  gmdate("Y-m-d H:i:s");
			$json_time = $pathList['timeStamp'];
			$data = $model->getDownloadedProjectStatus($projectId, $json_time);
			$zip_file = "Project_Documents_".$projectId.".zip";
			if($data[0]['count'] > 0){
				//JSON aviable, but its old one, so delete and goto fresh download
				$this->updateGenerateDownloadPath($projectId);
				$response = array('success' => "False", "description"=>"Download is deprecated");
				echo json_encode( $response );	
			}else{
				$model = new DocumentModel();
				//Will get either [filenames] or [file-name, downloadPath] array with tag fileNames=true/false 
				$documentData = $model->getDownloadPaths($projectId); 
				$response = array('success' => "True", "data"=>$documentData);
				echo json_encode( $response );	
			}
		}
	}

	public function getActionType($id){
		switch($id) {
			case 1:
				$type = 'document';
				break;
			case 2:
				$type = 'project';
				break;
			case 3:
				$type = 'document';
				break;
		}
		return $type;
	}

	public function getDocumentProperties(){
		//Fetching the doc-properties(Global seetings) from Model
		$settingsModel = new SettingsModel();
		$documentProperties = $settingsModel->getSettings("documentProperties");
		$documentProperties = json_decode($documentProperties[0]['options'], true);
		$docData = [ "title" => "",  "image" => "",  "footer" => "", "pageNos" => ""];
		foreach($documentProperties as $key => $val){
			if($val['key'] == "docTitle"){
				$docData["title"] = $val["value"];
			}
			if($val["key"] == "docIcon"){
				$docData["image"] = $val["value"];
			}
			if($val["key"] == "docConfidential"){
				$docData["footer"] = $val["value"];
			}
			if($val["key"] == "docPageNums"){
				$docData["pageNos"] = $val["value"];
			}
		}
		return $docData;
	}

	function sectionNumber($sectionStr) {
		return (int) filter_var($sectionStr, FILTER_SANITIZE_NUMBER_INT);
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

	public function checkGenerateDocuments(){
		$projectId = $this->returnProjectID();
		$model = new ProjectModel();
		$pathList = $model->select('download-path')->where('project-id', $projectId)->first();
		$pathList = json_decode($pathList['download-path'], true);

		if($pathList == "" || $pathList == null || $pathList == 'null'){
			//JSON not available, Goto fresh download
			//check if any documents are in approved state for the requested project
			$docModel = new DocumentModel();
			$dataCount = $docModel->getApprovedFilesCount($projectId);
			if($dataCount == 0 || $dataCount == null){
				$response = array('success' => "False", "description"=>'No downloads available');
				echo json_encode( $response );
			}else{
				$response = array('success' => "False", "description"=>'Download path is not available');
				echo json_encode( $response );	
			}
		}else{
			//JSON is available, check all document's update-date is lowerthan the zipfile timestamp
			$current_date =  gmdate("Y-m-d H:i:s");
			$json_time = $pathList['timeStamp'];
			$data = $model->getDownloadedProjectStatus($projectId, $json_time);
			$zip_file = "Project_Documents_".$projectId.".zip";
			if($data[0]['count'] > 0){
				//JSON aviable, but its old one, so delete and goto fresh download
				if(is_file($zip_file))
					unlink($zip_file);
				$res = $model->updateGenerateDocumentPath($projectId, NULL);
				$this->updateGenerateDownloadPath($projectId);
				$response = array('success' => "False", "description"=>"Download is deprecated");
				echo json_encode( $response );	
			}else{
				//JSON avilable, no need to download new, use existing one
				header('Content-Description: File Transfer');
				header('Content-Type: '.mime_content_type($zip_file).'');
				header("Content-Disposition: attachment; filename=\"".basename($zip_file)."\";");
				header('X-Sendfile: '.$zip_file);
				header('Content-Transfer-Encoding: binary');
				header("Cache-Control: no-cache");
				header('Content-Length: ' . filesize($zip_file));
				readfile($zip_file);
			}
		}
	}

	public function updateGenerateDocumentPath() {
		$projectId = $this->returnProjectID();
		$current_date =  gmdate("Y-m-d H:i:s");
		$downloadZipFileName = "Project_Documents_".$projectId.".zip";
		$json_data = array("timeStamp"=>$current_date, "filePath"=>$downloadZipFileName);

		$model = new ProjectModel();
		$res = $model->updateGenerateDocumentPath($projectId, json_encode($json_data));
		$this->updateGenerateDownloadPath($projectId);
		$response = array('success' => "True", "description"=>"Link Updated");
		echo json_encode( $response );	
	}

	public function updateGenerateDownloadPath($projectId){
		$model = new DocumentModel();
		$model->updateGenerateDownloadPath($projectId, NULL);
	}

	public function returnProjectID(){
		$uri = $this->request->uri;
		$id = $uri->getSegment(3);
		return $id;
	}
	
	public function formatUnorderedLists($data){
		try {
			if(strpos($data, '<ul>') !== false){
				$listsDataArray = explode("<ul>",$data);
				$updatedDataArray[0] = $listsDataArray[0];
				for($i=1; $i < count($listsDataArray); $i++ ){
					if(strpos($listsDataArray[$i],'</ul>') !== false && strpos($listsDataArray[$i],'</ul></li>') === false){
						# add a bullet dot symbol for regular unordered lists
						$tmp = $listsDataArray[$i];
						$tmpArray = explode('</ul>', $tmp);
						$tmpArray[0] = str_replace('<li>', '<div style="padding-left: 20pt;"><div style="width:15px;float: left;"><span style="font-size: 11pt;"><strong>&#8226;</strong></span></div><div style="margin-left: 15px;">', $tmpArray[0]);
						$tmpArray[0] = str_replace('</li>', '</div></div><div style="clear:both;"></div>', $tmpArray[0]);
						$updatedDataArray[$i] = implode('',$tmpArray); 
					} else if(strpos($listsDataArray[$i], '</ul>') === false) {
						# add symbols for unorder nested lists
						if(strpos($listsDataArray[$i+1], '</ul>') !== false && strpos($listsDataArray[$i+1],'</ul></li>') !== false){
							$tmp = $listsDataArray[$i];
							# add bullet circle symbol for second level list in nested lists
							$tmp = str_replace('<li>', '<div style="padding-left: 30pt;"><div style="width:15px;float: left;"><span style="font-size: 12pt;"><strong>&#9702;</strong></span></div><div style="margin-left: 15px;">', $tmp);
							$tmp = str_replace('</li>', '</div></div><div style="clear:both;"></div>', $tmp);
							$updatedDataArray[$i] = $tmp;
						} else if(strpos($listsDataArray[$i+1], '</ul>') === false && strpos($listsDataArray[$i+2], '</ul></li>') !== false ){
							$tmp = $listsDataArray[$i];
							# add bullet dot symbol for main list in nested lists
							$tmp = str_replace('<li>', '<div style="padding-left: 20pt;"><div style="width:15px;float: left;"><span style="font-size: 11pt;"><strong>&#8226;</strong></span></div><div style="margin-left: 15px;">', $tmp);
							$tmp = str_replace('</li>', '</div></div><div style="clear:both;"></div>', $tmp);
							$updatedDataArray[$i] = $tmp; 
						} else {
							$updatedDataArray[$i] = '<ul>' . $listsDataArray[$i]; 
						}
					} else if(strpos($listsDataArray[$i],'</ul></li>') !== false) {
						if(strpos($listsDataArray[$i-1],'</ul>') === false) {
							$tmp = $listsDataArray[$i];
							$tmpArray = explode('</ul></li>', $tmp);
							# add bullet squre symbol for third level list in the unordered nested lists
							$tmpArray[0] = str_replace('<li>', '<div style="padding-left: 35pt;"><div style="width:15px;float: left;"><span style="font-size: 7pt;"><strong>&#9632;</strong></span></div><div style="margin-left: 15px;">', $tmpArray[0]);
							$tmpArray[0] = str_replace('</li>', '</div></div><div style="clear:both;"></div>', $tmpArray[0]);
							$updatedDataArray[$i] = implode('',$tmpArray);
							$updatedDataArray[$i] = str_replace('</ul>','',$updatedDataArray[$i]);
						} else {
							$tmp = $listsDataArray[$i];
							$tmpArray = explode('</ul></li>', $tmp);
							# add bullet circle symbol for uroder list in the ordered nested lists
							$tmpArray[0] = str_replace('<li>', '<div style="padding-left: 20pt;"><div style="width:15px;float: left;"><span style="font-size: 12pt;"><strong>&#9702;</strong></span></div><div style="margin-left: 15px;">', $tmpArray[0]);
							$tmpArray[0] = str_replace('</li>', '</div></div><div style="clear:both;"></div>', $tmpArray[0]);
							$updatedDataArray[$i] = implode('',$tmpArray);
							$updatedDataArray[$i] = str_replace('</ul>','',$updatedDataArray[$i]);
						}	
					} 
					else {
						$updatedDataArray[$i] = '<ul>' . $listsDataArray[$i];
					}
				}
			
				$updatedListsData = implode("", $updatedDataArray);
				return $updatedListsData;
	
			} else {
				return $data;
			}
		} catch (Error $e) {
			echo "Error on formatting un order lists caught: " . $e->getMessage();
			return $data;
		}
		
	}

	public function downloadWordDocument(){
		try {
			$params = $this->returnParams();
			$projectId = $params[0];
			$fileName = $params[1];
			$projectDocsRootDir = "/var/www/html/docsgo/public";
			$directoryName = "Project_Word_Documents_".$projectId;
			$outputFilePath = $projectDocsRootDir . "/" . $directoryName . "/" . str_replace('.pdf', '.docx', $fileName);			
			$logFileName = "log-" . gmdate("Y-m-d") . ".log";
			#create folder for saving the docx files		
			if (!is_dir("Project_Word_Documents_" . $projectId)) {
				mkdir("Project_Word_Documents_" . $projectId, 0777);
			} else {
				// remove old docx file if it exists
				if(file_exists($outputFilePath)){
					exec("rm " . $outputFilePath);
				}
			}
			
			file_put_contents("/var/www/html/docsgo/writable/logs/" . $logFileName, "\r\nWord document download - Exporting pdf to docx: ". $fileName . "\r\n", FILE_APPEND);
			exec("cd ". $projectDocsRootDir ."/pdf-to-docx-converter; node export-pdf-to-docx.js " . $projectDocsRootDir . "/PreviewDocx/" . $fileName
			. " " . $outputFilePath . " >>" . " /var/www/html/docsgo/writable/logs/" . $logFileName . " 2>&1; cd /var/www/html/docsgo/public");
			
			// wait 15s for completion of reports conversion
			$now = time(); 
        	while ($now + 15 > time()){};
			$response = array('success' => "True", "status"=>"Converted pdf file to docx successfully", "fileName" => $fileName, "fileDownloadUrl" => $directoryName . "/" . str_replace('.pdf', '.docx', $fileName));
			echo json_encode( $response );
		} catch (Exception $e) {
			// echo "Error on converting pdf to docx file " . $e->getMessage();
			$response = array('success' => "False", "status"=>"failed to convert pdf to docx file", "fileName" => $fileName);
			echo json_encode( $response );
		}
	}
	
}
