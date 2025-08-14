<?php

namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\UnitTestModel;

class UnitTests extends BaseController
{
    public function index()
    {
        $data = [];
        $data['pageTitle'] = 'Unit Tests';
        $data['addBtn'] = true;
        $data['addUrl'] = "/unit-tests/add";
        $data['backUrl'] = '/unit-tests';

        $selectedProject = null;
        $selectedType = "my";
        helper('Helpers\utils');
		$prev_url = getPrevUrl();
        if(isset($prev_url)){
        if ($prev_url["name"] == "unitTestsList") {
            $vars = $prev_url["vars"];
            $selectedProject = $vars['project_id'];
            $selectedType = $vars['type'];
            $data['selectedProject'] = $selectedProject;
        }
    }

        $projectModel = new ProjectModel();
        $data['projects'] = $projectModel->getProjects(); //Projects Dropdown

        if ($selectedProject == null) {
            helper('Helpers\utils');
            $selectedProject = getActiveProjectId(); //Default project
            $data['selectedProject'] = $selectedProject;
        }

        $data['selectedType'] = $selectedType;

        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('UnitTests/list', $data);
        echo view('templates/footer');
    }

    public function list($project_id, $type)
    {
        $vars['project_id'] = $project_id;
        $vars['type'] = $type;
        helper('Helpers\utils');
        setPrevUrl('unitTestsList', $vars);

        $user_id = session()->get('id');

        $unitTestModel = new UnitTestModel();
        $condition["project_id"] = $project_id;

        if ($type == "my") {
            $condition["author_id"] = $user_id;
        }

        return json_encode($unitTestModel->getAll($condition));
    }


    public function add($project_id, $template_id = null)
    {
        $data = [];
        $data['pageTitle'] = 'Unit Tests';
        $data['backUrl'] = '/unit-tests';
        $data['addBtn'] = false;
        $data['customDD'] = $this->getCustomDD($project_id);
        $data['templateId'] = $template_id;
        $data['testCaseTemplate'] = file_get_contents(APPPATH . "Templates/UnitTests/testcases.json");

        $unitTest = new \App\Entities\UnitTest();

        $unitTest->project_id = $project_id;
        $unitTest->author_id = session()->get('id');

        if ($template_id != null) {
            $unitTestModel = new UnitTestModel();
            $existingUT = $unitTestModel->find($template_id);
            $unitTest->json = $existingUT->json;
        } else {
            $unitTest->json = file_get_contents(APPPATH . "Templates/UnitTests/main.json");
        }

        $data['unitTest'] = $unitTest;

        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('UnitTests/form', $data);
    }

    private function getCustomDD($project_id)
    {
        $unitTestModel = new UnitTestModel();

        $myUTDocs =  $unitTestModel->where('author_id', session()->get('id'))
            ->orderBy('updated_at', 'desc')
            ->findAll(5);

        $myUTDocsCount = count($myUTDocs);

        $otherUTDocs =  $unitTestModel->where('author_id <>', session()->get('id'))
            ->where('project_id', $project_id)
            ->orderBy('updated_at', 'desc')
            ->findAll(10);

        $otherUTDocsCount = count($otherUTDocs);

        $options = '';
        if ($myUTDocsCount == 0 && $otherUTDocsCount == 0) {
            $options = '<option disabled>No UT doc found</option>';
        } else {
            $options = $this->returnOptions('My UT docs', $myUTDocs);
            $options .= $this->returnOptions('Other user docs', $otherUTDocs);
        }

        return array('style' => 'table-primary', 'title' => 'Fill from existing', 'options' => $options);
    }

    private function returnOptions($groupLabel, $data)
    {
        $options = '';
        $dataCount = count($data);
        if ($dataCount > 0) {
            $option = '';
            foreach ($data as $utDoc) {
                $option .= "<option value='$utDoc->id'>$utDoc->name</option>";
            }
            $label = "$groupLabel ($dataCount)";
            $options .= "<optgroup label='" . $label . "'>
                            $option
                        </optgroup>";
        }
        return $options;
    }

    public function edit($id)
    {
        $data = [];
        $data['pageTitle'] = 'Unit Tests';
        $data['backUrl'] = '/unit-tests';
        $data['addBtn'] = false;

        $unitTestModel = new UnitTestModel();
        $unitTest = $unitTestModel->find($id);

        $data["unitTest"] = $unitTest;
        $data['testCaseTemplate'] = file_get_contents(APPPATH . "Templates/UnitTests/testcases.json");

        echo view('templates/header');
        echo view('templates/pageTitle', $data);
        echo view('UnitTests/form', $data);
    }

    public function save()
    {
        $id = $this->request->getVar('id');

        $data = $this->request->getPost();
        $data["json"] = json_encode($data["json"]);

        $unitTestModel = new UnitTestModel();

        if ($id == "") {
            $id = $unitTestModel->insert($data);
            $session = session();
            $session->setFlashdata('success', "Success: Unit Test created successfully!");
        } else {
            $unitTestModel->update($id, $data);
        }

        $response = array();
        $response["success"] = true;
        $response["id"] = $id;

        echo json_encode($response);
    }

    public function delete($id)
    {
        $unitTestModel = new UnitTestModel();
        $unitTestModel->delete($id);

        $response = array('success' => true);

        echo json_encode($response);
    }
}
