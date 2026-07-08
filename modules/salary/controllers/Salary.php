<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Salary extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('salary_model');
    }

    /**
     * Dashboard
     */
    public function index()
    {
        if (!has_permission('salary', '', 'view')) {
            access_denied('salary');
        }

        $data['title'] = _l('salary_dashboard');
        $data['stats'] = $this->salary_model->get_salary_statistics();
        $data['recent_advances'] = $this->salary_model->get_advance_salary();

        // Get recent salary updates
        $this->db->select('ss.*, CONCAT(s.firstname, " ", s.lastname) as staff_name');
        $this->db->from(db_prefix() . 'staff_salary ss');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = ss.staff_id', 'left');
        $this->db->order_by('ss.created_at', 'desc');
        $this->db->limit(5);
        $data['recent_salaries'] = $this->db->get()->result_array();

        // Load base currency
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $this->load->view('salary/dashboard', $data);
    }

    /**
     * Staff salary management
     */
    public function staff()
    {
        if (!has_permission('salary', '', 'view')) {
            access_denied('salary');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('staff_salary');
        }

        $data['title'] = _l('staff_salary');
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('salary/staff', $data);
    }

    /**
     * Add/Edit staff salary
     */
    public function staff_salary($id = '')
    {
        if (!has_permission('salary', '', 'view')) {
            access_denied('salary');
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            if ($id == '') {
                if (!has_permission('salary', '', 'create')) {
                    access_denied('salary');
                }
                $success = $this->salary_model->add_staff_salary($data);
                if ($success) {
                    set_alert('success', _l('added_successfully', _l('staff_salary')));
                    redirect(admin_url('salary/staff'));
                }
            } else {
                if (!has_permission('salary', '', 'edit')) {
                    access_denied('salary');
                }
                $success = $this->salary_model->add_staff_salary($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('staff_salary')));
                    redirect(admin_url('salary/staff'));
                }
            }
        }

        if ($id == '') {
            $title = _l('add_new', _l('staff_salary'));
        } else {
            $data['staff_salary'] = $this->salary_model->get_staff_salary($id);
            $data['salary_history'] = $this->salary_model->get_salary_history($id);
            $title = _l('edit', _l('staff_salary'));
        }

        $data['title'] = $title;
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);

        // Load base currency
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $this->load->view('salary/staff_salary', $data);
    }

    /**
     * Advance salary management
     */
    public function advance()
    {
        if (!has_permission('salary', '', 'view')) {
            access_denied('salary');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('advance_salary');
        }

        $data['title'] = _l('advance_salary');
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('salary/advance', $data);
    }

    /**
     * Add advance salary request
     */
    public function add_advance()
    {
        if (!has_permission('salary', '', 'create')) {
            access_denied('salary');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $success = $this->salary_model->add_advance_salary($data);

            if ($success) {
                set_alert('success', _l('added_successfully', _l('advance_salary_request')));
                redirect(admin_url('salary/advance'));
            }
        }

        $data['title'] = _l('add_new', _l('advance_salary_request'));
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);

        // Load base currency
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $this->load->view('salary/add_advance', $data);
    }

    /**
     * Edit advance salary request
     */
    public function edit_advance($id)
    {
        if (!has_permission('salary', '', 'edit')) {
            access_denied('salary');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $success = $this->salary_model->update_advance_salary($data, $id);

            if ($success) {
                set_alert('success', _l('updated_successfully', _l('advance_salary_request')));
                redirect(admin_url('salary/advance'));
            }
        }

        $data['advance'] = $this->salary_model->get_advance_salary($id);
        $data['title'] = _l('edit', _l('advance_salary_request'));
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);

        // Load base currency
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $this->load->view('salary/edit_advance', $data);
    }

    /**
     * Delete advance salary request
     */
    public function delete_advance($id)
    {
        if (!has_permission('salary', '', 'delete')) {
            access_denied('salary');
        }

        $success = $this->salary_model->delete_advance_salary($id);

        if ($success) {
            set_alert('success', _l('deleted', _l('advance_salary_request')));
        }

        redirect(admin_url('salary/advance'));
    }

    /**
     * Approve/Reject advance salary request
     */
    public function approve_advance($id, $status)
    {
        if (!has_permission('salary', '', 'edit')) {
            access_denied('salary');
        }

        $data = [
            'status' => $status,
            'approved_date' => date('Y-m-d')
        ];

        $success = $this->salary_model->update_advance_salary($data, $id);

        if ($success) {
            $status_text = $status == 'approved' ? _l('approved') : _l('rejected');
            set_alert('success', _l('advance_salary_request') . ' ' . $status_text);
        }

        redirect(admin_url('salary/advance'));
    }

    /**
     * Salary reports
     */
    public function reports()
    {
        if (!has_permission('salary', '', 'view')) {
            access_denied('salary');
        }

        $data['title'] = _l('salary_reports');

        // Get filter parameters
        $month = $this->input->get('month') ?: date('Y-m');
        $staff_id = $this->input->get('staff_id');

        $data['month'] = $month;
        $data['staff_id'] = $staff_id;
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);

        // Get salary data for the month
        $data['salary_data'] = $this->salary_model->get_staff_salary();

        // Get advance data for the month
        $this->db->where('MONTH(request_date)', date('m', strtotime($month . '-01')));
        $this->db->where('YEAR(request_date)', date('Y', strtotime($month . '-01')));
        if ($staff_id) {
            $this->db->where('staff_id', $staff_id);
        }
        $data['advance_data'] = $this->db->get(db_prefix() . 'staff_advance_salary')->result_array();

        // Load base currency
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $this->load->view('salary/reports', $data);
    }

    /**
     * Settings
     */
    public function settings()
    {
        if (!is_admin()) {
            access_denied('salary');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $success = $this->salary_model->update_salary_settings($data);

            if ($success) {
                set_alert('success', _l('settings_updated'));
            }

            redirect(admin_url('salary/settings'));
        }

        $data['title'] = _l('salary_settings');
        $data['settings'] = $this->salary_model->get_salary_settings();
        $this->load->view('salary/settings', $data);
    }

        /**
     * Test database tables
     */
    public function test_tables()
    {
        if (!is_admin()) {
            access_denied('Database Test');
        }

        echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
        echo '<h2>Database Tables Test</h2>';

        // Check if staff table has salary columns
        echo '<h3>1. Checking staff table...</h3>';
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = '" . $this->db->database . "' 
                AND TABLE_NAME = '" . db_prefix() . "staff' 
                AND COLUMN_NAME IN('initial_salary', 'current_salary', 'salary_effective_date')";
        
        $columns = $this->db->query($sql)->result_array();
        $salary_columns = array_column($columns, 'COLUMN_NAME');

        echo '<p>Salary columns found in staff table: ' . implode(', ', $salary_columns) . '</p>';

        if (count($salary_columns) < 3) {
            echo '<p style="color: red;">❌ Missing salary columns in staff table!</p>';
            echo '<p>Please run the salary module installation script.</p>';
        } else {
            echo '<p style="color: green;">✅ All salary columns found in staff table</p>';
        }

        // Check if staff_salary table exists
        echo '<h3>2. Checking staff_salary table...</h3>';
        $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = '" . $this->db->database . "' 
                AND TABLE_NAME = '" . db_prefix() . "staff_salary'";
        
        $table = $this->db->query($sql)->row();

        if ($table) {
            echo '<p style="color: green;">✅ staff_salary table exists</p>';
            
            // Check table structure
            $sql = "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = '" . $this->db->database . "' 
                    AND TABLE_NAME = '" . db_prefix() . "staff_salary'";
            
            $columns = $this->db->query($sql)->result_array();
            echo '<p>Columns: ';
            foreach ($columns as $col) {
                echo $col['COLUMN_NAME'] . ' (' . $col['DATA_TYPE'] . '), ';
            }
            echo '</p>';
        } else {
            echo '<p style="color: red;">❌ staff_salary table does not exist!</p>';
        }

        // Check if staff_advance_salary table exists
        echo '<h3>3. Checking staff_advance_salary table...</h3>';
        $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = '" . $this->db->database . "' 
                AND TABLE_NAME = '" . db_prefix() . "staff_advance_salary'";
        
        $table = $this->db->query($sql)->row();

        if ($table) {
            echo '<p style="color: green;">✅ staff_advance_salary table exists</p>';
            
            // Check table structure
            $sql = "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = '" . $this->db->database . "' 
                    AND TABLE_NAME = '" . db_prefix() . "staff_advance_salary'";
            
            $columns = $this->db->query($sql)->result_array();
            echo '<p>Columns: ';
            foreach ($columns as $col) {
                echo $col['COLUMN_NAME'] . ' (' . $col['DATA_TYPE'] . '), ';
            }
            echo '</p>';
        } else {
            echo '<p style="color: red;">❌ staff_advance_salary table does not exist!</p>';
        }

        // Test a simple query
        echo '<h3>4. Testing simple query...</h3>';
        try {
            $this->db->select('staffid, firstname, lastname, initial_salary, current_salary');
            $this->db->from(db_prefix() . 'staff');
            $this->db->where('active', 1);
            $this->db->limit(5);
            
            $result = $this->db->get()->result_array();
            echo '<p style="color: green;">✅ Query successful. Found ' . count($result) . ' staff members</p>';
            
            if (count($result) > 0) {
                echo '<p>Sample data:</p>';
                echo '<ul>';
                foreach ($result as $staff) {
                    echo '<li>' . $staff['firstname'] . ' ' . $staff['lastname'] . ' - Initial: ' . ($staff['initial_salary'] ?: 'Not set') . '</li>';
                }
                echo '</ul>';
            }
        } catch (Exception $e) {
            echo '<p style="color: red;">❌ Query failed: ' . $e->getMessage() . '</p>';
        }

        // Test datatable query
        echo '<h3>5. Testing datatable query...</h3>';
        try {
            $this->db->select('s.staffid, CONCAT(s.firstname, " ", s.lastname) as full_name, s.email, s.initial_salary, s.current_salary, COALESCE(ss.advance_salary, 0) as advance_salary, s.salary_effective_date');
            $this->db->from(db_prefix() . 'staff s');
            $this->db->join(db_prefix() . 'staff_salary ss', 's.staffid = ss.staff_id AND ss.status = "active"', 'left');
            $this->db->where('s.active', 1);
            $this->db->limit(5);
            
            $result = $this->db->get()->result_array();
            echo '<p style="color: green;">✅ Datatable query successful. Found ' . count($result) . ' records</p>';
            
            if (count($result) > 0) {
                echo '<p>Sample datatable data:</p>';
                echo '<ul>';
                foreach ($result as $row) {
                    echo '<li>' . $row['full_name'] . ' - Current: ' . ($row['current_salary'] ?: 'Not set') . '</li>';
                }
                echo '</ul>';
            }
        } catch (Exception $e) {
            echo '<p style="color: red;">❌ Datatable query failed: ' . $e->getMessage() . '</p>';
        }

        echo '<h3>6. Recommendations:</h3>';
        echo '<ul>';
        echo '<li>If tables are missing, run: <a href="' . admin_url('modules/activate/salary') . '">Activation Script</a></li>';
        echo '<li>If columns are missing, check the install.php file</li>';
        echo '<li>Make sure the database user has proper permissions</li>';
        echo '</ul>';

        echo '<p><a href="' . admin_url('salary') . '" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">Back to Salary Dashboard</a></p>';
        echo '</div>';
    }

}