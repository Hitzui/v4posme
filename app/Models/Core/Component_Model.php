<?php 
//posme:2023-02-27
namespace App\Models\Core;
use CodeIgniter\Model;


class Component_Model extends Model  {
   function __construct(){		
      parent::__construct();
   }  
   function get_rowByName($name){
		$db 	= db_connect();    
		$sql = "";
		$sql = sprintf("select componentID,name as componentName");
		$sql = $sql.sprintf(" from tb_component");
		$sql = $sql.sprintf(" where name = '$name' ");	
		
		//Ejecutar Consulta
		return $db->query($sql)->getRow();
   }
   
}
?>