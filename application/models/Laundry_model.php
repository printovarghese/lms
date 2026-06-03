<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Laundry_model extends CI_Model{

    public function insert_washing_data($data) {
        if (!empty($data)) {
            $this->db->insert_batch('washing', $data);
        }
    }
    public function get_washing_data_by_date($start_date, $end_date) {
    $start_date_obj = new DateTime($start_date);
    $end_date_obj = new DateTime($end_date);

    // Fetch all students
    $this->db->select('id, name, adm_no, department'); 
    $this->db->from('student');
    $students_query = $this->db->get();
    $students = $students_query->result_array();

    $data = [];
    foreach ($students as $student) {
        $data[$student['id']] = [
            'student_name' => $student['name'],
            'adm_no' => $student['adm_no'], 
            'department' => $student['department'],
            'total_washings' => 0,
            'text' => '',
            'amount' => 0, // Start with 0, calculate based on highest weekly washing count
            'lms_dates' => []
        ];
    }

    // Loop through each week
    while ($start_date_obj <= $end_date_obj) {
        $start_of_week = $start_date_obj->modify('monday this week')->format('Y-m-d');
        $end_of_week = $start_date_obj->modify('sunday this week')->format('Y-m-d');

        // Fetch washing data for the week
        $this->db->select('student.id, student.name AS student_name, GROUP_CONCAT(DISTINCT washing.date_lms) AS lms_dates');
        $this->db->from('washing');
        $this->db->join('student', 'student.id = washing.student_id');
        $this->db->where('date_lms >=', $start_of_week);
        $this->db->where('date_lms <=', $end_of_week);
        $this->db->where('washing_count', 1);
        $this->db->group_by('student.id');
        $query = $this->db->get();
        $weekly_washing_data = $query->result_array();

        foreach ($weekly_washing_data as $wash) {
            $student_id = $wash['id'];
            if (isset($data[$student_id])) {
                // Get distinct dates for washing
                $distinct_dates = array_unique(explode(',', $wash['lms_dates']));
                $weekly_wash_count = count($distinct_dates); // Count unique dates for the week

                // Merge the distinct dates with the existing list of dates
                $data[$student_id]['lms_dates'] = array_unique(array_merge($data[$student_id]['lms_dates'], $distinct_dates));

                // Increment total washing count by the number of distinct washings this week
                $data[$student_id]['total_washings'] += 1; // Count one for each week

                // Calculate the weekly amount based on the number of distinct washings (capped at 3)
                $current_week_amount = min($weekly_wash_count, 3) * 300;
                if ($current_week_amount > $data[$student_id]['amount']) {
                    $data[$student_id]['amount'] = $current_week_amount;
                }

                // Set text for 'more than one' if multiple washings occurred in this week
                if ($weekly_wash_count > 1) {
                    $data[$student_id]['text'] = 'more than one';
                } else {
                    $data[$student_id]['text'] = ''; // Reset text for weeks with only 1 wash
                }
            }
        }

        // Move to the next week
        $start_date_obj->modify('+1 week');
    }

    // Set amount to 300 if no washing data exists at all
    foreach ($data as &$student_data) {
        if ($student_data['total_washings'] == 0) {
            $student_data['amount'] = 300;
        }
        $student_data['lms_dates'] = implode(',', $student_data['lms_dates']); // Convert array of dates back to string
    }

    // Ensure no data before the start date is included
    $filtered_data = array_filter($data, function($student_data) use ($start_date) {
        $lms_dates = explode(',', $student_data['lms_dates']);
        foreach ($lms_dates as $date) {
            if ($date < $start_date) {
                return false;
            }
        }
        return true;
    });

    return array_values($filtered_data); // Reindex and return data
}
public function save_laundry_data($data)
    {
        $this->db->insert('student_dress_table', $data); // Replace 'laundry_table' with your actual table name
    }
    public function get_laundry_data_by_admno($admno)
    {
        $this->db->where('admno', $admno);
        $this->db->where('date', date('Y-m-d')); // Add condition for current date
        $query = $this->db->get('student_dress_table');
        return $query->result();
    }
    public function check_status_exists($admno, $current_date)
    {
        $this->db->where('admno', $admno);
        $this->db->where('date', $current_date);
        $query = $this->db->get('student_lms_status');

        return $query->num_rows() > 0; // Returns true if an entry exists
    }

    public function save_status_data($data)
    {
        $this->db->insert('student_lms_status', $data);
    }
    public function get_laundry_details_by_admno_and_date($admno, $date)
    {
        $this->db->select('student_dress_table.*, student.name');
        $this->db->from('student_dress_table');
        $this->db->join('student', 'student_dress_table.admno = student.adm_no');
        $this->db->where('student_dress_table.admno', $admno);
        $this->db->where('student_dress_table.date', $date);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function update_verification_status($verified_ids)
    {
        // Step 1: Update all records to 0 where the IDs are NOT in verified_ids
        if (!empty($verified_ids)) {
            $this->db->where_in('id', $verified_ids);
            $this->db->set('status', 1);
            $this->db->update('student_dress_table');

            // Step 3: Update student_lms_status to 'Washing' for the selected records
            // Get details of selected rows for updating student_lms_status
            $selected_entries = $this->db->select('admno, date')
                ->from('student_dress_table')
                ->where_in('id', $verified_ids)
                ->get()
                ->result_array();

            foreach ($selected_entries as $entry) {
                $this->db->where('admno', $entry['admno']);
                $this->db->where('date', $entry['date']);
                $this->db->set('status', 'Washing');
                $this->db->update('student_lms_status');
            }
        }
    }
    public function get_laundry_washing_date($admno, $date)
    {
        $this->db->select('student_dress_table.*, student.name');
        $this->db->from('student_dress_table');
        $this->db->join('student', 'student_dress_table.admno = student.adm_no');
        $this->db->where('student_dress_table.admno', $admno);
        $this->db->where('student_dress_table.date', $date);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function update_washing_verification_status($verified_ids)
    {
        // Step 1: Update all records to 0 where the IDs are NOT in verified_ids
        if (!empty($verified_ids)) {
            $this->db->where_in('id', $verified_ids);
            $this->db->set('status', 2);
            $this->db->update('student_dress_table');

            // Step 3: Update student_lms_status to 'Washing' for the selected records
            // Get details of selected rows for updating student_lms_status
            $selected_entries = $this->db->select('admno, date')
                ->from('student_dress_table')
                ->where_in('id', $verified_ids)
                ->get()
                ->result_array();

            foreach ($selected_entries as $entry) {
                $this->db->where('admno', $entry['admno']);
                $this->db->where('date', $entry['date']);
                $this->db->set('status', 'Completed');
                $this->db->update('student_lms_status');
            }
        }
    }
    public function check_entry_exists_this_week($admno, $start_of_week, $current_date)
    {
        // Check if there is an entry for this admission number within the current week but excluding today
        $this->db->where('admno', $admno);
        $this->db->where('date >=', $start_of_week);
        $this->db->where('date <', $current_date); // Exclude entries for today
        $query = $this->db->get('student_dress_table');
    
        // Returns true if an entry exists earlier in the current week
        return $query->num_rows() > 0;
    }
    public function check_pending_status_exists($admno, $current_date)
    {
        $this->db->where('admno', $admno);
        $this->db->where('status', 'Pending');
        $this->db->where('date !=', $current_date); // Exclude today's pending entry
        $query = $this->db->get('student_lms_status');

        return $query->num_rows() > 0; // Returns true if a previous pending record exists
    }

    public function get_total_dress_count($admno)
    {
        $this->db->select_sum('dress_count');
        $this->db->where('admno', $admno);
        $this->db->where('status', 0);
        $query = $this->db->get('student_dress_table');

        return $query->row()->dress_count ?? 0; // Return total or 0 if no records
    }

    public function get_laundry_by_id($id)
    {
        return $this->db->get_where('student_dress_table', ['id' => $id])->row();
    }

    // Update laundry data
    public function update_laundry($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('student_dress_table', $data);
    }

    // Delete laundry data
    public function delete_laundry($id)
    {
        return $this->db->delete('student_dress_table', ['id' => $id]);
    }
    public function update_password($admno, $new_password)
    {
        $data = array(
            'password' => $new_password
        );
        $this->db->where('admno', $admno);
        $this->db->update('user', $data);
    }
}

?>