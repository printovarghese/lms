<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laundry_student extends CI_Controller {


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
    public function lmsstud_form()
    {
        // Load session library
        $this->load->library('session');

        // Get admission number from session
        $admno = $this->session->userdata('admno');

        // Load the model if not already loaded
        $this->load->model('Laundry_model');

        // Fetch laundry data for the logged-in student
        $laundry_data = $this->Laundry_model->get_laundry_data_by_admno($admno);

        // Pass data to the view
        $data = [
            'laundry_data' => $laundry_data
        ];

        $this->load->view('lmsstud_form', $data);
    }

    public function laundry_dress_save()
    {
        // Load session library if not already loaded
        $this->load->library('session');

        // Get form data
        $dress = $this->input->post('dress');
        $dress_count = $this->input->post('dress_count'); 

        // Check if either dress or dress_count is missing
        if (empty($dress) || empty($dress_count)) {
            $this->session->set_flashdata('error', 'Please select both a dress type and count.');
            redirect(base_url('Laundry_student/lmsstud_form'));
            return;
        }

        // Get admission number and role from session
        $admno = $this->session->userdata('admno');
        $role = $this->session->userdata('role');

        // Get current date
        $current_date = date('Y-m-d');

        // Load the model if not already loaded
        $this->load->model('Laundry_model');

        // Check the total sum of dress_count where status = 0
        $total_dress_count = $this->Laundry_model->get_total_dress_count($admno);

        if ($total_dress_count + $dress_count > 15) {
            $this->session->set_flashdata('error', 'You cannot exceed the maximum dress count of 15.');
            redirect(base_url('Laundry_student/lmsstud_form'));
            return;
        }

        // Check if an entry with 'Pending' status exists but is NOT for today
        if ($this->Laundry_model->check_pending_status_exists($admno, $current_date)) {
            $this->session->set_flashdata('error', 'You already have a pending laundry request for a previous day.');
            redirect(base_url('Laundry_student/lmsstud_form'));
            return;
        }

        // Prepare data for insertion in student_dress_table
        $data = [
            'admno' => $admno,
            'dress' => $dress,
            'dress_count' => $dress_count,
            'date' => $current_date
        ];

        // Insert data into student_dress_table
        $this->Laundry_model->save_laundry_data($data);

        if (!$this->Laundry_model->check_status_exists($admno, $current_date)) {
            // If no entry for today, insert a new record in student_lms_status
            $status_data = [
                'admno' => $admno,
                'date' => $current_date,
                'status' => 'Pending', // Adjust status as needed
                'role' => $role // Save the role to the database
            ];
            $this->Laundry_model->save_status_data($status_data);
        }

        // Set success message
        $this->session->set_flashdata('success', 'Laundry dress information saved successfully.');

        // Redirect to another page after saving
        redirect(base_url('Laundry_student/lmsstud_form'));
    }

    public function previous_details()
    {
        // Load session library
        $this->load->library('session');

        // Get admission number from session
        $admno = $this->session->userdata('admno');

        // Load the model if not already loaded
        $this->load->model('Laundry_model');

        // Fetch laundry data for the logged-in student
        $laundry_data = $this->Laundry_model->get_laundry_data_by_admno($admno);

        // Pass data to the view
        $data = [
            'laundry_data' => $laundry_data
        ];

        $this->load->view('lms_studentdress', $data);
    }
    public function laundry_details_select()
    {
        $admno = $this->session->userdata('admno');
        $selected_date = $this->input->post('start_date'); // Get selected date from form
    
        // Fetch laundry records for the given admno and selected date
        $this->db->where('admno', $admno);
        if (!empty($selected_date)) {
            $this->db->where('date', $selected_date);
        }
        $query = $this->db->get('student_dress_table'); // Adjust table name if different
    
        $data['laundry_data'] = $query->result();
        
        // Fetch all available dates for dropdown
        $this->db->distinct();
        $this->db->select('date');
        $this->db->where('admno', $admno);
        $date_query = $this->db->get('student_dress_table');
        $data['dates'] = $date_query->result();
    
        $this->load->view('lms_studentdress', $data); // Replace 'your_view_file' with actual view filename
    }

   	public function logout()
    {
        // Unset session data
        $this->session->unset_userdata('admno');
        $this->session->sess_destroy(); 
    
        // Redirect to login page
        redirect(base_url(''));
    }

    public function edit($id)
    {
        $this->load->model('Laundry_model');

        // Fetch the existing laundry record
        $data['laundry'] = $this->Laundry_model->get_laundry_by_id($id);

        // Check if record exists
        if (!$data['laundry']) {
            $this->session->set_flashdata('error', 'Record not found.');
            redirect(base_url('Laundry_student/lmsstud_form'));
            return;
        }

        // Load the edit form view
        $this->load->view('laundry_edit_form', $data);
    }
    public function update()
    {
        $this->load->model('Laundry_model');

        $id = $this->input->post('id');
        $dress = $this->input->post('dress');
        $dress_count = $this->input->post('dress_count');

        // Validation
        if (empty($dress) || empty($dress_count)) {
            $this->session->set_flashdata('error', 'Please fill in all fields.');
            redirect(base_url('Laundry_student/edit/' . $id));
            return;
        }

        // Prepare update data
        $data = [
            'dress' => $dress,
            'dress_count' => $dress_count
        ];

        // Update record
        if ($this->Laundry_model->update_laundry($id, $data)) {
            $this->session->set_flashdata('success', 'Laundry record updated successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to update record.');
        }

        redirect(base_url('Laundry_student/lmsstud_form'));
    }
    public function delete($id)
    {
        $this->load->model('Laundry_model');

        // Delete record
        if ($this->Laundry_model->delete_laundry($id)) {
            $this->session->set_flashdata('success', 'Laundry record deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete record.');
        }

        redirect(base_url('Laundry_student/lmsstud_form'));
    }
    public function change_password()
    {
        $this->load->view('change_password');
    }
	public function update_password()
{
    $admno = $this->session->userdata('admno');  // FIXED

    if (!$admno) {
        $this->session->set_flashdata('error', 'Session expired. Please login again.');
        redirect(base_url('login'));
    }

    $new_password = $this->input->post('new_password');
    $confirm_password = $this->input->post('confirm_password');

    if ($new_password !== $confirm_password) {
        $this->session->set_flashdata('error', 'The passwords do not match.');
        redirect(base_url('Laundry_student/change_password'));
    }

    if (strlen($new_password) < 4) {
        $this->session->set_flashdata('error', 'The password must be at least 4 characters long.');
        redirect(base_url('Laundry_student/change_password'));
    }

    // Update password in DB
    $this->Laundry_model->update_password($admno, $new_password);

    $this->session->set_flashdata('success', 'Password updated successfully.');
    redirect(base_url('Laundry_student/change_password'));
}


}
