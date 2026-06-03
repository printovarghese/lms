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
                    <h1>  PREVIOUS DETAILS
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
                                <?php
                                    // Get the admission number from the session
                                    $admno = $this->session->userdata('admno');
                                    
                                    // Fetch unique dates from student_dress_table for the logged-in student
                                    $dates = $this->db->select('DISTINCT(date) as date')
                                        ->where('admno', $admno)
                                        ->order_by('date', 'DESC') // Latest dates first
                                        ->get('student_dress_table')
                                        ->result();
                                ?>
                        <!-- left column -->
                        <div class="col-md-12">
                            <!-- general form elements -->
                            <div class="box box-primary">
                                <div class="box-header">
                                </div><!-- /.box-header -->
                                    <!-- form start -->
                                <form action="<?php echo base_url('Laundry_student/laundry_details_select') ?>" method="post">
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Select Date</label>
                                            <select id="start_date" class="form-control" name="start_date">
                                                <option value="">-- Select Date --</option>
                                                <?php foreach ($dates as $date): ?>
                                                    <option value="<?php echo $date->date; ?>"><?php echo date('Y-m-d', strtotime($date->date)); ?></option>
                                                <?php endforeach; ?>
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
                                            <th>Actions</th> <!-- New Column for Actions -->
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
                                                    <td>
                                                        <?php if ($laundry->status == 0): ?> 
                                                            <!-- Edit Button -->
                                                            <a href="<?php echo base_url('Laundry_student/edit/' . $laundry->id); ?>" class="btn btn-warning btn-sm">
                                                                Edit
                                                            </a>
                                                            <!-- Delete Button -->
                                                            <a href="<?php echo base_url('Laundry_student/delete/' . $laundry->id); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this record?');">
                                                                Delete
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span> <!-- No actions if status is not 0 -->
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No laundry records available.</td>
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
