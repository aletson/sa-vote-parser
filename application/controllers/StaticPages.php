<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class StaticPages extends CI_Controller {

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
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('StaticPages/welcome_message');
	}

	public function not_found() {
		$this->load->view('StaticPages/not_found');
	}

	public function about() {
		$data = new stdClass;
		$data->title = 'About ISBN2Dewey';
		$this->load->view('header', $data);
		$this->load->view('StaticPages/about');
	}
}
