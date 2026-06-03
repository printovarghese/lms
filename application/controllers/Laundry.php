<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laundry extends CI_Controller {


	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->model('Laundry_model');
		// $this->load->library('PHPExcel');
	}
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/userguide3/general/urls.html
	 */
	public function index()
	{
		$this->load->view('laundrylogin_view');
	}


	public function login()
	{
		$admno = $this->input->post('admno');
		$password = $this->input->post('password');
		$logindata = $this->db->select(['admno' , 'password', 'role'])->where('admno', $admno)->get('user')->result_array();
		$date = date("Y-m-d");
	
		// Check if the entered password matches the hashed password
		if ($logindata && $password === $logindata[0]['password']) {
			if ($logindata[0]['role'] == 'user') {
				
				$this->session->set_userdata(array(
					'role' => $logindata[0]['role'],
					'admno' => $logindata[0]['admno'],
				));
				redirect(base_url("Laundry_student/lmsstud_form"));
			} elseif ($logindata[0]['role'] == 'admin') {
				$this->session->set_userdata(array(
					'role' => $logindata[0]['role'],
					'admno' => $logindata[0]['admno'],
				));
				redirect(base_url("Laundry/washing_list"));
			}// Check for priority role
			elseif ($logindata[0]['role'] == 'priority') {
				$this->session->set_userdata(array(
					'role' => $logindata[0]['role'],
					'admno' => $logindata[0]['admno'],
				));
				// Redirect to a page specific to the "priority" role
				redirect(base_url("Laundry_student/lmsstud_form"));
			}
			// Handle case if role is not found
			else {
				echo 'Role not recognized.';
			}
		} else {
			// Display an error message if the password is incorrect
			echo 'Incorrect username or password.';
		}
	}
	public function student_select()
    {
        $this->load->view('laundry_view');
    }
	public function laundry_select()
    {
		$day = $this->input->post('day');
		$date = $this->input->post('date');
		$data['date'] = $date;
        $data['student'] = $this->db->select("*")->where('day', $day)->get('student')->result_array();
		$this->load->view('student_list',$data);
    }
	public function laundry_submit() {
		// Retrieve the form data
		$names = $this->input->post('names');
		$student_ids = $this->input->post('student_ids'); // Array of student IDs
		$checkedValues = $this->input->post('checkedValues'); // Array of hidden input values
		$date = $this->input->post('date'); // Date from form
		
		// Initialize an array to store the processed data
		$data_to_insert = [];
	
		// Process the form data
		if (!empty($names) && is_array($names) && !empty($checkedValues) && is_array($checkedValues) && !empty($student_ids) && is_array($student_ids)) {
			foreach ($names as $index => $name) {
				// Ensure the index exists in all arrays
				if (isset($checkedValues[$index]) && isset($student_ids[$index])) {
					$data_to_insert[] = [
						'student_id' => $student_ids[$index], // Add student_id to the data
						'student_name' => $name,
						'washing_count' => $checkedValues[$index],
						'date_lms' => $date
					];
				}
			}
	
			// Load the model and insert data into the database
			$this->load->model('Laundry_model');
			if (!empty($data_to_insert)) {
				$this->Laundry_model->insert_washing_data($data_to_insert);
			}
		}
		redirect(base_url('Laundry/student_select'));
	}	

	public function additional_students()
	{
// 		$data['student'] = $this->db->select("*")->get('student')->result_array();
    $data['student'] = $this->db->select("*")
        ->from('student')
        ->order_by('name', 'ASC')
        ->get()
        ->result_array();

		$this->load->view('additional_students',$data);
	}
	public function additional_students_insert()
	{
		$student_id = $this->input->post('student_id');
		$date = $this->input->post('date'); 
		// Fetch the student name using the student ID
		$student_name = $this->db->select('name')->from('student')->where('id', $student_id)->get()->row()->name;
		$data = [
			'student_id' => $student_id,
			'student_name' => $student_name,
			'washing_count' => "1",
			'date_lms' => $date
		];

		$this->db->insert('washing', $data);
		redirect(base_url('Laundry/additional_students'));
	}

	public function report()
    {
        $this->load->view('report');
    }
	public function report_view() {
		$start_date = $this->input->post('start_date');
		$end_date = $this->input->post('end_date');
	
		// Fetch the washing data based on the date range
		$washing_data = $this->Laundry_model->get_washing_data_by_date($start_date, $end_date);
	
		// Pass the washing data to the view
		$data['washing_data'] = $washing_data;
		$this->load->view('report_view', $data);
	}
	public function washing_list()
	{
		// Step 1: Retrieve data from the database, prioritize 'priority' role entries with status 'Pending'
		$query = $this->db->select('student.*, student_dress_table.*, student_lms_status.*')
			->from('student_dress_table')
			->join('student', 'student_dress_table.admno = student.adm_no')
			->join('student_lms_status', 'student_dress_table.admno = student_lms_status.admno', 'left')
			->where('student_lms_status.status', 'Pending')
			->order_by("CASE WHEN student_lms_status.role = 'priority' AND student_lms_status.status = 'Pending' THEN 1 ELSE 2 END", 'ASC')
			->order_by('student_lms_status.id', 'ASC') // Secondary sorting by ID
			->get()
			->result_array();

		// Step 2: Process the data to merge entries with the same admno and date
		$processed_data = [];
		foreach ($query as $row) {
			$admno = $row['admno'];
			$date = $row['date'];

			// Use admno and date as a unique key to avoid duplicate entries
			$key = $admno . '_' . $date;

			// If the key doesn't exist, add the row; if it does, skip to avoid duplicates
			if (!isset($processed_data[$key])) {
				$processed_data[$key] = $row;
			}
		}

		// Convert processed data back to an indexed array for use in the view
		$data['washing'] = array_values($processed_data);

		// Step 3: Load the view with the processed data
		$this->load->view('washing_list', $data);
	}

	public function view_details($admno, $date)
	{
		$data['details'] = $this->Laundry_model->get_laundry_details_by_admno_and_date($admno, $date);
	
		// Pass data to the view
		$this->load->view('laundry_details', $data);
	}
	public function update_verification()
	{
		$verified_ids = $this->input->post('verify');

		// Update status in both tables
		$this->Laundry_model->update_verification_status($verified_ids);

		// Redirect back to the listing page or show a success message
		redirect('../Laundry/washing_list');
	}
	public function final_list()
	{
		// Step 1: Retrieve data from the database
		$query = $this->db->select('student.*, student_dress_table.*, student_lms_status.*')
			->from('student_dress_table')
			->join('student', 'student_dress_table.admno = student.adm_no')
			->join('student_lms_status', 'student_dress_table.admno = student_lms_status.admno', 'left')
			->where('student_lms_status.status', 'Washing')
			->order_by('student_lms_status.id', 'ASC')
			->get()
			->result_array();

		// Step 2: Process the data to merge entries with the same admno and date
		$processed_data = [];
		foreach ($query as $row) {
			$admno = $row['admno'];
			$date = $row['date'];

			// Use admno and date as a unique key to avoid duplicate entries
			$key = $admno . '_' . $date;

			// If the key doesn't exist, add the row; if it does, skip to avoid duplicates
			if (!isset($processed_data[$key])) {
				$processed_data[$key] = $row;
			}
		}

		// Convert processed data back to an indexed array for use in the view
		$data['washing'] = array_values($processed_data);

		// Step 3: Load the view with the processed data
		$this->load->view('final_list', $data);
	}
	public function view_washing_details($admno, $date)
	{
		$data['details'] = $this->Laundry_model->get_laundry_washing_date($admno, $date);
	
		// Pass data to the view
		$this->load->view('laundry_washing_details', $data);
	}
	public function update_washing_verification()
	{
		$verified_ids = $this->input->post('verify');

		// Update status in both tables
		$this->Laundry_model->update_washing_verification_status($verified_ids);

		// Redirect back to the listing page or show a success message
		redirect('../Laundry/final_list');
	}
	
	
}
