<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author      Ryun Shofner
 * @package     Profiles
 * @subpackage  Relationships Model
 * @since       v0.1
 *
 */

class relationships_m extends MY_Model
{    
    $_table = 'ps_relationships';

    function __construct()
    {
        parent::__construct();
    }
    
    function exists($data)
    {
        return ($this->count_by($data) > 0) ? TRUE : FALSE;
    }

    function get_user($user_id, $module, $type)
    {    
        $this->db->select('r.*, u.username, u.gravatar, u.name, u.image')
                ->from('ps_relationships r')
                ->join('users u', 'u.user_id = r.owner_id')
                ->where(array(
                    'r.user_id' => $user_id,
                    'r.module'  => $module,
                    'r.type'    => $type,
                    'r.status'  => 'Y'
                ));
        return $this->db->get()->result();        
    }
    
    function get_owner($owner_id, $module, $type)
    {    
 		$this->db->select('r.*, u.username, u.gravatar, u.name, u.image')
                ->from('ps_relationships r')
                ->join('users u', 'u.user_id = r.user_id')
                ->where(array(
                    'r.owner_id' => $owner_id,
                    'r.module'  => $module,
                    'r.type'    => $type,
                    'r.status'  => 'Y'
                ));
 		return $this->db->get()->result();	      
    }
        
    function add_relationship($data)
    {
 		$data['created_at'] = now();
 		$data['updated_at'] = now();
		
        $data['id'] = $this->insert($data);

		return (object)$data;
        //return $this->db->get_where('relationships', array('relationship_id' => $relationship_id))->row();	
    } 

    function update_relationship($id, $data)
    {
 		$data['updated_at'] = now();
		return $this->update($id, $data);
    }

    function delete_relationship($id)
    {
    	return $this->delete($id);
    }
    
}