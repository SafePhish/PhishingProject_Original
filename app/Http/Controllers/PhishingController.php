<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use mysqli;
use Illuminate\Support\Facades\Mail as Mail;
use Illuminate\Support\Facades\Input as Input;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Auth;

class PhishingController extends Controller {

	public function index()	{
		return view('displays.displayHome');
	}

	public function webbugEmailRedirect($id) {
		$urlid = substr($id,0,15);
		$username = DB::table('users')->where('USR_UniqueURLId',$urlid)->first();
		if(is_null($username)) {
			return view('errors.404');
		}
		header('Content-Type: image/png');
		$this->webbugExecutionEmail($urlid);
		//return Response::make(readfile("/img/FF4D00-0.png",200));
		exit;
	}

	public function webbugExecutionWebsite($urlid) {
		$db = $this->openDatabaseDefault();
		if(!empty($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
			$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
			$reqpath = $_SERVER['REQUEST_URI'];
			$projectID = substr($reqpath,16);
			$projectID = ltrim($projectID,'0');
			$projectID = intval(strval($projectID));
			$sql = "SELECT PRJ_ProjectName FROM gaig_users.projects WHERE PRJ_ProjectId=$projectID;";
			$projectNameResult = $db->query($sql);
			$project = $projectNameResult->fetch_assoc();
			$projectName = $project['PRJ_ProjectName'];
			$browseragent = $_SERVER['HTTP_USER_AGENT'];
			$date = date("Y-m-d");
			$time = date("H:i:s");
			$sql = "SELECT USR_Username FROM gaig_users.users WHERE USR_UniqueURLId='$urlid';";
			$userNameResult = $db->query($sql);
			$user = $userNameResult->fetch_assoc();
			$username = $user['USR_Username'];
			$sql = "INSERT INTO gaig_users.website_tracking (WBS_Id,WBS_Ip,WBS_Host,
		WBS_BrowserAgent,WBS_ReqPath,WBS_Username,WBS_ProjectName,WBS_AccessDate,WBS_AccessTime) VALUES 
		(null,'$ip','$host','$browseragent','$reqpath','$username','$projectName','$date',
		'$time');";
			$result = $db->query($sql);
		}
		$db->close();
	}

	public function webbugExecutionEmail($urlid) {
		$db = $this->openDatabaseDefault();
		if(!empty($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
			$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
			$reqpath = $_SERVER['REQUEST_URI'];
			$projectID = substr($reqpath,29);
			$projectID = ltrim($projectID,'0');
			$projectID = rtrim($projectID,'.');
			$projectID = intval(strval($projectID));
			$sql = "SELECT PRJ_ProjectName FROM gaig_users.projects WHERE PRJ_ProjectId=$projectID;";
			$projectNameResult = $db->query($sql);
			$project = $projectNameResult->fetch_assoc();
			$projectName = $project['PRJ_ProjectName'];
			$sql = "SELECT USR_Username FROM gaig_users.users WHERE USR_UniqueURLId='$urlid';";
			$userNameResult = $db->query($sql);
			$user = $userNameResult->fetch_assoc();
			$username = $user['USR_Username'];
			$date = date("Y-m-d");
			$time = date("H:i:s");
			$sql = "INSERT INTO gaig_users.email_tracking (EML_Id,EML_Ip,EML_Host,EML_Username,EML_ProjectName,
		EML_AccessDate,EML_AccessTime) VALUES (null,'$ip','$host','$username','$projectName','$date','$time');";
			$result = $db->query($sql);
		}
		$db->close();
	}

	public function openDatabaseDefault() {
		$db = new mysqli(getenv('DB_HOST'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), getenv('DB_DATABASE'));
		date_default_timezone_set('America/New_York');

		if (mysqli_connect_errno()) {
			echo 'Error: Could not connect to the database.';
			echo "Errno: " . $db->connect_errno . "\n";
			echo "Error: " . $db->connect_error . "\n";
			exit;
		}

		return $db;
	}

	public function create() {
		return redirect()->to('/breachReset');
	}

	public function breachReset() {
		return view("passwordReset.resetPage1");
	}

	public function breachVerify() {
		return view("passwordReset.resetPage2");
	}

	public function store()
	{
		return redirect()->to('/breachReset/verifyUser');
	}

	public function webbugWebsiteRedirect($id) {
		$urlid = substr($id,0,15);
		$username = DB::table('users')->where('USR_UniqueURLId',$urlid)->first();
		if(is_null($username)) {
			return view('errors.404');
		}
		$this->webbugExecutionWebsite($urlid);
		//return $this->breachReset();
	}

	public function edit($id)
	{
		//
	}

	public function update($id)
	{
		//
	}

	public function destroy($id)
	{
		//
	}

	private function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
	{
		$str = '';
		$max = mb_strlen($keyspace, '8bit') - 1;
		for ($i = 0; $i < $length; ++$i) {
			$str .= $keyspace[random_int(0, $max)];
		}
		return $str;
	}

	public function sendEmail(Request $request) {
		$fromEmail = $request['fromEmail'];
		$fromPass = $request['fromPass'];
		$host = $request['hostName'];
		$port = $request['port'];
		putenv("MAIL_HOST=$host");
		putenv("MAIL_PORT=$port");
		putenv("MAIL_USERNAME=$fromEmail");
		putenv("MAIL_PASSWORD=$fromPass");

		$subject = $request['subject'];
		$projectName = $request['projectName'];
		$projectId = substr($projectName,strpos($projectName,'_'));
		$projectName = substr($projectName,0,strpos($projectName,'_')-1);
		$companyName = $request['companyName'];
		$emailTemplate = 'emails.' . $request['emailTemplate'];
		$emailTemplateType = substr($request['emailTemplate'],0,3);
		$emailTemplateTarget = substr($request['emailTemplate'],3,1);

		//$fromEmail = 'gaigemailtest@gmail.com';
		//$subject = 'URGENT: Corporate Account Breach - Read Immediately';
		$db = $this->openDatabaseDefault();
		$sql = "SELECT * FROM gaig_users.users;";
		if(!$result = $db->query($sql)) {
			$this->databaseErrorLogging($sql,$db);
			exit;
		}
		if($result->num_rows === 0) {
			echo "Sorry. There are no users in this database.";
			exit;
		}
		while($user = $result->fetch_assoc()) {
			if($emailTemplateType != substr($user['USR_ProjectMostRecent'],-5,3) || $emailTemplateTarget != substr($user['USR_ProjectMostRecent'],-2,1)) {
				$urlID = null;
				if(!is_null($user['USR_UniqueURLId'])) {
					$urlID = $user['USR_UniqueURLId'];
				}
				while(is_null($urlID)) {
					$urlID = $this->random_str(15);
					$sql = "SELECT * FROM gaig_users.users WHERE USR_UniqueURLId=$urlID;";
					$tempResult = $db->query($sql);
					//if($tempResult->num_rows === 0) {
					//	break;
					//}
					//$urlID = null;
				}
				$username = $user['USR_Username'];
				$toEmail = $user['USR_Email'];
				$lastName = $user['USR_LastName'];
				$firstName = $user['USR_FirstName'];
				//$projectName = 'bscG6_9_16';
				/*
                 * NAMING FORMAT:
                 * 1. bsc/adv : First three letters defines whether its basic or advanced scam
                 * 2. G/T : This letter defines whether it's a generic scam or a targeted scam
                 * 3. Project Start Date
                 */
				$headers = array('from' => $fromEmail, 'to' => $toEmail, 'subject' => $subject, 'lastName' => $lastName,
					'urlID' => $urlID, 'username' => $username, 'projectName' => $projectName, 'companyName' => $companyName,
					'firstName' => $firstName, 'projectId' => $projectId);
				Mail::send(['html' => $emailTemplate],$headers, function($m) use ($fromEmail, $toEmail, $subject) {
					$m->from($fromEmail);
					$m->to($toEmail)->subject($subject);
				});
				if(!is_null($user['USR_UniqueURLId'])) {
					$project_mostRecent = $user['USR_ProjectMostRecent'];
					$project_previous = $user['USR_ProjectPrevious'];
					$sql = "UPDATE gaig_users.users SET USR_ProjectMostRecent='$projectName-$emailTemplate', USR_ProjectPrevious='$project_mostRecent', USR_ProjectLast='$project_previous' WHERE USR_Username='$username';";
					$updateResult = $db->query($sql);
				}
				else {
					$sql = "UPDATE gaig_users.users SET USR_UniqueURLId='$urlID', USR_ProjectMostRecent='$projectName-$emailTemplate' WHERE USR_Username='$username';";
					$updateResult = $db->query($sql);
				}
				echo "Mail sent to " . $toEmail;
				echo "Unique URL ID generated: " . $urlID . "<br />";
			} else {
				echo "Mail not sent to " . $user['USR_Username'] . "@gaig.com";
				echo "User's last project was " . $user['USR_ProjectMostRecent'] . "<br />";
			}
		}
		$db->close();
	}

	public function generateEmailForm() {
		$userId = \Session::get('authUserId');
		if($userId) {
			$db = $this->openDatabaseDefault();
			$sql = "SELECT DFT_MailServer,DFT_MailPort,DFT_Username,DFT_CompanyName FROM gaig_users.default_emailsettings
				WHERE DFT_UserId='$userId';";
			if(!$results = $db->query($sql)) {
				$this->databaseErrorLogging($sql,$db);
				exit;
			}
			if($results->num_rows === 0) {
				$dft_host = '';
				$dft_port = '';
				$dft_company = '';
				$dft_user = '';
			} else {
				$result = $results->fetch_assoc();
				$dft_host = $result['DFT_MailServer'];
				$dft_port = $result['DFT_MailPort'];
				$dft_user = $result['DFT_Username'];
				$dft_company = $result['DFT_CompanyName'];
			}
			$sql = "SELECT PRJ_ProjectId, PRJ_ProjectName, PRJ_ProjectStatus FROM gaig_users.projects;";
			if(!$projects = $db->query($sql)) {
				$this->databaseErrorLogging($sql,$db);
				exit;
			}
			if($projects->num_rows === 0) {
				echo "Sorry. There are no users in this database.";
				exit;
			}
			$project = $projects->fetch_all();
			//$data = array();
			$data = array();
			$projectSize = sizeof($project);
			for($i = 0; $i < $projectSize; $i++) {
				$data[$i] = array('PRJ_ProjectId'=>$project[$i][0],'PRJ_ProjectName'=>$project[$i][1],'PRJ_ProjectStatus'=>$project[$i][2]);
			}
			$files = [];
			$fileNames = [];
			$filesInFolder = \File::files('../resources/views/emails');
			foreach($filesInFolder as $path) {
				$files[] = pathinfo($path);
			}
			$templateSize = sizeof($files);
			for($i = 0; $i < $templateSize; $i++) {
				$fileNames[$i] = $files[$i]['filename'];
				$fileNames[$i] = substr($fileNames[$i],0,-6);
			}
			$varToPass = array('projectSize'=>$projectSize,'data'=>$data,'templateSize'=>$templateSize,'fileNames'=>$fileNames,
				'dft_host'=>$dft_host,'dft_port'=>$dft_port,'dft_user'=>$dft_user,'dft_company'=>$dft_company);
			$db->close();
			return view('forms.emailRequirements')->with($varToPass);
		} else {
			//not authenticated redirect
			\Session::put('loginRedirect',$_SERVER['REQUEST_URI']);
			return view('auth.loginTest');
		}
	}

	public function viewAllProjects() {
		$userId = \Session::get('authUserId');
		if($userId) {
			$db = $this->openDatabaseDefault();
			$sql = "SELECT PRJ_ProjectId, PRJ_ProjectName, PRJ_ProjectStatus FROM gaig_users.projects;";
			if(!$projects = $db->query($sql)) {
				$this->databaseErrorLogging($sql,$db);
				exit;
			}
			if($projects->num_rows === 0) {
				echo "Sorry. There are no users in this database.";
				exit;
			}
			$project = $projects->fetch_all();
			$data = array();
			$projectSize = sizeof($project);
			for($i = 0; $i < $projectSize; $i++) {
				$data[$i] = array('PRJ_ProjectId'=>$project[$i][0],'PRJ_ProjectName'=>$project[$i][1],'PRJ_ProjectStatus'=>$project[$i][2]);
			}
			$varToPass = array('projectSize'=>$projectSize,'data'=>$data);
			$db->close();
			return view('displays.showAllProjects')->with($varToPass);
		} else {
			\Session::put('loginRedirect',$_SERVER['REQUEST_URI']);
			return view('auth.loginTest');
		}
	}

	public function viewAllTemplates() {
		$userId = \Session::get('authUserId');
		if($userId) {
			$files = [];
			$fileNames = [];
			$filePrefaces = [];
			$fileTypes = [];
			$filesInFolder = \File::files('../resources/views/emails');
			foreach($filesInFolder as $path) {
				$files[] = pathinfo($path);
			}
			$templateSize = sizeof($files);
			for($i = 0; $i < $templateSize; $i++) {
				$fileNames[$i] = $files[$i]['filename'];
				$fileNames[$i] = substr($fileNames[$i],0,-6);
				$filePrefaces[$i] = substr($fileNames[$i],0,3);
				$fileTypes[$i] = substr($fileNames[$i],3,1);
				if($fileTypes[$i] == 'T') {
					$fileTypes[$i] = 'tar';
				} else if($fileTypes[$i] == 'G') {
					$fileTypes[$i] = 'gen';
				} else {
					$fileTypes[$i] = 'edu';
				}
			}
			$varToPass = array('templateSize'=>$templateSize,'fileNames'=>$fileNames,'filePrefaces'=>$filePrefaces,'fileTypes'=>$fileTypes);
			return view('displays.showAllTemplates')->with($varToPass);
		} else {
			\Session::put('loginRedirect',$_SERVER['REQUEST_URI']);
			return view('auth.loginTest');
		}
	}
	
	public function createNewProject(Request $request) {
		$projectName = $request->input('projectNameText');
		$projectAssignee = $request->input('projectAssigneeText');
		$date = date("Y-m-d");
		$db = $this->openDatabaseDefault();
		$sql = "INSERT INTO gaig_users.projects (PRJ_ProjectId,PRJ_ProjectName,PRJ_ProjectAssignee,PRJ_ProjectStart,
			PRJ_ProjectLastActive,PRJ_ProjectStatus,PRJ_ProjectTotalUsers,PRJ_EmailViews,PRJ_WebsiteViews,
			PRJ_ProjectTotalReports) VALUES (null,'$projectName','$projectAssignee','$date','$date','Inactive',0,0,0,0);";
		if(!$projects = $db->query($sql)) {
			$this->databaseErrorLogging($sql,$db);
			exit;
		}
		$db->close();
	}

	public function createNewTemplate(Request $request) {
		$path = '../resources/views/emails/';
		$templateName = $request->input('templateName');
		$path = $path . $templateName . '.blade.php';
		$templateContent = $request->input('templateContent');
		\File::put($path,$templateContent);
		\File::delete('../resources/views/emails/.blade.php');
	}

	public function htmlReturner($id) {
		$path = '../resources/views/emails/' . $id . '.blade.php';
		$contents = '';
		try {
			$contents = \File::get($path);
		}
		catch (FileNotFoundException $fnfe) {
			$contents = "Preview Unavailable";
		}
		return $contents;
	}

	public function updateDefaultEmailSettings(Request $request) {
		$username = $request['usernameText'];
		$company = $request['companyText'];
		$host = $request['mailServerText'];
		$port = $request['mailPortText'];
		$userId = \Session::get('authUserId');
		if($userId) {
			$db = $this->openDatabaseDefault();
			$checkExists = "SELECT DFT_UserId FROM gaig_users.default_emailsettings WHERE DFT_UserId='$userId';";
			if(!$checkExistsResult = $db->query($checkExists)) {
				$this->databaseErrorLogging($checkExists,$db);
				exit;
			}
			if($checkExistsResult->num_rows === 0) {
				$insert = "INSERT INTO gaig_users.default_emailsettings (DFT_UserId, DFT_MailServer, DFT_MailPort,
						DFT_Username, DFT_CompanyName) VALUES ('$userId','$host','$port','$username',
						'$company');";
				$insertResult = $db->query($insert);
				exit;
			} else {
				$update = "UPDATE gaig_users.default_emailsettings SET DFT_MailServer='$host', DFT_MailPort='$port',
						DFT_Username='$username', DFT_CompanyName='$company';";
				$updateResult = $db->query($update);
			}
			$db->close();
		} else {
			return view('auth.loginTest');
		}
	}

	public function generateDefaultEmailSettingsForm() {
		$userId = \Session::get('authUserId');
		if($userId) {
			$db = $this->openDatabaseDefault();
			$sql = "SELECT DFT_MailServer,DFT_MailPort,DFT_Username,DFT_CompanyName FROM gaig_users.default_emailsettings
				WHERE DFT_UserId='$userId';";
			if(!$results = $db->query($sql)) {
				$this->databaseErrorLogging($sql,$db);
				exit;
			}
			if($results->num_rows === 0) {
				$dft_host = '';
				$dft_port = '';
				$dft_company = '';
				$dft_user = '';
			} else {
				$result = $results->fetch_assoc();
				$dft_host = $result['DFT_MailServer'];
				$dft_port = $result['DFT_MailPort'];
				$dft_user = $result['DFT_Username'];
				$dft_company = $result['DFT_CompanyName'];
			}
			$varToPass = array('dft_host'=>$dft_host,'dft_port'=>$dft_port,'dft_user'=>$dft_user,'dft_company'=>$dft_company);
			$db->close();
			return view('forms.defaultEmailSettings')->with($varToPass);
		} else {
			//not authenticated redirect
			\Session::put('loginRedirect',$_SERVER['REQUEST_URI']);
			return redirect()->to('/auth/login');
		}
	}

	public function postLogin(Request $request) {
		$username = $request['usernameText'];
		$password = $request['passwordText'];
		$db = $this->openDatabaseDefault();
		$selectHash = "SELECT USR_Password,USR_UserId FROM gaig_users.users WHERE USR_Username='$username';";
		if(!$hashResults = $db->query($selectHash)) {
			$this->databaseErrorLogging($selectHash,$db);
			exit;
		}
		if($hashResults->num_rows === 0) {
			$varToPass = array('errors'=>array("We failed to find the username provided. Check your spelling and try 
				again. If this problem continues, contact your manager."));
			return view('auth.loginTest')->with($varToPass);
		}
		$hashResult = $hashResults->fetch_assoc();
		$db->close();
		if(password_verify($password,$hashResult['USR_Password'])) {
			\Session::put('authUser',$username);
			\Session::put('authUserId',$hashResult['USR_UserId']);
		} else {
			$varToPass = array('errors'=>array('The password provided does not match our records.'));
			return view('auth.loginTest')->with($varToPass);
		}
		$redirectPage = \Session::get('loginRedirect');
		if($redirectPage) {
			return redirect()->to($redirectPage);
		} else {
			return view('errors.500');
		}
	}

	public function postRegister(Request $request) {
		$username = $request['usernameText'];
		$password = $request['passwordText'];
		$firstName = $request['firstNameText'];
		$lastName = $request['lastNameText'];
		$password = password_hash($password,PASSWORD_DEFAULT);
		$db = $this->openDatabaseDefault();
		$insertUser = "INSERT INTO gaig_users.users (USR_UserId,USR_Username,USR_FirstName,USR_LastName,
				USR_UniqueURLId,USR_Password,USR_ProjectMostRecent,USR_ProjectPrevious,USR_ProjectLast) VALUES
				(null,'$username','$firstName','$lastName',null,'$password',null,null,null);";
		$insertResult = $db->query($insertUser);
		$selectUserId = "SELECT USR_UserId FROM gaig_users.users WHERE USR_Username='$username';";
		$selectResults = $db->query($selectUserId);
		$selectResult = $selectResults->fetch_assoc();
		$db->close();
		\Session::put('authUser',$username);
		\Session::put('authUserId',$selectResult['USR_UserId']);
	}

	public function logout() {
		\Session::forget('authUser');
		\Session::forget('authUserId');
		\Session::forget('loginRedirect');
		return redirect()->to('http://localhost:8888');
	}

	public function postWebsiteJson() {
		$userId = \Session::get('authUserId');
		if($userId) {
			$websiteData = array();
			$websiteSelect = "SELECT WBS_Ip,WBS_Host,WBS_ReqPath,WBS_Username,WBS_ProjectName,WBS_AccessDate,
						WBS_AccessTime FROM gaig_users.website_tracking;";
			$db = $this->openDatabaseDefault();
			if(!$websiteResults = $db->query($websiteSelect)) {
				$this->databaseErrorLogging($websiteSelect,$db);
				exit;
			}
			if($websiteResults->num_rows === 0) {
				//echo "Sorry. There are no users in this database.";
				//exit;
			}
			$websiteResult = $websiteResults->fetch_all();
			for($i = 0; $i < sizeof($websiteResult); $i++) {
				$websiteData[$i] = array('WBS_Ip'=>$websiteResult[$i][0],'WBS_Host'=>$websiteResult[$i][1],
					'WBS_ReqPath'=>$websiteResult[$i][2],'WBS_Username'=>$websiteResult[$i][3],
					'WBS_ProjectName'=>$websiteResult[$i][4],'WBS_AccessDate'=>$websiteResult[$i][5],
					'WBS_AccessTime'=>$websiteResult[$i][6]);
			}
			$db->close();
			return $websiteData;
		}
	}

	public function postEmailJson() {
		$userId = \Session::get('authUserId');
		if($userId) {
			$emailData = array();
			$emailSelect = "SELECT EML_Ip,EML_Host,EML_Username,EML_ProjectName,EML_AccessDate,
				EML_AccessTime FROM gaig_users.email_tracking;";
			$db = $this->openDatabaseDefault();
			if(!$emailResults = $db->query($emailSelect)) {
				$this->databaseErrorLogging($emailSelect,$db);
				exit;
			}
			if($emailResults->num_rows === 0) {
				//echo "Sorry. There are no users in this database.";
				//exit;
			}
			$emailResult = $emailResults->fetch_all();
			for($i = 0; $i < sizeof($emailResult); $i++) {
				$emailData[$i] = array('EML_Ip'=>$emailResult[$i][0],'EML_Host'=>$emailResult[$i][1],
					'EML_Username'=>$emailResult[$i][2],'EML_ProjectName'=>$emailResult[$i][3],
					'EML_AccessDate'=>$emailResult[$i][4],'EML_AccessTime'=>$emailResult[$i][5]);
			}
			$db->close();
			return $emailData;
		}
	}

	public function postReportsJson() {
		$userId = \Session::get('authUserId');
		if($userId) {
			$reportData = array();
			$reportsSelect = "SELECT RPT_EmailSubject,RPT_UserEmail,RPT_OriginalFrom,RPT_ReportDate FROM gaig_users.reports;";
			$db = $this->openDatabaseDefault();
			if(!$reportsResults = $db->query($reportsSelect)) {
				$this->databaseErrorLogging($reportsSelect,$db);
				exit;
			}
			if($reportsResults->num_rows === 0) {
				//echo "Sorry. There are no users in this database.";
				//exit;
			}
			$reportsResult = $reportsResults->fetch_all();
			for($i = 0; $i < sizeof($reportsResult); $i++) {
				$reportData[$i] = array('RPT_EmailSubject'=>$reportsResult[$i][0],'RPT_UserEmail'=>$reportsResult[$i][1],
					'RPT_OriginalFrom'=>$reportsResult[$i][2],'RPT_ReportDate'=>$reportsResult[$i][3]);
			}
			$db->close();
			return $reportData;
		}
	}

	public function isUserAuth() {
		$return = array('authCheck'=>\Session::get('authUserId'));
		return $return;
	}

	public function databaseErrorLogging($sql,$db) {
		echo "Sorry, the website is experiencing technical difficulties.";
		echo "Error: Our query failed to execute and here is why: \n";
		echo "Hash Select Query: " . $sql . "\n";
		echo "Errno: " . $db->errno . "\n";
		echo "Error: " . $db->error . "\n";
	}
}
