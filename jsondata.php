<?php
header("Content-type:application/json; charset=UTF-8");    
header("Cache-Control: no-store, no-cache, must-revalidate");         
header("Cache-Control: post-check=0, pre-check=0", false); 
require_once("inc/dbconnect.php");
////////////////////////////// BEGIN GET LISTITEM //////////////////////////////////////
////////// ส่วนของการคิวรี่แสดงรายการทั้งหมด พร้อมการแบ่งหน้า
if(isset($_POST['action']) && $_POST['action']=="list"){
	
	
	$per_page = 3;  // ตัวแปรเก็บจำนวนรายการที่่ต้องการแสดงในแต่ละหน้า (เปลี่ยนค่าได้)
	
	// รายการต่อไปนี้ไม่ต้องเปลี่ยนค่า
	$total = 0; // ตัวแปรจำนวนข้อูลทั้งหมด
	$start_page = 0; // ตัวแปรเก็บตัวกำหนด offset ใน LIMIT คำส่ัง sql
	$cur_page = 1; // ตำแปรเก็บหน้าปัจจุบัน
	$chk_page = 0;  // ตำแปรเก็บหน้าตรวจสอบ
	
	// คำสั่ง sql เปลี่ยนค่าตามต้องการ
	$sql = "
	 SELECT * FROM tbl_members WHERE 1
	";
	
	// รายการต่อไปนี้ไม่ต้องเปลี่ยนค่า
	$result = $mysqli->query($sql);
	if($result && $result->num_rows > 0){ // มีรายการข้อมูล
		$total = $result->num_rows; // นับจำนวนรายการทั้งหมดแล้วเก็บในตัวแปร $total
	}
	// มีการส่งหน้าที่ต้องการแสดงมา
	if(isset($_POST['page']) && $_POST['page']>0){
		// เปลี่ยนค่าตัวแปรตามเงื่อนไขค่าที่ส่งมา
		$chk_page = $_POST['page'];
		$cur_page = $_POST['page']+1;
		$start_page = $_POST['page']*$per_page;
	}
	$sql.="
		LIMIT ".$start_page.",".$per_page."
	";
	$i=0;
	$result = $mysqli->query($sql);
	if($result && $result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$i++;
			// เปลี่ยนค่าตามต้องการ item_id ในที่นี้จะเป็นเลขลำดับ ไม่จำเป็นต้องแก้ไข
			// ส่วนค่าอื่นๆ เปลี่ยนไปตามฟิลด์หรือรูปแบบข้อมูลที่ต้องการ
			$json_data['data'][] = array(
				"item_id" 			=> ($chk_page*$per_page)+$i,
				"mem_id" 				=> $row['member_id'],
				"mem_user" 		=> $row['member_username'],
				"mem_pass" 		=> $row['member_password'],
				"mem_fullname" 	=> $row['member_fullname'],						
				"mem_type" 			=> $row['member_type']
			);
		}
		// รายการต่อไปนี้ไม่ต้องเปลี่ยนค่า  ใช้สำหรับส่งค่าไปใช้ในการกำหนดหน้าข้อมูลที่แสดง
		if($result->num_rows > 0){
			$json_data['curpage'] = $cur_page;
			$json_data['perpage'] = $per_page;
			$json_data['total'] = 	$total;
			$json_data['allpage'] = ceil($total/$per_page); 
		}
	}
	// ถ้ามีข้อมูล จะด้ตัวแปร $json_data  สำหรับสร้างเป็น json data
}
////////////////////////////// END GET LISTITEM //////////////////////////////////////



////////////////////////////// BEGIN GET TEM //////////////////////////////////////
//////// ส่วนของการดึงข้อมูลแต่ละรายการ อ้างอิงจาก ค่าที่ส่งมาเช็ค ปกติใช้ primary 
if(isset($_POST['action']) && $_POST['action']=="item"){
	/// ในที่นี้เช็คจาก chk_user_id เปลี่ยนค่าและคำสั่ง sql ตามที่นำไปใช้งาน
	if(isset($_POST['chk_user_id']) && $_POST['chk_user_id']!=""){
		$sql = "
		 SELECT * FROM tbl_members WHERE member_id='".$_POST['chk_user_id']."'
		";
		$result = $mysqli->query($sql);
		if($result && $result->num_rows > 0){
				$row = $result->fetch_assoc();
				$json_data['data'][] = array(
					"mem_id" 				=> $row['member_id'],
					"mem_user" 		=> $row['member_username'],
					"mem_pass" 		=> $row['member_password'],
					"mem_fullname" 	=> $row['member_fullname'],						
					"mem_type" 			=> $row['member_type']
				);
		}
	}
	// ค่าข้องข้อมูลส่วนนี้จะถูกดึงไป ใช้แสดงใน form เพื่อแก้ไขข้อมูล
	// ถ้ามีข้อมูล จะด้ตัวแปร $json_data  สำหรับสร้างเป็น json data
}
////////////////////////////// END GET TEM //////////////////////////////////////



////////////////////////////// BEGIN DELETE //////////////////////////////////////
////////////////////////////// ส่วนของการลบข้อมูล
if(isset($_POST['action']) && $_POST['action']=="delete"){
	$_error_msg = null;
	$_success_msg = null;		
	if(isset($_POST['del_user_id']) && $_POST['del_user_id']!=""){
		$sql = "
		 DELETE FROM tbl_members WHERE member_id='".$_POST['del_user_id']."'
		";
		$result = $mysqli->query($sql);
		if($result && $mysqli->affected_rows>0){
			$_success_msg = "Delete user data successful!";
		}else{
			$_error_msg = "Eror, please try again!";
		}
	}else{
		$_error_msg = "Eror, please try again!";
	}
	$json_data[]=array(  
		"success" => $_success_msg,
		"error" => $_error_msg
	);     	
	// จะได้ตัวแปร $json_data  สำหรับสร้างเป็น json data	
}
////////////////////////////// BEGIN DELETE //////////////////////////////////////



////////////////////////////// BEGIN EDIT  //////////////////////////////////////
//////////////////////// ส่วนสำรหับส่ง่ค่ามาทำการแก้ไข หรืออัพเดทข้อมูล
if(isset($_POST['action']) && $_POST['action']=="edit"){
	$_error_msg = null;
	$_success_msg = null;	
	// อัพเดทข้อมูลโดยอ้างอิงจาก primary ในที่นี้ส่ง userid 
	if(isset($_POST['userid']) && $_POST['userid']!=""){			
		$sql = "
		UPDATE tbl_members SET 
		member_username='".$_POST['username']."',
		member_password='".$_POST['password']."',
		member_fullname='".$_POST['fullname']."',
		member_type='".$_POST['usertype']."'
		WHERE member_id=".$_POST['userid']."		
		";
		$result = $mysqli->query($sql);
		if($result){
			if($mysqli->affected_rows>0){
				$_success_msg = "Change user data successful!";
			}else{
				$_success_msg = "Update user successful!";
			}			
		}else{
			$_error_msg = "Eror, please try again!";
		}
	}
	$json_data[]=array(  
		"success" => $_success_msg,
		"error" => $_error_msg
	);     
	// จะได้ตัวแปร $json_data  สำหรับสร้างเป็น json data					
}
////////////////////////////// END EDIT  //////////////////////////////////////



////////////////////////////// BEGIN ADD  ////////////////////////////////////////////////////
///////////////////////////////////////////// ส่วนของการเพิ่มข้อมูลใหม่  /////////////////////
if(isset($_POST['action']) && $_POST['action']=="add"){
	$_error_msg = null;
	$_success_msg = null;	
	
	$sql = "
	INSERT INTO tbl_members SET 
	member_username='".$_POST['username']."',
	member_password='".$_POST['password']."',
	member_fullname='".$_POST['fullname']."',
	member_type='".$_POST['usertype']."'		
	";
	$result = $mysqli->query($sql);
	if($result && $mysqli->affected_rows>0){
		$insert_userID = $mysqli->insert_id;
		$_success_msg = "Add new user successful!";
	}else{
		$_error_msg = "Eror, please try again!";
	}
	$json_data[]=array(  
		"success" => $_success_msg,
		"error" => $_error_msg
	);    
	// จะได้ตัวแปร $json_data  สำหรับสร้างเป็น json data			 			
}
////////////////////////////// END ADD  //////////////////////////////////////


// แปลงตัวแปร $json_data array เป็นรูปแบบ json string  data
if(isset($json_data)){  
    $json= json_encode($json_data);    
    if(isset($_GET['callback']) && $_GET['callback']!=""){    
    echo $_GET['callback']."(".$json.");";        
    }else{    
    echo $json;    
    }    
}