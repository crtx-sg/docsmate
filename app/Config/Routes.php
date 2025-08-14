<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes(true);

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Users');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

$routes->get('/', 'Users::index', ['filter' => 'noauth']);
$routes->get('logout', 'Users::logout');
$routes->match(['get','post'],'validateEmail', 'Users::validateEmail');
$routes->match(['get','post'],'resetPassword', 'Users::resetPassword');
$routes->match(['get','post'],'register', 'Users::register', ['filter' => 'noauth']);
$routes->match(['get','post'],'profile', 'Users::profile',['filter' => 'auth']);
$routes->match(['get','post'],'admin/users', 'Users::viewUsers',['filter' => 'auth']);
$routes->post('admin/users/updateStatus', 'Users::updateAdminStatus',['filter' => 'auth']);
$routes->match(['get','post'],'admin/settings', 'Settings::index',['filter' => 'auth']);
$routes->post('admin/settings/addEnums', 'Settings::addEnums',['filter' => 'auth']);
$routes->post('admin/settings/addProduct', 'Settings::addProduct',['filter' => 'auth']);
$routes->get('admin/settings/getProducts', 'Settings::getProducts',['filter' => 'auth']);
$routes->match(['get','post'],'admin/settings/addProduct/(:num)', 'Settings::addProduct',['filter' => 'auth']);
$routes->match(['get','post'],'admin/settings/delete/(:num)', 'Settings::deleteProduct',['filter' => 'auth']);
$routes->post('admin/settings/updateRequirementValues', 'Settings::updateRequirementValues',['filter' => 'auth']);
$routes->post('admin/settings/updateTaskValues', 'Settings::updateTaskValues',['filter' => 'auth']);
$routes->post('admin/settings/updateTaskTypeValues', 'Settings::updateTaskTypeValues',['filter' => 'auth']);
$routes->post('admin/settings/updateTimeTrackerValues', 'Settings::updateTimeTrackerValues',['filter' => 'auth']);
$routes->post('admin/settings/updateRiskMethodologyValues', 'Settings::updateRiskMethodologyValues',['filter' => 'auth']);

$routes->get('dashboard', 'Dashboard::index',['filter' => 'auth']);
$routes->get('dashboard/getStats', 'Dashboard::getStats',['filter' => 'auth']);

$routes->get('projects', 'Projects::index',['filter' => 'auth']);
$routes->match(['get','post'],'projects/add', 'Projects::add',['filter' => 'auth']);
$routes->match(['get'],'projects/summary', 'Projects::summary',['filter' => 'auth']);
$routes->get('projects/reviewStats', 'Projects::reviewStats',['filter' => 'auth']);
$routes->match(['get','post'],'projects/downloadSummary/(:num)/(:num)', 'Projects::downloadSummary',['filter' => 'auth']);
// $routes->match(['get','post'],'projects/add/(:num)', 'Projects::add',['filter' => 'auth']);
// $routes->match(['get','post'],'projects/delete/(:num)', 'Projects::delete',['filter' => 'auth']);

$routes->get('documents-master', 'DocumentsMaster::index',['filter' => 'auth']);
$routes->match(['get','post'],'documents-master/add', 'DocumentsMaster::add',['filter' => 'auth']);
$routes->match(['get','post'],'documents-master/delete', 'DocumentsMaster::delete',['filter' => 'auth']);

$routes->get('documents-acronyms', 'Acronyms::index',['filter' => 'auth']);
$routes->match(['get','post'],'documents-acronyms/add', 'Acronyms::add',['filter' => 'auth']);
$routes->match(['get','post'],'documents-acronyms/add/(:num)', 'Acronyms::add',['filter' => 'auth']);
$routes->match(['get','post'],'documents-acronyms/delete/(:num)', 'Acronyms::delete',['filter' => 'auth']);

$routes->get('team', 'Team::index',['filter' => 'auth']);
$routes->match(['get','post'],'team/add', 'Team::add',['filter' => 'auth']);
$routes->match(['get','post'],'team/add/(:num)', 'Team::add',['filter' => 'auth']);
$routes->match(['get','post'],'team/delete/(:num)', 'Team::delete',['filter' => 'auth']);

$routes->get('risk-assessment', 'RiskAssessment::index',['filter' => 'auth']);
$routes->match(['get','post'],'risk-assessment/add', 'RiskAssessment::add',['filter' => 'auth']);
$routes->match(['get','post'],'risk-assessment/delete', 'RiskAssessment::delete',['filter' => 'auth']);
$routes->match(['get','post'],'risk-assessment/import', 'RiskAssessment::import', ['filter' => 'auth']);
$routes->get('risk-assessment/createRiskTemplate', 'RiskAssessment::createRiskAssessmentExcelTemplate',['filter' => 'auth']);

$routes->get('risk-mapping', 'RiskMapping::index',['filter' => 'auth']);
$routes->match(['get','post'],'risk-mapping/add', 'RiskMapping::add',['filter' => 'auth']);
$routes->match(['get','post'],'risk-mapping/add/(:num)', 'RiskMapping::add',['filter' => 'auth']);
$routes->get('risk-mapping/getRiskCategories', 'RiskMapping::getRiskCategories',['filter' => 'auth']);
$routes->match(['get','post'],'risk-mapping/delete/(:num)', 'RiskMapping::deleteRiskCategory',['filter' => 'auth']);


$routes->get('requirements', 'Requirements::index',['filter' => 'auth']);
$routes->match(['get','post'],'requirements/add', 'Requirements::add',['filter' => 'auth']);
$routes->match(['get','post'],'requirements/add/(:num)', 'Requirements::add',['filter' => 'auth']);
$routes->match(['get','post'],'requirements/delete', 'Requirements::delete',['filter' => 'auth']);
$routes->match(['get','post'],'requirements/import', 'Requirements::import', ['filter' => 'auth']);
$routes->get('requirements/createRequirementTemplate', 'Requirements::createRequirementTemplate',['filter' => 'auth']);


$routes->get('test-cases', 'TestCases::index',['filter' => 'auth']);
$routes->match(['get','post'],'test-cases/add', 'TestCases::add',['filter' => 'auth']);
$routes->match(['get','post'],'test-cases/add/(:num)', 'TestCases::add',['filter' => 'auth']);
$routes->match(['get','post'],'test-cases/delete/(:num)', 'TestCases::delete',['filter' => 'auth']);
$routes->match(['get','post'],'test-cases/import', 'TestCases::import', ['filter' => 'auth']);
$routes->get('test-cases/createTestCasesTemplate', 'TestCases::createTestCasesTemplate',['filter' => 'auth']);


$routes->get('traceability-matrix', 'TraceabilityMatrix::index',['filter' => 'auth']);
$routes->match(['get','post'],'traceability-matrix/add', 'TraceabilityMatrix::add',['filter' => 'auth']);
$routes->match(['get','post'],'traceability-matrix/add/(:num)', 'TraceabilityMatrix::add',['filter' => 'auth']);
$routes->match(['get','post'],'traceability-matrix/delete/(:num)', 'TraceabilityMatrix::delete',['filter' => 'auth']);
$routes->match(['get','post'],'traceability-matrix/getIDDescription/(:num)/(:num)', 'TraceabilityMatrix::getIDDescription',['filter' => 'auth']);
$routes->match(['get','post'],'traceability-matrix/getProductProjects/(:num)', 'TraceabilityMatrix::getProductProjects',['filter' => 'auth']);
$routes->match(['get','post'],'traceability-matrix/getDescriptionByReq/(:any)', 'TraceabilityMatrix::getDescriptionByReq',['filter' => 'auth']);

$routes->match(['get','post'],'generate-documents/downloadDocuments/(:num)/(:num)', 'GenerateDocuments::downloadDocuments',['filter' => 'auth']);
$routes->match(['get','post'],'generate-documents/checkGenerateDocuments/(:num)', 'GenerateDocuments::checkGenerateDocuments',['filter' => 'auth']);
$routes->match(['get','post'],'generate-documents/updateGenerateDocumentPath/(:num)', 'GenerateDocuments::updateGenerateDocumentPath',['filter' => 'auth']);
$routes->match(['get','post'],'generate-documents/startPDFDocxConvertion/(:num)/(:any)', 'GenerateDocuments::startPDFDocxConvertion',['filter' => 'auth']);
$routes->match(['get','post'],'generate-documents/getWordDocumentFileList/(:num)', 'GenerateDocuments::getWordDocumentFileList',['filter' => 'auth']);
$routes->post('generate-documents/updateDownloadUrl', 'GenerateDocuments::updateDownloadUrl',['filter' => 'auth']);
$routes->get('generate-documents/downloadWordDocument/(:num)/(:any)', 'GenerateDocuments::downloadWordDocument',['filter' => 'auth']);


$routes->get('reviews', 'Reviews::index',['filter' => 'auth']);
$routes->get('reviews/getReviews', 'Reviews::getReviews', ['filter' => 'auth']);
$routes->get('reviews/getReviewStats', 'Reviews::getReviewStats', ['filter' => 'auth']);
$routes->match(['get','post'],'reviews/add', 'Reviews::add',['filter' => 'auth']);
$routes->match(['get','post'],'reviews/add/(:num)', 'Reviews::add',['filter' => 'auth']);
$routes->match(['get','post'],'reviews/delete/(:num)', 'Reviews::delete',['filter' => 'auth']);
$routes->post('reviews/addDocReview', 'Reviews::addDocReview',['filter' => 'auth']);
$routes->post('reviews/saveComment', 'Reviews::saveComment',['filter' => 'auth']);
$routes->post('reviews/deleteComment', 'Reviews::deleteComment',['filter' => 'auth']);

$routes->get('documents', 'Documents::index',['filter' => 'auth']);
$routes->get('documents/getDocuments', 'Documents::getDocuments', ['filter' => 'auth']);
$routes->get('documents/getDocumentStats', 'Documents::getDocumentStats', ['filter' => 'auth']);
$routes->get('documents/add', 'Documents::add',['filter' => 'auth']);
$routes->post('documents/save', 'Documents::save',['filter' => 'auth']);
$routes->match(['get','post'],'documents/delete/(:num)', 'Documents::delete',['filter' => 'auth']);

//This should be removed
$routes->match(['get','post'],'documents/add/(:num)', 'Documents::add',['filter' => 'auth']);


$routes->get('documents-templates', 'DocumentTemplate::index',['filter' => 'auth']);
$routes->post('documents-templates/addTemplate', 'DocumentTemplate::addTemplate',['filter' => 'auth']);
$routes->match(['get','post'],'documents-templates/add', 'DocumentTemplate::add',['filter' => 'auth']);
$routes->match(['get','post'],'documents-templates/add/(:num)', 'DocumentTemplate::add',['filter' => 'auth']);
$routes->match(['get','post'],'documents-templates/delete/(:num)', 'DocumentTemplate::delete',['filter' => 'auth']);
$routes->match(['get'],'documents-templates/getTableContent/(:any)', 'DocumentTemplate::getTableContent',['filter' => 'auth']);

$routes->get('inventory-master', 'InventoryMaster::index',['filter' => 'auth']);
$routes->match(['get','post'],'inventory-master/add', 'InventoryMaster::add',['filter' => 'auth']);
$routes->match(['get','post'],'inventory-master/add/(:num)', 'InventoryMaster::add',['filter' => 'auth']);
$routes->match(['get','post'],'inventory-master/delete/(:num)', 'InventoryMaster::delete',['filter' => 'auth']);
$routes->get('inventory-master/createExcel', 'InventoryMaster::createExcel',['filter' => 'auth']);

$routes->get('bulk-insert', 'BulkInsert::index', ['filter' => 'auth']);

$routes->get('taskboard', 'Taskboard::index',['filter' => 'auth']);
$routes->get('taskboard/getTasks', 'Taskboard::getTasks',['filter' => 'auth']);
$routes->post('taskboard/addTask', 'Taskboard::addTask',['filter' => 'auth']);
$routes->post('taskboard/addComment', 'Taskboard::addComment',['filter' => 'auth']);
$routes->post('taskboard/deleteTask', 'Taskboard::deleteTask',['filter' => 'auth']);
$routes->post('taskboard/updateTaskColumn', 'Taskboard::updateTaskColumn',['filter' => 'auth']);
$routes->get('taskboard/createExcel/(:num)', 'Taskboard::createExcel',['filter' => 'auth']);

$routes->get('diagramsList', 'Diagram::index',['filter' => 'auth']);
$routes->get('diagrams/getDiagrams', 'Diagram::getDiagrams',['filter' => 'auth']);
$routes->get('diagrams/draw', 'Diagram::draw',['filter' => 'auth']);
$routes->post('diagrams/save', 'Diagram::save',['filter' => 'auth']);
$routes->post('diagrams/delete', 'Diagram::delete',['filter' => 'auth']);

$routes->get('courses', 'Courses::index',['filter' => 'auth']);
$routes->match(['get','post'],'courses/add', 'Courses::add',['filter' => 'auth']);
$routes->match(['get','post'],'courses/delete/(:num)', 'Courses::delete',['filter' => 'auth']);

$routes->get('userCourses', 'UserCourses::index',['filter' => 'auth']);
$routes->get('userCourses/getUserCourses', 'UserCourses::getUserCourses', ['filter' => 'auth']);
$routes->get('userCourses/getUserCoursesStats', 'UserCourses::getUserCoursesStats', ['filter' => 'auth']);
$routes->get('userCourses/getUserTotalKPoints', 'UserCourses::getUserTotalKPoints', ['filter' => 'auth']);
$routes->get('userCourses/getUserAchievedKPoints', 'UserCourses::getUserAchievedKPoints', ['filter' => 'auth']);
$routes->match(['get','post'],'userCourses/add', 'UserCourses::add',['filter' => 'auth']);
$routes->match(['get','post'],'userCourses/add/(:num)', 'UserCourses::add',['filter' => 'auth']);
$routes->match(['get','post'],'userCourses/delete/(:num)', 'UserCourses::delete',['filter' => 'auth']);
$routes->match(['get','post'],'userCourses/addCourse/(:num)', 'UserCourses::addCourse',['filter' => 'auth']);
$routes->get('userCourses/reports', 'UserCourses::getReports',['filter' => 'auth']);
$routes->get('userCourses/reports/getUserCoursesReports', 'UserCourses::getUserCoursesReports', ['filter' => 'auth']);


// $routes->get('actionList', 'ActionList::index',['filter' => 'auth']);
// $routes->post('actionList/update', 'ActionList::update',['filter' => 'auth']);
// $routes->post('actionList/delete', 'ActionList::delete',['filter' => 'auth']);

// $routes->get('timeTracker', 'TimeTracker::index',['filter' => 'auth']);
// $routes->get('timeTracker/show', 'TimeTracker::show',['filter' => 'auth']);
// $routes->post('timeTracker/create', 'TimeTracker::create',['filter' => 'auth']);
// $routes->post('timeTracker/delete', 'TimeTracker::delete',['filter' => 'auth']);
// $routes->get('timeTracker/get-weekly-stats', 'TimeTracker::getWeeklyStats', ['filter' => 'auth']);

$routes->get('timesheet', 'TimeSheet::index',['filter' => 'auth']);
$routes->get('timesheet/getTimesheets', 'TimeSheet::getTimesheets', ['filter' => 'auth']);
$routes->get('timesheet/getLogMessages/(:num)', 'TimeSheet::getLogMessages', ['filter' => 'auth']);
$routes->match(['get','post'],'timesheet/add', 'TimeSheet::add',['filter' => 'auth']);
$routes->match(['get','post'],'timesheet/add/(:num)', 'TimeSheet::add',['filter' => 'auth']);
$routes->match(['get','post'],'timesheet/delete/(:num)', 'TimeSheet::delete',['filter' => 'auth']);
$routes->match(['get','post'],'timesheet/sendStatusMail', 'TimeSheet::sendStatusMail');
$routes->get('timesheet/reports', 'TimeSheet::getReports',['filter' => 'auth']);
$routes->get('timesheet/reports/getTimesheetReports', 'TimeSheet::getTimesheetReports', ['filter' => 'auth']);
$routes->get('timesheet/createExcel', 'TimeSheet::createExcel',['filter' => 'auth']);

$routes->get('meeting', 'Meeting::index',['filter' => 'auth']);
$routes->get('meeting/getMeetings', 'Meeting::getMeetings', ['filter' => 'auth']);
$routes->match(['get','post'],'meeting/add', 'Meeting::add',['filter' => 'auth']);
$routes->match(['get','post'],'meeting/add/(:num)', 'Meeting::add',['filter' => 'auth']);

$routes->get('emailQueue', 'Queue::emailQueue');

$routes->get('unit-tests', 'UnitTests::index', ['filter' => 'auth']);
$routes->get('unit-tests/list/project/(:num)/type/(:alpha)', 'UnitTests::list/$1/$2',['filter' => 'auth']);
$routes->get('unit-tests/add/project/(:num)', 'UnitTests::add/$1',['filter' => 'auth']);
$routes->get('unit-tests/add/project/(:num)/template/(:num)', 'UnitTests::add/$1/$2',['filter' => 'auth']);
$routes->get('unit-tests/edit/(:num)', 'UnitTests::edit/$1',['filter' => 'auth']);
$routes->post('unit-tests/save', 'UnitTests::save',['filter' => 'auth']);
$routes->delete('unit-tests/delete/(:num)', 'UnitTests::delete/$1',['filter' => 'auth']);

$routes->get('action-list-upgrade', 'Upgrade::upgradeActionItems', ['filter' => 'auth']);
/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need to it be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
