<?php namespace App\Controllers;
use App\Models\SettingsModel;
use App\Models\DashboardModel;
use App\Models\TimeTrackerModel;
class Dashboard extends BaseController
{
    public function index()
    {
        $data = [];
        
        $dashboardModel = new DashboardModel();

        $data['reviewsData'] = json_encode($dashboardModel->getReviews());
        $data['documentsData'] = json_encode($dashboardModel->getDocuments());
        $data['unitTestsData'] = json_encode($dashboardModel->getUnitTests());
        $data['taskboardData'] = json_encode($dashboardModel->getTasks());
        $data['toDoData'] = json_encode($dashboardModel->getActionItems());

        echo view('templates/header', $data);
        echo view('dashboard', $data);
        echo view('templates/footer');
    }

    public function getStats()
    {
        $response = array("success" => "true");

        $settingsModel = new SettingsModel();
		$thirdParty = $settingsModel->getThirdPartyConfig();

        $sonarURL = $thirdParty["sonar"]["url"];
        $sonarKey = $thirdParty["sonar"]["key"];
        $response["sonar_stats_url"] = $thirdParty["sonar"]["url"];
        $response["sonar_stats"] = $this->getSonarStats($sonarURL, $sonarKey);

        $jenkinsURL = $thirdParty["jenkins"]["url"];
        $jenkinsKey = $thirdParty["jenkins"]["key"];
        $jenkinsJob = $thirdParty["jenkins"]["job"];
        $jenkinsUser = $thirdParty["jenkins"]["user"];
        $jenkinsFileMngrUrl = $thirdParty["jenkins"]["fileManagerUrl"];
        $dailyBuildLiveUrl = $thirdParty["generic"]["dailyBuildLiveUrl"];
        $response["jenkins_stats"] = $this->getJenkinsStats($jenkinsURL, $jenkinsKey, $jenkinsJob, $jenkinsUser, $jenkinsFileMngrUrl, $dailyBuildLiveUrl);
   
        return json_encode($response);
    }

    private function getSonarStats($sonar_ip, $authentication_token)
    {
        helper('Helpers\utils');
        $sonar_projects_url = $sonar_ip . "/api/components/search?qualifiers=TRK";
        $sonar_stats = curlGETRequest($sonar_projects_url,  "$authentication_token:");
        $sonar_projects = [];

        if ($sonar_stats != null) {
            if (count($sonar_stats)) {
                if (count($sonar_stats["components"])) {
                    $components = $sonar_stats["components"];
                    foreach ($components as $comp) {
                        array_push($sonar_projects, $comp["key"]);
                    }
                    $sonar_metric_keys = "&metricKeys=alert_status,bugs,vulnerabilities,code_smells";
                    $sonar_metrics_url = $sonar_ip . "/api/measures/search?projectKeys=" . join(",", $sonar_projects) . $sonar_metric_keys;
                    
                    $sonar_metrics = curlGETRequest($sonar_metrics_url,  "$authentication_token:");
                    if (count($sonar_metrics["measures"])) {
                        $measures = $sonar_metrics["measures"];
                        $stats = array();
                        $components = array();
                        foreach ($measures as $measure) {
                            $component_name = $measure["component"];
                            $metric = $measure["metric"];
                            $value = $measure["value"];

                            if (!in_array($component_name, $components)) {
                                $stats[$component_name] = array($metric => $value);
                                array_push($components, $component_name);
                            }

                            $stats[$component_name] += [$metric => $value];

                        }

                        return $stats;

                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }

    }

    private function getJenkinsStats($jenkins_ip,$jenkins_key, $jenkins_job_name, $jenkins_user, $fileManagerUrl, $dailyBuildUrl)
    {
        $jenkins_api = $jenkins_ip."/job/".$jenkins_job_name."/lastBuild/api/json";
        helper('Helpers\utils');
        $jenkins_stats = curlGETRequest($jenkins_api, "$jenkins_user:$jenkins_key");

        if ($jenkins_stats != null) {
            $buildInfo = array(
                "id" => $jenkins_stats["id"],
                "result" => $jenkins_stats["result"],
                "timestamp" => $jenkins_stats["timestamp"],
                "duration" => $jenkins_stats["duration"],
                "url" => $jenkins_stats["url"],
                "changeLog" => array(),
                "dailyBuildLiveUrl" => $dailyBuildUrl,
                "fileManagerUrl" => $fileManagerUrl
            );

            $changeSets = $jenkins_stats["changeSets"];
            foreach ($changeSets as $changes) {
                if(isset($changes["items"])){
                    foreach($changes["items"] as $item){
                        $changeLog = array(
                            "authorEmail" => $item["authorEmail"],
                            "msg" => $item["msg"],
                            "timestamp" => $item["timestamp"],
                        );
                        array_push($buildInfo["changeLog"], $changeLog);
                    }
                }
            }

            return $buildInfo;
        } else {
            return null;
        }
    }




}
