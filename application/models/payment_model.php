<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Payment_model extends CI_Model {
	var $payment_table = 'payment';
	var $banque_table = 'bank';
	var $user_table = 'users';

	public function __construct() {
	    $this->load->database();
		$this->load->model('Resa_model');
	}
		
	function create($payment) {
		//Insert payment into the database
		if(!$this->db->insert($this->payment_table, $payment)) { 
			return FALSE;						
		}
		return TRUE;
	}
	
	/**
	 * Update a payment
	 */
	function update($id, $payment) {
		$this->db->where('id', $id);
		$this->db->update($this->payment_table, $payment); 
	}
	

	public function get_payment($id = FALSE) {
		if ($id === FALSE) {
			$query = $this->db->get($this->payment_table);
			return $query->result_array();
		}
	
		$query = $this->db->get_where($this->payment_table, array('id' => $id));
		return $query->row_array();
	}
	
	public function get_payment_where($where) {
		$query = $this->db->get_where($this->payment_table, $where);
		return $query->result_array();
	}

	function get_total_payment_where($where) {
		$this->db->select_sum('amount');
		$this->db->from($this->payment_table);
		$this->db->where($where);
		$query = $this->db->get();
		return $query->row_array();
	}
	
	public function get_full_payment_where($where) {
		$this->db->select('*');
		$this->db->from($this->payment_table);
		$this->db->join('bank', 'bank.id = payment.bank_id');
		$this->db->join('users', 'users.id = payment.user_id');
		$this->db->where($where);
		
		$query = $this->db->get();
		return $query->result_array();
	}
	
	function delete($id = FALSE) {
		if ($id === FALSE) {
			return FALSE;
		}
		$this->db->delete($this->payment_table, array('id' => $id));
		if ($this->db->affected_rows() > 0)
			return TRUE;
		return FALSE;

	}
	
	// Util function /////////////////////////////////////
	
	function setPaymentFromPostData($post) {
		$payment["user_id"] = $post['user_id'];
		$payment["amount"] = strtr($post['amount'], ",", ".");
		$payment['month_paided'] = $post['month_paided'];
		if (isset($post['payment_date'])) {
		    $payment["payment_date"] = $post['payment_date'];
		} else {
		    $payment["payment_date"] = date('Y-m-d');
		}
		$payment["type"] = $post['type'];
		$payment["bank_id"] = $post['bank'];
		if (isset($post['chequeNum']) && $post['chequeNum']!='') {
			$payment["cheque_Num"] = $post['chequeNum'];
		} else {
			$payment["cheque_Num"] = 0;
		}
		if (isset($post['status'])) {
			$payment["status"] = $post['status'];
			if ($post['status']==3 && $post['previousStatus']!=3 ) {     //validation
			    $payment["validation_date"] = date('Y-m-d');
			}
		} else {
			$payment["status"] = 1;
		}		
		return $payment;
	}
	/*
	 * get last valid payment debt and month
	*/
	public function getLastValidDebt($userId, $year=null, $month=null) {
		if (!$year) $year=date("Y");
		if (!$month) $month=date("n");
		$monthlyDebt=array();
		$total=0;
		
        for ($i = $month; $i >= ($month-13); $i--) {
            if ($i>=1) {
                $curMonth=$i;
				$curYear=$year;
            } else {
                $curMonth=12+$i;
                $curYear=$year-1;
            }
			$where = array('user_id'=>$userId, 'status' => 3, 'YEAR(month_paided)' => $curYear, 'MONTH(month_paided)' => $curMonth);
			$payment=$this->get_payment_where($where);
			if ($payment) {
				//print_r($payment);
				$total+=$payment[0]["debt"];
				$ret=array('debt' => round($payment[0]["debt"],2), 'total' => round($total,2), 'monthlyDebt' => $monthlyDebt);
				//print_r($ret);
				return $ret;
			} else {
				$cost = $this->Resa_model->getResaSummary($curYear, $curMonth, $userId);
				//print_r($i);
				//print_r($cost['sum']);
				$monthlyDebt[] = round($cost['sum']['total'],2);
				$total += round($cost['sum']['total'],2);
			}
		}
		return array('debt' => $total, 'total' => $total, 'monthlyDebt' => $monthlyDebt);;
	}

	
}
?>
