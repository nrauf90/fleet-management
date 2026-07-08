<?php



defined('BASEPATH') or exit('No direct script access allowed');



class Salary_model extends App_Model

{

    public function __construct()

    {

        parent::__construct();

    }



    /**

     * Get all staff with salary information

     * @param  mixed $id Optional - staff id

     * @return mixed if id is passed return object else array

     */

     public function get_staff_salary($id = '')
    {
        $this->db->select('s.*, CONCAT(s.firstname, " ", s.lastname) as full_name, ss.initial_salary, ss.advance_salary, ss.effective_date, ss.status as salary_status, COALESCE(SUM(sas.amount), 0) as total_advance_amount');
        $this->db->from(db_prefix() . 'staff s');
        $this->db->join(db_prefix() . 'staff_salary ss', 's.staffid = ss.staff_id AND ss.status = "active"', 'left');
        $this->db->join(db_prefix() . 'staff_advance_salary sas', 's.staffid = sas.staff_id AND sas.status = "approved" AND MONTH(sas.approved_date) = ' . date('m') . ' AND YEAR(sas.approved_date) = ' . date('Y'), 'left');
        $this->db->where('s.active', 1);
        $this->db->group_by('s.staffid');

        if (is_numeric($id)) {
            $this->db->where('s.staffid', $id);
            return $this->db->get()->row();
        }

        $this->db->order_by('s.firstname', 'asc');
        return $this->db->get()->result_array();
    }



    /**

     * Add or update staff salary

     * @param array $data salary data

     * @param mixed $id staff id

     * @return mixed

     */

    public function add_staff_salary($data, $id = null)

    {

        $data['effective_date'] = to_sql_date($data['effective_date']);

        $data['created_by'] = get_staff_user_id();



        if ($id) {

            // Update existing salary record

            $this->db->where('staff_id', $id);

            $this->db->where('status', 'active');

            $existing = $this->db->get(db_prefix() . 'staff_salary')->row();



            if ($existing) {

                // Deactivate current salary record

                $this->db->where('id', $existing->id);

                $this->db->update(db_prefix() . 'staff_salary', ['status' => 'inactive']);

            }



            // Update staff table

            $this->db->where('staffid', $id);

            $this->db->update(db_prefix() . 'staff', [

                'initial_salary' => $data['initial_salary'],

                'current_salary' => $data['initial_salary'],

                'salary_effective_date' => $data['effective_date']

            ]);

        }



        // Insert new salary record

        $this->db->insert(db_prefix() . 'staff_salary', $data);

        $insert_id = $this->db->insert_id();



        if ($insert_id) {

            log_activity('Staff Salary Updated [Staff ID: ' . $id . ', Initial Salary: ' . $data['initial_salary'] . ']');

            return $insert_id;

        }



        return false;

    }



    /**

     * Get advance salary requests

     * @param  mixed $id Optional - request id

     * @return mixed if id is passed return object else array

     */

    public function get_advance_salary($id = '')

    {

        $this->db->select('sas.*, CONCAT(s.firstname, " ", s.lastname) as staff_name, CONCAT(approver.firstname, " ", approver.lastname) as approver_name');

        $this->db->from(db_prefix() . 'staff_advance_salary sas');

        $this->db->join(db_prefix() . 'staff s', 's.staffid = sas.staff_id', 'left');

        $this->db->join(db_prefix() . 'staff approver', 'approver.staffid = sas.approved_by', 'left');



        if (is_numeric($id)) {

            $this->db->where('sas.id', $id);

            return $this->db->get()->row();

        }



        $this->db->order_by('sas.created_at', 'desc');

        return $this->db->get()->result_array();

    }



    /**

     * Add advance salary request

     * @param array $data advance salary data

     * @return mixed

     */

    public function add_advance_salary($data)

    {

        $data['request_date'] = to_sql_date($data['request_date']);

        $data['created_by'] = get_staff_user_id();

       // $data['status'] = 'pending';



        $this->db->insert(db_prefix() . 'staff_advance_salary', $data);

        $insert_id = $this->db->insert_id();



        if ($insert_id) {

            log_activity('Advance Salary Request Added [Staff ID: ' . $data['staff_id'] . ', Amount: ' . $data['amount'] . ']');

            return $insert_id;

        }



        return false;

    }



    /**

     * Update advance salary request

     * @param array $data advance salary data

     * @param mixed $id request id

     * @return boolean

     */

    public function update_advance_salary($data, $id)

    {

        if (isset($data['approved_date']) && $data['approved_date']) {

            $data['approved_date'] = to_sql_date($data['approved_date']);

        }



        if (isset($data['status']) && $data['status'] == 'approved') {

            $data['approved_by'] = get_staff_user_id();

        }



        $this->db->where('id', $id);

        $this->db->update(db_prefix() . 'staff_advance_salary', $data);



        if ($this->db->affected_rows() > 0) {

            log_activity('Advance Salary Request Updated [ID: ' . $id . ']');

            return true;

        }



        return false;

    }



    /**

     * Delete advance salary request

     * @param mixed $id request id

     * @return boolean

     */

    public function delete_advance_salary($id)

    {

        $this->db->where('id', $id);

        $this->db->delete(db_prefix() . 'staff_advance_salary');



        if ($this->db->affected_rows() > 0) {

            log_activity('Advance Salary Request Deleted [ID: ' . $id . ']');

            return true;

        }



        return false;

    }



    /**

     * Get salary settings

     * @param string $setting_name Optional - specific setting name

     * @return mixed

     */

    public function get_salary_settings($setting_name = '')

    {

        if ($setting_name) {

            $this->db->where('setting_name', $setting_name);

            $result = $this->db->get(db_prefix() . 'salary_settings')->row();

            return $result ? $result->setting_value : null;

        }



        $settings = $this->db->get(db_prefix() . 'salary_settings')->result_array();

        $formatted_settings = [];



        foreach ($settings as $setting) {

            $formatted_settings[$setting['setting_name']] = $setting['setting_value'];

        }



        return $formatted_settings;

    }



    /**

     * Update salary settings

     * @param array $data settings data

     * @return boolean

     */

    public function update_salary_settings($data)

    {

        foreach ($data as $setting_name => $setting_value) {

            $this->db->where('setting_name', $setting_name);

            $exists = $this->db->get(db_prefix() . 'salary_settings')->row();



            if ($exists) {

                $this->db->where('setting_name', $setting_name);

                $this->db->update(db_prefix() . 'salary_settings', ['setting_value' => $setting_value]);

            } else {

                $this->db->insert(db_prefix() . 'salary_settings', [

                    'setting_name' => $setting_name,

                    'setting_value' => $setting_value

                ]);

            }

        }



        return true;

    }



    /**

     * Get salary statistics

     * @return array

     */

    public function get_salary_statistics()

    {

        $stats = [];



        // Total staff with salary

        $this->db->where('active', 1);

        $stats['total_staff'] = $this->db->count_all_results(db_prefix() . 'staff');



        // Total salary amount

        $this->db->select_sum('current_salary');

        $this->db->where('active', 1);

        $result = $this->db->get(db_prefix() . 'staff')->row();

        $stats['total_salary'] = $result->current_salary ?: 0;



        // Pending advance requests

        $this->db->where('status', 'pending');

        $stats['pending_advance'] = $this->db->count_all_results(db_prefix() . 'staff_advance_salary');



        // Total advance amount this month

        $this->db->select_sum('amount');

        $this->db->where('status', 'approved');

        $this->db->where('MONTH(approved_date)', date('m'));

        $this->db->where('YEAR(approved_date)', date('Y'));

        $result = $this->db->get(db_prefix() . 'staff_advance_salary')->row();

        $stats['monthly_advance'] = $result->amount ?: 0;



        return $stats;

    }



    /**

     * Get salary history for staff

     * @param mixed $staff_id staff id

     * @return array

     */

    public function get_salary_history($staff_id)

    {

        $this->db->where('staff_id', $staff_id);

        $this->db->order_by('effective_date', 'desc');

        return $this->db->get(db_prefix() . 'staff_salary')->result_array();

    }



    /**

     * Get advance salary history for staff

     * @param mixed $staff_id staff id

     * @return array

     */

    public function get_advance_history($staff_id)

    {

        $this->db->where('staff_id', $staff_id);

        $this->db->order_by('created_at', 'desc');

        return $this->db->get(db_prefix() . 'staff_advance_salary')->result_array();

    }

}