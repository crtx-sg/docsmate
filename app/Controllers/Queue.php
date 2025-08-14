<?php

namespace App\Controllers;

use App\Models\QueueModel;

class Queue extends BaseController
{
    protected $SUBMITTED = "SUBMITTED";
    protected $PROCESSING = "PROCESSING";
    protected $SUCCESS = "SUCCESS";
    protected $FAILED = "FAILED";
    protected $queueModel;

    public function __construct()
    {
        $this->queueModel = new QueueModel();
    }
    public function emailQueue()
    {
        $queue = $this->getSubmittedJobs("email");
        $jobIds = array_column($queue, "id");

        if (count($jobIds)) {
            $this->updateQueueStatus($jobIds, $this->PROCESSING);

            helper('Helpers\utils');
            foreach ($queue as $job) {
                $jobId = $job["id"];
                $json = json_decode($job['json'], true);

                $html = getEmailHtml($json["title"], $json["message"], $json["url"], 'View Here', 2);

                $emailSent = sendEmail($json["to"], $json["cc"], $json["subject"], $html);

                if ($emailSent) {
                    $this->updateQueueStatus($jobId, $this->SUCCESS);
                } else {
                    $this->updateQueueStatus($jobId, $this->FAILED);
                }
            }
        }

        $this->cleanUp();
    }

    private function getSubmittedJobs($type)
    {
        return $this->queueModel
            ->where("type", $type)
            ->where("status", $this->SUBMITTED)
            ->orderBy('created_at', 'asc')
            ->find();
    }

    private function updateQueueStatus($id, $status)
    {
        $this->queueModel
            ->whereIn('id', $id)
            ->set(['status' => $status])
            ->update();
    }

    private function cleanUp()
    {
        $threshold_date =  date('Y-m-d H:i:s', strtotime('-7 days'));
        return $this->queueModel
            ->where("created_at <", $threshold_date)
            ->delete();
    }
}
