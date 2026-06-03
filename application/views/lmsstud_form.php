<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>LAUNDRY</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- Bootstrap 3.3.2 -->
        <link href="<?= base_url('assets/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet" type="text/css" />
        <!-- Font Awesome Icons -->
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="<?= base_url('assets/dist/css/AdminLTE.min.css') ?>" rel="stylesheet" type="text/css" />
        <!-- AdminLTE Skins. Choose a skin from the css/skins 
            folder instead of downloading all of them to reduce the load. -->
        <link href="<?= base_url('assets/dist/css/skins/_all-skins.min.css') ?>" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="skin-blue">
        <div class="wrapper">
        <header class="main-header">
                <a href="" class="logo"><b>LAUNDRY</b></a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top" role="navigation">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown user user-menu">
                                <a href="../Laundry_student/logout">Sign out</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <!-- /.search form -->
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                        <li class="header">MAIN NAVIGATION</li>
                        <li>
                                <a href="lmsstud_form">
                                    <i class="fa fa-th"></i> <span>Laundry Form</span>
                                </a>
                            </li>
                            <li>
                                <a href="previous_details">
                                    <i class="fa fa-th"></i> <span>Previous Details</span>
                                </a>
                            </li>
                            <li>
                                <a href="change_password">
                                    <i class="fa fa-th"></i> <span>Change Password</span>
                                </a>
                            </li>
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>
            <!-- Right side column. Contains the navbar and content of the page -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>  LAUNDRY FORM
                    </h1>
                </section>
                <!-- Main content -->
                <section class="content">
                    <div class="row">
                        <?php if ($this->session->flashdata('success')): ?>
                            <div class="alert alert-success">
                                <?php echo $this->session->flashdata('success'); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger">
                                <?php echo $this->session->flashdata('error'); ?>
                            </div>
                        <?php endif; ?>
                        <!-- left column -->
                        <div class="col-md-12">
                            <!-- general form elements -->
                            <div class="box box-primary">
                                <div class="box-header">
                                <?php
                            // Get the admno from the session
                            $admno = $this->session->userdata('admno');
                            // Query to count the total number of 'pending' statuses in the table, prioritizing 'priority' roles
                            $data['total_priority_pending_count'] = $this->db->where('status', 'pending')
                                ->where('role', 'priority')
                                ->count_all_results('student_lms_status');
                            $data['total_non_priority_pending_count'] = $this->db->where('status', 'pending')
                                ->where('role !=', 'priority')
                                ->count_all_results('student_lms_status');
                            $data['total_pending_count'] = $data['total_priority_pending_count'] + $data['total_non_priority_pending_count'];
                                // Query to count the number of 'pending' statuses for the specific admno, prioritizing 'priority' roles
                            $data['admno_priority_pending_count'] = $this->db->where('admno', $admno)
                                ->where('status', 'pending')
                                ->where('role', 'priority')
                                ->count_all_results('student_lms_status');
                            $data['admno_non_priority_pending_count'] = $this->db->where('admno', $admno)
                                ->where('status', 'pending')
                                ->where('role !=', 'priority')
                                ->count_all_results('student_lms_status');
                            $data['admno_pending_count'] = $data['admno_priority_pending_count'] + $data['admno_non_priority_pending_count'];
                                // If the admno has pending statuses, query for the count of 'pending' statuses for lower admnos, considering priority first
                            if ($data['admno_pending_count'] > 0) {
                                    // Count pending statuses for priority roles with admnos lower than the current admno
                                $data['lower_admno_priority_pending_count'] = $this->db->where('status', 'pending')
                                    ->where('role', 'priority')
                                    ->where('admno <', $admno) // Filter for admnos lower than the current admno
                                    ->count_all_results('student_lms_status');
                                    // Count pending statuses for non-priority roles with admnos lower than the current admno
                                $data['lower_admno_non_priority_pending_count'] = $this->db->where('status', 'pending')
                                    ->where('role !=', 'priority')
                                    ->where('admno <', $admno) // Filter for admnos lower than the current admno
                                    ->count_all_results('student_lms_status');
                                // Combine the counts
                                $data['lower_admno_pending_count'] = $data['lower_admno_priority_pending_count'] + $data['lower_admno_non_priority_pending_count'];
                            } else {
                                // If no pending status for the current admno, set the lower_admno_pending_count to 0
                                $data['lower_admno_pending_count'] = 0;
                            }
                            ?>
                            <!-- Logic to display pending counts -->
                            <?php if ($data['admno_pending_count'] > 0): ?>
                                <!-- If the admno has pending statuses, show count of pending for lower admnos -->
                                Your dress will be washed after the dresses of <?php echo $data['lower_admno_pending_count']; ?> other individuals have been cleaned.
                            <?php else: ?>
                                <!-- If the admno has no pending statuses, show the total count of pending statuses -->
                                Total Pending LMS Statuses: <?php echo $data['total_pending_count']; ?>
                            <?php endif; ?>
                            <?php
                                // Get the admno from the session
                                $admno = $this->session->userdata('admno');
                                // Query to get the latest status based on the highest date for the given admno
                                $data['lms_status'] = $this->db->select('status')
                                    ->where('admno', $admno)
                                    ->order_by('date', 'DESC') // Order by date in descending order to get the latest date first
                                    ->limit(1) // Limit to 1 to only get the most recent entry
                                    ->get('student_lms_status') // Replace with your actual table name if different
                                    ->row(); // Use row() to get a single result object
                            ?>
                            <!-- Display the LMS Status -->
                            LMS Status: <?php echo isset($data['lms_status']->status) ? $data['lms_status']->status : 'No status found'; ?>
                                </div><!-- /.box-header -->
                                    <!-- form start -->
                                <form action="<?php echo base_url('Laundry_student/laundry_dress_save') ?>" method="post">
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Select Dress</label>
                                                <select name="dress" class="form-control tm-select" id="dress">
                                                    <option value="">Dress</option>
                                                    <option value="Shirt">Shirt</option>
                                                    <option value="TShirt">TShirt</option>
                                                    <option value="Kurtha">Kurtha</option>
                                                    <option value="Uniform-Shirt">Uniform-Shirt</option>
                                                    <option value="Uniform-Pant">Uniform-Pant</option>
                                                    <option value="Uniform-Coat">Uniform Coat</option>
                                                    <option value="Pants">Pants</option>
                                                    <option value="Shorts">Shorts</option>
                                                    <option value="Churidar-Top">Churidar Top</option>
                                                    <option value="Churidar-Pants">Churidar Pants</option>
                                                    <option value="Shawl">Shawl</option>
                                                    <option value="Bed-Sheet-single">Bed Sheet single</option>
                                                    <option value="Pillow-cover">Pillow cover</option>
                                                    <option value="Blanket">Blanket</option>
                                                    <option value="Bath-Towel">Bath Towel</option>
                                                    <option value="Mundu(Lungi)">Mundu(Lungi)</option>
                                                </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputPassword1">Select Count</label>
                                            <select name="dress_count" class="form-control tm-select" id="dress_count">
                                                <option value="">Count</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                                <option value="7">7</option>
                                                <option value="8">8</option>
                                                <option value="9">9</option>
                                                <option value="10">10</option>
                                            </select>
                                        </div>
                                    </div><!-- /.box-body -->
                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </form>
                            </div><!-- /.box -->
                        </form>
                        <div class="col-md-12">
                            <!-- general form elements -->
                            <div class="box box-primary">
                                <div class="box-header">
                                    <!-- <h3 class="box-title">Quick Example</h3> -->
                                </div><!-- /.box-header -->
                                    <!-- form start -->
                                    <table class="table table-bordered" id="myTable3">
                                        <thead>
                                            <tr>
                                                <th>Admission Number</th>
                                                <th>Dress Type</th>
                                                <th>Count</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($laundry_data)): ?>
                                                <?php foreach ($laundry_data as $laundry): ?>
                                                    <tr>
                                                        <td><?php echo $laundry->admno; ?></td>
                                                        <td><?php echo $laundry->dress; ?></td>
                                                        <td><?php echo $laundry->dress_count; ?></td>
                                                        <td><?php echo date('Y-m-d', strtotime($laundry->date)); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No laundry records available.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                            </div><!-- /.box -->
                        </form>
                    </div><!-- /.box-body -->
                </section><!-- /.content -->
            </div><!-- /.content-wrapper -->
            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <!-- <b>Version</b> 2.0 -->
                </div>
                <strong>Copyright &copy; <a href="https://jeccac.in/">JCS JEC</a>.</strong> All rights reserved.
            </footer>
        </div><!-- ./wrapper -->

        <!-- jQuery 2.1.3 -->
        <script src="<?= base_url('assets/plugins/jQuery/jQuery-2.1.3.min.js') ?>"></script>
        <!-- Bootstrap 3.3.2 JS -->
        <script src="<?= base_url('assets/bootstrap/js/bootstrap.min.js') ?>" type="text/javascript"></script>
        <!-- FastClick -->
        <script src='<?= base_url('assets/plugins/fastclick/fastclick.min.js') ?>'></script>
        <!-- AdminLTE App -->
        <script src="<?= base_url('assets/dist/js/app.min.js') ?>" type="text/javascript"></script>
        <!-- AdminLTE for demo purposes -->
        <!-- <script src="</?= base_url('assets/dist/js/demo.js') ?>" type="text/javascript"></script> -->
    </body>
</html>
