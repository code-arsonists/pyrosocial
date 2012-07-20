<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ratings model
 *
 * @subpackage	Listings/Ratings Module
 * @category	Models
 * @author		Ryun Shofner
 */
class likes_m extends MY_Model {
    
    protected $_table = 'ps_likes';
    public function __construct()
    {
        parent::__construct();
        //$this->_table = 'listing_reviews';
    }

    public function new_like()
    {
		/** ----------------------------------------
		/**  Is this an AJAX request?
		/** ---------------------------------------- */
        $this->ajax = FALSE;
        if ($this->input->get_post('ajax') != FALSE OR (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))
        {
            $this->ajax = TRUE;
        }


        $IP = sprintf("%u", ip2long($this->input->ip_address()));
        $form_data = array();
        $form_data['allow_guests'] = FALSE;
        $form_data['allow_multiple'] = FALSE;
        $form_data['return'] = ($this->post->input('return') != FALSE) ? $this->post->input('return') : FALSE;

        /** ----------------------------------------
          /**  What action? Like or Dislike
          /** ---------------------------------------- */
        $form_data['liked'] = 0;
        $form_data['disliked'] = 0;

        if ($this->post->input('action') == 'like')
		{
            $form_data['liked'] = 1;
		}
        elseif ($this->post->input('action') == 'dislike')
		{
            $form_data['disliked'] = 1;
		}
        else {
            // Response: error: missing_action
		}

		/** ----------------------------------------
		/**  Allow Guests?
		/** ---------------------------------------- */
        if ($this->post->input('allow_guests') != 'yes' && isset($this->user->id))
        {
            $this->new_like_response($this->lang->line('rating:error:not_authorized'));
        }
        elseif ($this->post->input('allow_guests') == 'yes')
        {
            $form_data['allow_guests'] = TRUE;
        }

        /** ----------------------------------------
          /**  Allow Multiple?
          /** ---------------------------------------- */
        if ($this->post->input('allow_multiple') == 'yes')
        {
            $form_data['allow_multiple'] = TRUE;
        }

        /** ----------------------------------------
          /**  Valid ID's?
          /** ---------------------------------------- */
        $data = array('stream_id' => 0, 'comment_id' => 0, 'like_type' => 1);

        // Comment_ID?
        if ($this->post->input('comment_id') != FALSE && is_numeric($this->post->input('comment_id')) != FALSE)
        {
            $data['like_type'] = 2;
            $data['comment_id'] = $this->post->input('comment_id');

            // Grab stream_id/channel_id
            $data['stream_id'] = $this->db->select('stream_id')->from('ps_comments')->where('id', $data['comment_id'])->limit(1)->get()->row()->stream_id;

        }
        elseif ($this->post->input('stream_id') != FALSE && is_numeric($this->post->input('stream_id')) != FALSE)
        {
            // Entry then?
            $data['stream_id'] = $this->post->input('stream_id');
        }
        else
        {
            // No Valid IDS
        }

		/** ----------------------------------------
		/**  Already Liked?
		/** ---------------------------------------- */
        $this->db->select('id');
        $this->db->from('ps_likes');
        $this->db->where('stream_id', $data['stream_id']);
        $this->db->where('comment_id', $data['comment_id']);
        $this->db->where('is_stats', 0);
        if ($this->user->id == 0)
        {
            $this->db->where('ip_address', $IP);
            $this->db->where('like_author_id', 0);
        }
        else {
            $this->db->where('like_author_id', $this->user->id);
		}
        $this->db->where('like_type', $data['like_type']);
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows > 0 && $form_data['allow_multiple'] == FALSE)
        {
            // No Duplicates
        }


         //  Lets insert!
        $data['ip_address'] = $IP;
        $data['author_id'] = $this->user->id;
        $data['like_date'] = time();
        $data['liked'] = $form_data['liked'];
        $data['disliked'] = $form_data['disliked'];

        $this->db->insert('ps_likes', $data);

        /** ----------------------------------------
          /**  Global Stats!
          /** ---------------------------------------- */
        // TODO: Move this to model!

        $query = $this->db->query("
			SELECT
				SUM(liked) as liked_sum,
				SUM(disliked) as disliked_sum,
				MAX(like_date) as like_last_date
			FROM ps_likes
			WHERE like_type = {$data['like_type']}
			AND stream_id = {$data['stream_id']}
			AND comment_id = {$data['comment_id']}
			AND is_stats = 0
			");

        $total = array();
        $total['liked'] = $query->row('liked_sum');
        $total['disliked'] = $query->row('disliked_sum');
        $total['like_date'] = $query->row('like_last_date');

        $query->free_result();

        // Does our stats entry exist for this entry?
        $query = $this->db->query("	SELECT id
										FROM ps_likes
										WHERE like_type = {$data['like_type']}
										AND stream_id = {$data['stream_id']}
										AND comment_id = {$data['comment_id']}
										AND is_stats = 1
										LIMIT 1");

        //----------------------------------------
        // Update Or Insert?
        //----------------------------------------
        if ($query->num_rows() == 0)
        {
            // new one Insert!
            $total['is_stats'] = 1;
            $total['like_type'] = $data['like_type'];
            $total['stream_id'] = $data['stream_id'];
            $total['comment_id'] = $data['comment_id'];
            $this->db->insert('ps_likes', $total);
        }
        else
        {
            // Update it!
            $this->db->update('ps_likes', $total, array('id' => $query->row('id')));
        }

        /** ----------------------------------------
          /**  Back to the USER!
          /** ---------------------------------------- */
        // Return goes first!
        if ($form_data['return'] != FALSE)
        {
            // Redirect people
            $form_data['return'] = $this->functions->remove_double_slashes($this->functions->create_url(trim_slashes($form_data['return'])));
            $this->functions->redirect($form_data['return']);
        }

        if ($this->ajax == TRUE)
        {
            exit($this->lang->line('rating:success:new_like'));
        }
        else
        {
            $REFERRED = $this->input->server('HTTP_REFERER');
            if ($REFERRED == FALSE)
                $REFERRED = $this->functions->fetch_site_index(0, 0);

            // We're done.
            $data = array('title' => $this->lang->line('thank_you'),
                'heading' => $this->lang->line('thank_you'),
                'content' => $this->lang->line('rating:success:new_like'),
                'redirect' => $REFERRED,
                'link' => array($REFERRED, $this->lang->line('back'))
            );

            $this->output->show_message($data);
            exit();
        }
    }

}

?>