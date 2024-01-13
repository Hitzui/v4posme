<?php
//posme:2023-02-27
namespace App\Controllers;
class app_cxc_customer extends _BaseController {
	
       
	function edit(){ 
		 try{ 
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCTION
			if(APP_NEED_AUTHENTICATION == true){
						$permited = false;
						$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						
						if(!$permited)
						throw new \Exception(NOT_ACCESS_CONTROL);
						
						$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"edit",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						if ($resultPermission 	== PERMISSION_NONE)
						throw new \Exception(NOT_ALL_EDIT);			
			
			}	
			
		
			//Redireccionar datos
									
			$companyID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$branchID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"branchID");//--finuri	
			$entityID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"entityID");//--finuri	
			$callback		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"callback");//--finuri	
			
			$branchIDUser	= $dataSession["user"]->branchID;
			$roleID 		= $dataSession["role"]->roleID;		
			$callback		= $callback === "" ?  "false" : $callback ;
			
			if((!$companyID || !$branchID || !$entityID))
			{ 
				$this->response->redirect(base_url()."/".'app_cxc_customer/add');	
			} 		
			
			
			//Obtener el Registro			
			$datView["objEntity"]	 			= $this->Entity_Model->get_rowByPK($companyID,$branchID,$entityID);
			$datView["objNatural"]	 			= $this->Natural_Model->get_rowByPK($companyID,$branchID,$entityID);
			$datView["objLegal"]	 			= $this->Legal_Model->get_rowByPK($companyID,$branchID,$entityID);
			$datView["objCustomer"]	 			= $this->Customer_Model->get_rowByPK($companyID,$branchID,$entityID);
			$datView["objEntityListEmail"]		= $this->Entity_Email_Model->get_rowByEntity($companyID,$branchID,$entityID);
			$datView["objEntityListPhone"]		= $this->Entity_Phone_Model->get_rowByEntity($companyID,$branchID,$entityID);
			$datView["objCustomerCredit"]		= $this->Customer_Credit_Model->get_rowByPK($companyID,$branchID,$entityID);
			$datView["objCustomerCreditLine"]	= $this->Customer_Credit_Line_Model->get_rowByEntity($companyID,$branchID,$entityID);
			$datView["objCustomerSinRiesgo"]	= $this->Customer_Consultas_Sin_Riesgo_Model->get_rowByCedula_FileName($companyID,str_replace("-","",$datView["objCustomer"]->identification));
			$datView["callback"]				= $callback;
			$objComponent						= $this->core_web_tools->getComponentIDBy_ComponentName("tb_customer");
			if(!$objComponent)
			throw new \Exception("00409 EL COMPONENTE 'tb_customer' NO EXISTE...");
			
			$objComponentAccount				= $this->core_web_tools->getComponentIDBy_ComponentName("tb_account");
			if(!$objComponentAccount)
			throw new \Exception("EL COMPONENTE 'tb_account' NO EXISTE...");
			
			
			$objListEntityAccount 						= $this->Entity_Account_Model->get_rowByEntity($companyID,$objComponent->componentID,$entityID);
			$objFirstEntityAccount						= $objListEntityAccount[0];
			$objAccount 								= $this->Account_Model->get_rowByPK($companyID,$objFirstEntityAccount->accountID);
			
			//Obtener Informacion
			$datView["objListWorkflowStage"]			= $this->core_web_workflow->getWorkflowStageByStageInit("tb_customer","statusID",$datView["objCustomer"]->statusID,$companyID,$branchIDUser,$roleID);
			$datView["objListCurrency"]					= $this->Company_Currency_Model->getByCompany($companyID);			
			$datView["objComponent"] 					= $objComponent;
			$datView["objCurrency"]						= $this->core_web_currency->getCurrencyDefault($companyID);
			$datView["objListIdentificationType"]		= $this->core_web_catalog->getCatalogAllItem("tb_customer","identificationType",$companyID);
			$datView["objListCountry"]					= $this->core_web_catalog->getCatalogAllItem("tb_customer","countryID",$companyID);
			$datView["objListState"]					= $this->core_web_catalog->getCatalogAllItem_Parent("tb_customer","stateID",$companyID,$datView["objCustomer"]->countryID);
			$datView["objListCity"]						= $this->core_web_catalog->getCatalogAllItem_Parent("tb_customer","cityID",$companyID,$datView["objCustomer"]->stateID);
			$datView["objListClasificationID"]			= $this->core_web_catalog->getCatalogAllItem("tb_customer","clasificationID",$companyID);
			$datView["objListCustomerTypeID"]			= $this->core_web_catalog->getCatalogAllItem("tb_customer","customerTypeID",$companyID);
			$datView["objListCategoryID"]				= $this->core_web_catalog->getCatalogAllItem("tb_customer","categoryID",$companyID);
			$datView["objListSubCategoryID"]			= $this->core_web_catalog->getCatalogAllItem("tb_customer","subCategoryID",$companyID);
			$datView["objListTypePay"]					= $this->core_web_catalog->getCatalogAllItem("tb_customer","typePay",$companyID);
			$datView["objListPayConditionID"]			= $this->core_web_catalog->getCatalogAllItem("tb_customer","payConditionID",$companyID);
			$datView["objListSexoID"]					= $this->core_web_catalog->getCatalogAllItem("tb_customer","sexoID",$companyID);
			
			$datView["objListEstadoCivilID"]			= $this->core_web_catalog->getCatalogAllItem("tb_naturales","statusID",$companyID);
			
			$datView["objListProfesionID"] 				= $this->core_web_catalog->getCatalogAllItem("tb_naturales","profesionID",$companyID);
			
			$datView["objListTypeFirmID"] 				= $this->core_web_catalog->getCatalogAllItem("tb_customer","typeFirm",$companyID);
			$datView["objComponentAccount"] 			= $objComponentAccount;
			$datView["objEntityAccount"] 				= $objFirstEntityAccount;
			$datView["objAccount"] 						= $objAccount;
			$datView["useMobile"]						= $dataSession["user"]->useMobile;			
			$datView["company"]							= $dataSession["company"];
			
			
			//Renderizar Resultado
			$dataSession["notification"]	= $this->core_web_error->get_error($dataSession["user"]->userID);
			$dataSession["message"]			=  $this->core_web_notification->get_message();
			
			$dataSession["head"]			= /*--inicio view*/ view('app_cxc_customer/edit_head',$datView);//--finview			
			$dataSession["body"]			= /*--inicio view*/ view('app_cxc_customer/edit_body',$datView);//--finview
			$dataSession["script"]			= /*--inicio view*/ view('app_cxc_customer/edit_script',$datView);//--finview
			$dataSession["footer"]			= "";				
			
			
			if($callback == "false")
			{
				
				return view("core_masterpage/default_masterpage",$dataSession);//--finview-r
			}
			else
			{
				
				return view("core_masterpage/default_popup",$dataSession);//--finview-r
			}
			
		}
		catch(\Exception $ex){
			exit($ex->getMessage());
		}	
	}
	function delete(){
		try{ 
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCTION
			if(APP_NEED_AUTHENTICATION == true){
						$permited = false;
						$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						
						if(!$permited)
						throw new \Exception(NOT_ACCESS_CONTROL);
						
						$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"delete",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						if ($resultPermission 	== PERMISSION_NONE)
						throw new \Exception(NOT_ALL_DELETE);			
			
			}	
			
			//Load Modelos
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			  
			  
			  
			
			//Nuevo Registro
			$companyID 			= /*inicio get post*/ $this->request->getPost("companyID");
			$branchID 			= /*inicio get post*/ $this->request->getPost("branchID");				
			$entityID 			= /*inicio get post*/ $this->request->getPost("entityID");				
			
			if((!$companyID && !$branchID && !$entityID)){
					throw new \Exception(NOT_PARAMETER);			
					 
			} 
			
			
			if ($entityID == APP_CUSTOMER01)
			{
				throw new \Exception("No es posible eliminar el cliente, edite el nombre");
			}
			
			
			if ($entityID == APP_CUSTOMER02)
			{
				throw new \Exception("No es posible eliminar el cliente, edite el nombre");
			}
			
			
			
			//OBTENER EL CLIENTE
			$objCustomer 		= $this->Customer_Model->get_rowByPK($companyID,$branchID,$entityID);	
			
			
			//PERMISO SOBRE EL REGISTRO
			if ($resultPermission 	== PERMISSION_ME && ($objCustomer->createdBy != $dataSession["user"]->userID))
			throw new \Exception(NOT_DELETE);
			
			
			//PERMISO PUEDE ELIMINAR EL REGISTRO SEGUN EL WORKFLOW
			if(!$this->core_web_workflow->validateWorkflowStage("tb_customer","statusID",$objCustomer->statusID,COMMAND_ELIMINABLE,$dataSession["user"]->companyID,$dataSession["user"]->branchID,$dataSession["role"]->roleID))
			throw new \Exception(NOT_WORKFLOW_DELETE);
			
			//Eliminar el Registro
			$this->Customer_Model->delete_app_posme($companyID,$branchID,$entityID);
					
			
			return $this->response->setJSON(array(
				'error'   => false,
				'message' => SUCCESS
			));//--finjson
			
			
		}
		catch(\Exception $ex){
			
			return $this->response->setJSON(array(
				'error'   => true,
				'message' => $ex->getLine()." ".$ex->getMessage()
			));//--finjson
			$this->core_web_notification->set_message(true,$ex->getLine()." ".$ex->getMessage());
		}		
		
	}
	function updateElement($dataSession){
		try{
			//PERMISO SOBRE LA FUNCTION
			if(APP_NEED_AUTHENTICATION == true){
				$permited = false;
				$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
				
				if(!$permited)
				throw new \Exception(NOT_ACCESS_CONTROL);
				
				$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"edit",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
				if ($resultPermission 	== PERMISSION_NONE)
				throw new \Exception(NOT_ALL_EDIT);	
			}
			
				
			
			
			
				
			
			
			
			
			
			
			
			//Obtener el Componente de Transacciones Other Input to Inventory
			$objComponent							= $this->core_web_tools->getComponentIDBy_ComponentName("tb_customer");
			if(!$objComponent)
			throw new \Exception("EL COMPONENTE 'tb_customer' NO EXISTE...");
			
			
			$branchID 								= $dataSession["user"]->branchID;
			$roleID 								= $dataSession["role"]->roleID;
			$companyID 								= $dataSession["user"]->companyID;
			
			//Moneda Dolares
			date_default_timezone_set(APP_TIMEZONE); 
			$objCurrencyDolares						= $this->core_web_currency->getCurrencyExternal($companyID);
			$dateOn 								= date("Y-m-d");
			$dateOn 								= date_format(date_create($dateOn),"Y-m-d");
			$exchangeRate 							= 0;
			$exchangeRateTotal 						= 0;
			$exchangeRateAmount 					= 0;
			
			$companyID_ 							= /*inicio get post*/ $this->request->getPost("txtCompanyID");
			$branchID_								= /*inicio get post*/ $this->request->getPost("txtBranchID");
			$entityID_								= /*inicio get post*/ $this->request->getPost("txtEntityID");
			$callback  								= /*inicio get post*/ $this->request->getPost("txtCallback"); 									
			$objCustomer							= $this->Customer_Model->get_rowByPK($companyID_,$branchID_,$entityID_);
			$oldStatusID 							= $objCustomer->statusID;
			
			//Validar Edicion por el Usuario
			if ($resultPermission 	== PERMISSION_ME && ($objCustomer->createdBy != $dataSession["user"]->userID))
			throw new \Exception(NOT_EDIT);
			
			//Validar si el estado permite editar
			if(!$this->core_web_workflow->validateWorkflowStage("tb_customer","statusID",$objCustomer->statusID,COMMAND_EDITABLE_TOTAL,$dataSession["user"]->companyID,$dataSession["user"]->branchID,$dataSession["role"]->roleID))
			throw new \Exception(NOT_WORKFLOW_EDIT);					
			
			
			
			$db=db_connect();
			$db->transStart();			
			//El Estado solo permite editar el workflow
			if($this->core_web_workflow->validateWorkflowStage("tb_customer","statusID",$oldStatusID,COMMAND_EDITABLE,$dataSession["user"]->companyID,$dataSession["user"]->branchID,$dataSession["role"]->roleID)){				
				$objCustomer["statusID"] 		= /*inicio get post*/ $this->request->getPost('txtStatusID');//--fin peticion get o post
				$this->Customer_Model->update_app_posme($companyID_,$branchID_,$entityID_,$objCustomer);
			}
			else{
				$objNatural["isActive"]		= true;
				$objNatural["firstName"]	= /*inicio get post*/ $this->request->getPost("txtFirstName");//--fin peticion get o post
				$objNatural["lastName"]		= /*inicio get post*/ $this->request->getPost("txtLastName");//--fin peticion get o post
				$objNatural["address"]		= /*inicio get post*/ $this->request->getPost("txtAddress");//--fin peticion get o post
				
				$objNatural["statusID"]		= /*inicio get post*/ $this->request->getPost("txtCivilStatusID");//--fin peticion get o post
				
				$objNatural["profesionID"]	= /*inicio get post*/ $this->request->getPost("txtProfesionID");//--fin peticion get o post
				$this->Natural_Model->update_app_posme($companyID_,$branchID_,$entityID_,$objNatural);
				$objLegal["isActive"]		= true;
				$objLegal["comercialName"]	= /*inicio get post*/ $this->request->getPost("txtCommercialName");//--fin peticion get o post
				$objLegal["legalName"]		= /*inicio get post*/ $this->request->getPost("txtLegalName");//--fin peticion get o post
				$objLegal["address"]		= /*inicio get post*/ $this->request->getPost("txtAddress");//--fin peticion get o post
				$this->Legal_Model->update_app_posme($companyID_,$branchID_,$entityID_,$objLegal);
				
				$objCustomer 						= NULL;
				$objCustomer["identificationType"]	= /*inicio get post*/ $this->request->getPost('txtIdentificationTypeID');//--fin peticion get o post
				$objCustomer["identification"]		= /*inicio get post*/ $this->request->getPost('txtIdentification');//--fin peticion get o post
				
				$validarCedula 				= $this->core_web_parameter->getParameterValue("CXC_VALIDAR_CEDULA_REPETIDA",$companyID);
				
				//validar que se permita la omision de la cedula
				if(strcmp($validarCedula,"true") == 0){
					//Validar que ya existe el cliente
					$objCustomerOld						= $this->Customer_Model->get_rowByIdentification($companyID,$objCustomer["identification"]);
					if($objCustomerOld)
					{
						if($objCustomerOld->entityID != $entityID_ )
						{
							throw new \Exception("Error identificacion del cliente ya existe.");
						}
					}
				}
				
				
				$objCustomer["countryID"]			= /*inicio get post*/ $this->request->getPost('txtCountryID');//--fin peticion get o post
				$objCustomer["stateID"]				= /*inicio get post*/ $this->request->getPost('txtStateID');//--fin peticion get o post
				$objCustomer["cityID"]				= /*inicio get post*/ $this->request->getPost("txtCityID");//--fin peticion get o post
				$objCustomer["location"]			= /*inicio get post*/ $this->request->getPost("txtLocation");//--fin peticion get o post
				$objCustomer["address"]				= /*inicio get post*/ $this->request->getPost("txtAddress");//--fin peticion get o post
				$objCustomer["currencyID"]			= /*inicio get post*/ $this->request->getPost("txtCurrencyID");//--fin peticion get o post
				$objCustomer["clasificationID"]		= /*inicio get post*/ $this->request->getPost('txtClasificationID');//--fin peticion get o post
				$objCustomer["categoryID"]			= /*inicio get post*/ $this->request->getPost('txtCategoryID');//--fin peticion get o post
				$objCustomer["subCategoryID"]		= /*inicio get post*/ $this->request->getPost('txtSubCategoryID');//--fin peticion get o post
				$objCustomer["customerTypeID"]		= /*inicio get post*/ $this->request->getPost("txtCustomerTypeID");//--fin peticion get o post
				$objCustomer["birthDate"]			= /*inicio get post*/ $this->request->getPost("txtBirthDate");//--fin peticion get o post
				$objCustomer["statusID"]			= /*inicio get post*/ $this->request->getPost('txtStatusID');//--fin peticion get o post
				$objCustomer["typePay"]				= /*inicio get post*/ $this->request->getPost('txtTypePayID');//--fin peticion get o post
				$objCustomer["payConditionID"]		= /*inicio get post*/ $this->request->getPost('txtPayConditionID');//--fin peticion get o post
				$objCustomer["sexoID"]				= /*inicio get post*/ $this->request->getPost('txtSexoID');//--fin peticion get o post
				$objCustomer["reference1"]			= /*inicio get post*/ $this->request->getPost("txtReference1");//--fin peticion get o post
				$objCustomer["reference2"]			= /*inicio get post*/ $this->request->getPost("txtReference2");//--fin peticion get o post
				$objCustomer["balancePoint"]		= /*inicio get post*/ $this->request->getPost("txtBalancePoint");//--fin peticion get o post
				$objCustomer["phoneNumber"]			= /*inicio get post*/ $this->request->getPost("txtPhoneNumber");//--fin peticion get o post
				$objCustomer["typeFirm"]			= /*inicio get post*/ $this->request->getPost("txtTypeFirmID");//--fin peticion get o post
				$objCustomer["isActive"]			= true;
				$this->Customer_Model->update_app_posme($companyID_,$branchID_,$entityID_,$objCustomer);
				
				//Actualizar Customer Credit
				$objCustomerCredit 							= $this->Customer_Credit_Model->get_rowByPK($companyID_,$branchID_,$entityID_);
				$objCustomerCreditNew["limitCreditDol"] 	= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtLimitCreditDol"));
				$objCustomerCreditNew["balanceDol"] 		= $objCustomerCreditNew["limitCreditDol"] - ($objCustomerCredit->limitCreditDol - $objCustomerCredit->balanceDol);
				$objCustomerCreditNew["incomeDol"] 			= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtIncomeDol"));
				$this->Customer_Credit_Model->update_app_posme($companyID_,$branchID_,$entityID_,$objCustomerCreditNew);
				
				//actualizar cuenta
				$objListEntityAccount 					= $this->Entity_Account_Model->get_rowByEntity($companyID_,$objComponent->componentID,$entityID_);
				$objFirstEntityAccount					= $objListEntityAccount[0];
				
				$objEntityAccount["accountID"]			= empty(/*inicio get post*/ $this->request->getPost("txtAccountID")) ? 0 : /*inicio get post*/ $this->request->getPost("txtAccountID");
				$this->Entity_Account_Model->update_app_posme($objFirstEntityAccount->entityAccountID,$objEntityAccount);
			
			}
			
			
			//Email
			$this->Entity_Email_Model->deleteByEntity($companyID_,$branchID_,$entityID_);
			$arrayListEntityEmail 				= /*inicio get post*/ $this->request->getPost("txtEntityEmail");
			$arrayListEntityEmailIsPrimary		= /*inicio get post*/ $this->request->getPost("txtEmailIsPrimary");			
			if(!empty($arrayListEntityEmail))
			foreach($arrayListEntityEmail as $key => $value){
				$objEntityEmail["companyID"]	= $companyID_;
				$objEntityEmail["branchID"]		= $branchID_;
				$objEntityEmail["entityID"]		= $entityID_;
				$objEntityEmail["email"]		= $value;
				$objEntityEmail["isPrimary"]	= $arrayListEntityEmailIsPrimary[$key] == 1 ? true : false;
				$this->Entity_Email_Model->insert_app_posme($objEntityEmail);
			}
			
			//Phone
			$this->Entity_Phone_Model->deleteByEntity($companyID_,$branchID_,$entityID_);
			$arrayListEntityPhoneTypeID			= /*inicio get post*/ $this->request->getPost("txtEntityPhoneTypeID");
			$arrayListEntityPhoneNumber 		= /*inicio get post*/ $this->request->getPost("txtEntityPhoneNumber");
			$arrayListEntityPhoneIsPrimary 		= /*inicio get post*/ $this->request->getPost("txtEntityPhoneIsPrimary");			
			if(!empty($arrayListEntityPhoneTypeID))
			foreach($arrayListEntityPhoneTypeID as $key => $value){
				$objEntityPhone["companyID"]	= $companyID_;
				$objEntityPhone["branchID"]		= $branchID_;
				$objEntityPhone["entityID"]		= $entityID_;
				$objEntityPhone["typeID"]		= $value;
				$objEntityPhone["number"]		= $arrayListEntityPhoneNumber[$key];
				$objEntityPhone["isPrimary"]	= $arrayListEntityPhoneIsPrimary[$key];
				$this->Entity_Phone_Model->insert_app_posme($objEntityPhone);
			}	
			
			//Lineas de Creditos
			$arrayListCustomerCreditLineID	= /*inicio get post*/ $this->request->getPost("txtCustomerCreditLineID");
			$arrayListCreditLineID			= /*inicio get post*/ $this->request->getPost("txtCreditLineID");
			$arrayListCreditCurrencyID		= /*inicio get post*/ $this->request->getPost("txtCreditCurrencyID");
			$arrayListCreditStatusID		= /*inicio get post*/ $this->request->getPost("txtCreditStatusID");
			$arrayListCreditInterestYear	= /*inicio get post*/ $this->request->getPost("txtCreditInterestYear");
			$arrayListCreditInterestPay		= /*inicio get post*/ $this->request->getPost("txtCreditInterestPay");
			$arrayListCreditTotalPay		= /*inicio get post*/ $this->request->getPost("txtCreditTotalPay");
			$arrayListCreditTotalDefeated	= /*inicio get post*/ $this->request->getPost("txtCreditTotalDefeated");
			$arrayListCreditDateOpen		= /*inicio get post*/ $this->request->getPost("txtCreditDateOpen");
			$arrayListCreditPeriodPay		= /*inicio get post*/ $this->request->getPost("txtCreditPeriodPay");
			$arrayListCreditDateLastPay		= /*inicio get post*/ $this->request->getPost("txtCreditDateLastPay");
			$arrayListCreditTerm			= /*inicio get post*/ $this->request->getPost("txtCreditTerm");
			$arrayListCreditNote			= /*inicio get post*/ $this->request->getPost("txtCreditNote");
			$arrayListCreditLine			= /*inicio get post*/ $this->request->getPost("txtLine");
			$arrayListCreditNumber			= /*inicio get post*/ $this->request->getPost("txtLineNumber");
			$arrayListCreditLimit			= /*inicio get post*/ $this->request->getPost("txtLineLimit");
			$arrayListCreditBalance			= /*inicio get post*/ $this->request->getPost("txtLineBalance");
			$arrayListCreditStatus			= /*inicio get post*/ $this->request->getPost("txtLineStatus");
			$arrayListTypeAmortization		= /*inicio get post*/ $this->request->getPost("txtTypeAmortization");
			$limitCreditLine 				= 0;
			//Limpiar Lineas de Creditos
			$this->Customer_Credit_Line_Model->deleteWhereIDNotIn($companyID_,$branchID_,$entityID_,$arrayListCustomerCreditLineID);
			
			if(!empty($arrayListCustomerCreditLineID))
			foreach($arrayListCustomerCreditLineID as $key => $value){
			
				$customerCreditLineID 						= $value;
				if($customerCreditLineID == 0 ){
					$objCustomerCreditLine					= NULL;
					$objCustomerCreditLine["companyID"]		= $companyID_;
					$objCustomerCreditLine["branchID"]		= $branchID_;
					$objCustomerCreditLine["entityID"]		= $entityID_;
					$objCustomerCreditLine["creditLineID"]	= $arrayListCreditLineID[$key];
					$objCustomerCreditLine["accountNumber"]	= $this->core_web_counter->goNextNumber($dataSession["user"]->companyID,$dataSession["user"]->branchID,"tb_customer_credit_line",0);
					$objCustomerCreditLine["currencyID"]	= $arrayListCreditCurrencyID[$key];
					$objCustomerCreditLine["limitCredit"]	= helper_StringToNumber($arrayListCreditLimit[$key]);
					$objCustomerCreditLine["balance"]		= helper_StringToNumber($arrayListCreditLimit[$key]);
					$objCustomerCreditLine["interestYear"]	= helper_StringToNumber($arrayListCreditInterestYear[$key]);
					$objCustomerCreditLine["interestPay"]	= $arrayListCreditInterestPay[$key];
					$objCustomerCreditLine["totalPay"]		= $arrayListCreditTotalPay[$key];
					$objCustomerCreditLine["totalDefeated"]	= $arrayListCreditTotalDefeated[$key];
					$objCustomerCreditLine["dateOpen"]		= date("Y-m-d");
					$objCustomerCreditLine["periodPay"]		= $arrayListCreditPeriodPay[$key];
					$objCustomerCreditLine["dateLastPay"]	= date("Y-m-d");
					$objCustomerCreditLine["term"]			= helper_StringToNumber($arrayListCreditTerm[$key]);
					$objCustomerCreditLine["note"]			= $arrayListCreditNote[$key];
					$objCustomerCreditLine["statusID"]		= $arrayListCreditStatusID[$key];
					$objCustomerCreditLine["isActive"]		= 1;
					$objCustomerCreditLine["typeAmortization"]		= $arrayListTypeAmortization[$key];
					$limitCreditLine 								= $limitCreditLine + $objCustomerCreditLine["limitCredit"];
					$exchangeRate 									= $this->core_web_currency->getRatio($companyID,$dateOn,1,$objCustomerCreditLine["currencyID"],$objCurrencyDolares->currencyID,);
					$exchangeRateAmount								= $objCustomerCreditLine["limitCredit"];
					$this->Customer_Credit_Line_Model->insert_app_posme($objCustomerCreditLine);
					
					if($objCustomerCreditLine["balance"] > $objCustomerCreditLine["limitCredit"])
					throw new \Exception("BALANCE NO PUEDE SER MAYOR QUE EL LIMITE EN LA LINEA");
				}
				else{					
					$objCustomerCreditLine 							= $this->Customer_Credit_Line_Model->get_rowByPK($customerCreditLineID);
					$objCustomerCreditLineNew						= NULL;
					$objCustomerCreditLineNew["creditLineID"]		= $arrayListCreditLineID[$key];
					$objCustomerCreditLineNew["limitCredit"]		= helper_StringToNumber($arrayListCreditLimit[$key]);
					$objCustomerCreditLineNew["interestYear"]		= helper_StringToNumber($arrayListCreditInterestYear[$key]);
					$objCustomerCreditLineNew["balance"] 			= $objCustomerCreditLineNew["limitCredit"] - ($objCustomerCreditLine->limitCredit - $objCustomerCreditLine->balance);
					$objCustomerCreditLineNew["periodPay"]			= $arrayListCreditPeriodPay[$key];
					$objCustomerCreditLineNew["term"]				= helper_StringToNumber($arrayListCreditTerm[$key]);
					$objCustomerCreditLineNew["note"]				= $arrayListCreditNote[$key];
					$objCustomerCreditLineNew["statusID"]			= $arrayListCreditStatusID[$key];
					$objCustomerCreditLineNew["typeAmortization"]		= $arrayListTypeAmortization[$key];
					$limitCreditLine 									= $limitCreditLine + $objCustomerCreditLineNew["limitCredit"];
					$exchangeRate 										= $this->core_web_currency->getRatio($companyID,$dateOn,1,$objCustomerCreditLine->currencyID,$objCurrencyDolares->currencyID);					
					$exchangeRateAmount									= $objCustomerCreditLineNew["limitCredit"];
					
					//Si el balance es mayor que el limite igual el balance al limite
					if($objCustomerCreditLineNew["balance"] > $objCustomerCreditLineNew["limitCredit"])
					$objCustomerCreditLineNew["balance"] = $objCustomerCreditLineNew["limitCredit"];
					
					//actualizar
					$this->Customer_Credit_Line_Model->update_app_posme($customerCreditLineID,$objCustomerCreditLineNew);
					
					
			
				}
				
				
				
				
				//sumar los limites en dolares
				if($exchangeRate == 1)
					$exchangeRateTotal = $exchangeRateTotal + $exchangeRateAmount;
				//sumar los limite en cordoba
				else
					$exchangeRateTotal = $exchangeRateTotal + ($exchangeRateAmount / $exchangeRate);
					
				
			}
			
			
			//Validar Limite de Credito
			if($exchangeRateTotal > $objCustomerCreditNew["limitCreditDol"])
			throw new \Exception("LINEAS DE CREDITOS MAL CONFIGURADAS LÍMITE EXCEDIDO");
			
			//Actualizar Balance
			if($objCustomerCreditNew["balanceDol"] > $objCustomerCreditNew["limitCreditDol"]){
				$objCustomerCreditNew["balanceDol"] = $objCustomerCreditNew["limitCreditDol"];
				$this->Customer_Credit_Model->update_app_posme($companyID_,$branchID_,$entityID_,$objCustomerCreditNew);
			}
			
			//Confirmar Entidad
			if($db->transStatus() !== false){
				$db->transCommit();						
				$this->core_web_notification->set_message(false,SUCCESS);
				$this->response->redirect(base_url()."/".'app_cxc_customer/edit/companyID/'.$companyID_."/branchID/".$branchID_."/entityID/".$entityID_."/callback/".$callback);
			}
			else{
				$db->transRollback();						
				$this->core_web_notification->set_message(true,$this->db->_error_message());
				$this->response->redirect(base_url()."/".'app_cxc_customer/add');	
			}
		}
		catch(\Exception $ex){
			exit($ex->getMessage());
		}			
	}
	function insertElement($dataSession){
		try{
			
			
			
			//PERMISO SOBRE LA FUNCTION
			if(APP_NEED_AUTHENTICATION == true){
				$permited = false;
				$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
				
				if(!$permited)
				throw new \Exception(NOT_ACCESS_CONTROL);
				
				$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"add",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
				if ($resultPermission 	== PERMISSION_NONE)
				throw new \Exception(NOT_ALL_INSERT);	
			}	
			
			//Obtener el Componente de Transacciones Other Input to Inventory
			$objComponent							= $this->core_web_tools->getComponentIDBy_ComponentName("tb_customer");
			if(!$objComponent)
			throw new \Exception("EL COMPONENTE 'tb_customer' NO EXISTE...");
			
			//Obtener transaccion
			$companyID 								= $dataSession["user"]->companyID;			
			$objEntity["companyID"] 				= $dataSession["user"]->companyID;			
			$objEntity["branchID"]					= $dataSession["user"]->branchID;	
			$branchID 								= $dataSession["user"]->branchID;
			$roleID 								= $dataSession["role"]->roleID;
			$this->core_web_auditoria->setAuditCreated($objEntity,$dataSession,$this->request);
			
			//Moneda Dolares
			date_default_timezone_set(APP_TIMEZONE); 
			$objCurrencyDolares						= $this->core_web_currency->getCurrencyExternal($companyID);
			$dateOn 								= date("Y-m-d");
			$dateOn 								= date_format(date_create($dateOn),"Y-m-d");
			$exchangeRate 							= 0;
			$exchangeRateTotal 						= 0;
			$exchangeRateAmount 					= 0;
			
			
			
			$this->core_web_permission->getValueLicense($dataSession["user"]->companyID,get_class($this)."/"."index");
			$db=db_connect();
			$db->transStart();
			
			
			$entityID = $this->Entity_Model->insert_app_posme($objEntity);
			$callback  					= /*inicio get post*/ $this->request->getPost("txtCallback"); 
			$objNatural["companyID"]	= $objEntity["companyID"];
			$objNatural["branchID"] 	= $objEntity["branchID"];
			$objNatural["entityID"]		= $entityID;
			$objNatural["isActive"]		= true;
			$objNatural["firstName"]	= /*inicio get post*/ $this->request->getPost("txtFirstName");//--fin peticion get o post
			$objNatural["lastName"]		= /*inicio get post*/ $this->request->getPost("txtLastName");//--fin peticion get o post
			$objNatural["address"]		= /*inicio get post*/ $this->request->getPost("txtAddress");//--fin peticion get o post
			$objNatural["statusID"]		= /*inicio get post*/ $this->request->getPost("txtCivilStatusID");//--fin peticion get o post
			$objNatural["profesionID"]	= /*inicio get post*/ $this->request->getPost("txtProfesionID");//--fin peticion get o post
			$result 					= $this->Natural_Model->insert_app_posme($objNatural);
			
			$objLegal["companyID"]		= $objEntity["companyID"];
			$objLegal["branchID"]		= $objEntity["branchID"];
			$objLegal["entityID"]		= $entityID;
			$objLegal["isActive"]		= true;
			$objLegal["comercialName"]	= /*inicio get post*/ $this->request->getPost("txtCommercialName");//--fin peticion get o post
			$objLegal["legalName"]		= /*inicio get post*/ $this->request->getPost("txtLegalName");//--fin peticion get o post
			$objLegal["address"]		= /*inicio get post*/ $this->request->getPost("txtAddress");//--fin peticion get o post
			$result 					= $this->Legal_Model->insert_app_posme($objLegal);
			
			$paisDefault 				= $this->core_web_parameter->getParameterValue("CXC_PAIS_DEFAULT",$companyID);
			$departamentoDefault 		= $this->core_web_parameter->getParameterValue("CXC_DEPARTAMENTO_DEFAULT",$companyID);
			$municipioDefault 			= $this->core_web_parameter->getParameterValue("CXC_MUNICIPIO_DEFAULT",$companyID);
			$plazoDefault 				= $this->core_web_parameter->getParameterValue("CXC_PLAZO_DEFAULT",$companyID);
			$typeAmortizationDefault 	= $this->core_web_parameter->getParameterValue("CXC_TYPE_AMORTIZATION",$companyID);
			$frecuencyDefault 			= $this->core_web_parameter->getParameterValue("CXC_FRECUENCIA_PAY_DEFAULT",$companyID);
			$creditLineDefault 			= $this->core_web_parameter->getParameterValue("CXC_CREDIT_LINE_DEFAULT",$companyID);
			$validarCedula 				= $this->core_web_parameter->getParameterValue("CXC_VALIDAR_CEDULA_REPETIDA",$companyID);
			
			
			$paisID = empty (/*inicio get post*/ $this->request->getPost('txtCountryID') /*//--fin peticion get o post*/ ) ?  $paisDefault : /*inicio get post*/ $this->request->getPost('txtCountryID');  /*//--fin peticion get o post*/
			$departamentoId= empty (/*inicio get post*/ $this->request->getPost('txtStateID') /*//--fin peticion get o post*/ ) ?  $departamentoDefault : /*inicio get post*/ $this->request->getPost('txtStateID');  /*//--fin peticion get o post*/
			$municipioId= empty (/*inicio get post*/ $this->request->getPost('txtCityID') /*//--fin peticion get o post*/ ) ?  $municipioDefault : /*inicio get post*/ $this->request->getPost('txtCityID');  /*//--fin peticion get o post*/
			
			
			$objCustomer["companyID"]			= $objEntity["companyID"];
			$objCustomer["branchID"]			= $objEntity["branchID"];
			$objCustomer["entityID"]			= $entityID;
			$objCustomer["customerNumber"]		= $this->core_web_counter->goNextNumber($dataSession["user"]->companyID,$dataSession["user"]->branchID,"tb_customer",0);
			$objCustomer["identificationType"]	= /*inicio get post*/ $this->request->getPost('txtIdentificationTypeID');//--fin peticion get o post
			$objCustomer["identification"]		= /*inicio get post*/ $this->request->getPost('txtIdentification');//--fin peticion get o post
			

			//validar que se permita la omision de la cedula
			if(strcmp($validarCedula,"true") == 0)
			{
				//Validar que ya existe el cliente
				$objCustomerOld					= $this->Customer_Model->get_rowByIdentification($companyID,$objCustomer["identification"]);
				if($objCustomerOld)
				{
					throw new \Exception("Error identificacion del cliente ya existe.");
				}
			} 
				
			
			$objCustomer["countryID"]			= $paisID;
			$objCustomer["stateID"]				= $departamentoId;
			$objCustomer["cityID"]				= $municipioId;
			$objCustomer["location"]			= /*inicio get post*/ $this->request->getPost("txtLocation");//--fin peticion get o post
			$objCustomer["address"]				= /*inicio get post*/ $this->request->getPost("txtAddress");//--fin peticion get o post
			$objCustomer["currencyID"]			= /*inicio get post*/ $this->request->getPost("txtCurrencyID");//--fin peticion get o post
			$objCustomer["clasificationID"]		= /*inicio get post*/ $this->request->getPost('txtClasificationID');//--fin peticion get o post
			$objCustomer["categoryID"]			= /*inicio get post*/ $this->request->getPost('txtCategoryID');//--fin peticion get o post
			$objCustomer["subCategoryID"]		= /*inicio get post*/ $this->request->getPost('txtSubCategoryID');//--fin peticion get o post
			$objCustomer["customerTypeID"]		= /*inicio get post*/ $this->request->getPost("txtCustomerTypeID");//--fin peticion get o post
			$objCustomer["birthDate"]			= /*inicio get post*/ $this->request->getPost("txtBirthDate");//--fin peticion get o post
			$objCustomer["statusID"]			= /*inicio get post*/ $this->request->getPost('txtStatusID');//--fin peticion get o post
			$objCustomer["typePay"]				= /*inicio get post*/ $this->request->getPost('txtTypePayID');//--fin peticion get o post
			$objCustomer["payConditionID"]		= /*inicio get post*/ $this->request->getPost('txtPayConditionID');//--fin peticion get o post
			$objCustomer["sexoID"]				= /*inicio get post*/ $this->request->getPost('txtSexoID');//--fin peticion get o post
			$objCustomer["reference1"]			= /*inicio get post*/ $this->request->getPost("txtReference1");//--fin peticion get o post
			$objCustomer["reference2"]			= /*inicio get post*/ $this->request->getPost("txtReference2");//--fin peticion get o post
			$objCustomer["balancePoint"]		= /*inicio get post*/ $this->request->getPost("txtBalancePoint");//--fin peticion get o post
			$objCustomer["phoneNumber"]			= /*inicio get post*/ $this->request->getPost("txtPhoneNumber");//--fin peticion get o post
			$objCustomer["typeFirm"]			= /*inicio get post*/ $this->request->getPost("txtTypeFirmID");//--fin peticion get o post
			$objCustomer["isActive"]			= true;
			$this->core_web_auditoria->setAuditCreated($objCustomer,$dataSession,$this->request);
			$result 							= $this->Customer_Model->insert_app_posme($objCustomer);
			
			//Ingresar registro en el lector biometrico				
			$dataUser["id"]							= $entityID;
			$dataUser["name"]						= "buscar en otra base";
			$dataUser["email"]						= "buscar en otra base";
			$dataUser["email_verified_at"]			= "0000-00-00 00:00:00";
			$dataUser["password"]					= "buscar en otra base";
			$dataUser["remember_token"]				= "buscar en otra base";
			$dataUser["created_at"]					= "0000-00-00 00:00:00";
			$dataUser["updated_at"]					= "0000-00-00 00:00:00";
			$dataUser["image"]						= "";
			$resultUser 							= $this->Biometric_User_Model->delete_app_posme($dataUser["id"]);
			$resultUser 							= $this->Biometric_User_Model->insert_app_posme($dataUser);
			
			
			
			//Ingresar Cuenta
			$objEntityAccount["companyID"]			= $objEntity["companyID"];
			$objEntityAccount["componentID"]		= $objComponent->componentID;
			$objEntityAccount["componentItemID"]	= $entityID;
			$objEntityAccount["name"]				= "";
			$objEntityAccount["description"]		= "";
			$objEntityAccount["accountTypeID"]		= "0";
			$objEntityAccount["currencyID"]			= "0";
			$objEntityAccount["classID"]			= "0";
			$objEntityAccount["balance"]			= "0";
			$objEntityAccount["creditLimit"]		= "0";
			$objEntityAccount["maxCredit"]			= "0";
			$objEntityAccount["debitLimit"]			= "0";
			$objEntityAccount["maxDebit"]			= "0";
			$objEntityAccount["statusID"]			= "0";
			
			$objEntityAccount["accountID"]			= empty(/*inicio get post*/ $this->request->getPost("txtAccountID")) ? '0': /*inicio get post*/ $this->request->getPost("txtAccountID");
			$objEntityAccount["statusID"]			= "0";
			$objEntityAccount["isActive"]			= 1;
			$this->core_web_auditoria->setAuditCreated($objEntityAccount,$dataSession,$this->request);
			$this->Entity_Account_Model->insert_app_posme($objEntityAccount);
			
			//Ingresar Customer Credit
			$objCustomerCredit["companyID"] 		= $objEntity["companyID"];
			$objCustomerCredit["branchID"] 			= $objEntity["branchID"];
			$objCustomerCredit["entityID"] 			= $entityID;
			$objCustomerCredit["limitCreditDol"] 	= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtLimitCreditDol"));
			$objCustomerCredit["balanceDol"] 		= $objCustomerCredit["limitCreditDol"];
			$objCustomerCredit["incomeDol"] 		= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtIncomeDol"));
			$this->Customer_Credit_Model->insert_app_posme($objCustomerCredit);
			
			//Email
			$arrayListEntityEmail 				= /*inicio get post*/ $this->request->getPost("txtEntityEmail");
			$arrayListEntityEmailIsPrimary		= /*inicio get post*/ $this->request->getPost("txtEmailIsPrimary");			
			if(!empty($arrayListEntityEmail))
			foreach($arrayListEntityEmail as $key => $value){
				$objEntityEmail["companyID"]	= $objEntity["companyID"];
				$objEntityEmail["branchID"]		= $objEntity["branchID"];
				$objEntityEmail["entityID"]		= $entityID;
				$objEntityEmail["email"]		= $value;
				$objEntityEmail["isPrimary"]	= $arrayListEntityEmailIsPrimary[$key];
				$this->Entity_Email_Model->insert_app_posme($objEntityEmail);
			}
			
			//Phone
			$arrayListEntityPhoneTypeID			= /*inicio get post*/ $this->request->getPost("txtEntityPhoneTypeID");
			$arrayListEntityPhoneNumber 		= /*inicio get post*/ $this->request->getPost("txtEntityPhoneNumber");
			$arrayListEntityPhoneIsPrimary 		= /*inicio get post*/ $this->request->getPost("txtEntityPhoneIsPrimary");			
			if(!empty($arrayListEntityPhoneTypeID))
			foreach($arrayListEntityPhoneTypeID as $key => $value){
				$objEntityPhone["companyID"]	= $objEntity["companyID"];
				$objEntityPhone["branchID"]		= $objEntity["branchID"];
				$objEntityPhone["entityID"]		= $entityID;
				$objEntityPhone["typeID"]		= $value;
				$objEntityPhone["number"]		= $arrayListEntityPhoneNumber[$key];
				$objEntityPhone["isPrimary"]	= $arrayListEntityPhoneIsPrimary[$key];
				$this->Entity_Phone_Model->insert_app_posme($objEntityPhone);
			}
			
			//Lineas de Creditos
			$arrayListCustomerCreditLineID	= /*inicio get post*/ $this->request->getPost("txtCustomerCreditLineID");
			$arrayListCreditLineID			= /*inicio get post*/ $this->request->getPost("txtCreditLineID");
			$arrayListCreditCurrencyID		= /*inicio get post*/ $this->request->getPost("txtCreditCurrencyID");
			$arrayListCreditStatusID		= /*inicio get post*/ $this->request->getPost("txtCreditStatusID");
			$arrayListCreditInterestYear	= /*inicio get post*/ $this->request->getPost("txtCreditInterestYear");
			$arrayListCreditInterestPay		= /*inicio get post*/ $this->request->getPost("txtCreditInterestPay");
			$arrayListCreditTotalPay		= /*inicio get post*/ $this->request->getPost("txtCreditTotalPay");
			$arrayListCreditTotalDefeated	= /*inicio get post*/ $this->request->getPost("txtCreditTotalDefeated");
			$arrayListCreditDateOpen		= /*inicio get post*/ $this->request->getPost("txtCreditDateOpen");
			$arrayListCreditPeriodPay		= /*inicio get post*/ $this->request->getPost("txtCreditPeriodPay");
			$arrayListCreditDateLastPay		= /*inicio get post*/ $this->request->getPost("txtCreditDateLastPay");
			$arrayListCreditTerm			= /*inicio get post*/ $this->request->getPost("txtCreditTerm");
			$arrayListCreditNote			= /*inicio get post*/ $this->request->getPost("txtCreditNote");
			$arrayListCreditLine			= /*inicio get post*/ $this->request->getPost("txtLine");
			$arrayListCreditNumber			= /*inicio get post*/ $this->request->getPost("txtLineNumber");
			$arrayListCreditLimit			= /*inicio get post*/ $this->request->getPost("txtLineLimit");
			$arrayListCreditBalance			= /*inicio get post*/ $this->request->getPost("txtLineBalance");
			$arrayListCreditStatus			= /*inicio get post*/ $this->request->getPost("txtLineStatus");
			$arrayListTypeAmortization		= /*inicio get post*/ $this->request->getPost("txtTypeAmortization");			
			$limitCreditLine 				= 0;
			
			if(empty($arrayListCustomerCreditLineID))
			{
				 $arrayListCustomerCreditLineID[0]	= 1;
				 $arrayListCreditLineID[0] 			= $creditLineDefault;
				 $arrayListCreditCurrencyID[0]		= $this->core_web_currency->getCurrencyDefault($companyID)->currencyID;
				 $arrayListCreditLimit[0]			= 80000;
				 $arrayListCreditInterestYear[0]	= 0;
				 $arrayListCreditInterestPay[0]		= 0;
				 $arrayListCreditTotalPay[0]		= 0;
				 $arrayListCreditTotalDefeated[0]	= 0;
				 $arrayListCreditPeriodPay[0]		= $frecuencyDefault;
				 $arrayListCreditTerm[0]			= $plazoDefault;
				 $arrayListCreditNote[0]			= "-";
				 $arrayListTypeAmortization[0]		= $typeAmortizationDefault;
				 $arrayListCreditStatusID[0]		= $this->core_web_workflow->getWorkflowInitStage("tb_customer_credit_line","statusID",$companyID,$branchID,$roleID)[0]->workflowStageID;
				 
			}
			
			if(!empty($arrayListCustomerCreditLineID))
			{
				foreach($arrayListCustomerCreditLineID as $key => $value)
				{
					$objCustomerCreditLine["companyID"]		= $objEntity["companyID"];
					$objCustomerCreditLine["branchID"]		= $objEntity["branchID"];
					$objCustomerCreditLine["entityID"]		= $entityID;
					$objCustomerCreditLine["creditLineID"]	= $arrayListCreditLineID[$key];
					$objCustomerCreditLine["accountNumber"]	= $this->core_web_counter->goNextNumber($dataSession["user"]->companyID,$dataSession["user"]->branchID,"tb_customer_credit_line",0);
					$objCustomerCreditLine["currencyID"]	= $arrayListCreditCurrencyID[$key];
					$objCustomerCreditLine["limitCredit"]	= helper_StringToNumber($arrayListCreditLimit[$key]);
					$objCustomerCreditLine["balance"]		= helper_StringToNumber($arrayListCreditLimit[$key]);
					$objCustomerCreditLine["interestYear"]	= helper_StringToNumber($arrayListCreditInterestYear[$key]);
					$objCustomerCreditLine["interestPay"]	= $arrayListCreditInterestPay[$key];
					$objCustomerCreditLine["totalPay"]		= $arrayListCreditTotalPay[$key];
					$objCustomerCreditLine["totalDefeated"]	= $arrayListCreditTotalDefeated[$key];
					$objCustomerCreditLine["dateOpen"]		= date("Y-m-d");
					$objCustomerCreditLine["periodPay"]		= $arrayListCreditPeriodPay[$key];
					$objCustomerCreditLine["dateLastPay"]	= date("Y-m-d");
					$objCustomerCreditLine["term"]			= helper_StringToNumber($arrayListCreditTerm[$key]);
					$objCustomerCreditLine["note"]			= $arrayListCreditNote[$key];
					$objCustomerCreditLine["statusID"]		= $arrayListCreditStatusID[$key];
					$objCustomerCreditLine["isActive"]		= 1;
					$objCustomerCreditLine["typeAmortization"]	= $arrayListTypeAmortization[$key];
					$limitCreditLine 							= $limitCreditLine + $objCustomerCreditLine["limitCredit"];
					$exchangeRate 								= $this->core_web_currency->getRatio($companyID,$dateOn,1,$objCustomerCreditLine["currencyID"],$objCurrencyDolares->currencyID);//cordobas a dolares, o dolares a dolares.
					$exchangeRateAmount							= $objCustomerCreditLine["limitCredit"];
					$this->Customer_Credit_Line_Model->insert_app_posme($objCustomerCreditLine);
					
					
					
					
					//sumar los limites en dolares
					if($exchangeRate == 1)
						$exchangeRateTotal = $exchangeRateTotal + $exchangeRateAmount;
					//sumar los limite en cordoba
					else
						$exchangeRateTotal = $exchangeRateTotal + ($exchangeRateAmount / $exchangeRate);
					
					
					
				}
			}
			
			
			//Validar Limite de Credito
			if($exchangeRateTotal > $objCustomerCredit["limitCreditDol"])
			throw new \Exception("LINEAS DE CREDITOS MAL CONFIGURADAS LÍMITE EXCEDIDO");
			
			//Crear la Carpeta para almacenar los Archivos del Cliente
			$pathfile = PATH_FILE_OF_APP."/company_".$companyID."/component_".$objComponent->componentID."/component_item_".$entityID;			
			
			if (!file_exists($pathfile))
			{
				mkdir($pathfile, 0700);
			}
			
		
			
			if($db->transStatus() !== false){
				$db->transCommit();						
				$this->core_web_notification->set_message(false,SUCCESS);
				$this->response->redirect(base_url()."/".'app_cxc_customer/edit/companyID/'.$companyID."/branchID/".$objEntity["branchID"]."/entityID/".$entityID."/callback/".$callback);
			}
			else{
				$db->transRollback();						
				$this->core_web_notification->set_message(true,$this->db->_error_message());
				$this->response->redirect(base_url()."/".'app_cxc_customer/add');	
			}
			
		}
		catch(\Exception $ex){
			exit($ex->getMessage());
		}	
	}
	function save($mode=""){
			$mode = helper_SegmentsByIndex($this->uri->getSegments(),1,$mode);	
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//Validar Formulario						
			//$this->validation->setRule("txtCountryID","Pais","required");
			//$this->validation->setRule("txtStateID","Departamento","required");
			//$this->validation->setRule("txtCityID","Municipio","required");
			$this->validation->setRule("txtIdentification","Identificacion","required");
				
				
			//Validar Formulario
			if(!$this->validation->withRequest($this->request)->run()){
				$stringValidation = $this->core_web_tools->formatMessageError($this->validation->getErrors());
				$this->core_web_notification->set_message(true,$stringValidation);
				$this->response->redirect(base_url()."/".'app_cxc_customer/add');
				exit;
			} 
			
			//Guardar o Editar Registro						
			if($mode == "new"){
				$this->insertElement($dataSession);
			}
			else if ($mode == "edit"){
				$this->updateElement($dataSession);
			}
			else{
				$stringValidation = "El modo de operacion no es correcto (new|edit)";
				$this->core_web_notification->set_message(true,$stringValidation);
				$this->response->redirect(base_url()."/".'app_cxc_customer/add');
				exit;
			}
			
	}
	function add(){ 
	
		try{ 
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCTION
			if(APP_NEED_AUTHENTICATION == true){
						$permited = false;
						$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						
						if(!$permited)
						throw new \Exception(NOT_ACCESS_CONTROL);
						
						$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"add",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						if ($resultPermission 	== PERMISSION_NONE)
						throw new \Exception(NOT_ALL_INSERT);			
			
			}	
			 
						
			$dataView							= null;
			
			//Obtener Tasa de Cambio			
			
			$companyID 							= $dataSession["user"]->companyID;
			$branchID 							= $dataSession["user"]->branchID;
			$roleID 							= $dataSession["role"]->roleID;
			$callback 							= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"callback");//--finuri
			$callback							= $callback === "" ?  "false" : $callback;
			$comando							= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"comando");//--finuri
			$comando							= $comando === "" ? "false" : $comando;
			
			$objComponentAccount				= $this->core_web_tools->getComponentIDBy_ComponentName("tb_account");
			if(!$objComponentAccount)
			throw new \Exception("EL COMPONENTE 'tb_account' NO EXISTE...");
			
			$objParameterPais	= $this->core_web_parameter->getParameter("CXC_PAIS_DEFAULT",$companyID);			
			$objParameterPais 	= $objParameterPais->value;
			$dataView["objParameterPais"] = $objParameterPais;
			
			$objParameterDepartamento	= $this->core_web_parameter->getParameter("CXC_DEPARTAMENTO_DEFAULT",$companyID);			
			$objParameterDepartamento 	= $objParameterDepartamento->value;
			$dataView["objParameterDepartamento"] = $objParameterDepartamento;
			
			$objParameterMunicipio	= $this->core_web_parameter->getParameter("CXC_MUNICIPIO_DEFAULT",$companyID);			
			$objParameterMunicipio 	= $objParameterMunicipio->value;
			$dataView["objParameterMunicipio"] = $objParameterMunicipio;		
			
			$dataView["objListWorkflowStage"]			= $this->core_web_workflow->getWorkflowInitStage("tb_customer","statusID",$companyID,$branchID,$roleID);
			$dataView["objListIdentificationType"]		= $this->core_web_catalog->getCatalogAllItem("tb_customer","identificationType",$companyID);
			$dataView["objListCountry"]					= $this->core_web_catalog->getCatalogAllItem("tb_customer","countryID",$companyID);
			$dataView["objListClasificationID"]			= $this->core_web_catalog->getCatalogAllItem("tb_customer","clasificationID",$companyID);
			$dataView["objListCustomerTypeID"]			= $this->core_web_catalog->getCatalogAllItem("tb_customer","customerTypeID",$companyID);
			$dataView["objListCategoryID"]				= $this->core_web_catalog->getCatalogAllItem("tb_customer","categoryID",$companyID);
			$dataView["objListSubCategoryID"]			= $this->core_web_catalog->getCatalogAllItem("tb_customer","subCategoryID",$companyID);
			$dataView["objListTypePay"]					= $this->core_web_catalog->getCatalogAllItem("tb_customer","typePay",$companyID);
			$dataView["objListPayConditionID"]			= $this->core_web_catalog->getCatalogAllItem("tb_customer","payConditionID",$companyID);
			$dataView["objListSexoID"]					= $this->core_web_catalog->getCatalogAllItem("tb_customer","sexoID",$companyID);
			
			$dataView["objListEstadoCivilID"]			= $this->core_web_catalog->getCatalogAllItem("tb_naturales","statusID",$companyID);
			
			$dataView["objListProfesionID"] 			= $this->core_web_catalog->getCatalogAllItem("tb_naturales","profesionID",$companyID);
			
			$dataView["objListTypeFirmID"] 				= $this->core_web_catalog->getCatalogAllItem("tb_customer","typeFirm",$companyID);
			$dataView["objListCurrency"]				= $this->Company_Currency_Model->getByCompany($companyID);
			$objCurrency								= $this->core_web_currency->getCurrencyDefault($companyID);			
			$dataView["objCurrency"]					= $objCurrency;
			$dataView["objComponentAccount"]			= $objComponentAccount;
			$dataView["callback"]						= $callback;
			$dataView["comando"]						= $comando;
			$dataView["useMobile"]						= $dataSession["user"]->useMobile;		
			$dataView["company"]						= $dataSession["company"];
			
			
			
			
			//Renderizar Resultado
			$dataSession["notification"]	= $this->core_web_error->get_error($dataSession["user"]->userID);			
			$dataSession["message"]			= $this->core_web_notification->get_message();
			$dataSession["head"]			= /*--inicio view*/ view('app_cxc_customer/news_head',$dataView);//--finview
			$dataSession["body"]			= /*--inicio view*/ view('app_cxc_customer/news_body',$dataView);//--finview
			$dataSession["script"]			= /*--inicio view*/ view('app_cxc_customer/news_script',$dataView);//--finview
			$dataSession["footer"]			= "";
			
			
			if($callback == "false")
				return view("core_masterpage/default_masterpage",$dataSession);//--finview-r
			else
				return view("core_masterpage/default_popup",$dataSession);//--finview-r
			
		}
		catch(\Exception $ex){
			exit($ex->getMessage());
		}	
			
    }
	function index($dataViewID = null){	
	try{ 
		
			
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
		
			//PERMISO SOBRE LA FUNCTION
			if(APP_NEED_AUTHENTICATION == true){				
				
						$permited = false;
						$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						
						if(!$permited)
						throw new \Exception(NOT_ACCESS_CONTROL);
						
						$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"index",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						if ($resultPermission 	== PERMISSION_NONE)
						throw new \Exception(NOT_ACCESS_FUNCTION);			
			
			}	
			//Obtener el componente Para mostrar la lista de AccountType
			$objComponent		= $this->core_web_tools->getComponentIDBy_ComponentName("tb_customer");
			if(!$objComponent)
			throw new \Exception("00409 EL COMPONENTE 'tb_customer' NO EXISTE...");
			
			
			//Vista por defecto 
			if($dataViewID == null){								
				
				$targetComponentID			= $this->session->get('company')->flavorID;
				$parameter["{companyID}"]	= $this->session->get('user')->companyID;				
				$dataViewData				= $this->core_web_view->getViewDefault($this->session->get('user'),$objComponent->componentID,CALLERID_LIST,$targetComponentID,$resultPermission,$parameter);			
				
				
				if(!$dataViewData){
					$targetComponentID			= 0;	
					$parameter["{companyID}"]	= $this->session->get('user')->companyID;					
					$dataViewData				= $this->core_web_view->getViewDefault($this->session->get('user'),$objComponent->componentID,CALLERID_LIST,$targetComponentID,$resultPermission,$parameter);				
				}
				
				$dataViewRender				= $this->core_web_view->renderGreed($dataViewData,'ListView',"fnTableSelectedRow");
				
				
				
				
			}
			//Otra vista
			else{									
				$parameter["{companyID}"]	= $this->session->get('user')->companyID;
				$dataViewData				= $this->core_web_view->getViewBy_DataViewID($this->session->get('user'),$objComponent->componentID,$dataViewID,CALLERID_LIST,$resultPermission,$parameter); 			
				$dataViewRender				= $this->core_web_view->renderGreed($dataViewData,'ListView',"fnTableSelectedRow");
			} 
			 
			//Renderizar Resultado
			$dataView["objParameterCORE_VIEW_CUSTOM_SCROLL_IN_LIST_CUSTOMER"]	
											= $this->core_web_parameter->getParameterValue("CORE_VIEW_CUSTOM_SCROLL_IN_LIST_CUSTOMER",$this->session->get('user')->companyID);
			$dataSession["notification"]	= $this->core_web_error->get_error($dataSession["user"]->userID);
			$dataSession["message"]			= $this->core_web_notification->get_message();
			$dataSession["head"]			= /*--inicio view*/ view('app_cxc_customer/list_head',$dataView);//--finview
			$dataSession["footer"]			= /*--inicio view*/ view('app_cxc_customer/list_footer',$dataView);//--finview
			$dataSession["body"]			= $dataViewRender; 
			$dataSession["script"]			= /*--inicio view*/ view('app_cxc_customer/list_script',$dataView);//--finview
			$dataSession["script"]			= $dataSession["script"].$this->core_web_javascript->createVar("componentID",$objComponent->componentID);   
			return view("core_masterpage/default_masterpage",$dataSession);//--finview-r	
		}
		catch(\Exception $ex){
			exit($ex->getMessage());
		}
	}
	function edit_credit_line(){
			
			//AUTENTICACION
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCION
			if(APP_NEED_AUTHENTICATION == true){
				$permited = false;
				$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
				
				if(!$permited)
				throw new \Exception(NOT_ACCESS_CONTROL);
				
				$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"index",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
				if ($resultPermission 	== PERMISSION_NONE)
				throw new \Exception(NOT_ACCESS_FUNCTION);			
			}
			
			
			
			
			
			
			
			$customerCreditLineID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"customerCreditLineID");//--finuri
			$companyID 							= $dataSession["user"]->companyID;
			$branchID 							= $dataSession["user"]->branchID;
			$roleID 							= $dataSession["role"]->roleID;
			
			
			$dataView["objListLine"]			= $this->Credit_Line_Model->get_rowByCompany($companyID);
			$dataView["objCurrencyList"]		= $this->Company_Currency_Model->getByCompany($companyID);
			$dataView["objListWorkflowStage"]	= $this->core_web_workflow->getWorkflowInitStage("tb_customer_credit_line","statusID",$companyID,$branchID,$roleID);
			$dataView["objListPay"]				= $this->core_web_catalog->getCatalogAllItem("tb_customer_credit_line","periodPay",$companyID);
			$dataView["objCustomerCreditLine"] 	= $this->Customer_Credit_Line_Model->get_rowByPK($customerCreditLineID);
			$dataView["objListTypeAmortization"]= $this->core_web_catalog->getCatalogAllItem("tb_customer_credit_line","typeAmortization",$companyID);
			
			//Renderizar Resultado
			$dataSession["message"]		= "";
			$dataSession["head"]		= /*--inicio view*/ view('app_cxc_customer/popup_editcreditline_head',$dataView);//--finview
			$dataSession["body"]		= /*--inicio view*/ view('app_cxc_customer/popup_editcreditline_body',$dataView);//--finview
			$dataSession["script"]		= /*--inicio view*/ view('app_cxc_customer/popup_editcreditline_script',$dataView);//--finview
			return view("core_masterpage/default_popup",$dataSession);//--finview-r
	}
	function add_credit_line(){
			
			//AUTENTICACION
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCION
			if(APP_NEED_AUTHENTICATION == true){
				$permited = false;
				$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
				
				if(!$permited)
				throw new \Exception(NOT_ACCESS_CONTROL);
				
				$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"index",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
				if ($resultPermission 	== PERMISSION_NONE)
				throw new \Exception(NOT_ACCESS_FUNCTION);			
			}
			
			
			
			
			$companyID 								= $dataSession["user"]->companyID;
			$branchID 								= $dataSession["user"]->branchID;
			$roleID 								= $dataSession["role"]->roleID;
			$dataView["objListLine"]				= $this->Credit_Line_Model->get_rowByCompany($companyID);
			$dataView["objCurrencyList"]			= $this->Company_Currency_Model->getByCompany($companyID);
			$dataView["objListWorkflowStage"]		= $this->core_web_workflow->getWorkflowInitStage("tb_customer_credit_line","statusID",$companyID,$branchID,$roleID);
			$dataView["objListPay"]					= $this->core_web_catalog->getCatalogAllItem("tb_customer_credit_line","periodPay",$companyID);
			$dataView["objListTypeAmortization"]	= $this->core_web_catalog->getCatalogAllItem("tb_customer_credit_line","typeAmortization",$companyID);
			
			$objParameterCurrenyDefault	= $this->core_web_parameter->getParameter("ACCOUNTING_CURRENCY_NAME_FUNCTION",$companyID);			
			$objParameterCurrenyDefault 	= $objParameterCurrenyDefault->value;
			$dataView["objParameterCurrenyDefault"] = $objParameterCurrenyDefault;
			
			
			$objParameterAmortizationDefault	= $this->core_web_parameter->getParameter("CXC_TYPE_AMORTIZATION",$companyID);			
			$objParameterAmortizationDefault 	= $objParameterAmortizationDefault->value;
			$dataView["objParameterAmortizationDefault"] = $objParameterAmortizationDefault;
			
			
			$objParameterPayDefault						= $this->core_web_parameter->getParameter("CXC_FRECUENCIA_PAY_DEFAULT",$companyID);			
			$objParameterPayDefault 					= $objParameterPayDefault->value;
			$dataView["objParameterPayDefault"] 		= $objParameterPayDefault;
			$dataView["objParameterCXC_PLAZO_DEFAULT"]	= $this->core_web_parameter->getParameterValue("CXC_PLAZO_DEFAULT",$companyID);			
			
			//Renderizar Resultado
			$dataSession["message"]		= "";
			$dataSession["head"]		= /*--inicio view*/ view('app_cxc_customer/popup_addcreditline_head',$dataView);//--finview
			$dataSession["body"]		= /*--inicio view*/ view('app_cxc_customer/popup_addcreditline_body',$dataView);//--finview
			$dataSession["script"]		= /*--inicio view*/ view('app_cxc_customer/popup_addcreditline_script',$dataView);//--finview
			return view("core_masterpage/default_popup",$dataSession);//--finview-r
	}
	function add_email(){
			
			//AUTENTICACION
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCION
			if(APP_NEED_AUTHENTICATION == true){
				$permited = false;
				$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
				
				if(!$permited)
				throw new \Exception(NOT_ACCESS_CONTROL);
				
				$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"index",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
				if ($resultPermission 	== PERMISSION_NONE)
				throw new \Exception(NOT_ACCESS_FUNCTION);			
			}
			
			
			//Renderizar Resultado
			$dataSession["message"]		= "";
			$dataSession["head"]		= /*--inicio view*/ view('app_cxc_customer/popup_addemail_head');//--finview
			$dataSession["body"]		= /*--inicio view*/ view('app_cxc_customer/popup_addemail_body');//--finview
			$dataSession["script"]		= /*--inicio view*/ view('app_cxc_customer/popup_addemail_script');//--finview
			return view("core_masterpage/default_popup",$dataSession);//--finview-r
	}
	function add_phone(){
			
			//AUTENTICACION
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCION
			if(APP_NEED_AUTHENTICATION == true){
				$permited = false;
				$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
				
				if(!$permited)
				throw new \Exception(NOT_ACCESS_CONTROL);
				
				$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"index",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
				if ($resultPermission 	== PERMISSION_NONE)
				throw new \Exception(NOT_ACCESS_FUNCTION);			
			}
			
			$companyID 						= $dataSession["user"]->companyID;
			$data["objListPhoneTypeID"]		= $this->core_web_catalog->getCatalogAllItem("tb_entity_phone","typeID",$companyID);
			
			//Renderizar Resultado
			$dataSession["message"]		= "";
			$dataSession["head"]		= /*--inicio view*/ view('app_cxc_customer/popup_addphone_head');//--finview
			$dataSession["body"]		= /*--inicio view*/ view('app_cxc_customer/popup_addphone_body',$data);//--finview
			$dataSession["script"]		= /*--inicio view*/ view('app_cxc_customer/popup_addphone_script');//--finview
			return view("core_masterpage/default_popup",$dataSession);//--finview-r
	}

	
}
?>