<?php 
//posme:2023-02-27
namespace App\Models;
use CodeIgniter\Model;

class Notification_Model extends Model  {
   function __construct(){	
      parent::__construct();
   }
   function update_app_posme($notificationID,$data){
		$db 		= db_connect();
		$builder	= $db->table("tb_notification");
		
		$builder->where("notificationID",$notificationID);
		return $builder->update($data);
		
   }
   function delete_app_posme($notificationID){
		$db 	= db_connect();
		$builder	= $db->table("tb_notification");		
  		$data["isActive"] = 0;
		
		$builder->where("notificationID",$notificationID);
		return $builder->update($data);
		
   } 
   function insert_app_posme($data){
		$db 		= db_connect();
		$builder	= $db->table("tb_notification");
		$result		= $builder->insert($data);
		return $result;
   }
   function get_rowByPK($notificationID){
		$db 	= db_connect();
		$builder	= $db->table("tb_notification");    
		$sql = "";
		$sql = sprintf("select notificationID,errorID,`from`,`to`,`subject`,message,summary,title,tagID,createdOn,sendOn,isActive,phoneFrom,phoneTo,programDate,programHour,sendEmailOn,sendWhatsappOn,addedCalendarGoogle");
		$sql = $sql.sprintf(" from tb_notification n");
		$sql = $sql.sprintf(" where n.isActive= 1");		
		$sql = $sql.sprintf(" and n.notificationID = $notificationID");		
		
		//Ejecutar Consulta
		return $db->query($sql)->getRow();
   }
   function get_rows($top){
		$db 		= db_connect();
		$builder	= $db->table("tb_notification");    
   
		$sql = "";
		$sql = sprintf("select notificationID,errorID,`from`,`to`,`subject`,message,summary,title,tagID,createdOn,sendOn,isActive,phoneFrom,phoneTo,programDate,programHour,sendEmailOn,sendWhatsappOn,addedCalendarGoogle");
		$sql = $sql.sprintf(" from tb_notification n");
		$sql = $sql.sprintf(" where n.isActive= 1");
		$sql = $sql.sprintf(" and n.sendOn is null");
		$sql = $sql.sprintf(" limit 0,$top ");
		
		echo $sql;
		//Ejecutar Consulta
		return $db->query($sql)->getResult();
		
   }
   
    function get_rowsEmail($top){
		$db 		= db_connect();
		$builder	= $db->table("tb_notification");    
   
		$sql = "";
		$sql = sprintf("select notificationID,errorID,`from`,`to`,`subject`,message,summary,title,tagID,createdOn,sendOn,isActive,phoneFrom,phoneTo,programDate,programHour,sendEmailOn,sendWhatsappOn,addedCalendarGoogle");
		$sql = $sql.sprintf(" from tb_notification n");
		$sql = $sql.sprintf(" where n.isActive= 1");
		$sql = $sql.sprintf(" and n.sendEmailOn is null");
		$sql = $sql.sprintf(" limit 0,$top ");
		
		
		//Ejecutar Consulta
		return $db->query($sql)->getResult();
		
   }
   

	
	
    function get_rowsWhatsappPrimerEmployeerOcupado($datetime_cliente,$business)
	{
		$db 		= db_connect();
		$builder	= $db->table("tb_notification");    
   
		$sql = "";
		$sql = sprintf("select notificationID,errorID,`from`,`to`,`subject`,message,summary,title,tagID,createdOn,sendOn,isActive,phoneFrom,phoneTo,programDate,programHour,sendEmailOn,sendWhatsappOn,addedCalendarGoogle");
		$sql = $sql.sprintf(" from tb_notification n");
		$sql = $sql.sprintf(" where n.isActive = 1 ");
		$sql = $sql.sprintf(" and   n.summary = '$business' ");
		$sql = $sql.sprintf(" and 
							  (
									(
										ADDTIME(CAST(CONCAT(n.programDate,' ',n.programHour,':00') AS DATETIME),'+00:30:00') >= 
										CAST('".$datetime_cliente."' AS DATETIME) and 
										CAST(n.programDate AS DATETIME) = CAST('".$datetime_cliente."' AS DATE) 
									)
									
									or 
									
									(
										ADDTIME(CAST('".$datetime_cliente."' AS DATETIME),'-00:30:00') <= 
										CAST(CONCAT(n.programDate,' ',n.programHour,':00') AS DATETIME)  and 			
										CAST(n.programDate AS DATETIME) = CAST('".$datetime_cliente."' AS DATE)
									)
									
							  )
							");
		
			
			
		
		
		
		//Ejecutar Consulta
		return $db->query($sql)->getResult();
		
	}
	
	function get_rowsToAddedGoogleCalendar($tagID,$business)
	{
		$db 		= db_connect();
		$builder	= $db->table("tb_notification");    
   
		$sql = "";
		$sql = sprintf("select notificationID,errorID,`from`,`to`,`subject`,message,summary,title,tagID,createdOn,sendOn,isActive,phoneFrom,phoneTo,programDate,programHour,sendEmailOn,sendWhatsappOn,addedCalendarGoogle");
		$sql = $sql.sprintf(" from tb_notification n");
		$sql = $sql.sprintf(" where n.isActive= 1");
		$sql = $sql.sprintf(" and n.addedCalendarGoogle = 0 and n.tagID = ".$tagID);
		$sql = $sql.sprintf(" and n.summary = '".$business."'");
		
		//Ejecutar Consulta
		return $db->query($sql)->getResult();
	}
    function get_rowsWhatsapp($top){
		$db 		= db_connect();
		$builder	= $db->table("tb_notification");    
   
		$sql = "";
		$sql = sprintf("select notificationID,errorID,`from`,`to`,`subject`,message,summary,title,tagID,createdOn,sendOn,isActive,phoneFrom,phoneTo,programDate,programHour,sendEmailOn,sendWhatsappOn,addedCalendarGoogle");
		$sql = $sql.sprintf(" from tb_notification n");
		$sql = $sql.sprintf(" where n.isActive= 1");
		$sql = $sql.sprintf(" and n.sendWhatsappOn is null");
		$sql = $sql.sprintf(" and 
									CAST(CONCAT(n.programDate,' ',n.programHour,':00') AS DATETIME) > 
									ADDTIME(ADDTIME(now(),'".APP_HOUR_DIFERENCE."') , '-00:30:00')   
									
							  and 
									CAST(CONCAT(n.programDate,' ',n.programHour,':00') AS DATETIME) <= 
									ADDTIME(ADDTIME(now(),'".APP_HOUR_DIFERENCE."') , '+00:30:00')   
							");
		
			
			
		$sql = $sql.sprintf(" limit 0,$top ");
		
		
		//Ejecutar Consulta
		return $db->query($sql)->getResult();
		
   }
   function get_rowsByToMessage($to,$message){
		$db 	= db_connect();
		    
		$sql = "";
		$sql = sprintf("select notificationID,errorID,`from`,`to`,`subject`,message,summary,title,tagID,createdOn,sendOn,isActive,phoneFrom,phoneTo,programDate,programHour,sendEmailOn,sendWhatsappOn,addedCalendarGoogle");
		$sql = $sql.sprintf(" from tb_notification n");
		$sql = $sql.sprintf(" where n.isActive= 1");
		$sql = $sql.sprintf(" and n.to = '$to' ");
		$sql = $sql.sprintf(" and n.message = ".$db->escape($message)." ");		
		
		//Ejecutar Consulta
		return $db->query($sql)->getRow();	
   }
   
}
?>