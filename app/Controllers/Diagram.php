<?php namespace App\Controllers;

use App\Models\DiagramModel;

class Diagram extends BaseController
{
    public function index(){
        $data = [];

		$data['pageTitle'] = 'Diagrams';
		$data['addBtn'] = true;
		$data['addUrl'] = '/diagrams/draw';

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Diagrams/list',$data);
		echo view('templates/footer');
    }


    public function getDiagrams(){

        $type = $this->request->getVar('type');
        $vars['type'] = $type;
        
        helper('Helpers\utils');
        setPrevUrl('diagramsList', $vars);
        
        $whereCondition = "";
        if($type != 'all'){
            $author_id = session()->get('id');
            $whereCondition = " WHERE diagrams.`author_id` = ".$author_id;
        }
        $diagramModel = new DiagramModel();
        $data = $diagramModel->getDiagrams($whereCondition);
        $response["success"] = "True";
        $response["diagrams"] = $data;
        
        echo json_encode($response);
    }

	public function draw()
    {
		$data = [];

		$data['pageTitle'] = 'Diagrams';
		$data['addBtn'] = false;
        $data['backUrl'] = '/diagramsList';
        
        $id = $this->request->getVar('id');
        if($id != ""){
            $diagramModel = new DiagramModel();
            $data['diagram'] = $diagramModel->find($id);
        }

		echo view('templates/header');
		echo view('templates/pageTitle', $data);
		echo view('Diagrams/draw',$data);
		
    }

    public function save()
    { 
        $id = $this->request->getVar('id');
        $name = $this->request->getVar('diagram_name');
        $markdown = $this->request->getVar('markdown');
        $svg_code = $this->request->getVar('svg_code');
        $currentTime = gmdate("Y-m-d H:i:s");

        $response["success"] = "True";
        $diagramModel = new DiagramModel();

        $diagram = [
            "diagram_name" => $name,
            "markdown" => $markdown,
            'updated_at' => $currentTime,
            'author_id' => session()->get('id')
        ];

        $updateImage = false;
        if($id != ""){
           $previousEntry = $diagramModel->find($id);
           if($previousEntry["markdown"] != $markdown){
                $updateImage = true;
                $this->deleteSVGimage($previousEntry["link"]);
            }
        }
        
        if($id == "" || $updateImage){
            $link = $this->saveSVGimage($name, $svg_code);
            if($link){
                $diagram["link"] = $link;
            }else{
                $response["success"] = "False";
            }
        }
        
        if($id != ""){
            $diagramModel->update($id, $diagram);
        }else{
            $id = $diagramModel->insert($diagram);
            $session = session();
			$session->setFlashdata('success', "Diagram Created successfully!");
        }
        
        $diagram["id"] = $id;
       
        $response["diagram"] = $diagram;
        
        echo json_encode($response);
    }

    private function saveSVGimage($name, $svg_code){
        $diagramsDir = "diagrams";
        if (!is_dir($diagramsDir)) {
            mkdir($diagramsDir, 0777);
        }

        $ts = time();
        $fileName = $diagramsDir."/".$ts."_".str_replace(' ', '_', $name).".svg";
        if(file_put_contents($fileName, $svg_code)) { 
            $link = "/".$fileName;
            return $link;
        }else{
            return false;
        }

    }

    private function deleteSVGimage($imagePath){
        $existingFile = ltrim($imagePath, '/'); 
                
        if(file_exists($existingFile)){
            unlink($existingFile);
        }
    }

    public function delete(){
        $id = $this->request->getVar('id');

        $diagramModel = new DiagramModel();

        $diagram = $diagramModel->find($id);
        $this->deleteSVGimage($diagram["link"]);

        $diagramModel->delete($id);

        $response = array('success' => "True");
        echo json_encode($response);
    }

}