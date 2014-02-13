<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.

 *  The Original Code is OpenVBX, released June 15, 2010.

 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.

 * Contributor(s):
 *  - Dmitry Minaev (minaevd@gmail.com)
 **/

/** Description:
 * 1. show custom login form: index()
 * 2. on form submit try to find active user with login/pwd pair: login()
 * 3. if success: create a cookie using rules in standard Login
 * 		and redirect user to his environment
 * 4. if failure: show error message
**/


class SingleSignIn extends MY_Controller
{
	protected $user_id;
	protected $js_assets = 'loginjs';

	function __construct()
	{
		parent::__construct();
		$this->config->load('openvbx');
		$this->config->load('config', TRUE);
		$this->load->database();
		$this->template->write('title', '');
		
		// no cache
		$ci =& get_instance();
		$ci->cache->enabled(false);

		$this->user_id = $this->session->userdata('user_id');
	}

	public function index()
	{
		$this->template->write('title', 'Single Sign In');
		$data = array();
		
		if($this->input->post('login'))
		{
			$this->login($redirect);
		}

		// admin check sets flashdata error message
		if(!isset($data['error']))
		{
			$error = $this->session->flashdata('error');
			if(!empty($error)) $data['error'] = CI_Template::literal($error);
		}

		return $this->respond('', 'singlesignin', $data, 'login-wrapper', 'layout/login');
	}
	
	private function redirect($redirect)
	{
		$redirect = preg_replace('/^(http|https):\/\//i', '', $redirect);
		redirect($redirect);
	}
	
	private function login() //$redirect)
	{
		try
		{
			$user = VBX_User::singlesignin($this->input->post('email'),
									$this->input->post('pw'),
									$this->input->post('captcha'),
									$this->input->post('captcha_token'));
			if ($user) {

// TODO: check if the block commented below is necessary or not?
//				$connect_auth = OpenVBX::connectAuthTenant($user->tenant_id);

// 				// we kick out non-admins, admins will have an opportunity to re-auth the account
// 				if (!$connect_auth && !$user->is_admin) 
// 				{
// 					$this->session->set_flashdata('error', 'Connect auth denied');
// 					return redirect('auth/connect/account_deauthorized');
// 				}

				$userdata = array(
					'email' => $user->email,
					'user_id' => $user->id,
					'is_admin' => $user->is_admin,
					'loggedin' => TRUE,
					'signature' => VBX_User::signature($user),
				);

// 				$bu = base_url();
				$ci =& get_instance();

				$tenants = $ci->db
				->select('url_prefix')
				->from('tenants')
				->where('id =', $user->tenant_id)
				->where('active', 1)
				->limit(1)
				->get()->result();

				$url_prefix = $tenants[0]->url_prefix;

				$redirect = '';
				if(!strstr(current_url(), $url_prefix))
					$redirect = $url_prefix;

				$this->session->set_userdata($userdata);

// 				if(OpenVBX::schemaVersion() >= 24)
// 				{
// 					return $this->after_login_completed($user, $redirect);
// 				}

				return $this->redirect($redirect);
			}
			
			$this->session->set_flashdata('error',
										  'Email address and/or password is incorrect');
			return redirect('');
		}
		catch(GoogleCaptchaChallengeException $e)
		{
			$this->session->set_flashdata('error', $e->getMessage());

			$data['error'] = $e->getMessage();
			$data['captcha_url'] = $e->captcha_url;
			$data['captcha_token'] = $e->captcha_token;
		}
	}

}
