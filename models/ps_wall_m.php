<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @author 		Ryun Shofner
 * @package		Profiles
 * @subpackage	Streams Module
 * @since		v0.1
 *
 * Media, Notification, Comment, Link	
 */ 	

class ps_wall_m extends MY_Model
{
	protected $_table = 'ps_wall';

    function __construct()
    {
		parent::__construct();
		$this->streams_tbl = $this->db->dbprefix('ps_wall');
    }
    
	function get($ref_id, $limit=20, $offset=0)
	{
		return $this->db
			->where('ref_id', $ref_id)
			->limit($limit, $offset)
			->get()->result_array();			
	}
	
	/*	Add Stream
	 *	@param 	$obj_id		unique identifier
	 *	@param 	$obj_type	type of object (custom, page, module)
	 *
	 */
function is_base64_enc()
    {
        if (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

	private function add_stream($i)
	{
		$_insert = array(
			'user_id' 		=> ( !empty($i['user_id']) ) ? $i['user_id'] : $this->current_user->id,
			'body' 			=> $i['body'],
			'stream_type' 	=> ( !empty($i['content_type']) ) ? $i['content_type'] : 'update',
			'object_type' 	=> ( !empty($i['object_type']) ) ? $i['object_type'] : 'user',
			'object_id' 	=> ( !empty($i['object_id']) ) ? $i['object_id'] : 0,
			'created_on'	=> time(),
			'ip_address'	=> ip2long($this->input->ip_address()),
			'is_active'		=> ( !empty($i['is_active']) ) ? $i['is_active'] : 1,
		);
		$_insert['id'] = $this->insert($_insert);
		return $_insert;
	}


	function add_notification($user_id, $object_type, $body, $object_id=0)
	{
		// insert comment

		$_insert = array(
			'user_id' 		=> ( !empty( $user_id ) ) ? $user_id : $this->current_user->id,
			'stream_type' 	=> 'notify',
			'object_type' 	=> ( !empty($object_type ) ) ? $object_type : 'user',
			'object_id' 	=> ( !empty($object_id) ) ? $object_id : 0,
			'created_on'	=> time(),
			'ip_address'	=> ip2long($this->input->ip_address()),
			'body' 			=> $body,
			'is_active'		=> 1,
		);
		return $this->db->insert('ps_wall', $_insert);		
	}
	
	public function email_notification($recipients, $reply)
	{
		$this->load->library('email');
		$this->load->helper('url');

		foreach($recipients as $person)
		{
			// No need to email the user that entered the reply
			if($person['email'] == $this->current_user->email)
			{
				continue;
			}
			$text_body = $reply['content'];

			$this->email->clear();
			$this->email->from($this->settings->server_email, $this->settings->site_name . ' Streams');
			$this->email->to($person['email']);

			$this->email->subject('Subscription Notification: ' . $reply['title']);
			$text_body = 'Reply to <strong>"' . $reply['title'] . '"</strong>.<br /><br />' . $text_body;

			$this->email->message($text_body);
			$this->email->send();
		}
	}

	function add_update($user_id, $body, $type=false)
	{
		// insert comment
		$_insert = array(
			'object_type'	=> 'user',
			'stream_type'	=> ($type) ? $type : 'update',
			'body' 			=> $body,
			'is_active'		=> 1,
		);
		return $this->add_stream($_insert);
	}

	function add_comment($user_id, $stream_id, $body)
	{
		// insert comment
		$_insert = array(
			'user_id' 		=> ( !empty($i['user_id']) ) ? $i['user_id'] : $this->current_user->id,
			'created_on'	=> time(),
			'ip_address'	=> ip2long($this->input->ip_address()),
			'stream_type'	=> 'comment',
			'object_type'	=> 'stream',
			'object_id'		=> $stream_id,
			'body' 			=> $body,
			'is_active'		=> 1
		);
		$comment_id = $this->insert($_insert);
		
		// get last three comments as array
		$comments = $this->db
			->select('ps_wall.*, users.username, users.email')
			->join('users', 'users.id = ps_wall.user_id')
			->where('stream_type', 'comment')
			->where('object_id', $stream_id)
			->order_by('created_on', 'desc')
			->get('ps_wall', 3)
			->result_array();

		if (is_array($comments))
		{
			foreach ($comments as $comment)
			{
				$prep_comments[$comment['id']] = array(
					'user_id' => $comment['user_id'],
					'username' => $comment['username'],
					'email' => $comment['email'],
					'avatar' =>  gravatar($comment['email'], 25,'x', true),
					'body' =>  $comment['body'],
					/*
					SWITCH TO TIMESTAMP, find js time formater
					*/
					'created_on' => $comment['created_on']
				);
				
				$response_comments[$comment['id']] = $prep_comments[$comment['id']];
				$response_comments[$comment['id']]['created_iso860'] = standard_date('DATE_ISO8601', $comment['created_on']);
				$response_comments[$comment['id']]['is_author'] = ($comment['user_id'] == $this->current_user->id) ? true : false;

			}
			// serialize comments array
			$comments_db = serialize($prep_comments);
			
			// update stream db
			$this->db->where('id', $stream_id);
			$this->db->set('recent_comments', $comments_db);
			$this->db->set('num_comments', 'num_comments+1', FALSE);
			$this->db->update('ps_wall');
			return array('sid'=>$stream_id, 'id'=>$comment_id, 'comments'=>$response_comments, 'count'=>$this->count_comments($stream_id));
		}
	}

	function count_comments($stream_id)
	{
		return $this->db
			->where('stream_type', 'comment')
			->where('object_id', $stream_id)
			->count_all_results('ps_wall');
	}

	function get_comments($stream_id, $limit=3)
	{
		$comments = $this->db
			->select('ps_wall.*, users.username, users.email')
			->join('users', 'users.id = ps_wall.user_id')
			->where('stream_type', 'comment')
			->where('object_id', $stream_id)
			->order_by('created_on', 'desc')
			->get('ps_wall', $limit)
			->result_array();
		if (is_array($comments))
		{
			foreach ($comments as $comment)
			{
				$prep_comments[$comment['id']] = array(
					'user_id' => $comment['user_id'],
					'username' => $comment['username'],
					'email' => $comment['email'],
					'avatar' =>  gravatar($comment['email'], 25,'x', true),
					'body' =>  $comment['body'],
					'created_on' => timespan($comment['created_on']),
					'created_iso860' => standard_date('DATE_ISO8601', $comment['created_on']),
					'is_author' => ($comment['user_id'] == $this->current_user->id) ? true : false
				);
			}
			return array('sid'=> (int)$stream_id, 'comments'=>$prep_comments, 'count'=>$this->count_comments($stream_id));
		}
		return array();
	}
	
	function del_stream($stream_id)
	{
			$this->db
				->where('id', $stream_id)
				->or_where('object_id', $stream_id)
				->delete('ps_wall');
			return $this->db->affected_rows();

	}

}