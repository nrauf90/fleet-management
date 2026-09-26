<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Account_statement_pdf extends App_pdf
{
    protected $statement;

    public function __construct($statement)
    {
        parent::__construct();

        $this->statement = $statement;

        $this->SetTitle(_l('accounts_statement'));
    }

    public function prepare()
    {
        $this->set_view_vars([
            'statement' => $this->statement,
        ]);

        return $this->build();
    }

    public function get_format_array()
    {
        $format = parent::get_format_array();

        if (empty($format['orientation'])) {
            $format['orientation'] = 'P';
        }
        if (empty($format['format'])) {
            $format['format'] = 'A4';
        }

        return $format;
    }

    protected function type()
    {
        return 'account_statement';
    }

    protected function file_path()
    {
        $customPath = module_dir_path(ACCOUNTS_MODULE_NAME) . 'views/my_statement_pdf.php';
        $actualPath = module_dir_path(ACCOUNTS_MODULE_NAME) . 'views/statement_pdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
