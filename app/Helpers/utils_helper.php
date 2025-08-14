<?php
    use App\Models\ProjectModel;
    use App\Models\ProductModel;
    use App\Models\TeamModel;
    use App\Models\QueueModel;
    use TP\Tools\Openfire;
    use App\Models\CourseModel;

    function setPrevUrl($name, $vars){
        // log_message('info', "$name");
        // log_message('info', implode(",",$vars));
        $session = session();
        if(isset($_SESSION['PREV_URL'])){
            $session->remove('PREV_URL');
        }

        $PREV_URL = [
            'name'  => $name,
            'vars'  => $vars
        ];

        $session->set('PREV_URL', $PREV_URL );
    }

    function getPrevUrl(){
        $session = session();
        $prevUrl = $session->get('PREV_URL');
        return $prevUrl;
    }

    function getActiveProjectId()
    {

        $projectModel = new ProjectModel();
        $activeProject = $projectModel->where("status", "Active")->orderBy('start-date', 'desc')->first();

        if ($activeProject != "") {
            return $activeProject['project-id'];
        } else {
            $activeProject = $projectModel->first();
            if ($activeProject != "") {
                return $activeProject['project-id'];
            } else {
                return null;
            }
        }
    }

    function getActiveProductId()
    {

        $productModel = new ProductModel();
        $activeProduct = $productModel->where("status", "Active")->first();

        if ($activeProduct != "") {
            return $activeProduct['product-id'];
        } else {
            $activeProduct = $productModel->first();
            if ($activeProduct != "") {
                return $activeProduct['product-id'];
            } else {
                return null;
            }
        }
    }

    function getActiveCourseId()
    {

        $courseModel = new CourseModel();
        $activeCourse = $courseModel->where("status", "Active")->orderBy('created_at', 'desc')->first();

        if ($activeCourse != "") {
            return $activeCourse['course_id'];
        } else {
            $activeCourse = $courseModel->first();
            if ($activeCourse != "") {
                return $activeCourse['course_id'];
            } else {
                return null;
            }
        }
    }

    function sendNotification($receiverId, $reviewId, $title, $referenceLink, $notificationMessage)
	{
		if (getenv('OF_ENABLED') == "true") {
			$openfire = new Openfire();
			$teamModel = new TeamModel();
			$userName = $teamModel->getUsername($receiverId);
			$openfireMessage = "$notificationMessage \nLink - " . $referenceLink;
			$openfire->sendNotification($userName, $openfireMessage);
		}

		if (getenv('EMAIL_ENABLED') == "true") {
            $teamModel = new TeamModel();
			$receiver = $teamModel->where('id', $receiverId)->findColumn('email');
			$to = $receiver[0];
			$cc = session()->get('email');
			$subject = "DocsGo: $title on $reviewId";

            $queueJson = [
                'to' => $to,
                'cc' => $cc,
                'subject' => $subject,
                'title' => $title,
                'message' => $notificationMessage,
                'url' => $referenceLink,
            ];

            $queueData = [
                'type' => 'email',
                'status' => 'SUBMITTED',
                'json' => json_encode($queueJson)
            ];

            $queueModel = new QueueModel();
            $queueModel->insert($queueData);

		}
	}

    function sendEmail($to, $cc, $subject, $message)
    {
		$email = \Config\Services::email();

        $from = getenv('email.SMTPUser');
		$email->setFrom($from, "DocsGo");
		$email->setTo($to);
		$email->setCC($cc);
		$email->setSubject($subject);
		$email->setMessage($message);

		if($email->send()){
			return true;
		}else{
			// $data = $email->printDebugger(['headers']);
			// print_r($data);
            return false;
		}
    }

    function getEmailHtml($emailTitle, $emailBody, $referenceLink, $referenceLinkText, $templateType = 2){
       
		$baseUrl = base_url();
        $iconBaseUrl = getenv('app.baseURL');
		$logo = $iconBaseUrl."/Docsgo-Logo.png";
		
        if($templateType == 1){
            $templatePath = './templates/email/email_1.html';
            $image_1 =  $baseUrl."/templates/images/template1_image_1.png";
            $image_2 =  $baseUrl."/templates/images/template1_image_2.png";
        }else{
            $templatePath = './templates/email/email_2.html';
            $image_1 =  $baseUrl."/templates/images/template2_image_1.png";
        }
       
        $doc = new \DOMDocument();

        libxml_use_internal_errors(true);
        $doc->loadHTMLFile($templatePath);
        libxml_use_internal_errors(false);

        $img = $doc->getElementById('logo');
		$img->setAttribute('src', $logo);

		$img = $doc->getElementById('image_1');
		$img->setAttribute('src', $image_1);

        if($templateType == 1){
	        $img = $doc->getElementById('image_2');
		    $img->setAttribute('src', $image_2);
        }
        
		$title = $doc->getElementById('title');
		$title->nodeValue = $emailTitle;

		$message_body = $doc->getElementById('message_body');
		$message_body->nodeValue = $emailBody;
		
		$link = $doc->getElementById('link');
		$link->setAttribute('href', $referenceLink);
		
		$link_text = $doc->getElementById('link_text');
		$link_text->nodeValue = $referenceLinkText;

		$html = $doc->saveHTML();
		
		return $html;
    }

    function curlGETRequest($url, $authentication_token = "", $inArray=true)
    {
        $curl = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        );

        if($authentication_token != ""){
            $baseEncodedToken = base64_encode($authentication_token);
            $curl_options[CURLOPT_MAXREDIRS] = 10;
            $curl_options[CURLOPT_HTTPHEADER] = array(
                "Authorization: Basic $baseEncodedToken"
            );
        }

        curl_setopt_array($curl, $curl_options);

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, $inArray);
    }

?>