<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author 		Ryun Shofner
 * @package		Profiles
 * @subpackage	Friends Module
 * @since		v0.1
 *
 */
class ps_friends_m extends MY_Model {

    function __construct()
    {
        parent::__construct();
        $this->tbl = array();
        $this->tbl = array('u' => $this->db->dbprefix('users'),
                           'p' => $this->db->dbprefix('profiles'),
                           'g' => $this->db->dbprefix('groups'),
                           'f' => $this->db->dbprefix('ps_friends'));
    }

    function get_friends($filter = '', $limit = 20, $offset = false)
    {
        $sql = 'u.id, u.created_on, u.last_login,'.
               'IF(p.last_name = NULL, p.first_name, CONCAT(p.first_name, " ", p.last_name)) as full_name,'.
               'IFNULL(IF(f.friend_id = ' . $this->current_user->id . ' or f.user_id = ' . $this->current_user->id . ',1,0 ),0) AS is_friend, f.friend_id, f.user_id, f.is_confirmed '.
               'FROM '.$this->tbl['u'].' u '.
               'LEFT JOIN '.$this->tbl['g'].' g ON g.id = u.group_id '.
               'LEFT JOIN '.$this->tbl['p'].' p ON p.user_id = u.id ';
           // ->join('profiles', 'profiles.user_id = users.id', 'left');

        switch ($filter)
        {
            // Friends ( requested / awaiting confirmation)
            case 'awaiting':
                $sql .= 'LEFT JOIN '.$this->tbl['f'].' f ON f.friend_id = users.id
                         WHERE f.user_id = '. $this->current_user->id.
                        ' AND f.is_confirmed = 0 ';
                break;

            // Friends (requests)
            case 'requests':
                $sql .= 'LEFT JOIN '.$this->tbl['f'].' f ON f.user_id = users.id
                         WHERE f.friend_id = '. $this->current_user->id.
                        ' AND f.is_confirmed = 0 ';
                break;

            // Friends (confirmed)
            case 'friends':
                $sql .= 'LEFT JOIN '.$this->tbl['f'].' f ON f.user_id = u.id OR f.friend_id = u.id
                         WHERE u.id != '. $this->current_user->id.
                        ' AND f.is_confirmed = 1';
                        ' AND (f.user_id = ' . $this->current_user->id . ' OR f.friend_id = ' . $this->current_user->id . ') ';
                break;

            default:
                $sql .= 'LEFT JOIN '.$this->tbl['f'].' f ON f.user_id = u.id OR f.friend_id = u.id
                         WHERE u.id != '. $this->current_user->id.' ';
        }

        $sql .= 'GROUP BY u.id '.
                'ORDER BY u.id DESC '.
                'LIMIT '.$limit . ' ';

        if ($offset)
            $sql .= ','.$offset;
        return $this->db->query($sql)->result();
    }

    function is_friend($friend_id)
    {
        return $this->db
                        ->where('((friend_id = ' . $friend_id . ' AND user_id = ' . $this->current_user->id . ') OR (user_id = ' . $friend_id . ' AND friend_id = ' . $this->current_user->id . '))')
                        ->where('is_confirmed', 1)
                        ->count_all_results('ps_friends');
    }

    function is_waiting($friend_id)
    {
        return $this->db
                        ->where('((friend_id = ' . $friend_id . ' AND user_id = ' . $this->current_user->id . ') OR (user_id = ' . $friend_id . ' AND friend_id = ' . $this->current_user->id . '))')
                        ->where('is_confirmed', 0)
                        ->count_all_results('ps_friends');
    }

    function friend_status($friend_id)
    {
        $r = $this->db
                        ->select('is_confirmed')
                        ->where('((friend_id = ' . $friend_id . ' AND user_id = ' . $this->current_user->id . ') OR (user_id = ' . $friend_id . ' AND friend_id = ' . $this->current_user->id . '))')
                        ->get('ps_friends')->row();
        return (!empty($r)) ? $r->is_confirmed : false;
    }

    function update_friend_count()
    {
        $this->db
                ->set('friends_count', 'friends_count + 1', FALSE)
                ->where_in(array($friend_id, $this->current_user->id))
                ->update('ps_friends_meta');
    }

    function cancel_friendship($friend_id)
    {
        return $this->db
                        ->where('(ps_friends.user_id = ' . $this->current_user->id . ' AND ps_friends.friend_id = ' . $friend_id . ')')
                        ->or_where('(ps_friends.friend_id = ' . $this->current_user->id . ' AND ps_friends.user_id = ' . $friend_id . ')')
                        ->delete('ps_friends');
    }

    function accept_friendship($friend_id)
    {
        return $this->db->where('user_id', $friend_id)->where('friend_id', $this->current_user->id)->update('ps_friends', array('is_confirmed' => 1));
    }

    function request_friendship($friend_id)
    {
        return $this->db->insert('ps_friends', array('user_id' => $this->current_user->id, 'friend_id' => $friend_id, 'date_created' => now()));
    }

    function reject_friendship($friend_id)
    {
        return $this->db->delete('ps_friends', array('friend_id' => $this->current_user->id, 'user_id' => $friend_id));
        //return $wpdb->query( $this->db( "DELETE FROM {$bp->friends->table_name} WHERE id = %d AND friend_user_id = %d", $friendship_id, $bp->loggedin_user->id ) );
    }

    private function friend_requests($friend_id)
    {
        $r = $this->db
                        ->select('is_confirmed')
                        ->where('friend_id', $friend_id)
                        ->where('is_confirmed', 0)
                        ->get('ps_friends')->result();
        return (!empty($r)) ? $r->is_confirmed : false;
    }

    function get_user($id, $extra = false)
    {
        if ($extra)
            $this->db->select($extra);

        return $this->db->select('users.email')
                        ->select('IF(p.last_name = "", p.first_name, CONCAT(p.first_name, " ", p.last_name)) as full_name', FALSE)
                        ->join($this->tbl['p'].' p', 'p.user_id = users.id', 'left')
                        ->where('users.id', $id)
                        ->get('users', 1)->row();
    }

    function get_friendship_request_user_ids($user_id)
    {

        $ids = $this->db->select("user_id")
                ->where("is_confirmed", 0)
                ->where("friend_id", $user_id)
                ->get('ps_friends')
                ->result_array();

        if ($ids)
            return array_values($ids);
        else
            return false;
    }

    function get_friend_user_ids($user_id, $friend_requests_only = false)
    {
        if ($friend_requests_only)
        {
            $this->db
                    ->where("is_confirmed", 0)
                    ->where("friend_id", $user_id);
        }
        else
        {
            $this->db
                    ->where("is_confirmed", 1)
                    ->where('user_id', $user_id)
                    ->or_where('friend_id', $user_id);
        }
        $ids = $this->db->select('IFNULL(IF(friend_id = ' . $user_id . ', user_id, friend_id),0) AS friend_id', FALSE)->get('ps_friends')->result_array();
        if ($ids)
            return array_values($ids);
        else
            return false;
    }

    function update_meta($key, $value, $user_id = 0, $autoinc = false)
    {
        if (!$user_id)
        {
            $user_id = $this->current_user->id;
        }

        if (is_object($value) || is_array($value))
        {
            $value = serialize($value);
        }



        if (($val = $this->is_meta($user_id, $key)))
        {
            $this->db
                    ->where('user_id', $user_id)
                    ->where('meta_key', $key);

            if ($autoinc === '-')
            {
                $newval = $val->meta_value - $value;
                return $this->db->update('ps_friends_meta', array('meta_value' => ($newval < 0) ? 0 : $newval));
            }
            elseif ($autoinc === '+')
            {
                return $this->db->update('ps_friends_meta', array('meta_value' => $val->meta_value + $value));
            }
            else
            {
                return $this->db->update('ps_friends_meta', array('meta_value' => $value));
            }
        }
        else
        {
            return $this->db->insert('ps_friends_meta', array('user_id' => $user_id, 'meta_key' => $key, 'meta_value' => $value));
        }
    }

    /* fix */

    function is_meta($object_id, $meta_key)
    {
        return $this->db
                        ->select('meta_value')
                        ->where('user_id', $object_id)
                        ->where('meta_key', $meta_key)
                        ->get('ps_friends_meta')->row();
    }

    /*
      function get_friend_user_ids( $user_id )
      {
      $friend_ids = $this->db
      ->select('IFNULL(IF(friend_id = '.$user_id.', user_id, friend_id),0) AS friend_id, is_confirmed')
      ->where('user_id',$user_id)
      ->or_where('friend_id', $user_id)
      //orderby datecreated
      ->get('friends')->result_array();
      }
     */
}