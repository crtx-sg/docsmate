<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/css/style.css?1.4" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.10.0/css/all.css" />
    <!-- <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700;800&display=swap" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/assets/css/bootstrap4-toggle.min.css" />
    <link rel="stylesheet" href="/assets/css/simplemde_v1.11.1.min.css" />
    <link rel="stylesheet" href="/assets/css/bootstrap-select_v1.13.14.min.css" />

    <link rel="stylesheet" href="/assets/css/headerStyle.css?1.7">

    <!-- For Showing Code Diff  -->
    <link rel="stylesheet" href="/assets/css/github_diff.min.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/diff2html.min.css" />

    <!-- For Datatables -->
    <link rel="stylesheet" type="text/css" href="/assets/css/datatables.min.css" />

    <!-- <script type="module" src="https://unpkg.com/ionicons@5.1.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule="" src="https://unpkg.com/ionicons@5.1.2/dist/ionicons/ionicons.js"></script> -->

    <script type="text/javascript" src="/assets/js/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="/assets/js/utilites.js?1.4"></script>
    <script type="text/javascript" src="/assets/js/popper.min.js"></script>
    <script type="text/javascript" src="/assets/js/bootstrap.min.js"></script>

    <script type="text/javascript" src="/assets/js/bootbox.min.js"></script>
    <script type="text/javascript" src="/assets/js/bootstrap4-toggle.min.js"></script>
    <script type="text/javascript" src="/assets/js/simplemde.min.js"></script>
    <script type="text/javascript" src="/assets/js/bootstrap-select.min.js"></script>
    <script type="text/javascript" src="/assets/js/diff2html-ui.min.js"></script>
    <script type="text/javascript" src="/assets/js/datatables.min.js"></script>
    <!-- For Charts-->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- For drawing diagrams feature -->
    <script src="/assets/js/mermaid.min.js"></script>
    <!-- For taskboard drag and drop feature -->
    <link rel="stylesheet" href="/assets/css/jquery-ui_1.12.1.css">
    <script src="/assets/js/jquery-ui.min_1.12.1.js"></script>

    <!--For Pie charts -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.js"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>  -->

    <title>DocsGo</title>
    <link rel="icon" href="<?= base_url() ?>/Docsgo-Logo.png" type="image/gif">
    <style>
        .CodeMirror,
        .CodeMirror-scroll {
            height: auto;
            min-height: 70px;
        }

        body {
            /* font-family: "Open Sans"; */
            /* font-family: "Poppins", sans-serif; */
            background-color: rgb(239, 244, 247);

        }


        .page-content {
            overflow-x: hidden;
        }

        .my_nav_link {
            color: #12192C;
        }

        .my_nav_link:hover {
            color: white;
            text-decoration: none;
        }

        .sidebar-footer {
            position: fixed;
            bottom: 0px;
            padding: 8px;
            /* width: 210px; */
            left: 16px;
        }

        .sidebar-footer a:hover {
            color: black;
        }

        .collapse__menu li:hover a {
            background-color: white;
            color: black !important;
            border-radius: 10px;
            text-decoration: none;
        }

        .collapse:hover a {
            color: white;
        }

        #loading-overlay {
            position: fixed;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            display: none;
            align-items: center;
            background-color: #000;
            z-index: 999;
            opacity: 0.5;
        }

        .loading-icon {
            position: absolute;
            margin: 0 auto;
            position: absolute;
            left: 50%;
            margin-left: -20px;
            top: 50%;
            margin-top: -20px;
            z-index: 4;
        }

        .carousel-control-prev-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23007bff' viewBox='0 0 8 8'%3E%3Cpath d='M5.25 0l-4 4 4 4 1.5-1.5-2.5-2.5 2.5-2.5-1.5-1.5z'/%3E%3C/svg%3E");
        }

        .carousel-control-next-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23007bff' viewBox='0 0 8 8'%3E%3Cpath d='M2.75 0l-1.5 1.5 2.5 2.5-2.5 2.5 1.5 1.5 4-4-4-4z'/%3E%3C/svg%3E");
        }

        .carousel-indicators {
            bottom: 30px;
        }

        .carousel-indicators li {
            background-color: #91c6ff;
        }

        .carousel-indicators .active {
            background-color: #007bff;
        }

        .carousel-control-next,
        .carousel-control-prev {
            top: 50px;
            bottom: 76px;
        }

        .back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            display: none;
            border: 1px solid;
        }

        .dropdown-menu {
            max-width: 235px;
        }

        .website-footer {
            background-color: #313783;
            color: #fff !important;
            font-size: 14px;
            bottom: 0;
            position: fixed;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 1;
            height: 30px;
        }

        /* Side menu icon styles */
        /* .nav__icon {
        font-size: 0.9rem
    } */
    </style>

    <script>
        $(document).ready(function() {
            // $.getScript("/assets/js/header.js");

            $(window).scroll(function() {
                if ($(this).scrollTop() > 50) {
                    $('#back-to-top').fadeIn();
                } else {
                    $('#back-to-top').fadeOut();
                }
            });
            // scroll body to 0px on click
            $('#back-to-top').click(function() {
                $('body,html').animate({
                    scrollTop: 0
                }, 400);
                return false;
            });

        });
    </script>
</head>

<body id="body-pd">
    <?php $uri = service('uri');
    $currentUrl = $_SERVER['REQUEST_URI'];
    $baseUrl = getenv('app.baseURL');
    ?>

    <?php if (session()->get('isLoggedIn')) : ?>

        <body id="body-pd">

            <div class="sidebar">
                <div class="logo-details">
                    <img src="<?= $baseUrl ?>/Docsgo-Logo.png" style="width:60px;margin-left:11px" alt="DocsGo">
                    <span class="logo_name">DocsGo</span>
                </div>
                <ul class="nav-links">
                    <li class="<?= ($uri->getSegment(1) == 'dashboard' ? 'active-nav-link' : '') ?>">
                        <a href="/dashboard">
                            <i class='bx bx-grid-alt'></i>
                            <span class="link_name">Dashboard</span>
                        </a>
                        <ul class="sub-menu blank">
                            <li><a class="link_name" href="/dashboard">Dashboard</a></li>
                        </ul>
                    </li>
                    <li class="<?= ($uri->getSegment(1) == 'projects'  || $uri->getSegment(1) == 'taskboard'  ? 'active-nav-link' : '') ?>">
                        <a href="/projects">
                            <i class='bx bx-folder-open'></i>
                            <span class="link_name">Projects</span>
                        </a>
                        <ul class="sub-menu blank">
                            <li><a class="link_name" href="/projects">Projects</a></li>
                        </ul>
                    </li>
                    <li class="<?= ($uri->getSegment(1) == 'reviews'   ? 'active-nav-link' : '') ?>">
                        <a href="/reviews">
                            <i class='bx bx-book-content'></i>
                            <span class="link_name">Review Register</span>
                        </a>
                        <ul class="sub-menu blank">
                            <li><a class="link_name" href="/reviews">Review Register</a></li>
                        </ul>
                    </li>
                    <li class="<?= (($uri->getSegment(1) == 'documents' || $uri->getSegment(1) == 'unit-tests' || $uri->getSegment(1) == 'documents-templates' || $uri->getSegment(1) == 'documents-master' || $uri->getSegment(1) == 'documents-acronyms') ? 'active-nav-link' : '')  ?>">
                        <div class="iocn-link">
                            <a href="/documents">
                                <i class='bx bx-file'></i>
                                <span class="link_name">Documents</span>
                            </a>
                            <i class='bx bxs-chevron-down arrow'></i>
                        </div>
                        <ul class="sub-menu">
                            <li><a class="link_name" href="/documents">Documents</a></li>
                            <li><a href="/unit-tests">Unit Tests</a></li>
                            <li><a href="/documents-templates">Templates</a></li>
                            <li><a href="/documents-master" >References</a></li>
                            <li><a href="/documents-acronyms" >Acronyms</a></li>
                        </ul>
                    </li>
                    <li class="<?= ((($uri->getSegment(1) == 'timesheet') || $uri->getSegment(1) == 'diagramsList' || $uri->getSegment(2) == 'reports')? 'active-nav-link' : '')  ?>">
                        <div class="iocn-link">
                            <a href="/timesheet">
                                <i class='bx bx-plug'></i>
                                <span class="link_name">Utilities</span>
                            </a>
                            <i class='bx bxs-chevron-down arrow'></i>
                        </div>
                        <ul class="sub-menu">
                            <li><a class="link_name" href="/timesheet">Utilities</a></li>
                            <li><a href="/timesheet">TimeSheet</a></li>
                            <?php if (session()->get('is-admin')): ?>
                            <li><a href="/timesheet/reports">Timesheet Reports</a></li>
                            <?php endif; ?>
                            <li><a href="/meeting">Meeting Minutes</a></li>
                            <li><a href="/diagramsList">Draw Diagram</a></li>
                        </ul>
                    </li>
                    <li class="<?= (($uri->getSegment(1) == 'risk-assessment' || $uri->getSegment(1) == 'risk-mapping') ? 'active-nav-link' : '')  ?>">
                        <div class="iocn-link">
                            <a href="/risk-assessment">
                                <i class='bx bx-shield-quarter'></i>
                            <span class="link_name">Risk Assessment</span>
                            </a>
                            <i class='bx bxs-chevron-down arrow'></i>
                        </div>
                        <ul class="sub-menu">
                            <li><a class="link_name" href="/risk-assessment">Risk Assessment</a></li>
                            <?php if (session()->get('is-admin')): ?>
			                <li><a href="/risk-mapping">Risk Mapping</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <li class="<?= ((($uri->getSegment(1) == 'requirements') || $uri->getSegment(1) == 'test-cases' || $uri->getSegment(1) == 'traceability-matrix')  ? 'active-nav-link' : '') ?>">
                        <div class="iocn-link">
                            <a href="/traceability-matrix">
                                <i class='bx bx-collection'></i>
                                <span class="link_name">Traceability</span>
                            </a>
                            <i class='bx bxs-chevron-down arrow'></i>
                        </div>
                        <ul class="sub-menu">
                            <li><a class="link_name" href="/traceability-matrix">Traceability</a></li>
                            <li><a href="/requirements">Requirements</a></li>
                            <li><a href="/test-cases">Test</a></li>
                        </ul>
                    </li>
                    <li class="<?= ($uri->getSegment(1) == 'inventory-master'   ? 'active-nav-link' : '') ?>">
                        <a href="/inventory-master">
                            <i class='bx bx-basket'></i>
                            <span class="link_name">Assets</span>
                        </a>
                        <ul class="sub-menu blank">
                            <li><a class="link_name" href="/inventory-master">Assets</a></li>
                        </ul>
                    </li>
                    <li class="<?= ((($uri->getSegment(1) == 'courses') || $uri->getSegment(1) == 'userCourses' || ($uri->getSegment(1) == 'userCourses' && $uri->getSegment(2) == 'reports'))  ? 'active-nav-link' : '') ?>">
                        <div class="iocn-link">
                            <a href="/courses">
                                <i class='fas fa-chalkboard-teacher'></i>
                                <span class="link_name">Learning</span>
                            </a>
                            <i class='bx bxs-chevron-down arrow'></i>
                        </div>
                        
                        <ul class="sub-menu">
                            <li><a href="/courses">Courses</a></li>
                            <li><a href="/userCourses">User Courses</a></li>
                            <li><a href="/userCourses/reports">Reports</a></li>
                        </ul>
                    </li>
                    <li class="<?= ($uri->getSegment(1) == 'team'   ? 'active-nav-link' : '') ?>">
                        <a href="/team">
                            <i class='bx bx-user'></i>
                            <span class="link_name">Team</span>
                        </a>
                        <ul class="sub-menu blank">
                            <li><a class="link_name" href="/team">Team</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="/storage/repo">
                            <i class='bx bx-hdd'></i>
                            <span class="link_name">Storage</span>
                        </a>
                        <ul class="sub-menu blank">
                            <li><a class="link_name" href="/storage/repo">Storage</a></li>
                        </ul>
                    </li>
                    <?php if (session()->get('is-admin')): ?>
                    <li class="<?= ($uri->getSegment(2) == 'settings'   ? 'active-nav-link' : '') ?>">
                        <a href="/admin/settings">
                            <i class='bx bx-cog'></i>
                            <span class="link_name">Settings</span>
                        </a>
                        <ul class="sub-menu blank">
                            <li><a class="link_name" href="/admin/settings">Settings</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li>
                        <div class="profile-details">
                            <a href="/profile" class="profile-content" title="<?= session()->get('name') ?>">
                                <img src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_640.png" alt="profileImg">
                            </a>
                            <div class="name-job" title="<?= session()->get('name') ?>">
                                <div class="profile_name"><?= session()->get('name') ?></div>
                            </div>
                            <a href="/logout" title="Log Out">
                                <i class='bx bx-log-out' style="color:#fff"></i>
                            </a>

                        </div>
                    </li>
                    <div class="toggle-menu" id="nav-toggle"><i class='bx bxs-right-arrow'></i></div>
                </ul>
            </div>
            <script>
                let arrow = document.querySelectorAll(".arrow");
                for (var i = 0; i < arrow.length; i++) {
                    arrow[i].addEventListener("click", (e) => {
                        let arrowParent = e.target.parentElement.parentElement; //selecting main parent of arrow
                        arrowParent.classList.toggle("showMenu");
                    });
                }
                let sidebar = document.querySelector(".sidebar");
                let sidebarBtn = document.querySelector(".toggle-menu");

                sidebarBtn.addEventListener("click", () => {
                    sidebar.classList.toggle("menuClose");
                    sidebarBtn.classList.toggle("showMenu");
                });
            </script>
            <section class="home-section">
                <main class="page-content">
                    <div id="loading-overlay">
                        <div class="loading-icon"><i class="fa fa-spinner fa-spin fa-3x text-primary"></i></div>
                    </div>
                    <!-- <div class="floating-alert alert text-light box-shadow-left success-alert" style="display: none;z-index:9999" role="alert"></div> -->
                <?php endif; ?>