<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Profiles - Members controller (frontend)
 *
 * @author 	Ryun Shofner
 * @package 	Profiles
 * @subpackage 	Members module
 * @category	Modules
 *
 * -- EVENTS notifications ---------------------
 *
 * on_POST_from_friend  email
 * on_friend_request    email
 * on_friend_confirm    email, stream
 * on_comment_stream    email,
 *
 */
class Pyrosocial extends Public_Controller {

    /**
     * The ID of the user
     * @var int
     */
    private $user_id = 0;

    /**
     * Constructor method
     *
     * @return void
     */
    function __construct()
    {
        // Call the parent's constructor method
        parent::__construct();

        // Get the user ID, if it exists
        if (!$this->current_user)
        {
            redirect('users/login');
        }

        $this->profile_tbl = $this->db->dbprefix('profiles');
        $this->friends_tbl = $this->db->dbprefix('ps_friends');

        // Load the required classes
        $this->load->model('ps_wall_m');
        $this->load->helper('json');
        $this->load->library('form_validation');
        $this->template->append_css('module::styles.css');
    }

    public function index($offset = 0)
    {

        $this->db->select('ps_wall.*, users.email')
                ->select('IF(default_profiles.last_name = "", profiles.first_name, CONCAT(default_profiles.first_name, " ", profiles.last_name)) as full_name', FALSE)
                ->select('IFNULL(CONCAT(sl.liked, ":", sl.disliked), "0:0") AS mylikes, (sl.liked - sl.disliked) as total, ss.liked, ss.disliked', FALSE)
                ->join('users', 'users.id = ps_wall.user_id', 'left')
                ->join('ps_likes sl', 'sl.stream_id=ps_wall.id AND sl.author_id=' . $this->current_user->id, 'left')
                ->join('ps_likes ss', 'ss.stream_id=ps_wall.id AND ss.is_stats=1', 'left')
                ->join('profiles', 'profiles.user_id = ps_wall.user_id', 'left')
                ->where('ps_wall.stream_type !=', 'comment')
                ->order_by('ps_wall.created_on', 'desc');

        if ($this->input->is_ajax_request())
        {
            $this->data->streams = $this->db->get('ps_wall', 10, $offset)->result();
            $this->template->set_layout(FALSE);
            die($this->template->build('stream_tpl', $this->data, true));
        }
        else
        {
            $this->data->streams = $this->db->get('ps_wall', 10, $offset)->result();
        }

        // Render the view
        $this->template
                ->title('Streams')
                ->append_css('module::dependencies/screen.css')
                ->append_css('module::facebox.css')
                ->append_metadata('
					<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css" media="screen" rel="stylesheet" type="text/css" />
					<script type="text/javascript" src="http://www.google.com/jsapi"></script>
					<script type="text/javascript">google.load("jqueryui", "1.8.16");</script>
					<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true&libraries=places" ></script>')
                ->append_js('module::facebox.js')
                ->append_js('module::jquery.form.js')
                ->append_js('module::jquery.busy.js')
                ->append_js('module::jquery.notice.js')
                ->append_js('module::places.js')
                ->append_js('module::jquery.elastic.js')
                ->append_js('module::jquery.watermarkinput.js')
                ->build('streams_index', $this->data);
    }

    function scrape_url()
    {
        $resp = array();

        $url = prep_url($this->input->get('url'));
        $this->load->helper('dom');
        // Grab HTML From the URL
        $html = file_get_html($url);

        $images = $html->find('img');
        $css_images = $html->find('header, #header, .header, #logo, .logo');

        $imgs = array();
        foreach ($css_images as $cssi)
        {
            if (isset($cssi->attr['style']))
            {
                $re = '/url\(\s*[\'"]?(\S*\.(?:jpe?g|gif|png))[\'"]?\s*\)[^;}]*?no-repeat/i';
                if (preg_match($re, $cssi->attr['style'], $matches))
                {
                    list($w, $h) = getimagesize($matches[1]);
                    $resp['img'][] = array(
                        'src' => $matches[1],
                        'h' => $w,
                        'w' => $h,
                        'html' => '<img src="' . $matches[1] . '" height="' . $h . '" width="' . $w . '"><br />'
                    ); 
                }
            }
        }

        foreach ($html->find('title') as $element)
        {
            $resp['title'] = prep_title($element->plaintext);
        }
        foreach ($html->find('meta[name=description]') as $element)
        {
            $resp['desc'] = prep_title($element->content);
        }
        $img_links = array();
        foreach ($images as $e)
        {
            if (isset($e->height) && $e->width > 30 && $e->height > 30)
            {

                if (!in_array($e->src, $img_links))
                {
                    $img_links[] = $e->src;
                    if (!get_valid_url($e->src))
                    {
                        $purl = parse_url($url);
                        $url = $purl['scheme'] . '://' . $purl['host'];
                        if (strpos($e->src, $purl['host']) === false && substr($e->src, 0, 4) == 'http')
                            continue;
                        if ($e->src[0] == '/')
                        {
                            $e->src = $url . $e->src;
                        }
                        else
                        {
                            $purl = parse_url($url);
                            $url = $purl['scheme'] . '://' . $purl['host'];
                            $e->src = $url . '/' . $e->src;
                        }
                        if (!get_valid_url($e->src))
                            continue;
                    }
                    $resp['img'][] = array(
                        'src' => $e->src,
                        'h' => $e->height,
                        'w' => $e->width,
                        'html' => '<img src="' . $e->src . '" width="' . $e->height . '" width="' . $e->width . '"><br />'
                    );
                }
            }
        }
        die(json_encode($resp));
    }

    /*
     * @todo handle check-in maps entry
     *
     */

    function update()
    {
        $oe = array();
        $upload_resp = array();
        $body = $this->security->xss_clean(strip_tags($this->input->post('body')));
        $link_url = $this->input->post('link_url');

        $this->db->trans_begin();

        // Fetch oEmbed Videos data
        if ($this->input->post('oetype'))
        {
            $this->load->library('oembed');
            $oe = $this->oembed->call($this->input->post('oeprovider'), $this->input->post('oeurl'));
            if (!empty($oe))
            {
                $body .= '<h4>' . $oe->title . '</h4>';
                $body .= '<div class="stream-vid-img" style="clear:both;"><img src="' . $oe->thumbnail_url . '" alt="' . $oe->title . '" width="' . $oe->thumbnail_width . '" height="' . $oe->thumbnail_height . '" /></div>';
                $body .= '<div class="stream-vid-obj" style="display:none;">' . trim($oe->html) . '</div>';
            }
        }

        // Link Share
        if ($link_url)
        {
            $img_url = $this->input->post('link_img_url');
            if ($img_url)
            {
                $base_path = FCPATH . 'uploads/profiles/links/';

                // @todo Check if Directory exists
                if (!is_dir($base_path))
                    mkdir($base_path, 0777, TRUE);

                $image_name = basename($img_url);
                $abs_path = 'uploads/profiles/links/' . $image_name;
                $img_path = FCPATH . 'uploads/profiles/links/' . $image_name;
                file_put_contents($img_path, file_get_contents($img_url));

                $this->load->library('image_lib');
                $image = getimagesize($img_path);

                $ratio = $image['1'] / $image['0'];

                $config = array();
                $config['image_library'] = 'gd2';
                $config['source_image'] = $img_path;
                $config['new_image'] = $img_path;
                $config['maintain_ratio'] = TRUE;
                //$config['width'] = $this->settings->main_width;
                $config['width'] = 150;
                $config['height'] = $ratio * $config['width'];

                //initialize the image lib with this configuration
                $this->image_lib->initialize($config);
                //$this->image_lib->convert('jpg', TRUE);
                //resize the main image
                $this->image_lib->resize();
            }
            $link_url = prep_url($link_url);
            $body .= '<h4><a href="' . $link_url . '" target="_blank">' . $this->input->post('link_title') . '</a></h4>';
            $body .= '<p><img src="' . $abs_path . '" style="float:left;">' . $this->input->post('link_desc') . ' &nbsp; <a href="' . $link_url . '" target="_blank"> ..[read more]</a></p>';
        }
        
        //Static Street view (maps)
        //http://maps.googleapis.com/maps/api/streetview?size=600x300&location=56.960654,-2.201815&heading=250&fov=90&pitch=-10&sensor=false

        // Add Stream
        $stream_array = $this->ps_wall_m->add_update($this->current_user->id, $body, $this->input->post('stype'));

        // Set Stream ID
        $stream_id = $stream_array['id'];

        // Image Upload
        if (!empty($_FILES) || (isset($_FILES['link_image']) && !empty($_FILES['link_image']) && !isset($img_url)))
        {

            //load the necessary libraries
            $this->load->model('image_m');
            $this->load->library('image_lib');
            $this->load->library('upload');

            $upload_resp = $this->image_m->upload($this->current_user->id, $stream_id);
            if (is_array($upload_resp) && !array_key_exists('error', $upload_resp))
            {
                $stream_array['images'] = $upload_resp;

                $str = '';

                foreach ($upload_resp as $img)
                {
                    $str .= '<a href="uploads/profiles/main/' . $this->current_user->id . '/' . $img . '.jpg" class="" target="_blank" rel="popbox"><img src="uploads/profiles/thumbs/' . $this->current_user->id . '/' . $img . '.jpg"></a>';
                }

                $this->db->set('body', 'CONCAT(body, ' . $this->db->escape($str) . ')', FALSE);
                $this->db->where('id ', $stream_id);
                $this->db->update('ps_wall');
            }
            else
            {
                $this->db->trans_rollback();
                die(json_encode($upload_resp));
            }
        }

        // Insert oEmbed data
        if (!empty($oe))
        {
            $to_insert = array(
                'user_id' => $this->current_user->id,
                'stream_id' => $stream_id,
                'oe_type' => $oe->type,
                'provider_name' => $oe->provider_name,
                'provider_url' => $oe->provider_url,
                'title' => $oe->title,
                'author_name' => $oe->author_name,
                'author_url' => isset($oe->author_url) ? $oe->author_url : '',
                'media_main' => $oe->html,
                'media_thumb' => serialize(array('url' => $oe->thumbnail_url, 'width' => $oe->thumbnail_width, 'height' => $oe->thumbnail_height))
            );
            $this->db->insert('ps_media_oembed', $to_insert);
        }

        // Set response data
        $stream_array['full_name']       = $this->current_user->display_name;
        $stream_array['avatar']          = gravatar($this->current_user->email, 25, 'x', true);
        $stream_array['created_iso8601'] = standard_date('DATE_ISO8601', $stream_array['created_on']);
        $stream_array['created_on']      = timespan($stream_array['created_on']);

        if ($this->db->trans_status() === FALSE || array_key_exists('error', $upload_resp))
        {
            $this->db->trans_rollback();
            die(json_encode($upload_resp));
        }
        else
        {
            $this->db->trans_commit();
        }
        die(json_encode($stream_array));
    }

    function comment()
    {

        $stream_array = $this->ps_wall_m->add_comment($this->current_user->id, $this->input->post('stream_id'), $this->input->post('comment'));
        //die(json_encode(array('user_id'=>$this->current_user->id, 'user_name'=>$this->current_user->display_name, 'body'=>$this->input->post('body'))));
        //die('<p>'.$this->input->post('body').'</p><br /> update posted on <b>'.format_date(now()).'</b> by <a href="users/'.$this->current_user->id.'">'.$this->current_user->display_name.'</a>');
        //$stream_array['full_name'] = $this->current_user->display_name;
        //$stream_array['avatar'] = gravatar($this->current_user->email, 25,'x', true);
        //$stream_array['created_on'] = timespan($stream_array['created_on']);
        die(json_encode($stream_array));
    }

    function show_comments($stream_id, $limit = 20)
    {
        die(json_encode($this->ps_wall_m->get_comments($stream_id, $limit)));
    }

    function del_comment($comment_id)
    {
        $stream_id = $this->input->get_post('stream_id', FALSE);
        if ($this->input->get_post('stream_id', TRUE))
        {
            $out = $this->ps_wall_m->del_stream($comment_id);

            // get last three comments as array
            $comments = $this->db
                    ->select('ps_wall.*, users.username, users.email', FALSE)
                    ->join('users', 'users.id = ps_wall.user_id')
                    ->where('stream_type', 'comment')
                    ->where('object_id', $stream_id)
                    ->order_by('created_on', 'desc')
                    ->get('ps_wall', 3)
                    ->result_array();

            if (is_array($comments) && !empty($comments))
            {
                foreach ($comments as $comment)
                {
                    $prep_comments[$comment['id']] = array(
                        'user_id' => $comment['user_id'],
                        'username' => $comment['username'],
                        'email' => timespan($comment['email']),
                        'avatar' => gravatar($comment['email'], 25, 'x', true),
                        'body' => $comment['body'],
                        'created_on' => $comment['created_on'],
                        'created_iso860' => standard_date('DATE_ISO8601', $comment['created_on'])
                    );
                }
                $comments_db = serialize($prep_comments);
            }
            else
            {
                $comments_db = NULL;
            }
            // update stream db
            $this->db->where('id', $stream_id);
            $this->db->set('recent_comments', $comments_db);
            $this->db->set('num_comments', 'num_comments-1', FALSE);
            $this->db->update('ps_wall');

            die(json_encode(array('resp' => $out)));
        }
    }

    function del_stream($stream_id)
    {
        die(json_encode(array('resp' => $this->ps_wall_m->del_stream($stream_id))));
    }

    function new_like()
    {
        /** ----------------------------------------
          /**  Is this an AJAX request?
          /** ---------------------------------------- */
        $this->ajax = FALSE;
        if ($this->input->is_ajax_request())
        {
            $this->ajax = TRUE;
        }


        $IP = sprintf("%u", ip2long($this->input->ip_address()));
        $form_data = array();
        $form_data['allow_guests'] = FALSE;
        $form_data['allow_multiple'] = FALSE;
        $form_data['return'] = ($this->input->post('return') != FALSE) ? $this->input->post('return') : FALSE;

        /** ----------------------------------------
          /**  What action? Like or Dislike
          /** ---------------------------------------- */
        $form_data['liked'] = 0;
        $form_data['disliked'] = 0;

        if ($this->input->post('action') == 'like')
        {
            $form_data['liked'] = 1;
        }
        elseif ($this->input->post('action') == 'dislike')
        {
            $form_data['disliked'] = 1;
        }
        else
        {
            // Response: error: missing_action
        }

        /** ----------------------------------------
          /**  Allow Guests?
          /** ---------------------------------------- */
        /* if ($this->conf->allow_guests != 'yes' && isset($this->current_user->id))
          {
          echo 'Not authorized';
          //$this->new_like_response($this->lang->line('rating:error:not_authorized'));
          }
          elseif ($this->input->post('allow_guests') == 'yes')
          {
          $form_data['allow_guests'] = TRUE;
          }

          //**  Allow Multiple?
          if ($this->input->post('allow_multiple') == 'yes')
          {
          $form_data['allow_multiple'] = TRUE;
          } */

        /** ----------------------------------------
          /**  Valid ID's?
          /** ---------------------------------------- */
        $data = array('stream_id' => 0, 'comment_id' => 0, 'like_type' => 1);

        // Comment_ID?
        if ($this->input->post('comment_id') != FALSE && is_numeric($this->input->post('comment_id')) != FALSE)
        {
            $data['like_type'] = 2;
            $data['comment_id'] = $this->input->post('comment_id');

            // Grab stream_id/channel_id
            //$query = $this->db->select('stream_id')->from('exp_comments')->where('comment_id', $data['comment_id'])->limit(1)->get();
            // We need those!
            //$data['stream_id'] = $query->stream_id;
        }
        elseif ($this->input->post('stream_id') != FALSE && is_numeric($this->input->post('stream_id')) != FALSE)
        {
            // Entry then?
            $data['stream_id'] = $this->input->post('stream_id');
        }
        else
        {
            die('no valid ID\'s');
        }

        /** ----------------------------------------
          /**  Already Liked?
          /** ---------------------------------------- */
        $this->db->select('id');
        $this->db->from('ps_likes');
        $this->db->where('stream_id', $data['stream_id']);
        $this->db->where('comment_id', $data['comment_id']);
        $this->db->where('is_stats', 0);
        if ($this->current_user->id == 0)
        {
            $this->db->where('ip_address', $IP);
            $this->db->where('author_id', 0);
        }
        else
        {
            $this->db->where('author_id', $this->current_user->id);
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
        $data['author_id'] = $this->current_user->id;
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
			FROM ".$this->db->dbprefix('ps_likes')."
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
        $query = $this->db->query("SELECT id
                                    FROM ".$this->db->dbprefix('ps_likes')."
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
        die(json_encode($total));
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
                $REFERRED = site_url('');

            // We're done.
            $data = array('title' => $this->lang->line('thank_you'),
                'heading' => $this->lang->line('thank_you'),
                'content' => $this->lang->line('rating:success:new_like'),
                'redirect' => $REFERRED,
                'link' => array($REFERRED, $this->lang->line('back'))
            );

            //$this->output->show_message($data);
            echo json_encode($data);
            exit();
        }
    }

    function request()
    {
        // Setup default response
        $response = array(
            'status' => 'err',
            'data' => 'error requesting friendship'
        );

        // Set friend ID
        $friend_id = $this->input->get('fid');

        // No valid ID
        if (!$friend_id || is_numeric($friend_id) && $friend_id < 0)
        {
            $response['data'] = 'invalid user id';
        }

        // Requesting yourself?
        elseif ($friend_id == $this->current_user->id)
        {
            $response['data'] = 'no requesting yourself';
        }
        else
        {

            $friend_status = $this->friend_status($friend_id);
            // Waiting form confirmation
            if ($friend_status != false && $friend_status == 0)
            {
                $response['data'] = 'awaiting friend confirmation';
            }

            // Allready friends
            elseif ($friend_status == 1)
            {
                $response['data'] = 'they are already your friend';
            }
            elseif (!$friend_status && $this->db->insert('ps_friends', array('user_id' => $this->current_user->id, 'friend_id' => $friend_id, 'date_created' => now())))
            {
                $response = array(
                    'status' => 'ok',
                    'data' => 'friendship requested'
                );
            }
        }
        die(json_encode($response));
    }

    function confirm()
    {
        $response = array(
            'status' => 'err',
            'data' => 'error confirming friendship'
        );

        $friend_id = $this->input->get('fid');

        if (!$friend_id || is_numeric($friend_id) && $friend_id < 0)
        {
            $response['data'] = 'invalid friend id';
        }
        else
        {
            $friend_status = $this->friend_status($friend_id);


            // Allready friends
            if ($friend_status == 1)
            {
                $response['data'] = 'they are already your friend';
            }
            // Waiting form confirmation
            elseif ($friend_status == 0)
            {
                if ($this->db->update('ps_friends', array('is_confirmed' => 1)))
                {
                    // Update Friends count
                    $this->db
                            ->set('friends_count', 'friends_count + 1', FALSE)
                            ->where_in(array($friend_id, $this->current_user->id))
                            ->update('ps_friends_meta');

                    $response = array(
                        'status' => 'ok',
                        'data' => 'friendship confirmed'
                    );
                } // end update
            } // end confirmation
        }
        die(json_encode($response));
    }

    private function is_friend($friend_id)
    {
        return $this->db
                        ->where('((friend_id = ' . $friend_id . ' AND user_id = ' . $this->current_user->id . ') OR (user_id = ' . $friend_id . ' AND friend_id = ' . $this->current_user->id . '))')
                        ->where('is_confirmed', 1)
                        ->count_all_results('ps_friends');
    }

    private function is_waiting($friend_id)
    {
        return $this->db
                        ->where('((friend_id = ' . $friend_id . ' AND user_id = ' . $this->current_user->id . ') OR (user_id = ' . $friend_id . ' AND friend_id = ' . $this->current_user->id . '))')
                        ->where('is_confirmed', 0)
                        ->count_all_results('ps_friends');
    }

    private function friend_status($friend_id)
    {
        $r = $this->db
                        ->select('is_confirmed')
                        ->where('((friend_id = ' . $friend_id . ' AND user_id = ' . $this->current_user->id . ') OR (user_id = ' . $friend_id . ' AND friend_id = ' . $this->current_user->id . '))')
                        ->get('ps_friends')->row();
        return (!empty($r)) ? $r->is_confirmed : false;
    }

    function get_friend_user_ids($user_id)
    {
        $ids = $this->db
                        ->select('IFNULL(IF(friend_id = ' . $user_id . ', friend_id, user_id),0) AS friend_id')
                        ->where('is_confirmed', 1)
                        ->where('user_id', $user_id)
                        ->or_where('friend_id', $user_id)
                        ->join('profiles', 'profiles.user_id = users.id', 'left')
                        ->get()->result_array();
        //orderby datecreated
        return (is_array($ids)) ? array_values($ids) : array();
    }

    /**
     * Show the current user's profile
     *
     * @access public
     * @return void
     */
    /* public function index()
      {
      $this->view($this->current_user_id);
      } */

    /**
     * View a user profile based on the ID
     *
     * @param	mixed $id The Username or ID of the user
     * @return	void
     */
    public function view($id = NULL)
    {
        // No user? Show a 404 error. Easy way for now, instead should show a custom error message
        if (!$user = $this->ion_auth->get_user($id))
        {
            show_404();
        }

        foreach ($user as &$data)
        {
            $data = escape_tags($data);
        }

        // Render view
        $this->data->view_user = $user; //needs to be something other than $this->data->user or it conflicts with the current user
        $this->data->user_settings = $user;
        $this->template->build('profile/view', $this->data);
    }

    /**
     * Let's login, shall we?
     *
     * @return void
     */
    public function login()
    {
        // Check post and session for the redirect place
        $redirect_to = $this->input->post('redirect_to') ? $this->input->post('redirect_to') : $this->session->userdata('redirect_to');

        // Any idea where we are heading after login?
        if (!$_POST AND $args = func_get_args())
        {
            $this->session->set_userdata('redirect_to', $redirect_to = implode('/', $args));
        }

        // Get the user data
        $user_data = (object) array(
                    'email' => $this->input->post('email'),
                    'password' => $this->input->post('password')
        );

        $validation = array(
            array(
                'field' => 'email',
                'label' => lang('user_email_label'),
                'rules' => 'required|trim|callback__check_login'
            ),
            array(
                'field' => 'password',
                'label' => lang('user_password_label'),
                'rules' => 'required|min_length[6]|max_length[20]'
            ),
        );

        // Set the validation rules
        $this->form_validation->set_rules($validation);

        // If the validation worked, or the user is already logged in
        if ($this->form_validation->run() or $this->ion_auth->logged_in())
        {
            $this->session->set_flashdata('success', lang('user_logged_in'));

            // Kill the session
            $this->session->unset_userdata('redirect_to');

            // Deprecated.
            $this->hooks->_call_hook('post_user_login');

            // trigger a post login event for third party devs
            Events::trigger('post_user_login');

            redirect($redirect_to ? $redirect_to : '');
        }

        $this->template->build('login', array(
            'user_data' => $user_data,
            'redirect_to' => $redirect_to,
        ));
    }

    /**
     * Method to log the user out of the system
     *
     * @return void
     */
    public function logout()
    {
        // allow third party devs to do things right before the user leaves
        Events::trigger('pre_user_logout');

        $this->ion_auth->logout();
        $this->session->set_flashdata('success', lang('user_logged_out'));
        redirect('');
    }

    /**
     * Method to register a new user
     *
     * @return void
     */
    public function register()
    {
        // Validation rules
        $validation = array(
            array(
                'field' => 'first_name',
                'label' => lang('user_first_name'),
                'rules' => 'required'
            ),
            array(
                'field' => 'last_name',
                'label' => lang('user_last_name'),
                'rules' => ($this->settings->require_lastname ? 'required' : '')
            ),
            array(
                'field' => 'password',
                'label' => lang('user_password'),
                'rules' => 'required|min_length[6]|max_length[20]'
            ),
            array(
                'field' => 'confirm_password',
                'label' => lang('user_confirm_password'),
                'rules' => 'required|matches[password]',
            ),
            array(
                'field' => 'email',
                'label' => lang('user_email'),
                'rules' => 'required|valid_email|callback__email_check',
            ),
            array(
                'field' => 'confirm_email',
                'label' => lang('user_confirm_email'),
                'rules' => 'required|valid_email|matches[email]',
            ),
            array(
                'field' => 'username',
                'label' => lang('user_username'),
                'rules' => 'required|alpha_numeric|min_length[3]|max_length[20]|callback__username_check',
            ),
            array(
                'field' => 'display_name',
                'label' => lang('user_display_name'),
                'rules' => 'min_length[3]|max_length[50]',
            ),
        );

        // Set the validation rules
        $this->form_validation->set_rules($validation);

        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $username = $this->input->post('username');
        $user_data_array = array(
            'first_name' => $this->input->post('first_name'),
            'last_name' => $this->input->post('last_name'),
            'display_name' => $this->input->post('display_name'),
        );

        // Convert the array to an object
        $user_data = new stdClass();
        $user_data->first_name = $user_data_array['first_name'];
        $user_data->last_name = $user_data_array['last_name'];
        $user_data->display_name = $user_data_array['display_name'];
        $user_data->username = $username;
        $user_data->email = $email;
        $user_data->password = $password;
        $user_data->confirm_email = $this->input->post('confirm_email');

        if ($this->form_validation->run())
        {
            // Try to create the user
            if ($id = $this->ion_auth->register($username, $password, $email, $user_data_array))
            {
                // trigger an event for third party devs
                Events::trigger('post_user_register', $id);

                $this->session->set_flashdata(array('notice' => $this->ion_auth->messages()));
                redirect('users/activate');
            }

            // Can't create the user, show why
            else
            {
                $this->data->error_string = $this->ion_auth->errors();
            }
        }
        else
        {
            // Return the validation error
            $this->data->error_string = $this->form_validation->error_string();
        }

        foreach ($user_data as &$data)
        {
            $data = escape_tags($data);
        }

        $this->data->user_data = & $user_data;
        $this->template->title(lang('user_register_title'));
        $this->template->build('register', $this->data);
    }

    /**
     * Activate a user
     *
     * @param int $id The ID of the user
     * @param str $code The activation code
     * @return void
     */
    public function activate($id = 0, $code = NULL)
    {
        // Get info from email
        if ($this->input->post('email'))
        {
            $this->data->activate_user = $this->ion_auth->get_user_by_email($this->input->post('email'));
            $id = $this->data->activate_user->id;
        }

        $code = ($this->input->post('activation_code')) ? $this->input->post('activation_code') : $code;

        // If user has supplied both bits of information
        if ($id AND $code)
        {
            // Try to activate this user
            if ($this->ion_auth->activate($id, $code))
            {
                $this->session->set_flashdata('activated_email', $this->ion_auth->messages());

                // Deprecated
                $this->hooks->_call_hook('post_user_activation');

                // trigger an event for third party devs
                Events::trigger('post_user_activation', $id);

                redirect('users/activated');
            }
            else
            {
                $this->data->error_string = $this->ion_auth->errors();
            }
        }

        $this->template->title($this->lang->line('user_activate_account_title'));
        $this->template->set_breadcrumb($this->lang->line('user_activate_label'), 'users/activate');
        $this->template->build('activate', $this->data);
    }

    /**
     * Activated page
     *
     * @return void
     */
    public function activated()
    {
        //if they are logged in redirect them to the home page
        if ($this->ion_auth->logged_in())
        {
            redirect(base_url());
        }

        $this->data->activated_email = ($email = $this->session->flashdata('activated_email')) ? $email : '';

        $this->template->title($this->lang->line('user_activated_account_title'));
        $this->template->build('activated', $this->data);
    }

    /**
     * Reset a user's password
     *
     * @return void
     */
    public function reset_pass($code = FALSE)
    {
        //if user is logged in they don't need to be here. and should use profile options
        if ($this->ion_auth->logged_in())
        {
            $this->session->set_flashdata('error', $this->lang->line('user_already_logged_in'));
            redirect('my-profile');
        }

        if ($this->input->post('btnSubmit'))
        {
            $uname = $this->input->post('user_name');
            $email = $this->input->post('email');

            $user_meta = $this->ion_auth->get_user_by_email($email);

            //supplied username match the email also given?  if yes keep going..
            if ($user_meta && $user_meta->username == $uname)
            {
                $new_password = $this->ion_auth->forgotten_password($email);

                if ($new_password)
                {
                    //set success message
                    $this->data->success_string = lang('forgot_password_successful');
                }
                else
                {
                    // Set an error message explaining the reset failed
                    $this->data->error_string = $this->ion_auth->errors();
                }
            }
            else
            {
                //wrong username / email combination
                $this->data->error_string = $this->lang->line('user_forgot_incorrect');
            }
        }

        //code is supplied in url so lets try to reset the password
        if ($code)
        {
            //verify reset_code against code stored in db
            $reset = $this->ion_auth->forgotten_password_complete($code);

            //did the password reset?
            if ($reset)
            {
                redirect('users/reset_complete');
            }
            else
            {
                //nope, set error message
                $this->data->error_string = $this->ion_auth->errors();
            }
        }

        $this->template->title($this->lang->line('user_reset_password_title'));
        $this->template->build('reset_pass', $this->data);
    }

    /**
     * Password reset is finished
     *
     * @param string $code Optional parameter the reset_password_code
     * @return void
     */
    public function reset_complete()
    {
        //if user is logged in they don't need to be here. and should use profile options
        if ($this->ion_auth->logged_in())
        {
            $this->session->set_flashdata('error', $this->lang->line('user_already_logged_in'));
            redirect('my-profile');
        }

        //set page title
        $this->template->title($this->lang->line('user_password_reset_title'));

        //build and render the output
        $this->template->build('reset_pass_complete', $this->data);
    }

    /**
     *
     */
    public function edit()
    {


        // Got login?
        if (!$this->ion_auth->logged_in())
        {
            redirect('users/login');
        }

        // Validation rules
        $this->validation_rules = array(
            array(
                'field' => 'first_name',
                'label' => lang('user_first_name'),
                'rules' => 'xss_clean|required'
            ),
            array(
                'field' => 'last_name',
                'label' => lang('user_last_name'),
                'rules' => 'xss_clean' . ($this->settings->require_lastname ? '|required' : '')
            ),
            array(
                'field' => 'password',
                'label' => lang('user_password'),
                'rules' => 'xss_clean|min_length[6]|max_length[20]'
            ),
            array(
                'field' => 'confirm_password',
                'label' => lang('user_confirm_password'),
                'rules' => 'xss_clean|' . ($this->input->post('password') ? 'required|' : '') . 'matches[password]'
            ),
            array(
                'field' => 'email',
                'label' => lang('user_email'),
                'rules' => 'xss_clean|valid_email'
            ),
            array(
                'field' => 'confirm_email',
                'label' => lang('user_confirm_email'),
                'rules' => 'xss_clean|valid_email|matches[email]'
            ),
            array(
                'field' => 'lang',
                'label' => lang('user_lang'),
                'rules' => 'xss_clean|alpha|max_length[2]'
            ),
            array(
                'field' => 'display_name',
                'label' => lang('profile_display'),
                'rules' => 'xss_clean|trim|required'
            ),
            // More fields
            array(
                'field' => 'gender',
                'label' => lang('profile_gender'),
                'rules' => 'xss_clean|trim|max_length[1]'
            ),
            array(
                'field' => 'dob_day',
                'label' => lang('profile_dob_day'),
                'rules' => 'xss_clean|trim|numeric|max_length[2]|required'
            ),
            array(
                'field' => 'dob_month',
                'label' => lang('profile_dob_month'),
                'rules' => 'xss_clean|trim|numeric|max_length[2]|required'
            ),
            array(
                'field' => 'dob_year',
                'label' => lang('profile_dob_year'),
                'rules' => 'xss_clean|trim|numeric|max_length[4]|required'
            ),
            array(
                'field' => 'bio',
                'label' => lang('profile_bio'),
                'rules' => 'xss_clean|trim|max_length[1000]'
            ),
            array(
                'field' => 'phone',
                'label' => lang('profile_phone'),
                'rules' => 'xss_clean|trim|alpha_numeric|max_length[20]'
            ),
            array(
                'field' => 'mobile',
                'label' => lang('profile_mobile'),
                'rules' => 'xss_clean|trim|alpha_numeric|max_length[20]'
            ),
            array(
                'field' => 'address_line1',
                'label' => lang('profile_address_line1'),
                'rules' => 'xss_clean|trim'
            ),
            array(
                'field' => 'address_line2',
                'label' => lang('profile_address_line2'),
                'rules' => 'xss_clean|trim'
            ),
            array(
                'field' => 'address_line3',
                'label' => lang('profile_address_line3'),
                'rules' => 'xss_clean|trim'
            ),
            array(
                'field' => 'postcode',
                'label' => lang('profile_postcode'),
                'rules' => 'xss_clean|trim|max_length[20]'
            ),
            array(
                'field' => 'website',
                'label' => lang('profile_website'),
                'rules' => 'xss_clean|trim|max_length[255]'
            ),
            array(
                'field' => 'msn_handle',
                'label' => lang('profile_msn_handle'),
                'rules' => 'xss_clean|trim|valid_email'
            ),
            array(
                'field' => 'aim_handle',
                'label' => lang('profile_aim_handle'),
                'rules' => 'xss_clean|trim|alpha_numeric'
            ),
            array(
                'field' => 'yim_handle',
                'label' => lang('profile_yim_handle'),
                'rules' => 'xss_clean|trim|alpha_numeric'
            ),
            array(
                'field' => 'gtalk_handle',
                'label' => lang('profile_gtalk_handle'),
                'rules' => 'xss_clean|trim|valid_email'
            ),
            array(
                'field' => 'gravatar',
                'label' => lang('profile_gravatar'),
                'rules' => 'xss_clean|trim|valid_email'
            )
        );



        // Set the validation rules
        $this->form_validation->set_rules($this->validation_rules);

        // Get settings for this user
        $user_settings = $this->ion_auth->get_user();


        // Get the user ID, if it exists
        if ($user_settings)
        {
            $this->current_user_id = $user_settings->id;
        }

        // If this user already has a profile, use their data if nothing in post array
        if ($user_settings)
        {
            $user_settings->dob_day = date('j', $user_settings->dob);
            $user_settings->dob_month = date('n', $user_settings->dob);
            $user_settings->dob_year = date('Y', $user_settings->dob);
        }

        // Settings valid?
        if ($this->form_validation->run())
        {

            // Loop through each POST item and add it to the secure_post array
            $secure_post = $this->input->post();

            // Set the full date of birth
            $secure_post['dob'] = mktime(0, 0, 0, $secure_post['dob_month'], $secure_post['dob_day'], $secure_post['dob_year']);

            // Unset the data that's no longer required
            unset($secure_post['dob_month']);
            unset($secure_post['dob_day']);
            unset($secure_post['dob_year']);

            // Set the language for this user
            if ($secure_post['lang'])
            {
                $this->ion_auth->set_lang($secure_post['lang']);
                $_SESSION['lang_code'] = $secure_post['lang'];
            }
            else
            {
                unset($secure_post['lang']);
            }

            // If password is being changed (and matches)
            if (!$secure_post['password'])
            {
                unset($secure_post['password']);
            }
            // We don't need this anymore
            unset($secure_post['confirm_password']);

            // Set the time of update
            $secure_post['updated_on'] = now();

            if ($this->ion_auth->update_user($this->current_user_id, $secure_post) !== FALSE)
            {
                Events::trigger('post_user_update');

                $this->session->set_flashdata('success', $this->ion_auth->messages());
            }
            else
            {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
            }

            redirect('edit-settings');
        }
        else
        {
            // Loop through each validation rule
            foreach ($this->validation_rules as $rule)
            {
                if ($this->input->post($rule['field']) !== FALSE)
                {
                    $user_settings->{$rule['field']} = set_value($rule['field']);
                }
            }
        }

        // Take care of the {} braces in the content
        $escape_fields = array(
            'bio', 'address_line1', 'address_line2', 'address_line3', 'postcode',
            'website', 'msn_handle', 'gtalk_handle', 'gravatar'
        );
        foreach ($escape_fields as $field)
        {
            $user_settings->{$field} = escape_tags($user_settings->{$field});
        }

        // Fix the months
        $this->lang->load('calendar');
        $month_names = array(
            lang('cal_january'),
            lang('cal_february'),
            lang('cal_march'),
            lang('cal_april'),
            lang('cal_mayl'),
            lang('cal_june'),
            lang('cal_july'),
            lang('cal_august'),
            lang('cal_september'),
            lang('cal_october'),
            lang('cal_november'),
            lang('cal_december'),
        );
        $this->data->days = array_combine($days = range(1, 31), $days);
        $this->data->months = array_combine($months = range(1, 12), $month_names);
        $this->data->years = array_combine($years = range(date('Y'), date('Y') - 120), $years);

        // Format languages for the dropdown box
        $this->data->languages = array();
        foreach ($this->config->item('supported_languages') as $lang_code => $lang)
        {
            $this->data->languages[$lang_code] = $lang['name'];
        }

        $this->data->user_settings = & $user_settings;

        // Render the view
        $this->template->build('profile/edit', $this->data);
    }

    /**
     * Authenticate to Twitter with oAuth
     *
     * @author Ben Edmunds
     * @return boolean
     */
    public function twitter()
    {
        $this->load->library('twitter/twitter');

        // Try to authenticate
        $auth = $this->twitter->oauth($this->settings->item('twitter_consumer_key'), $this->settings->item('twitter_consumer_key_secret'), $this->current_user->twitter_access_token, $this->current_user->twitter_access_token_secret);

        if ($auth != 1 && $this->settings->item('twitter_consumer_key') && $this->settings->item('twitter_consumer_key_secret'))
        {
            if (isset($auth['access_token']) && !empty($auth['access_token']) && isset($auth['access_token_secret']) && !empty($auth['access_token_secret']))
            {
                // Save the access tokens to the users profile
                $this->ion_auth->update_user($this->current_user->id, array(
                    'twitter_access_token' => $auth['access_token'],
                    'twitter_access_token_secret' => $auth['access_token_secret'],
                ));

                if (isset($_GET['oauth_token']))
                {
                    $parts = explode('?', $_SERVER['REQUEST_URI']);

                    // redirect the user since we've saved their info
                    redirect($parts[0]);
                }
            }
        }
        elseif ($auth == 1)
        {
            redirect('edit-settings', 'refresh');
        }
    }

    /**
     * Callback method used during login
     *
     * @param str $email The Email address
     * @return bool
     */
    public function _check_login($email)
    {
        $remember = FALSE;
        if ($this->input->post('remember') == 1)
        {
            $remember = TRUE;
        }

        if ($this->ion_auth->login($email, $this->input->post('password'), $remember))
        {
            return TRUE;
        }

        $this->form_validation->set_message('_check_login', $this->ion_auth->errors());
        return FALSE;
    }

    /**
     * Username check
     *
     * @return bool
     * @author Ben Edmunds
     */
    public function _username_check($username)
    {
        if ($this->ion_auth->username_check($username))
        {
            $this->form_validation->set_message('_username_check', $this->lang->line('user_error_username'));
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

    /**
     * Email check
     *
     * @return bool
     * @author Ben Edmunds
     */
    public function _email_check($email)
    {
        if ($this->ion_auth->email_check($email))
        {
            $this->form_validation->set_message('_email_check', $this->lang->line('user_error_email'));
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

}
