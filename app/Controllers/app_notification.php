<?php
//posme:2023-02-27
namespace App\Controllers;
class app_notification extends _BaseController {
	
    
	//procesar las notificaciones
	function fillCurrentNotification()
	{
			
		$tagName		= "NOTIFICAR OBLIGACION";
		$objListCompany = $this->Company_Model->get_rows();
		$objTag			= $this->Tag_Model->get_rowByName($tagName);
		
		//Recorrer Empresas
		if($objListCompany)
		foreach($objListCompany as $i){
			$objListItem		= $this->Remember_Model->getNotificationCompany($i->companyID);			
			//Recorrer las Notificaciones
			if($objListItem){				
				foreach($objListItem as $noti){
					$hoy_			= date_format(date_create(),"Y-m-d");
					$lastNoti 		= date_format(date_create($noti->lastNotificationOn),"Y-m-d");
					
					//Recorrer desde la ultima notificacion, hasta la fecha de hoy
					while ($lastNoti <= $hoy_){						
						//Validar si Ya esta procesado el Dia.
						
						$objListItemDetail		= $this->Remember_Model->getProcessNotification($noti->rememberID,$lastNoti);	
						
						
						if($objListItemDetail)
						if($objListItemDetail->diaProcesado == $noti->day)
						{
	
							//echo $noti;
							//echo $objListItemDetail;
							
							$item 					= $objListItemDetail;
							$mensaje				= "";
							$mensaje				.= "<span class='badge badge-important'>OBLIGACION</span>".$item->title;
							$mensaje				.= " => ".$item->description." => ".$item->Fecha." => <span class='badge badge-important'>ATRAZO</span>";
							
							//Ver si el mensaje ya existe para el administrador
							$objError			= $this->Error_Model->get_rowByMessageUser(0,$mensaje);
							$data				= null;
							$errorID 			= 0;
							//tag con notificacion
							if($objTag->sendNotificationApp){
								$objListUsuario				= $this->User_Tag_Model->get_rowByPK($objTag->tagID);						
								//Lista de Usuarios
								if ($objListUsuario)
								foreach($objListUsuario as $usuario){
									
									$objErrorUser			= $this->Error_Model->get_rowByMessageUser($usuario->userID,$mensaje);
									if(!$objErrorUser){
										$data					= null;
										$data["tagID"]			= $objTag->tagID;
										$data["notificated"]	= "notificar obligacion";
										$data["message"]		= $mensaje;
										$data["isActive"]		= 1;
										$data["userID"]			= $usuario->userID;
										$data["createdOn"]		= date_format(date_create(),"Y-m-d H:i:s");
										$this->Error_Model->insert_app_posme($data);
									}
								}
							}
							
							if(!$objError){
								$data				= null;
								$data["notificated"]= "notificar obligacion";
								$data["tagID"]		= $objTag->tagID;
								$data["message"]	= $mensaje;
								$data["isActive"]	= 1;
								$data["createdOn"]	= date_format(date_create(),"Y-m-d H:i:s");
								$errorID			= $this->Error_Model->insert_app_posme($data);
							}
							else 
								$errorID 			= $objError->errorID;
							
							//tag con correo
							if($objTag->sendEmail){
								
								$objListUsuario				= $this->User_Tag_Model->get_rowByPK($objTag->tagID);						
								if ($objListUsuario)
								foreach($objListUsuario as $usuario){
									
									$objNotificationUser		= $this->Notification_Model->get_rowsByToMessage($usuario->email,$mensaje);
									if(!$objNotificationUser){
										$data						= null;
										$data["errorID"]			= $errorID;
										$data["from"]				= EMAIL_APP;
										$data["to"]					= $usuario->email;
										$data["subject"]			= "notificar obligacion";
										$data["message"]			= $mensaje;
										$data["summary"]			= "notificar obligacion";
										$data["title"]				= "notificar obligacion";
										$data["tagID"]				= $objTag->tagID;
										$data["createdOn"]			= date_format(date_create(),"Y-m-d H:i:s");
										$data["isActive"]			= 1;
										$this->Notification_Model->insert_app_posme($data);
									}
								}
							}
						}
						//Actualizar Base de Datos
						$dataRemember						= NULL;
						$dataRemember["lastNotificationOn"]	= $lastNoti;
						$this->Remember_Model->update_app_posme($noti->rememberID,$dataRemember);	
						//Siguiente Fecha
						$lastNoti = date_format(date_add(date_create($lastNoti),date_interval_create_from_date_string("1 days")),"Y-m-d");
					}
				}
			}
		}
		
	}
	
	
	function fillSendWhatsappCustomer()
	{
			
		$tagName			= "ENVIAR WHATSAPP A CLIENTE";
		$objListCompany 	= $this->Company_Model->get_rows();
		$objTag				= $this->Tag_Model->get_rowByName($tagName);
		$objListUsuario		= $this->User_Tag_Model->get_rowByPK($objTag->tagID);
		$objListCustomer 	= $this->Customer_Model->get_rowByCompany_phoneAndEmail(APP_COMPANY); 		
		$objListEmail		= $this->Entity_Email_Model->get_rowByCompany(APP_COMPANY); 
		$objParameter		= $this->core_web_parameter->getParameter("CORE_CSV_SPLIT",$companyID);
		$characterSplie 	= $objParameter->value;
				
		//Recorrer Empresas
		if($objListCompany)
		foreach($objListCompany as $i)
		{
			$objListItem		= $this->Remember_Model->getNotificationCompanyByTagId($i->companyID,$objTag->tagID );			
			//Recorrer las Notificaciones
			if($objListItem)
			{				
				foreach($objListItem as $noti)
				{
					//Leer archivo   que esta en remember
					$path 	= PATH_FILE_OF_APP."/company_".APP_COMPANY."/component_76/component_item_".$noti->rememberID;			
					$path 	= $path.'/send.csv';
					$table 			= null;
					$fila 			= 0;
						
					if (file_exists($path))
					{
						$this->csvreader->separator = $characterSplie;
						$table 			= $this->csvreader->parse_file($path); 
						
						if($table)
						{					
							if (count($table) >= 1)
							{							
								if(!array_key_exists("Destino",$table[0])){
									$table = null;
								}
								if(!array_key_exists("Mensaje",$table[0])){
									$table = null;
								}
								
								if( !is_null($table) )
								{
									$objListCustomer = array();
									foreach ($table as $row) 
									{
										$rowx 					= array();
										$rowx["firstName"] 		= "";
										$rowx["phoneNumber"] 	= $row["Destino"];
										$rowx["mensaje"] 		= $row["Mensaje"];
										array_push($objListCustomer, $rowx );
									}
								}
							}
						}
						
					}
					
					
					
					
					
					
					$hoy_			= date_format(date_create(),"Y-m-d");
					$lastNoti 		= date_format(date_create($noti->lastNotificationOn),"Y-m-d");
					
					
					//Recorrer desde la ultima notificacion, hasta la fecha de hoy
					while ($lastNoti <= $hoy_)
					{				
						
						//Validar si Ya esta procesado el Dia.						
						$objListItemDetail		= $this->Remember_Model->getProcessNotification($noti->rememberID,$lastNoti);	
						
						
						if($objListItemDetail)
						if($objListItemDetail->diaProcesado == $noti->day)
						{	
							
							$item 					= $objListItemDetail;
							$mensaje				=  " ";
							$mensaje				.= " ";
							$mensaje				.= " => ".$item->description;
							$mensaje				.= " ";
							
							//Ver si el mensaje ya existe para el administrador
							$objError			= $this->Error_Model->get_rowByMessageUser(0,$mensaje);
							$data				= null;
							$errorID 			= 0;
							
							if(!$objError){
								$data				= null;
								$data["notificated"]= "mensaje al cliente";
								$data["tagID"]		= $objTag->tagID;
								$data["message"]	= $mensaje;
								$data["isActive"]	= 1;
								$data["createdOn"]	= date_format(date_create(),"Y-m-d H:i:s");
								$errorID			= $this->Error_Model->insert_app_posme($data);
							}
							else 
								$errorID 			= $objError->errorID;
					
							
							//tag con notificacion
							if($objTag->sendSMS == "1" )
							{
								
								//Lista de Usuarios
								if ($objListCustomer)
								foreach($objListCustomer as $usuarioX){
									
									$phoneNumber  	= $item->leerFile == 1 ? $usuarioX["phoneNumber"] : $usuarioX->phoneNumber;
									$firstName  	= $item->leerFile == 1 ? $usuarioX["firstName"] : $usuarioX->firstName;
									
									$objNotificationUser		= $this->Notification_Model->get_rowsByToMessage($phoneNumber ,$mensaje);									
									if(!$objNotificationUser){
										$data						= null;
										$data["errorID"]			= $errorID;
										$data["from"]				= PHONE_POSME;
										$data["to"]					= $firstName;
										$data["phoneFrom"]			= PHONE_POSME;
										$data["phoneTo"]			= $phoneNumber;
										$data["programDate"]		= $hoy_;
										$data["programHour"]		= "00:00:00";
										$data["subject"]			= "notificacion";
										$data["message"]			= $mensaje;
										$data["summary"]			= "summary... ";
										$data["title"]				= "title";
										$data["tagID"]				= $objTag->tagID;
										$data["createdOn"]			= date_format(date_create(),"Y-m-d H:i:s");
										$data["isActive"]			= 1;
										$this->Notification_Model->insert_app_posme($data);
									}
									
								}
							}
							
							
							
							//tag con correo
							if($objTag->sendEmail == "1" )
							{
								if ($objListEmail)
								foreach($objListEmail as $customerX){									
									$objListCustomer		= $this->Notification_Model->get_rowsByToMessage($customerX->email,$mensaje);
									if(!$objNotificationUser){
										$data						= null;
										$data["errorID"]			= $errorID;
										$data["from"]				= EMAIL_APP;
										$data["to"]					= $usuarioX->email;
										$data["subject"]			= "notificar obligacion";
										$data["message"]			= $mensaje;
										$data["summary"]			= "notificar obligacion";
										$data["title"]				= "notificar obligacion";
										$data["tagID"]				= $objTag->tagID;
										$data["createdOn"]			= date_format(date_create(),"Y-m-d H:i:s");
										$data["isActive"]			= 1;
										$this->Notification_Model->insert_app_posme($data);
									}
								}
							}
							
							
						}
						
						
						//Actualizar Base de Datos
						$dataRemember						= NULL;
						$dataRemember["lastNotificationOn"]	= $lastNoti;
						$this->Remember_Model->update_app_posme($noti->rememberID,$dataRemember);	
						//Siguiente Fecha
						$lastNoti = date_format(date_add(date_create($lastNoti),date_interval_create_from_date_string("1 days")),"Y-m-d");
					}
				}
			}
		}
		
	}
	
	
	
	//mostrar las notificaciones en sistema, de si falta la tasa de cambio
	function fillTipoCambio()
	{
				
		$tagName		= "NOTIFICAR TIPO DE CAMBIO";
		$date_			= date_format(date_create(),"Y-m-d");
		$objListCompany = $this->Company_Model->get_rows();
		$objTag			= $this->Tag_Model->get_rowByName($tagName);
		if($objListCompany)
		foreach($objListCompany as $i){
				$defaultCurrencyID	= $this->core_web_currency->getCurrencyDefault($i->companyID)->currencyID;
				$reportCurrencyID	= $this->core_web_currency->getCurrencyExternal($i->companyID)->currencyID;
				
				try {
					$exchangeRate		= $this->core_web_currency->getRatio($i->companyID,$date_,1,$reportCurrencyID,$defaultCurrencyID);					
				} catch (\Exception $e) {
					$mensaje			= $e->getMessage();	
					
					$objError			= $this->Error_Model->get_rowByMessageUser(0,$mensaje);
					$data				= null;
					$errorID 			= 0;
					if(!$objError){
						$data["notificated"]= "tipo de cambio...";
						$data["tagID"]		= $objTag->tagID;
						$data["message"]	= $mensaje;
						$data["isActive"]	= 1;
						$data["createdOn"]	= date_format(date_create(),"Y-m-d H:i:s");
						$errorID			= $this->Error_Model->insert_app_posme($data);
					}
					else 
						$errorID 			= $objError->errorID;
					
					//tag con correo
					if($objTag->sendEmail){
						$objListUsuario				= $this->User_Tag_Model->get_rowByPK($objTag->tagID);						
						if ($objListUsuario)
						foreach($objListUsuario as $usuario){
							$objNotificationUser		= $this->Notification_Model->get_rowsByToMessage($usuario->email,$mensaje);
							if(!$objNotificationUser){
								$data						= null;
								$data["errorID"]			= $errorID;
								$data["from"]				= EMAIL_APP;
								$data["to"]					= $usuario->email;
								$data["subject"]			= "TIPO DE CAMBIO";
								$data["message"]			= $mensaje;
								$data["summary"]			= "TIPO DE CAMBIO NO INGRESADO";
								$data["title"]				= "TIPO DE CAMBIO";
								$data["tagID"]				= $objTag->tagID;
								$data["createdOn"]			= date_format(date_create(),"Y-m-d H:i:s");
								$data["isActive"]			= 1;
								$this->Notification_Model->insert_app_posme($data);
							}
						}
					}
					
					//tag con notificacion
					if($objTag->sendNotificationApp){
						$objListUsuario				= $this->User_Tag_Model->get_rowByPK($objTag->tagID);						
						if ($objListUsuario)
						foreach($objListUsuario as $usuario){
							$objErrorUser			= $this->Error_Model->get_rowByMessageUser($usuario->userID,$mensaje);
							if(!$objErrorUser){
								$data					= null;
								$data["tagID"]			= $objTag->tagID;
								$data["notificated"]	= "tasa de cambio";
								$data["message"]		= $mensaje;
								$data["isActive"]		= 1;
								$data["userID"]			= $usuario->userID;
								$data["createdOn"]		= date_format(date_create(),"Y-m-d H:i:s");
								$this->Error_Model->insert_app_posme($data);
							}
						}
					}
					
				}
		}
		
	}
	
	//mostrar las notificaciones en sistema, de inventarios minimos
	function fillInventarioMinimo()
	{
				
		$tagName		= "NOTIFICAR INVENTARIO MINIMO";
		$objListCompany = $this->Company_Model->get_rows();
		$objTag			= $this->Tag_Model->get_rowByName($tagName);
		if($objListCompany)
		foreach($objListCompany as $i){
			$objListItem		= $this->Itemwarehouse_Model->get_rowLowMinimus($i->companyID);
			if($objListItem){
				foreach($objListItem as $item){
					$mensaje			 = "";
					$mensaje			.= "<span class='badge badge-warning'>PRODUCTO</span>:".$item->itemNumber." ".$item->itemName."<br/>";
					$mensaje			.= "<span class='badge badge-warning'>BODEGA</span>:".$item->warehouseNumber." ".$item->warehouseName."<br/>";
					$mensaje			.= "<span class='badge badge-warning'>CANTIDAD</span>:".$item->quantity.",<span class='badge badge-warning'>CANTIDAD MINIMA</span>:".$item->quantityMin;
					
					$objError			= $this->Error_Model->get_rowByMessageUser(0,$mensaje);
					$data				= null;
					$errorID 			= 0;
					if(!$objError){
						$data["notificated"]= "inventario minimo";
						$data["tagID"]		= $objTag->tagID;
						$data["message"]	= $mensaje;
						$data["isActive"]	= 1;
						$data["createdOn"]	= date_format(date_create(),"Y-m-d H:i:s");
						$errorID			= $this->Error_Model->insert_app_posme($data);
					}
					else 
						$errorID 			= $objError->errorID;
					
					//tag con correo
					if($objTag->sendEmail){
						$objListUsuario				= $this->User_Tag_Model->get_rowByPK($objTag->tagID);						
						if ($objListUsuario)
						foreach($objListUsuario as $usuario){
							$objNotificationUser		= $this->Notification_Model->get_rowsByToMessage($usuario->email,$mensaje);
							if(!$objNotificationUser){
								$data						= null;
								$data["errorID"]			= $errorID;
								$data["from"]				= EMAIL_APP;
								$data["to"]					= $usuario->email;
								$data["subject"]			= "INVENTARIO MINIMO";
								$data["message"]			= $mensaje;
								$data["summary"]			= "INVENTARIO MINIMO";
								$data["title"]				= "INVENTARIO MINIMO";
								$data["tagID"]				= $objTag->tagID;
								$data["createdOn"]			= date_format(date_create(),"Y-m-d H:i:s");
								$data["isActive"]			= 1;
								$this->Notification_Model->insert_app_posme($data);
							}
						}
					}
					
					//tag con notificacion
					if($objTag->sendNotificationApp){
						$objListUsuario				= $this->User_Tag_Model->get_rowByPK($objTag->tagID);						
						if ($objListUsuario)
						foreach($objListUsuario as $usuario){
							$objErrorUser			= $this->Error_Model->get_rowByMessageUser($usuario->userID,$mensaje);
							if(!$objErrorUser){
								$data					= null;
								$data["tagID"]			= $objTag->tagID;
								$data["notificated"]	= "inventario minimo";
								$data["message"]		= $mensaje;
								$data["isActive"]		= 1;
								$data["userID"]			= $usuario->userID;
								$data["createdOn"]		= date_format(date_create(),"Y-m-d H:i:s");
								$this->Error_Model->insert_app_posme($data);
							}
						}
					}
					
				}
			}
		}
	}
	
	function fillInventarioFechaVencimiento()
	{
		$tagName		= "FECHA DE VENCIMIENTO";
		$objListCompany = $this->Company_Model->get_rows();
		$objTag			= $this->Tag_Model->get_rowByName($tagName);
		if($objListCompany)
		{
			foreach($objListCompany as $i)
			{
				$objListItem		= $this->Item_Warehouse_Expired_Model->getBy_ItemIDAproxVencimiento($i->companyID);
				if($objListItem)
				{
					foreach($objListItem as $item)
					{
						$mensaje			 = "";
						$mensaje			.= "<span class='badge badge-warning'>PRODUCTO</span>:".$item->itemNumber." ".$item->itemName."<br/>";
						$mensaje			.= "<span class='badge badge-warning'>BODEGA</span>:".$item->warehouseNumber." ".$item->warehouseName."<br/>";
						$mensaje			.= "<span class='badge badge-warning'>CANTIDAD</span>:".$item->quantity."----(".$item->dateExpired.")<span class='badge badge-warning'>VENCIMIENTO</span>:";
						
						//insertar error
						$objError			= $this->Error_Model->get_rowByMessageUser(0,$mensaje);
						$data				= null;
						$errorID 			= 0;
						if(!$objError){
							$data["notificated"]= "fecha de vencimiento";
							$data["tagID"]		= $objTag->tagID;
							$data["message"]	= $mensaje;
							$data["isActive"]	= 1;
							$data["createdOn"]	= date_format(date_create(),"Y-m-d H:i:s");
							$errorID			= $this->Error_Model->insert_app_posme($data);
						}
						else 
							$errorID 			= $objError->errorID;
						
						//tag con correo
						if($objTag->sendEmail)
						{
							$objListUsuario				= $this->User_Tag_Model->get_rowByPK($objTag->tagID);						
							if ($objListUsuario)
							foreach($objListUsuario as $usuario){
								$objNotificationUser		= $this->Notification_Model->get_rowsByToMessage($usuario->email,$mensaje);
								if(!$objNotificationUser){
									$data						= null;
									$data["errorID"]			= $errorID;
									$data["from"]				= EMAIL_APP;
									$data["to"]					= $usuario->email;
									$data["subject"]			= "INVENTARIO MINIMO";
									$data["message"]			= $mensaje;
									$data["summary"]			= "INVENTARIO MINIMO";
									$data["title"]				= "INVENTARIO MINIMO";
									$data["tagID"]				= $objTag->tagID;
									$data["createdOn"]			= date_format(date_create(),"Y-m-d H:i:s");
									$data["isActive"]			= 1;
									$this->Notification_Model->insert_app_posme($data);
								}
							}
						}
						
						//tag con notificacion
						if($objTag->sendNotificationApp)
						{
							$objListUsuario				= $this->User_Tag_Model->get_rowByPK($objTag->tagID);						
							if ($objListUsuario)
							{
								foreach($objListUsuario as $usuario)
								{
									
									$objErrorUser			= $this->Error_Model->get_rowByMessageUser($usuario->userID,$mensaje);
									if(!$objErrorUser){
										$data					= null;
										$data["tagID"]			= $objTag->tagID;
										$data["notificated"]	= "fecha de vencimiento";
										$data["message"]		= $mensaje;
										$data["isActive"]		= 1;
										$data["userID"]			= $usuario->userID;
										$data["createdOn"]		= date_format(date_create(),"Y-m-d H:i:s");
										$this->Error_Model->insert_app_posme($data);
									}
								}
							}
						}
						
					}
				}
			}
		
		}
	}
	
	//mostrar las notificaciones en sistema, de cumple de clientes
	function fillCumpleayo()
	{
		$tagName		= "FELIZ CUMPLE";		
		$objTag			= $this->Tag_Model->get_rowByName($tagName);
		
		//Para cada empresa
		$objListCompany = $this->Company_Model->get_rows();		
		if($objListCompany)
		foreach($objListCompany as $i){
			
			//Obtener los cumple de la empresa
			$mensaje			= null;
			$objListItem		= $this->Customer_Model->get_happyBirthDay($i->companyID);
			
			
			if($objListItem)
			foreach($objListItem as $usuario){
				$mensaje					= "<span class='badge badge-info'>FELIZ CUMPLE</span>:".$usuario->firstName." : =>".$usuario->birthDate." AVISO DEL PERIODO = ".date_format(date_create(),"Y");
				
				//Enviar Mensaje por Correo
				/*
				$objNotificationUser		= $this->Notification_Model->get_rowsByToMessage($usuario->email,$mensaje);
				if(!$objNotificationUser){					
					$data["errorID"]			= NULL;
					$data["from"]				= EMAIL_APP;
					$data["to"]					= $usuario->email;
					$data["subject"]			= "FELIZ CUMPLE";
					$data["message"]			= $mensaje;
					$data["summary"]			= "FELIZ CUMPLE";
					$data["title"]				= "FELIZ CUMPLE";
					$data["tagID"]				= NULL;
					$data["createdOn"]			= date_format(date_create(),"Y-m-d H:i:s");
					$data["isActive"]			= 1;
					$this->Notification_Model->insert_app_posme($data);
				}
				*/
				
				//Notificaciones al administrador
				$objError			= $this->Error_Model->get_rowByMessageUser(0,$mensaje);
				$data				= null;
				$errorID 			= 0;
					
				if(!$objError){
					$data				= null;
					$data["notificated"]= "cumple";
					$data["tagID"]		= $objTag->tagID;
					$data["message"]	= $mensaje;
					$data["isActive"]	= 1;
					$data["createdOn"]	= date_format(date_create(),"Y-m-d H:i:s");
					$errorID			= $this->Error_Model->insert_app_posme($data);
				}
				else 
					$errorID 			= $objError->errorID;
					
				
				//Notificacioin a los usuarios
				if($objTag->sendNotificationApp){
					$objListUsuario				= $this->User_Tag_Model->get_rowByPK($objTag->tagID);						
					//Lista de Usuarios
					if ($objListUsuario)
					foreach($objListUsuario as $usuario){
						$objErrorUser			= $this->Error_Model->get_rowByMessageUser($usuario->userID,$mensaje);
						if(!$objErrorUser){
							$data					= null;
							$data["tagID"]			= $objTag->tagID;
							$data["notificated"]	= "FELIZ CUMPLE";
							$data["message"]		= $mensaje;
							$data["isActive"]		= 1;
							$data["userID"]			= $usuario->userID;
							$data["createdOn"]		= date_format(date_create(),"Y-m-d H:i:s");
							$this->Error_Model->insert_app_posme($data);
						}
					}
				}
				
				
					
				
			}
			
		}
	}
	
	//mostrar las notificaciones en sistema, de cuotas atrazadas
	function fillCuotaAtrasada()
	{	
		
		$tagName		= "NOTIFICAR CUOTA VENCIDA";
		$objListCompany = $this->Company_Model->get_rows();
		$objTag			= $this->Tag_Model->get_rowByName($tagName);
		
		//Lista de empresa
		if($objListCompany)
		foreach($objListCompany as $i){
			$objListItem		= $this->Customer_Credit_Amortization_Model->get_rowShareLate($i->companyID);
			//Lista de Avisos
			if($objListItem){
				foreach($objListItem as $item){
					$objCurrency		= $this->Currency_Model->get_rowByPK($item->currencyID);
					$mensaje			= "";
					$mensaje			.= "<span class='badge badge-success'>CLIENTE</span>:".$item->customerNumber."-".$item->firstName." ".$item->lastName." => ";
					$mensaje			.= "".$item->documentNumber." => ".$item->dateApply." => <span class='badge badge-success'>ATRAZO</span>: ".$objCurrency->simbol." ".sprintf("%01.2f",$item->remaining);
					  
					//Ver si el mensaje ya existe para el administrador
					$objError			= $this->Error_Model->get_rowByMessageUser(0,$mensaje);
					$data				= null;
					$errorID 			= 0;
					
					if(!$objError){
						$data				= null;
						$data["notificated"]= "cuota atrasada";
						$data["tagID"]		= $objTag->tagID;
						$data["message"]	= $mensaje;
						$data["isActive"]	= 1;
						$data["createdOn"]	= date_format(date_create(),"Y-m-d H:i:s");
						$errorID			= $this->Error_Model->insert_app_posme($data);
					}
					else 
						$errorID 			= $objError->errorID;
					
					
					//tag con correo
					if($objTag->sendEmail){
						$objListUsuario				= $this->User_Tag_Model->get_rowByPK($objTag->tagID);						
						if ($objListUsuario)
						foreach($objListUsuario as $usuario){
							$objNotificationUser		= $this->Notification_Model->get_rowsByToMessage($usuario->email,$mensaje);
							if(!$objNotificationUser){
								$data						= null;
								$data["errorID"]			= $errorID;
								$data["from"]				= EMAIL_APP;
								$data["to"]					= $usuario->email;
								$data["subject"]			= "CUOTA ATRASADA";
								$data["message"]			= $mensaje;
								$data["summary"]			= "CUOTA ATRASADA";
								$data["title"]				= "CUOTA ATRASADA";
								$data["tagID"]				= $objTag->tagID;
								$data["createdOn"]			= date_format(date_create(),"Y-m-d H:i:s");
								$data["isActive"]			= 1;
								$this->Notification_Model->insert_app_posme($data);
							}
						}
					}
					
					
					//tag con notificacion
					if($objTag->sendNotificationApp){
						$objListUsuario				= $this->User_Tag_Model->get_rowByPK($objTag->tagID);						
						//Lista de Usuarios
						if ($objListUsuario)
						foreach($objListUsuario as $usuario){
							$objErrorUser			= $this->Error_Model->get_rowByMessageUser($usuario->userID,$mensaje);
							if(!$objErrorUser){
								$data					= null;
								$data["tagID"]			= $objTag->tagID;
								$data["notificated"]	= "cuota atrasada";
								$data["message"]		= $mensaje;
								$data["isActive"]		= 1;
								$data["userID"]			= $usuario->userID;
								$data["createdOn"]		= date_format(date_create(),"Y-m-d H:i:s");
								$this->Error_Model->insert_app_posme($data);
							}
						}
					}
					
				
				}
			}
		}
	}
		
	//crear las notificaciones en la base de datos. para revisar cuales son las siguientes visitas
	function fillNextVisit($companyID="")
	{
		$tagName		= "PROXIMA VISITA";
		$objTag			= $this->Tag_Model->get_rowByName($tagName);
		$companyID 		= helper_SegmentsByIndex($this->uri->getSegments(),1,$companyID);	
		$objLTM			= $this->Transaction_Master_Model->get_rowByNotification($companyID);
		
		if($objLTM)
		{
			//Recorrer lista de transacciones
			foreach($objLTM as $objTM)
			{
				
				$mensaje			= "";
				$mensaje			.= "<span class='badge badge-success'>TELEFONO</span>:".$objTM->numberPhone." => ";
				$mensaje			.= $objTM->transactionNumber." => ".$objTM->note;
					 
				if($objTag->sendNotificationApp)
				{
					//lista de usuarios
					$objListUsuario				= $this->User_Tag_Model->get_rowByPK($objTag->tagID);												
					if ($objListUsuario)
					{
						foreach($objListUsuario as $usuario)
						{
							$objErrorUser			= $this->Error_Model->get_rowByMessageUser($usuario->userID,$mensaje);
							if(!$objErrorUser)
							{
								$data					= null;
								$data["tagID"]			= $objTag->tagID;
								$data["notificated"]	= "proxima visita";
								$data["message"]		= $mensaje;
								$data["isActive"]		= 1;
								$data["userID"]			= $usuario->userID;
								$data["createdOn"]		= date_format(date_create(),"Y-m-d H:i:s");
								$erroID 				= $this->Error_Model->insert_app_posme($data);
							}
						}
					}
				}	

				//actualizar notificacion
				$dataTM 					= null;
				$dataTM["notificationID"] 	= 1;
				$this->Transaction_Master_Model->update_app_posme($objTM->companyID,$objTM->transactionID,$objTM->transactionMasterID,$dataTM);
					
			}
			
		}
		
		
		return view('core_template/close');//--finview-r
		
	}
	
	//mandar las notificacioneds que estan guardadas, mandarlas por correo
	function sendEmail(){
		//Cargar Libreria
		
		//Obtener lista de email
		$objListNotification = $this->Notification_Model->get_rowsEmail(20);
		if($objListNotification)
		foreach($objListNotification as $i){
			
			//Enviar Email			
			$this->email->setFrom(EMAIL_APP, HELLOW);
			$this->email->setTo($i->to);
			$this->email->setSubject($i->subject);
			$this->email->setMessage("Hola mi nombre es:".$i->title." agende una cita con el objetivo ".$i->message." (".$i->phoneFrom." ".$i->from.")");
			$data["sendOn"]			= date_format(date_create(),"Y-m-d H:i:s");
			$data["sendEmailOn"]	= date_format(date_create(),"Y-m-d H:i:s");
			$this->Notification_Model->update_app_posme($i->notificationID,$data);
			$resultSend01 			= $this->email->send();
		}
		
		echo "SUCCESS";
	}
	
	
	
	//mandar reporte de caja
	function file_job_send_report_daly_reprote_de_caja($companyID="")
	{	
		ini_set('max_execution_time', 0); 
		$companyID 		= helper_SegmentsByIndex($this->uri->getSegments(),1,$companyID);	
		$versionTest 	= "";
	
		//Obtener parametros
		$parameterEmail = $this->core_web_parameter->getParameter("CORE_PROPIETARY_EMAIL",APP_COMPANY);
		$parameterEmail = $parameterEmail->value;
		
		$parameterBalance = $this->core_web_parameter->getParameter("CORE_CUST_PRICE_BALANCE",APP_COMPANY);
		$parameterBalance = $parameterBalance->value;
		
		$parameterLastNotification 		= $this->core_web_parameter->getParameter("CORE_LAST_NOTIFICACION",APP_COMPANY);
		$parameterLastNotificationId 	= $parameterLastNotification->parameterID;
		$parameterLastNotification 		= $parameterLastNotification->value;
		
		
		$parameterDaySleep					= $this->core_web_parameter->getParameter("INVOICE_BILLING_DAY_SLEEP",$companyID);
		$parameterDaySleep					= $parameterDaySleep->value;
			
		$tocken			= '';
		//Obtener compania
		$objCompany 	= $this->Company_Model->get_rowByPK($companyID);
		//Get Logo
		$objParameter	= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
		
			
		
		$fechaNowWile  		= \DateTime::createFromFormat('Y-m-d',$parameterLastNotification);  			//ahora		
		$fechaNowWile		= $fechaNowWile->modify('-'.$parameterDaySleep.' day');		
		
		
		$fechaBeforeWile  	= \DateTime::createFromFormat('Y-m-d',date("Y-m-d"));  			
		$fechaBeforeWile	= $fechaBeforeWile->modify('-'.$parameterDaySleep.' day');	
		
		
		
		
		
		$fechaNow  		= $fechaNowWile->format("Y-m-d");	
		$fechaBefore	= "";
		if( intval($parameterDaySleep) == 0)
		{
			$fechaBefore	= $fechaBeforeWile->format("Y-m-d 23:59:59");				
		}
		else
		{
			$fechaBefore	= $fechaBeforeWile->format("Y-m-d");				
		}
		
		echo "Procesando Envio: ".$fechaNow. ", Al ".$fechaBefore."<br/>"; 
		
		
		//Reporte de Caja
		//
		//////////////////////////////////////////////////
		//////////////////////////////////////////////////		
		$authorization		= 0;
		$query			= "CALL pr_box_get_report_abonos(?,?,?,?,?,?,?);";
		$objData		= $this->Bd_Model->executeRender(
			$query,
			[APP_USERADMIN,$tocken,APP_COMPANY,$authorization,$fechaNow,$fechaBefore,0]
		);			
		//Get Datos de Facturacion				
		$query			= "CALL pr_sales_get_report_sales_summary(?,?,?,?,?,?,?);";
		$objDataSales	= $this->Bd_Model->executeRender(
			$query,
			[APP_COMPANY,$tocken,APP_USERADMIN,$fechaNow,$fechaBefore,0,"-1"]
		);			
		
		$query					= "CALL pr_sales_get_report_sales_summary_credit(?,?,?,?,?,?,?);";
		$objDataSalesCredito	= $this->Bd_Model->executeRender(
			$query,
			[APP_COMPANY,$tocken,APP_USERADMIN,$fechaNow,$fechaBefore,0,"-1"]
		);		
		
		//Get Datos de Entrada de Efectivo y Salida				
		$query			= "CALL pr_box_get_report_input_cash(?,?,?,?,?,?,?,?);";
		$objDataCash	= $this->Bd_Model->executeRender(
			$query,
			[APP_USERADMIN,$tocken,APP_COMPANY,$authorization,$fechaNow,$fechaBefore,0,"-1"]
		);			
		
		$query			= "CALL pr_box_get_report_output_cash(?,?,?,?,?,?,?,?);";
		$objDataCashOut	= $this->Bd_Model->executeRender(
			$query,
			[APP_USERADMIN,$tocken,APP_COMPANY,$authorization,$fechaNow,$fechaBefore,0,"-1"]
		);	
		
			
		if(isset($objData))
		$objDataResult["objDetail"]					= $objData;
		else
		$objDataResult["objDetail"]					= NULL;
		
		
		if(isset($objDataSales))
		$objDataResult["objSales"]					= $objDataSales;
		else
		$objDataResult["objSales"]					= NULL;
	
		if(isset($objDataSalesCredito))
		$objDataResult["objSalesCredito"]			= $objDataSalesCredito;
		else
		$objDataResult["objSalesCredito"]			= NULL;
	
		
		if(isset($objDataCash))				
		$objDataResult["objCash"]					= $objDataCash;
		else
		$objDataResult["objCash"]					= NULL;
		
		if(isset($objDataCashOut))				
		$objDataResult["objCashOut"]				= $objDataCashOut;
		else
		$objDataResult["objCashOut"]				= NULL;
		
		$params_["message"]							= str_replace(" 00:00:00","",$fechaNow)." ".$versionTest." CAJA : ".$objCompany->name." 3/4";
		$objDataResult["objCompany"] 				= $objCompany;
		$objDataResult["objLogo"] 					= $objParameter;
		$objDataResult["startOn"] 					= $fechaNow;
		$objDataResult["endOn"] 					= $fechaBefore;
		$objDataResult["objFirma"] 					= "{companyID:" .  ",branchID:" .  ",userID:" . ",fechaID:" . date('Y-m-d H:i:s') . ",reportID:" . "pr_cxc_get_report_customer_credit" . ",ip:". $this->request->getIPAddress() . ",sessionID:" . ",agenteID:". $this->request->getUserAgent()->getAgentString() .",lastActivity:".  /*inicio last_activity */ "activity" /*fin last_activity*/ . "}"  ;
		$objDataResult["objFirmaEncription"] 		= md5 ($objDataResult["objFirma"]);
		
		$body3  				= /*--inicio view*/ view('app_box_report/share/view_a_disemp',$objDataResult);
		$subject3 				= $params_["message"];
		
		
		//Calcular el monto total
		$montoTotal = 0;		
		//Abonos
		if($objDataResult["objDetail"] != null){
			$objTempoDetail 	= array_filter($objDataResult["objDetail"],function($var){
				if (strtoupper($var["moneda"]) == "CORDOBA")
					return true;
			});			
			$montoTotal = $montoTotal + array_sum(array_column($objTempoDetail, 'montoTotal'));
		}
		
		//Ventas de Contado
		if($objDataResult["objSales"] != null){
			$objTempoDetail 	= array_filter($objDataResult["objSales"],function($var){
				if (strtoupper($var["currencyName"]) == "CORDOBA")
					return true;
			});			
			$montoTotal = $montoTotal + array_sum(array_column($objTempoDetail, 'totalDocument'));
		}
		
		//Ventas de Credito Prima
		if($objDataResult["objSalesCredito"] != null){
			$objTempoDetail 	= array_filter($objDataResult["objSalesCredito"],function($var){
				if (strtoupper($var["currencyName"]) == "CORDOBA")
					return true;
			});			
			$montoTotal = $montoTotal + array_sum(array_column($objTempoDetail, 'receiptAmount'));
		}
		
		//Ingreos de Caja
		if($objDataResult["objCash"] != null){
			$objTempoDetail 	= array_filter($objDataResult["objCash"],function($var){
				if (strtoupper($var["moneda"]) == "CORDOBA")
					return true;
			});			
			$montoTotal = $montoTotal + array_sum(array_column($objTempoDetail, 'montoTransaccion'));
		}
		
		//Salida de Caja
		if($objDataResult["objCashOut"] != null){
			$objTempoDetail 	= array_filter($objDataResult["objCashOut"],function($var){
				if (strtoupper($var["moneda"]) == "CORDOBA")
					return true;
			});			
			$montoTotal = $montoTotal - array_sum(array_column($objTempoDetail, 'montoTransaccion'));
		}
		$objDataResult["mensaje"] 		= "Monto en caja: ".$montoTotal;
		
		
		
	
		
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		//Enviar Correos
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		$this->email->setFrom(EMAIL_APP);
		$this->email->setTo($parameterEmail);
		$this->email->setSubject($subject3);			
		$this->email->setMessage($body3); 
		$resultSend01 = $this->email->send();
		$resultSend02 = $this->email->printDebugger();
		echo "*****************************<br/>";
		echo print_r($resultSend02,true);
		echo "*****************************<br/>";
		sleep(60);
		//enviar al administrador de posme
		$this->email->setFrom(EMAIL_APP);
		$this->email->setTo(EMAIL_APP_COPY);
		$this->email->setSubject($subject3);			
		$this->email->setMessage($body3); 		
		$resultSend01 = $this->email->send();
		$resultSend02 = $this->email->printDebugger();
		echo "*****************************<br/>";
		echo print_r($resultSend02,true);
		echo "*****************************<br/>";
		sleep(60);
		
		
		
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		//Enviar Whatsapp
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		if($this->core_web_whatsap->validSendMessage(APP_COMPANY))
		{
			$this->core_web_whatsap->sendMessage(APP_COMPANY, $objDataResult["mensaje"] );
		}
			
		return view('core_template/close');//--finview-r
		
	}
	
	//mandar reporte de detalle de ventas
	function file_job_send_report_daly_reprote_de_detalle_de_ventas($companyID="")
	{	
		ini_set('max_execution_time', 0); 
		$companyID 		= helper_SegmentsByIndex($this->uri->getSegments(),1,$companyID);	
		$versionTest 	= ":007:";
	
		//Obtener parametros
		$parameterEmail = $this->core_web_parameter->getParameter("CORE_PROPIETARY_EMAIL",APP_COMPANY);
		$parameterEmail = $parameterEmail->value;
		
		$parameterBalance = $this->core_web_parameter->getParameter("CORE_CUST_PRICE_BALANCE",APP_COMPANY);
		$parameterBalance = $parameterBalance->value;
		
		$parameterLastNotification 		= $this->core_web_parameter->getParameter("CORE_LAST_NOTIFICACION",APP_COMPANY);
		$parameterLastNotificationId 	= $parameterLastNotification->parameterID;
		$parameterLastNotification 		= $parameterLastNotification->value;
		
		
		$parameterDaySleep					= $this->core_web_parameter->getParameter("INVOICE_BILLING_DAY_SLEEP",$companyID);
		$parameterDaySleep					= $parameterDaySleep->value;
			
		$tocken			= '';
		//Obtener compania
		$objCompany 	= $this->Company_Model->get_rowByPK($companyID);
		//Get Logo
		$objParameter	= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
		
			
		
		$fechaNowWile  		= \DateTime::createFromFormat('Y-m-d',$parameterLastNotification);  			//ahora		
		$fechaNowWile		= $fechaNowWile->modify('-'.$parameterDaySleep.' day');		
		
		
		$fechaBeforeWile  	= \DateTime::createFromFormat('Y-m-d',date("Y-m-d"));  			
		$fechaBeforeWile	= $fechaBeforeWile->modify('-'.$parameterDaySleep.' day');	
		
		
		
		
		
		$fechaNow  		= $fechaNowWile->format("Y-m-d");	
		$fechaBefore	= "";
		if( intval($parameterDaySleep) == 0)
		{
			$fechaBefore	= $fechaBeforeWile->format("Y-m-d 23:59:59");				
		}
		else
		{
			$fechaBefore	= $fechaBeforeWile->format("Y-m-d");				
		}			
		echo "Procesando Envio: ".$fechaNow. ", Al ".$fechaBefore."<br/>"; 
	
		//Reporte de Venta
		//
		//////////////////////////////////////////////////
		//////////////////////////////////////////////////
		//Obtener ventas
		$query			= "CALL pr_sales_get_report_sales_detail(?,?,?,?,?,?,?);";
		$objData		= $this->Bd_Model->executeRender(
			$query,
			[APP_COMPANY,$tocken,APP_USERADMIN,$fechaNow,$fechaBefore,0,0]
		);			
		
		
		if(isset($objData)){
			$objDataResult["objDetail"]				= $objData;
		}
		else{
			$objDataResult["objDetail"]				= $objData;
		}
				
		
		
		//parametros de reportes
		$params_["objCompany"]			= $objCompany;
		$params_["objStartOn"]			= str_replace(" 00:00:00","",$fechaNow);		
		$params_["objEndOn"]			= str_replace(" 00:00:00","",$fechaBefore);				
		$params_["objDetail"]			= $objDataResult["objDetail"];		
		
		$params_["message"]			= str_replace(" 00:00:00","",$fechaNow)." ".$versionTest." VENTAS: ".$objCompany->name." 1/4";
		$params_["title1"]			= "Reporte diario: 002";
		$params_["title2"]			= "Reporte diario: 003";
		$params_["titleParrafo"]	= "Reporte diario: 005";
		$params_["cuerpo"]			= "Reporte diario: 005";
		
		$params_["sumaryLeft1"]		= "Reporte diario: 005";
		$params_["sumaryLeft2"]		= "Reporte diario: 005";
		$params_["sumaryRight1"]	= "Reporte diario: 005";
		$params_["sumaryRight2"]	= "Reporte diario: 005";
		
		$params_["sumaryLine001"]	= "Reporte diario: 005";
		$params_["sumaryLine002"]	= "Reporte diario: 004";
		$params_["sumaryLine003"]	= "Reporte diario: 003";
		$params_["sumaryLine004"]	= "Reporte diario: 002";
		$params_["sumaryLine005"]	= "Reporte diario: 001";
		$params_["sumaryLine006"]	= "Reporte diario: 006";
		$params_["objFirma"] 					= "{companyID:" .  ",branchID:" .  ",userID:" . ",fechaID:" . date('Y-m-d H:i:s') . ",reportID:" . "pr_sales_get_report_sales_detail" . ",ip:". $this->request->getIPAddress() . ",sessionID:" . ",agenteID:". $this->request->getUserAgent()->getAgentString() .",lastActivity:".  /*inicio last_activity */ "activity" /*fin last_activity*/ . "}"  ;
		$params_["objFirmaEncription"] 			= md5 ($params_["objFirma"]);
		
		//vista
		$subject1 				= $params_["message"];
		$body1  				= /*--inicio view*/ view('app_sales_report/sales_detail/view_a_disemp_email',$params_);//--finview
		
	
		
		
		
		
		
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		//Enviar Correos
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		$this->email->setFrom(EMAIL_APP);
		$this->email->setTo($parameterEmail);
		$this->email->setSubject($subject1);			
		$this->email->setMessage($body1); 
		$resultSend01 = $this->email->send();
		$resultSend02 = $this->email->printDebugger();
		echo "*****************************<br/>";
		echo print_r($resultSend02,true);
		echo "*****************************<br/>";
		sleep(60);
		//enviar al administrador de posme
		$this->email->setFrom(EMAIL_APP);
		$this->email->setTo(EMAIL_APP_COPY);
		$this->email->setSubject($subject1);			
		$this->email->setMessage($body1); 		
		$resultSend01 = $this->email->send();
		$resultSend02 = $this->email->printDebugger();
		echo "*****************************<br/>";
		echo print_r($resultSend02,true);
		echo "*****************************<br/>";
		sleep(60);
		
		return view('core_template/close');//--finview-r
		
	}
	
	//mandar reporte de transacciones regitradas
	function file_job_send_report_daly_reprote_de_tran_registradas($companyID="")
	{	
		ini_set('max_execution_time', 0); 
		$companyID 		= helper_SegmentsByIndex($this->uri->getSegments(),1,$companyID);	
		$versionTest 	= ":007:";
	
		//Obtener parametros
		$parameterEmail = $this->core_web_parameter->getParameter("CORE_PROPIETARY_EMAIL",APP_COMPANY);
		$parameterEmail = $parameterEmail->value;
		
		$parameterBalance = $this->core_web_parameter->getParameter("CORE_CUST_PRICE_BALANCE",APP_COMPANY);
		$parameterBalance = $parameterBalance->value;
		
		$parameterLastNotification 		= $this->core_web_parameter->getParameter("CORE_LAST_NOTIFICACION",APP_COMPANY);
		$parameterLastNotificationId 	= $parameterLastNotification->parameterID;
		$parameterLastNotification 		= $parameterLastNotification->value;
		
		
		$parameterDaySleep					= $this->core_web_parameter->getParameter("INVOICE_BILLING_DAY_SLEEP",$companyID);
		$parameterDaySleep					= $parameterDaySleep->value;
			
		$tocken			= '';
		//Obtener compania
		$objCompany 	= $this->Company_Model->get_rowByPK($companyID);
		//Get Logo
		$objParameter	= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
		
			
		
		$fechaNowWile  		= \DateTime::createFromFormat('Y-m-d',$parameterLastNotification);  			//ahora		
		$fechaNowWile		= $fechaNowWile->modify('-'.$parameterDaySleep.' day');		
		
		
		$fechaBeforeWile  	= \DateTime::createFromFormat('Y-m-d',date("Y-m-d"));  			
		$fechaBeforeWile	= $fechaBeforeWile->modify('-'.$parameterDaySleep.' day');	
		
		
		
		
		
		$fechaNow  		= $fechaNowWile->format("Y-m-d");	
		$fechaBefore	= "";
		if( intval($parameterDaySleep) == 0)
		{
			$fechaBefore	= $fechaBeforeWile->format("Y-m-d 23:59:59");				
		}
		else
		{
			$fechaBefore	= $fechaBeforeWile->format("Y-m-d");				
		}				
		echo "Procesando Envio: ".$fechaNow. ", Al ".$fechaBefore."<br/>"; 
	
		
		
		//Reporte de Transacciones Anuladas
		//
		//////////////////////////////////////////////////
		//////////////////////////////////////////////////	
		$params_["message"]			= str_replace(" 00:00:00","",$fechaNow)." ".$versionTest." T-ANULADAS - REGISTRADAS: ".$objCompany->name." 4/4";
		
		$query			= "CALL pr_transaction_report_registradas_anuladas(?,?,?,?,?);";
		$objData		= $this->Bd_Model->executeRender(
			$query,
			[APP_COMPANY,$tocken,APP_USERADMIN,$fechaNow,$fechaBefore]
		);			
		
		
		if(isset($objData)){
			$objDataResult["objDetail"]				= $objData;
		}
		else{
			$objDataResult["objDetail"]				= $objData;
		}
				
		$params_["objCompany"]			= $objCompany;
		$params_["objStartOn"]			= str_replace(" 00:00:00","",$fechaNow);		
		$params_["objEndOn"]			= str_replace(" 00:00:00","",$fechaBefore);				
		$params_["objDetail"]			= $objDataResult["objDetail"];		
		$params_["objFirma"] 			= "{companyID:" .  ",branchID:" .  ",userID:" . ",fechaID:" . date('Y-m-d H:i:s') . ",reportID:" . "pr_transaction_report_registradas_anuladas" . ",ip:". $this->request->getIPAddress() . ",sessionID:" . ",agenteID:". $this->request->getUserAgent()->getAgentString() .",lastActivity:".  /*inicio last_activity */ "activity" /*fin last_activity*/ . "}"  ;
		$params_["objFirmaEncription"] 	= md5 ($params_["objFirma"]);
		
		
		$body4 							= /*--inicio view*/ view('app_sales_report/transaction_anuladas/view_a_disemp_email',$params_);//--finview
		$subject4 						= $params_["message"];
		
		
		
		
		
		
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		//Enviar Correos
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		$this->email->setFrom(EMAIL_APP);
		$this->email->setTo($parameterEmail);
		$this->email->setSubject($subject4);			
		$this->email->setMessage($body4); 
		$resultSend01 = $this->email->send();
		$resultSend02 = $this->email->printDebugger();
		echo "*****************************<br/>";
		echo print_r($resultSend02,true);
		echo "*****************************<br/>";
		sleep(60);
		//enviar al administrador de posme
		$this->email->setFrom(EMAIL_APP);
		$this->email->setTo(EMAIL_APP_COPY);
		$this->email->setSubject($subject4);			
		$this->email->setMessage($body4); 		
		$resultSend01 = $this->email->send();
		$resultSend02 = $this->email->printDebugger();
		echo "*****************************<br/>";
		echo print_r($resultSend02,true);
		echo "*****************************<br/>";
		

			
		
		
		
		
		return view('core_template/close');//--finview-r
		
	}
	
	//mandar reporte de compras
	function file_job_send_report_daly_reprote_de_compras($companyID="")
	{	
		ini_set('max_execution_time', 0); 
		$companyID 		= helper_SegmentsByIndex($this->uri->getSegments(),1,$companyID);	
		$versionTest 	= ":007:";
	
		//Obtener parametros
		$parameterEmail = $this->core_web_parameter->getParameter("CORE_PROPIETARY_EMAIL",APP_COMPANY);
		$parameterEmail = $parameterEmail->value;
		
		$parameterBalance = $this->core_web_parameter->getParameter("CORE_CUST_PRICE_BALANCE",APP_COMPANY);
		$parameterBalance = $parameterBalance->value;
		
		$parameterLastNotification 		= $this->core_web_parameter->getParameter("CORE_LAST_NOTIFICACION",APP_COMPANY);
		$parameterLastNotificationId 	= $parameterLastNotification->parameterID;
		$parameterLastNotification 		= $parameterLastNotification->value;
		
		
		$parameterDaySleep					= $this->core_web_parameter->getParameter("INVOICE_BILLING_DAY_SLEEP",$companyID);
		$parameterDaySleep					= $parameterDaySleep->value;
			
		$tocken			= '';
		//Obtener compania
		$objCompany 	= $this->Company_Model->get_rowByPK($companyID);
		//Get Logo
		$objParameter	= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
		
			
		
		$fechaNowWile  		= \DateTime::createFromFormat('Y-m-d',$parameterLastNotification);  			//ahora		
		$fechaNowWile		= $fechaNowWile->modify('-'.$parameterDaySleep.' day');		
		
		
		$fechaBeforeWile  	= \DateTime::createFromFormat('Y-m-d',date("Y-m-d"));  			
		$fechaBeforeWile	= $fechaBeforeWile->modify('-'.$parameterDaySleep.' day');	
		
		
		
		
		
		$fechaNow  		= $fechaNowWile->format("Y-m-d");	
		$fechaBefore	= "";
		if( intval($parameterDaySleep) == 0)
		{
			$fechaBefore	= $fechaBeforeWile->format("Y-m-d 23:59:59");				
		}
		else
		{
			$fechaBefore	= $fechaBeforeWile->format("Y-m-d");				
		}			
		echo "Procesando Envio: ".$fechaNow. ", Al ".$fechaBefore."<br/>"; 
	
		
	
		//Reporte de Buy
		//
		//////////////////////////////////////////////////
		//////////////////////////////////////////////////
		//Obtener Resument por transaccin
		$query			= "CALL pr_notification_buy(?,?,?,?,?);";
		$objData		= $this->Bd_Model->executeRender(
			$query,
			[APP_COMPANY,$tocken,APP_USERADMIN,$fechaNow,$fechaBefore]
		);			
		
		
		if(isset($objData)){
			$objDataResultBy["objDetail"]				= $objData;
		}
		else{
			$objDataResultBy["objDetail"]				= $objData;
		}
				
		
		
		//parametros de reportes
		$params_["objCompany"]			= $objCompany;
		$params_["objStartOn"]			= str_replace(" 00:00:00","",$fechaNow);		
		$params_["objEndOn"]			= str_replace(" 00:00:00","",$fechaBefore);				
		$params_["objDetail"]			= $objDataResultBy["objDetail"];		
		
		$params_["message"]						= str_replace(" 00:00:00","",$fechaNow)." ".$versionTest." RESUMEN DE TRANSACCION: ".$objCompany->name." 2/4";
		$params_["objFirma"] 					= "{companyID:" .  ",branchID:" .  ",userID:" . ",fechaID:" . date('Y-m-d H:i:s') . ",reportID:" . "pr_sales_get_report_sales_detail" . ",ip:". $this->request->getIPAddress() . ",sessionID:" . ",agenteID:". $this->request->getUserAgent()->getAgentString() .",lastActivity:".  /*inicio last_activity */ "activity" /*fin last_activity*/ . "}"  ;
		$params_["objFirmaEncription"] 			= md5 ($params_["objFirma"]);
		
		//vista
		$subject2 			= $params_["message"];
		$body2  			= /*--inicio view*/ view('app_notification/report_buy/view_a_disemp_email',$params_);//--finview		
		
		
		
		
		
		
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		//Enviar Correos
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		/////////////////////////////////////////////
		$this->email->setFrom(EMAIL_APP);
		$this->email->setTo($parameterEmail);
		$this->email->setSubject($subject2);			
		$this->email->setMessage($body2); 
		$resultSend01 = $this->email->send();
		$resultSend02 = $this->email->printDebugger();
		echo "*****************************<br/>";
		echo print_r($resultSend02,true);
		echo "*****************************<br/>";
		sleep(60);
		//enviar al administrador de posme
		$this->email->setFrom(EMAIL_APP);
		$this->email->setTo(EMAIL_APP_COPY);
		$this->email->setSubject($subject2);			
		$this->email->setMessage($body2); 		
		$resultSend01 = $this->email->send();
		$resultSend02 = $this->email->printDebugger();
		echo "*****************************<br/>";
		echo print_r($resultSend02,true);
		echo "*****************************<br/>";
		sleep(60);
		
		return view('core_template/close');//--finview-r
		
	}
	
	//mandar informe de moniotores de caja
	function file_monitory_cash_box($companyID="")
	{	
		ini_set('max_execution_time', 0); 
		$companyID 		= helper_SegmentsByIndex($this->uri->getSegments(),1,$companyID);	
		$versionTest 	= ":007:";
	
		//Obtener parametros
		$parameterEmail 		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_EMAIL",APP_COMPANY);
		$parameterEmail 		= $parameterEmail->value;
		
		$parameterAmountCash 	= $this->core_web_parameter->getParameter("INVOICE_BILLING_BOX_MAX_AMOUNT",APP_COMPANY);
		$parameterAmountCash 	= $parameterAmountCash->value;
			
		$tocken			= '';
		//Obtener compania
		$objCompany 	= $this->Company_Model->get_rowByPK($companyID);
		//Get Logo
		$objParameter	= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);		
			
		
		$fechaStart 	= \DateTime::createFromFormat('Y-m-d',date("Y-m-d"));  			
		$fechaStart		= $fechaStart->modify('-0 day');
		$fechaStart		= $fechaStart->format("Y-m-d 00:00:00");				
		
		$fechaEnd  		= \DateTime::createFromFormat('Y-m-d',date("Y-m-d"));  			
		$fechaEnd		= $fechaEnd->modify('-0 day');
		$fechaEnd		= $fechaEnd->format("Y-m-d 23:59:59");				
		
		
		echo "Procesando Envio: ".$fechaStart. ", Al ".$fechaEnd."<br/>"; 
		//Reporte de Caja
		//
		//////////////////////////////////////////////////
		//////////////////////////////////////////////////		
		$authorization		= 0;
		$query			= "CALL pr_box_get_report_abonos(?,?,?,?,?,?,?);";
		$objData		= $this->Bd_Model->executeRender(
			$query,
			[APP_USERADMIN,$tocken,APP_COMPANY,$authorization,$fechaStart,$fechaEnd,0]
		);			
		//Get Datos de Facturacion				
		$query			= "CALL pr_sales_get_report_sales_summary(?,?,?,?,?,?,?);";
		$objDataSales	= $this->Bd_Model->executeRender(
			$query,
			[APP_COMPANY,$tocken,APP_USERADMIN,$fechaStart,$fechaEnd,0,"-1"]
		);			
		
		$query					= "CALL pr_sales_get_report_sales_summary_credit(?,?,?,?,?,?,?);";
		$objDataSalesCredito	= $this->Bd_Model->executeRender(
			$query,
			[$companyID,$tocken,APP_USERADMIN,$fechaStart,$fechaEnd,0,"-1"]
		);					
				
				
		//Get Datos de Entrada de Efectivo y Salida				
		$query			= "CALL pr_box_get_report_input_cash(?,?,?,?,?,?,?,?);";
		$objDataCash	= $this->Bd_Model->executeRender(
			$query,
			[APP_USERADMIN,$tocken,APP_COMPANY,$authorization,$fechaStart,$fechaEnd,0,"-1"]
		);			
		
		$query			= "CALL pr_box_get_report_output_cash(?,?,?,?,?,?,?,?);";
		$objDataCashOut	= $this->Bd_Model->executeRender(
			$query,
			[APP_USERADMIN,$tocken,APP_COMPANY,$authorization,$fechaStart,$fechaEnd,0,"-1"]
		);	
		
			
		if(isset($objData))
		$objDataResult["objDetail"]					= $objData;
		else
		$objDataResult["objDetail"]					= NULL;
		
		
		if(isset($objDataSales))
		$objDataResult["objSales"]					= $objDataSales;
		else
		$objDataResult["objSales"]					= NULL;
		
		if(isset($objDataSalesCredito))
		$objDataResult["objSalesCredito"]			= $objDataSalesCredito;
		else
		$objDataResult["objSalesCredito"]			= NULL;
			
			
		if(isset($objDataCash))				
		$objDataResult["objCash"]					= $objDataCash;
		else
		$objDataResult["objCash"]					= NULL;
		
		if(isset($objDataCashOut))				
		$objDataResult["objCashOut"]				= $objDataCashOut;
		else
		$objDataResult["objCashOut"]				= NULL;
	
	
		$montoTotal = 0;
		
		//Abonos
		if($objDataResult["objDetail"] != null){
			$objTempoDetail 	= array_filter($objDataResult["objDetail"],function($var){
				if (strtoupper($var["moneda"]) == "CORDOBA")
					return true;
			});			
			$montoTotal = $montoTotal + array_sum(array_column($objTempoDetail, 'montoTotal'));
		}
		
		//Ventas de Contado
		if($objDataResult["objSales"] != null){
			$objTempoDetail 	= array_filter($objDataResult["objSales"],function($var){
				if (strtoupper($var["currencyName"]) == "CORDOBA")
					return true;
			});			
			$montoTotal = $montoTotal + array_sum(array_column($objTempoDetail, 'totalDocument'));
		}
		
		//Ventas de Credito Prima
		if($objDataResult["objSalesCredito"] != null){
			$objTempoDetail 	= array_filter($objDataResult["objSalesCredito"],function($var){
				if (strtoupper($var["currencyName"]) == "CORDOBA")
					return true;
			});			
			$montoTotal = $montoTotal + array_sum(array_column($objTempoDetail, 'receiptAmount'));
		}
		
		//Ingreos de Caja
		if($objDataResult["objCash"] != null){
			$objTempoDetail 	= array_filter($objDataResult["objCash"],function($var){
				if (strtoupper($var["moneda"]) == "CORDOBA")
					return true;
			});			
			$montoTotal = $montoTotal + array_sum(array_column($objTempoDetail, 'montoTransaccion'));
		}
		
		//Salida de Caja
		if($objDataResult["objCashOut"] != null){
			$objTempoDetail 	= array_filter($objDataResult["objCashOut"],function($var){
				if (strtoupper($var["moneda"]) == "CORDOBA")
					return true;
			});			
			$montoTotal = $montoTotal - array_sum(array_column($objTempoDetail, 'montoTransaccion'));
		}
		
		
		$params_["message"]							= str_replace(" 00:00:00","",$fechaStart)." ".$versionTest." CAJA : ".$objCompany->name." 3/4";		
		$objDataResult["mensaje"] 					= "Monto en caja: ".$montoTotal;
		$objDataResult["objCompany"] 				= $objCompany;
		$objDataResult["objLogo"] 					= $objParameter;
		$objDataResult["startOn"] 					= $fechaStart;
		$objDataResult["endOn"] 					= $fechaEnd;
		$objDataResult["objFirma"] 					= "{companyID:" .  ",branchID:" .  ",userID:" . ",fechaID:" . date('Y-m-d H:i:s') . ",reportID:" . "pr_cxc_get_report_customer_credit" . ",ip:". $this->request->getIPAddress() . ",sessionID:" . ",agenteID:". $this->request->getUserAgent()->getAgentString() .",lastActivity:".  /*inicio last_activity */ "activity" /*fin last_activity*/ . "}"  ;
		$objDataResult["objFirmaEncription"] 		= md5 ($objDataResult["objFirma"]);		
		$body3  				= /*--inicio view*/ view('core_template/email_notificacion',$objDataResult);
		$subject3 				= $params_["message"];
		
		
		
		
		if( $parameterAmountCash < $montoTotal &&  ($parameterAmountCash != 0) )
		{
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			//Enviar Correos
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			$this->email->setFrom(EMAIL_APP);
			$this->email->setTo($parameterEmail);
			$this->email->setSubject($subject3);			
			$this->email->setMessage($body3); 
			$resultSend01 = $this->email->send();
			$resultSend02 = $this->email->printDebugger();
			echo "*****************************<br/>";
			echo print_r($resultSend02,true);
			echo "*****************************<br/>";		
			sleep(60);	
			
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			//Enviar Whatsapp
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			if($this->core_web_whatsap->validSendMessage(APP_COMPANY))
			{
				$this->core_web_whatsap->sendMessage(APP_COMPANY, $objDataResult["mensaje"] );
			}
			
		}	
		
		return view('core_template/close');//--finview-r
		
	}
	
	function file_job_send_report_daly_share_sumary_80mm_general()
	{
		try
		{			
		
			ini_set('max_execution_time', 0); 
			$companyID			= 2;
			$branchID			= 2;
			$userID				= 2;
			$tocken				= '';			
			$authorization		= "1";
			
			$viewReport			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"viewReport");//--finuri	
			$startOn			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"startOn");//--finuri
			$endOn				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"endOn");//--finuri		
			$hourOn				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"hourStart");//--finuri
			$hourEnd			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"hourEnd");//--finuri
			$userIDFilter		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"userIDFilter");//--finuri	;
			$format				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"format");//--finuri	;
			
			
			$format 			= $format ?  $format : "empty";
			$viewReport			= $viewReport ? $viewReport  : "empty";
			$startOn			= $startOn ? $startOn : "empty";
			$endOn				= $endOn ?  $endOn : "empty";
			$hourOn				= $hourOn ?	$hourOn : "00";
			$hourEnd			= $hourEnd ? $hourEnd : "23";
			$userIDFilter		= $userIDFilter ? $userIDFilter : "1";
			
			
			
			//Obtener parametros email del propietario
			$parameterEmail 		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_EMAIL",APP_COMPANY);
			$parameterEmail 		= $parameterEmail->value;
			
			//Obtener la ultima notificacion
			$parameterLastNotification 		= $this->core_web_parameter->getParameter("CORE_LAST_NOTIFICACION",APP_COMPANY);
			$parameterLastNotificationId 	= $parameterLastNotification->parameterID;
			$parameterLastNotification 		= $parameterLastNotification->value;
			
			//Obtener el deslizamineto del reprote
			$parameterDaySleep					= $this->core_web_parameter->getParameter("INVOICE_BILLING_DAY_SLEEP",$companyID);
			$parameterDaySleep					= $parameterDaySleep->value;
			
			
			//Obtener la fecha inicial y fecha final del reporte
			$fechaNowWile  		= \DateTime::createFromFormat('Y-m-d',$parameterLastNotification);  			//ahora		
			$fechaNowWile		= $fechaNowWile->modify('-'.$parameterDaySleep.' day');		
			
			
			$fechaNow			= $fechaNowWile->format("Y-m-d");						
			$fechaBefore		= $fechaNowWile->format("Y-m-d")." 23:59:59";
				
			
			
			if($format == "pdf")
			{
				$fechaNow		= $startOn." ".$hourOn.":00:00";
				$fechaBefore	= $endOn." ".$hourEnd.":59:59";
			}
			
			
			
			//Procesar reporte			
			$obUserModel	= $this->User_Model->get_rowByPK($companyID,$branchID,$userIDFilter);				
			$companyID 		= $companyID;			
			$objComponent	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");			
			$objParameter	= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);			
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);
			
			$query			= "CALL pr_box_get_report_closed(?,?,?,?,?,?,?);";
			$objData		= $this->Bd_Model->executeRender(
				$query,
				[$userID,$tocken,$companyID,$authorization,$fechaNow,$fechaBefore,$userIDFilter]
			);
			
			if(isset($objData))
			$objDataResult["objDetail"]					= $objData;
			else
			$objDataResult["objDetail"]					= NULL;
			
			
				
			//parametros de reportes
			$params_["objCompany"]					= $objCompany;
			$params_["objStartOn"]					= str_replace(" 00:00:00","",$fechaNow);		
			$params_["startOn"]						= str_replace(" 00:00:00","",$fechaNow);		
			$params_["objEndOn"]					= str_replace(" 00:00:00","",$fechaBefore);				
			$params_["endOn"]						= str_replace(" 00:00:00","",$fechaBefore);		
			$params_["dateCurrent"]					= date("Y-m-d H:i:s");
			$params_["obUserModel"]					= $obUserModel;
			$params_["objDetail"]					= $objDataResult["objDetail"];		
			$params_["objLogo"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);	
			$params_["message"]						= str_replace(" 00:00:00","",$fechaNow)." CIERRE DE CAJA: ".$objCompany->name." ";
			$params_["objFirma"] 					= "{companyID:" .  ",branchID:" .  ",userID:" . ",fechaID:" . date('Y-m-d H:i:s') . ",reportID:" . "pr_box_get_report_closed" . ",ip:". $this->request->getIPAddress() . ",sessionID:" . ",agenteID:". $this->request->getUserAgent()->getAgentString() .",lastActivity:".  /*inicio last_activity */ "activity" /*fin last_activity*/ . "}"  ;
			$params_["objFirmaEncription"] 			= md5 ($params_["objFirma"]);
			$subject								= $params_["message"];
			$html  									= /*--inicio view*/ view('app_box_report/share_summary_80mm_general/view_a_disemp',$params_);//--finview
				
			if($format != "pdf")
			{
				
				echo $html;
				//enviar correo
				$this->email->setFrom(EMAIL_APP);
				$this->email->setTo($parameterEmail);
				$this->email->setSubject($subject);			
				$this->email->setMessage($html); 
				$resultSend01 = $this->email->send();
				$resultSend02 = $this->email->printDebugger();
			}
			else
			{
				$this->dompdf->loadHTML($html);
				$this->dompdf->render();
				$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
				$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
				$objParameterShowDownloadPreview	= $this->core_web_parameter->getParameter("CORE_SHOW_DOWNLOAD_PREVIEW",$companyID);
				$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview->value;
				$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview == "true" ? true : false;
			
				$fileNamePut = "caja_0_".date("dmYhis").".pdf";
				$path        = "./resource/file_company/company_".$companyID."/component_48/component_item_0/".$fileNamePut;
					
				file_put_contents($path,$this->dompdf->output());								
				chmod($path, 644);
					
				if($objParameterShowLinkDownload == "true")
				{
					echo "<a href='".base_url()."/resource/file_company/company_".$companyID."/component_48/component_item_0/".$fileNamePut."'>download caja</a>"; 				
				}
				else{			
					//visualizar
					$this->dompdf->stream("file.pdf ", ['Attachment' => $objParameterShowDownloadPreview ]);
				}
			}
			
			
				
				
			
		}
		catch(\Exception $ex)
		{
			exit($ex->getMessage());
		}
	}
	
	//mandar informe de moniotores de caja
	function file_next_date($companyID="")
	{	
		ini_set('max_execution_time', 0); 
		$companyID 		= helper_SegmentsByIndex($this->uri->getSegments(),1,$companyID);	
		
		
		$parameterLastNotification 		= $this->core_web_parameter->getParameter("CORE_LAST_NOTIFICACION",APP_COMPANY);
		$parameterLastNotificationId 	= $parameterLastNotification->parameterID;
		$parameterLastNotification 		= $parameterLastNotification->value;
		
		
			
		$parameterDaySleep					= $this->core_web_parameter->getParameter("INVOICE_BILLING_DAY_SLEEP",$companyID);
		$parameterDaySleep					= $parameterDaySleep->value;
			
			
		
		$fechaBeforeWile  				= \DateTime::createFromFormat('Y-m-d',$parameterLastNotification);  		
		$dataNewParameter 				= array();		
		$fechaBeforeWile				= $fechaBeforeWile->modify('+'.$parameterDaySleep.' day');	
		$dataNewParameter["value"] 		= $fechaBeforeWile->format("Y-m-d");
		$this->Company_Parameter_Model->update_app_posme($companyID,$parameterLastNotificationId,$dataNewParameter);	
		
		
		
		
		return view('core_template/close');//--finview-r
		
	}
	
	
	//job o proceso que me permite cancelar las facturas con balances 0 a 0.2
	function file_job_process_customer_credit_document_to_cancel($companyID="")
	{
		$companyID 						= helper_SegmentsByIndex($this->uri->getSegments(),1,$companyID);
		$objListCustomerCreditDocument 	= $this->Customer_Credit_Document_Model->get_rowByBalanceBetweenCeroAndCeroPuntoCinco($companyID);
		
		$parameterCancelCuota 		= $this->core_web_parameter->getParameter("SHARE_CANCEL",APP_COMPANY);
		$parameterCancelCuota 		= $parameterCancelCuota->value;
		
		$parameterCancelDocumento 		= $this->core_web_parameter->getParameter("SHARE_DOCUMENT_CANCEL",APP_COMPANY);
		$parameterCancelDocumento 		= $parameterCancelDocumento->value;
		
		
		//recorarer lista de documentos
		if($objListCustomerCreditDocument)
		{
			
			foreach($objListCustomerCreditDocument as $objCustomCreditDocument)
			{
				$objCustomCreditAmoritization = $this->Customer_Credit_Amortization_Model->get_rowByCreditDocumentAndBalanceMinim($objCustomCreditDocument->customerCreditDocumentID);
				if($objCustomCreditAmoritization)
				{
					
					if(count($objCustomCreditAmoritization) == 1)
					{
						
						//recorrar lista de cuotas de amortizacion
						foreach($objCustomCreditAmoritization as $i)
						{
							$data 					= null;
							$data["remaining"] 		= 0;
							$data["statusID"] 		= $parameterCancelCuota;
							$this->Customer_Credit_Amortization_Model->update_app_posme($i->creditAmortizationID,$data);
							
							
							$data 					= null;
							$data["balance"] 		= 0;
							$data["statusID"] 		= $parameterCancelDocumento;
							$this->Customer_Credit_Document_Model->update_app_posme($objCustomCreditDocument->customerCreditDocumentID,$data);
							
						}
					}
				}
			}
		}
		
		
		return view('core_template/close');//--finview-r
		
	}
	
	
	
	
	function sendWhatsappPosMeSendMessage()
	{
		//Cargar Libreria
		
		//Obtener lista de email
		$objListNotification = $this->Notification_Model->get_rowsWhatsappPosMeSendMessage(20);
		if($objListNotification)
		foreach($objListNotification as $i)
		{
			
			
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			//Enviar Whatsapp
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			if($this->core_web_whatsap->validSendMessage(APP_COMPANY))
			{
				$this->core_web_whatsap->sendMessageUltramsg(
					APP_COMPANY, 
					"Hola ".$i->to." ".$i->message,
					$i->phoneTo
				);
				
				
				$data["sendOn"]			= date_format(date_create(),"Y-m-d H:i:s");
				$data["sendWhatsappOn"]	= date_format(date_create(),"Y-m-d H:i:s");
				$this->Notification_Model->update_app_posme($i->notificationID,$data);
				
			}
				
				
				
		}
		
		echo "SUCCESS";
		
	}
	
	function sendWhatsappPosMeCalendar(){
		//Cargar Libreria
		
		//Obtener lista de email
		$objListNotification = $this->Notification_Model->get_rowsWhatsappPosMeCalendar(20);
		if($objListNotification)
		foreach($objListNotification as $i){
			
			
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			//Enviar Whatsapp
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			/////////////////////////////////////////////
			if($this->core_web_whatsap->validSendMessage(APP_COMPANY))
			{
				$this->core_web_whatsap->sendMessageUltramsg(
					APP_COMPANY, 
					"Hola mi nombre es:".$i->title." agende una cita con el objetivo ".$i->message." (".$i->phoneFrom." ".$i->from.")" 
				);
				
				
				$data["sendOn"]			= date_format(date_create(),"Y-m-d H:i:s");
				$data["sendWhatsappOn"]	= date_format(date_create(),"Y-m-d H:i:s");
				$this->Notification_Model->update_app_posme($i->notificationID,$data);
				
			}
				
				
				
		}
		
		echo "SUCCESS";
		
	}
	
	
	function sendWhatsappGlobalProLaptopMenorA14400Frecuency7Meses()
	{
		
		$objNotificar = $this->Transaction_Master_Detail_Model->GlobalPro_get_Notification_LaptopMenorA14400_7Meses();		
		if($objNotificar)
		foreach($objNotificar as $i)
		{	
			echo clearNumero($i->Destino)."---".$i->Mensaje."</br></br>";
			$this->core_web_whatsap->sendMessageByWaapi(
				APP_COMPANY, 
				replaceSimbol($i->Mensaje),
				clearNumero($i->Destino) 
			);
				
		}
		
		echo "SUCCESS";
	}
	
	
	
}
?>