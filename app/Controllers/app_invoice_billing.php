<?php
//posme:2023-02-27
namespace App\Controllers;
class app_invoice_billing extends _BaseController {
	
   
    function edit()
	{ 
		 try
		 { 
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCTION  aa
			if(APP_NEED_AUTHENTICATION == true){
						$permited = false;
						$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						
						if(!$permited)
						throw new \Exception(NOT_ACCESS_CONTROL);
						
						$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"edit",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						if ($resultPermission 	== PERMISSION_NONE)
						throw new \Exception(NOT_ALL_EDIT);			
			
			}	
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			//Redireccionar datos	
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			$codigoMesero			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"codigoMesero");//--finuri	
			$branchID 				= $dataSession["user"]->branchID;
			$roleID 				= $dataSession["role"]->roleID;			
			$userID					= $dataSession["user"]->userID;
			
		
			
			if((!$companyID || !$transactionID  || !$transactionMasterID))
			{ 
				$this->response->redirect(base_url()."/".'app_invoice_billing/add');	
			} 		
			
			//Obtener el componente de Item
			$objComponentCustomer	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_customer");
			if(!$objComponentCustomer)
			throw new \Exception("EL COMPONENTE 'tb_customer' NO EXISTE...");
			
			//Componente de facturacion
			$objComponentTransactionBilling	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_transaction_master_billing");
			if(!$objComponentTransactionBilling)
			throw new \Exception("EL COMPONENTE 'tb_transaction_master_billing' NO EXISTE...");
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			
			
		
			
			
			
			
			$objCurrency						= $this->core_web_currency->getCurrencyDefault($companyID);
			$targetCurrency						= $this->core_web_currency->getCurrencyExternal($companyID);			
			$customerDefault					= $this->core_web_parameter->getParameter("INVOICE_BILLING_CLIENTDEFAULT",$companyID);
			$objListPrice 						= $this->List_Price_Model->getListPriceToApply($companyID);
			$objListCurrency					= $this->Company_Currency_Model->getByCompany($companyID);
			$urlPrinterDocument					= $this->core_web_parameter->getParameter("INVOICE_URL_PRINTER",$companyID);
			
			if(!$objListPrice)
			throw new \Exception("NO EXISTE UNA LISTA DE PRECIO PARA SER APLICADA");			
			
			
			$objPublicCatalogId							= 0;
			$objPubliCatalogMesasConfig 				= $this->Public_Catalog_Model->asObject()
																	->where("systemName","tb_transaction_master_billing.mesas_x_meseros")
																	->where("isActive",1)
																	->where("flavorID",$dataSession["company"]->flavorID)
																	->find();
			
			if($codigoMesero != "none" && !$objPubliCatalogMesasConfig )
			{
				throw new \Exception("CONFIGURAR EL CATALOGO DE MESAS tb_transaction_master_billing.mesas_x_meseros");
			}
			
			$objPublicCatalogId							= $codigoMesero == "none" ? 0 : $objPubliCatalogMesasConfig[0]->publicCatalogID;
			$objPubliCatalogDetailMesasConfiguradas		= $this->Public_Catalog_Detail_Model->asObject()
																->where("publicCatalogID",$objPublicCatalogId)
																->where( "isActive",1)	
																->where( "name",$codigoMesero)
																->findAll();
			
			
			
			$dataView["codigoMesero"]					= $codigoMesero;
			$objParameterInvoiceTypeEmployer			= $this->core_web_parameter->getParameter("INVOICE_TYPE_EMPLOYEER",$companyID);
			$objParameterInvoiceTypeEmployer			= $objParameterInvoiceTypeEmployer->value;
			
			$parameterValue 							= $this->core_web_parameter->getParameter("INVOICE_BUTTOM_PRINTER_FIDLOCAL_PAYMENT_AND_AMORTIZACION",$companyID);
			$dataView["objParameterInvoiceButtomPrinterFidLocalPaymentAndAmortization"] = $parameterValue->value;
			
			
			$objParameterDirect 									= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_PRINTER_DIRECT",$companyID);
			$objParameterInvoiceBillingQuantityZero					= $this->core_web_parameter->getParameter("INVOICE_BILLING_QUANTITY_ZERO",$companyID);
			$dataView["objParameterInvoiceBillingQuantityZero"]		= $objParameterInvoiceBillingQuantityZero->value;
			
			$objParameterInvoiceBillingPrinterDirect				= $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT",$companyID);
			$dataView["objParameterInvoiceBillingPrinterDirect"]	= $objParameterInvoiceBillingPrinterDirect->value;
			$objParameterInvoiceBillingPrinterDirectUrl					= $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_URL",$companyID);
			$dataView["objParameterInvoiceBillingPrinterDirectUrl"]		= $objParameterInvoiceBillingPrinterDirectUrl->value;
			
			$objParameterShowComandoDeCocina							= $this->core_web_parameter->getParameter("INVOICE_BILLING_SHOW_COMMAND_FOOT",$companyID);
			$dataView["objParameterShowComandoDeCocina"]				= $objParameterShowComandoDeCocina->value;			
			$urlPrinterDocumentCocina									= $this->core_web_parameter->getParameter("INVOICE_URL_PRINTER_COCINA",$companyID);
			$dataView["urlPrinterDocumentCocina"]						= $urlPrinterDocumentCocina->value;
			$urlPrinterDocumentCocinaDirect								= $this->core_web_parameter->getParameter("INVOICE_URL_PRINTER_COCINA_DIRECT",$companyID);
			$dataView["urlPrinterDocumentCocinaDirect"]					= $urlPrinterDocumentCocinaDirect->value;
			$objParameterImprimirPorCadaFactura							= $this->core_web_parameter->getParameter("INVOICE_PRINT_BY_INVOICE",$companyID);
			$dataView["objParameterImprimirPorCadaFactura"]				= $objParameterImprimirPorCadaFactura->value;
			$objParameterRegresarAListaDespuesDeGuardar					= $this->core_web_parameter->getParameter("INVOICE_BILLING_SAVE_AFTER_TO_LIST",$companyID);
			$dataView["objParameterRegresarAListaDespuesDeGuardar"]		= $objParameterRegresarAListaDespuesDeGuardar->value;
			$objParameterScanerProducto									= $this->core_web_parameter->getParameter("INVOICE_SHOW_POPUP_FIND_PRODUCTO_NOT_SCANER",$companyID);
			$objParameterScanerProducto									= $objParameterScanerProducto->value;
			$dataView["objParameterScanerProducto"] 					= $objParameterScanerProducto;
			
			$objParameterUrlServidorDeImpresion							= $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_SERVER_PATH",$companyID);
			$objParameterUrlServidorDeImpresion							= $objParameterUrlServidorDeImpresion->value;
			$dataView["objParameterUrlServidorDeImpresion"] 			= $objParameterUrlServidorDeImpresion;
			
			
			$objParameterCantidadItemPoup								= $this->core_web_parameter->getParameter("INVOICE_CANTIDAD_ITEM",$companyID);
			$objParameterCantidadItemPoup								= $objParameterCantidadItemPoup->value;			
			$dataView["objParameterCantidadItemPoup"] 					= $objParameterCantidadItemPoup;
			
			$objParameterHidenFiledItemNumber							= $this->core_web_parameter->getParameter("INVOICE_HIDEN_ITEMNUMBER_IN_POPUP",$companyID);
			$objParameterHidenFiledItemNumber							= $objParameterHidenFiledItemNumber->value;			
			$dataView["objParameterHidenFiledItemNumber"] 				= $objParameterHidenFiledItemNumber;
			
			$objParameterEsResrarante									= $this->core_web_parameter->getParameter("INVOICE_BILLING_IS_RESTAURANT",$this->session->get('user')->companyID);
			$objParameterEsResrarante									= $objParameterEsResrarante->value;
			$dataView["objParameterEsResrarante"] 						= $objParameterEsResrarante;
						
			$objParameterAmortizationDuranteFactura						= $this->core_web_parameter->getParameter("INVOICE_PARAMTER_AMORITZATION_DURAN_INVOICE",$companyID);
			$objParameterAmortizationDuranteFactura						= $objParameterAmortizationDuranteFactura->value;			
			$dataView["objParameterAmortizationDuranteFactura"] 		= $objParameterAmortizationDuranteFactura;
			
			
			$objParameterAlturaDelModalDeSeleccionProducto					= $this->core_web_parameter->getParameter("INVOICE_ALTO_MODAL_DE_SELECCION_DE_PRODUCTO_AL_FACTURAR",$companyID);
			$objParameterAlturaDelModalDeSeleccionProducto					= $objParameterAlturaDelModalDeSeleccionProducto->value;			
			$dataView["objParameterAlturaDelModalDeSeleccionProducto"] 		= $objParameterAlturaDelModalDeSeleccionProducto;
			
			$objParameterScrollDelModalDeSeleccionProducto					= $this->core_web_parameter->getParameter("INVOICE_SCROLL_DE_MODAL_EN_SELECCION_DE_PRODUTO_AL_FACTURAR",$companyID);
			$objParameterScrollDelModalDeSeleccionProducto					= $objParameterScrollDelModalDeSeleccionProducto->value;
			$dataView["objParameterScrollDelModalDeSeleccionProducto"] 		= $objParameterScrollDelModalDeSeleccionProducto;
			
			$objParameterMostrarImagenEnSeleccion					= $this->core_web_parameter->getParameter("INVOICE_BILLING_SHOW_IMAGE_IN_DETAIL_SELECTION",$companyID);
			$objParameterMostrarImagenEnSeleccion					= $objParameterMostrarImagenEnSeleccion->value;	
			$dataView["objParameterMostrarImagenEnSeleccion"] 		= $objParameterMostrarImagenEnSeleccion;
			
			$objParameterPantallaParaFacturar				= $this->core_web_parameter->getParameter("INVOICE_PANTALLA_FACTURACION",$this->session->get('user')->companyID);
			$objParameterPantallaParaFacturar				= $objParameterPantallaParaFacturar->value;
			$dataView["objParameterPantallaParaFacturar"] 	= $objParameterPantallaParaFacturar;
			
			
			//Tipo de Factura
			$agent 												= $this->request->getUserAgent();			
			$dataView["isMobile"]								= helper_RequestGetValue($agent->isMobile(),"0");
			$dataView["urlPrinterDocument"]						= $urlPrinterDocument->value;
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			$dataView["objTransactionMaster"]->transactionOn 	= date_format(date_create($dataView["objTransactionMaster"]->transactionOn),"Y-m-d");
			$dataView["objTransactionMaster"]->transactionOn2 	= date_format(date_create($dataView["objTransactionMaster"]->transactionOn2),"Y-m-d");
			$dataView["objTransactionMasterDetailCredit"]		= null;	
			$dataView["companyID"]				= $dataSession["user"]->companyID;
			$dataView["userID"]					= $dataSession["user"]->userID;
			$dataView["userName"]				= $dataSession["user"]->nickname;
			$dataView["roleID"]					= $dataSession["role"]->roleID;
			$dataView["roleName"]				= $dataSession["role"]->name;
			$dataView["isAdmin"]				= $dataSession["role"]->isAdmin;
			$dataView["branchID"]				= $dataSession["branch"]->branchID;
			$dataView["branchName"]				= $dataSession["branch"]->name;
			$dataView["useMobile"]				= $dataSession["user"]->useMobile;
			$dataView["exchangeRate"]			= $this->core_web_currency->getRatio($companyID,date("Y-m-d"),1,$targetCurrency->currencyID,$objCurrency->currencyID);			
			$dataView["objCurrency"]			= $objCurrency;
			$dataView["company"]				= $dataSession["company"];
			
			$dataView["objListEmployee"]		= $this->Employee_Model->get_rowByBranchIDAndType($companyID,$branchID,  $objParameterInvoiceTypeEmployer );
			$dataView["objListBank"]			= $this->Bank_Model->getByCompany($companyID);
			$dataView["objListPrice"]			= $objListPrice;
			$dataView["objComponentBilling"]			= $objComponentTransactionBilling;
			$dataView["objComponentTransactionBilling"]	= $objComponentTransactionBilling;
			$dataView["objComponentItem"]		= $objComponentItem;
			$dataView["objComponentCustomer"]	= $objComponentCustomer;
			$dataView["objCaudal"]				= $this->Transaction_Causal_Model->getCausalByBranch($companyID,$transactionID,$branchID);			
			$dataView["warehouseID"]			= $dataView["objCaudal"][0]->warehouseSourceID;
			$dataView["objListWarehouse"]		= $this->Userwarehouse_Model->getRowByUserIDAndFacturable($companyID,$userID);
			$dataView["objListWorkflowStage"]	= $this->core_web_workflow->getWorkflowStageByStageInit("tb_transaction_master_billing","statusID",$dataView["objTransactionMaster"]->statusID,$companyID,$branchID,$roleID);
			$dataView["objCustomerDefault"]		= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objListTypePrice"]		= $this->core_web_catalog->getCatalogAllItem("tb_price","typePriceID",$companyID);
			$dataView["objListZone"]			= $this->core_web_catalog->getCatalogAllItem("tb_transaction_master_info_billing","zoneID",$companyID);
			$dataView["objListMesa"]			= $this->core_web_catalog->getCatalogAllItem("tb_transaction_master_info_billing","mesaID",$companyID);			
			
			
			
			//Filtrar la lista de mesas que el mesero tiene permiso
			$listMesasByMesero = array_map(function($item) {
				return $item->display;
			}, $objPubliCatalogDetailMesasConfiguradas);

			
			$listMesaFiltradas = array_filter($dataView["objListMesa"] , function($item) use ($listMesasByMesero) {
				return in_array($item->name, $listMesasByMesero);
			});

			$dataView["objListMesa"] = $codigoMesero == "none" ? $dataView["objListMesa"]  : $listMesaFiltradas;
			if(!$dataView["objListMesa"])
			throw new \Exception("NO ES POSIBLE CONTINUAR CONFIGURAR CATALOGO MESS");
		
			$mesaID = $dataView["objTransactionMasterInfo"]->mesaID;
			
			$listMesaFiltradas = array_filter($dataView["objListMesa"] , function($item) use ($mesaID) {
				return $item->catalogItemID == $mesaID;
			});
			if(!$listMesaFiltradas)
			throw new \Exception("NO TIENE ACCESO AL CATALOGO MESS");	
			
			
			$dataView["objListPay"]				= $this->core_web_catalog->getCatalogAllItem("tb_customer_credit_line","periodPay",$companyID);
			$dataView["listCurrency"]			= $objListCurrency;
			$dataView["listProvider"]			= $this->Provider_Model->get_rowByCompany($companyID);
			$dataView["objListaPermisos"]		= $dataSession["menuHiddenPopup"];				
			$dataView["useMobile"]												= $dataSession["user"]->useMobile;			
			$dataView["objParameterINVOICE_OPEN_CASH_WHEN_PRINTER_INVOICE"] 	= $this->core_web_parameter->getParameterValue("INVOICE_OPEN_CASH_WHEN_PRINTER_INVOICE",$companyID);
			$dataView["objParameterINVOICE_OPEN_CASH_PASSWORD"] 				= $this->core_web_parameter->getParameterValue("INVOICE_OPEN_CASH_PASSWORD",$companyID);
			$dataView["objParameterCustomPopupFacturacion"]											= $this->core_web_parameter->getParameterValue("CORE_VIEW_CUSTOM_PANTALLA_DE_FACTURACION_POPUP_SELECCION_PRODUCTO_FORMA_MOSTRAR",$companyID);
			$dataView["objParameterTipoPrinterDonwload"]											= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_PRINTER_DOWNLOAD",$companyID);
			$dataView["objParameterINVOICE_BILLING_APPLY_TYPE_PRICE_ON_DAY_POR_MAYOR"]				= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_APPLY_TYPE_PRICE_ON_DAY_POR_MAYOR",$companyID);
			$dataView["objParameterINVOICE_BILLING_SHOW_COMMAND_BAR"]								= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_SHOW_COMMAND_BAR",$companyID);
			$dataView["objParameterINVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT_BAR"]				= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT_BAR",$companyID);
			$dataView["objParameterINVOICE_BILLING_PRINTER_DIRECT_URL_BAR"]							= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_PRINTER_DIRECT_URL_BAR",$companyID);
			$dataView["objParameterINVOICE_BILLING_PRINTER_URL_BAR"]								= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_PRINTER_URL_BAR",$companyID);			
			$dataView["objListParameterJavaScript"]													= $this->core_web_parameter->getParameterAllToJavaScript($companyID);
			$dataView["objParameterINVOICE_BILLING_SELECTITEM"]										= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_SELECTITEM",$companyID);
			
			
			if(!$dataView["objCustomerDefault"])
			throw new \Exception("NO EXISTE EL CLIENTE POR DEFECTO");
			
			$dataView["objNaturalDefault"]		= $this->Natural_Model->get_rowByPK($companyID,$dataView["objCustomerDefault"]->branchID,$dataView["objCustomerDefault"]->entityID);
			$dataView["objLegalDefault"]		= $this->Legal_Model->get_rowByPK($companyID,$dataView["objCustomerDefault"]->branchID,$dataView["objCustomerDefault"]->entityID);
			
			//Procesar Datos
			if($dataView["objTransactionMasterDetail"])
			foreach($dataView["objTransactionMasterDetail"] as $key => $value)
			{
				$dataView["objTransactionMasterDetail"][$key]->itemName = htmlentities($value->itemName,ENT_QUOTES);
				$dataView["objTransactionMasterDetailCredit"]			= $this->Transaction_Master_Detail_Credit_Model->get_rowByPK($value->transactionMasterDetailID);
			}
			
			
			
			//Obtener la linea de credito del cliente por defecto
			$objCurrencyDolares						= $this->core_web_currency->getCurrencyExternal($companyID);
			$objCurrencyCordoba						= $this->core_web_currency->getCurrencyDefault($companyID);			
			$parameterCausalTypeCredit 				= $this->core_web_parameter->getParameter("INVOICE_BILLING_CREDIT",$companyID);			
			$objCustomerCreditAmoritizationAll		= $this->Customer_Credit_Amortization_Model->get_rowByCustomerID( $dataView["objTransactionMaster"]->entityID );
			$objListCustomerCreditLine 				= $this->Customer_Credit_Line_Model->get_rowByEntityBalanceMayorCero($companyID,$dataSession["user"]->branchID,$dataView["objTransactionMaster"]->entityID);			
			
			
			$dataView["objListCustomerCreditLine"]	  		=  $objListCustomerCreditLine;				
			$dataView["objCausalTypeCredit"]				=  $parameterCausalTypeCredit;
			$dataView["objCurrencyDolares"] 				=  $objCurrencyDolares;
			$dataView["objCurrencyCordoba"] 				=  $objCurrencyCordoba;
			$dataView["objCustomerCreditAmoritizationAll"] 	=  $objCustomerCreditAmoritizationAll;
			
			//Obtener los datos de precio, sku y conceptos de la transaccoin
			$dataView["objTransactionMasterItemPrice"]			= $this->Price_Model->get_rowByTransactionMasterID($companyID,$objListPrice->listPriceID, $dataView["objTransactionMaster"]->transactionMasterID );
			$dataView["objTransactionMasterItemConcepto"]		= $this->Company_Component_Concept_Model->get_rowByTransactionMasterID($companyID,$objComponentItem->componentID, $dataView["objTransactionMaster"]->transactionMasterID );
			$dataView["objTransactionMasterItemSku"]			= $this->Item_Sku_Model->get_rowByTransactionMasterID($companyID, $dataView["objTransactionMaster"]->transactionMasterID );
			$dataView["objTransactionMasterItem"]				= $this->Item_Model->get_rowByTransactionMasterID( $dataView["objTransactionMaster"]->transactionMasterID  );
			
			
			//Datos para imprimir la factura
			//------------------------------------------						
			if($objParameterDirect  == "true")
			{
				$dataPostPrinter["objTransactionMaster"]					= $dataView["objTransactionMaster"];
				$dataPostPrinter["objTransactionMasterInfo"]				= $dataView["objTransactionMasterInfo"];
				$dataPostPrinter["objTransactionMasterDetail"]				= $dataView["objTransactionMasterDetail"];
				$dataPostPrinter["objTransactionMasterDetailWarehouse"]		= $dataView["objTransactionMasterDetailWarehouse"];
				$dataPostPrinter["objTransactionMasterDetailConcept"]		= $dataView["objTransactionMasterDetailConcept"];
				$dataPostPrinter["objComponentCompany"]				= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
				$dataPostPrinter["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
				$dataPostPrinter["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
				$dataPostPrinter["objCompany"] 						= $this->Company_Model->get_rowByPK($companyID);			
				$dataPostPrinter["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataPostPrinter["objTransactionMaster"]->createdAt,$dataPostPrinter["objTransactionMaster"]->createdBy);
				$dataPostPrinter["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
				$dataPostPrinter["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataPostPrinter["objTransactionMaster"]->branchID);
				$dataPostPrinter["objTipo"]							= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataPostPrinter["objTransactionMaster"]->transactionID,$dataPostPrinter["objTransactionMaster"]->transactionCausalID);
				$dataPostPrinter["objCustumer"]						= $this->Customer_Model->get_rowByEntity($companyID,$dataPostPrinter["objTransactionMaster"]->entityID);
				$dataPostPrinter["objCurrency"]						= $this->Currency_Model->get_rowByPK($dataPostPrinter["objTransactionMaster"]->currencyID);
				$dataPostPrinter["prefixCurrency"]					= $dataPostPrinter["objCurrency"]->simbol." ";
				$dataPostPrinter["cedulaCliente"] 					= $dataPostPrinter["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataPostPrinter["objCustumer"]->customerNumber :  $dataPostPrinter["objTransactionMasterInfo"]->referenceClientIdentifier;
				$dataPostPrinter["nombreCliente"] 					= $dataPostPrinter["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataPostPrinter["objCustumer"]->firstName : $dataPostPrinter["objTransactionMasterInfo"]->referenceClientName ;
				$dataPostPrinter["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataPostPrinter["objTransactionMaster"]->statusID);
				$serializedDataPostPrinter 							= serialize($dataPostPrinter);
				$serializedDataPostPrinter 							= base64_encode($serializedDataPostPrinter);
				$dataView["dataPrinterLocal"]						= $serializedDataPostPrinter;
				
			}
			else 
			{
				$dataView["dataPrinterLocal"]						= "";
			}
			
			//------------------------------------------
			//Renderizar Resultado 
			$dataSession["notification"]	= $this->core_web_error->get_error($dataSession["user"]->userID);
			$dataSession["message"]			= $this->core_web_notification->get_message();
			$dataSession["head"]			= /*--inicio view*/ view('app_invoice_billing/edit_head',$dataView);//--finview
			$dataSession["body"]			= /*--inicio view*/ view('app_invoice_billing/edit_body',$dataView);//--finview
			$dataSession["script"]			= /*--inicio view*/ view('app_invoice_billing/edit_script',$dataView);//--finview
			$dataSession["footer"]			= "";
			
			
			return view("core_masterpage/default_popup",$dataSession);//--finview-r	
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}	
	}	
	
	function editv2(){ 
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
				
			
			
			$companyID				= $dataSession["company"]->companyID;
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri
			
			$transactionMasterID	= $transactionMasterID === "" ? 0 : $transactionMasterID;
			$transactionID			= $transactionID === "" ? 0 : $transactionID;
			
			$branchID 				= $dataSession["user"]->branchID;
			$roleID 				= $dataSession["role"]->roleID;			
			$userID					= $dataSession["user"]->userID;
			
				
			
			
			//Obtener el componente de Item
			$objComponentCustomer	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_customer");
			if(!$objComponentCustomer)
			throw new \Exception("EL COMPONENTE 'tb_customer' NO EXISTE...");
			
			//Componente de facturacion
			$objComponentTransactionBilling	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_transaction_master_billing");
			if(!$objComponentTransactionBilling)
			throw new \Exception("EL COMPONENTE 'tb_transaction_master_billing' NO EXISTE...");
		
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
		
			//Obtener el componente de Item
			$objComponentItemCategory	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item_category");
			if(!$objComponentItemCategory)
			throw new \Exception("EL COMPONENTE 'tb_item_category' NO EXISTE...");
			
			$objCurrency						= $this->core_web_currency->getCurrencyDefault($companyID);
			$targetCurrency						= $this->core_web_currency->getCurrencyExternal($companyID);			
			$customerDefault					= $this->core_web_parameter->getParameter("INVOICE_BILLING_CLIENTDEFAULT",$companyID);
			$objListPrice 						= $this->List_Price_Model->getListPriceToApply($companyID);
			$objListCurrency					= $this->Company_Currency_Model->getByCompany($companyID);
			$urlPrinterDocument					= $this->core_web_parameter->getParameter("INVOICE_URL_PRINTER",$companyID);
			
			if(!$objListPrice)
			throw new \Exception("NO EXISTE UNA LISTA DE PRECIO PARA SER APLICADA");
		
			
			
		
			
			$objParameterInvoiceBillingQuantityZero					= $this->core_web_parameter->getParameter("INVOICE_BILLING_QUANTITY_ZERO",$companyID);
			$dataView["objParameterInvoiceBillingQuantityZero"]		= $objParameterInvoiceBillingQuantityZero->value;
			$objParameterInvoiceBillingPrinterDirect				= $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT",$companyID);
			$dataView["objParameterInvoiceBillingPrinterDirect"]	= $objParameterInvoiceBillingPrinterDirect->value;
			$objParameterInvoiceBillingPrinterDirectUrl					= $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_URL",$companyID);
			$dataView["objParameterInvoiceBillingPrinterDirectUrl"]		= $objParameterInvoiceBillingPrinterDirectUrl->value;
			$objParameterInvoiceBillingPrinterDirectCocinaUrl					= $this->core_web_parameter->getParameter("INVOICE_URL_PRINTER_COCINA_DIRECT",$companyID);
			$dataView["objParameterInvoiceBillingPrinterDirectCocinaUrl"]		= $objParameterInvoiceBillingPrinterDirectCocinaUrl->value;
			
			//Tipo de Factura
			$dataView["urlPrinterDocument"]						= $urlPrinterDocument->value;
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			//Formato de fecha
			if($dataView["objTransactionMaster"]){
				$dataView["objTransactionMaster"]->transactionOn 	= date_format(date_create($dataView["objTransactionMaster"]->transactionOn),"Y-m-d");
				$dataView["objTransactionMaster"]->transactionOn2 	= date_format(date_create($dataView["objTransactionMaster"]->transactionOn2),"Y-m-d");
			}
			
			$agent 												= $this->request->getUserAgent();			
			$dataView["isMobile"]								= helper_RequestGetValue($agent->isMobile(),"0");
			$dataView["widthPanelComando"]						= $dataView["isMobile"] == "0" ? "280" : "450";
			$dataView["widthPanelTeclado"]						= $dataView["isMobile"] == "0" ? "325" : "350";
			$dataView["widthPanelNueva"]						= $dataView["isMobile"] == "0" ? "280" : "210";
			$dataView["widthPanelCategoria"]					= $dataView["isMobile"] == "0" ? "350" : "420";
			$dataView["widthPanelCategoriaAndProductoPhone"]	= $dataView["isMobile"] == "0" ? "350" : "380";
			
				


			$dataView["objTransactionMasterDetailCredit"]		= null;	
			$dataView["companyID"]				= $dataSession["user"]->companyID;
			$dataView["userID"]					= $dataSession["user"]->userID;
			$dataView["userName"]				= $dataSession["user"]->nickname;
			$dataView["roleID"]					= $dataSession["role"]->roleID;
			$dataView["roleName"]				= $dataSession["role"]->name;
			$dataView["branchID"]				= $dataSession["branch"]->branchID;
			$dataView["branchName"]				= $dataSession["branch"]->name;
			$dataView["exchangeRate"]			= $this->core_web_currency->getRatio($companyID,date("Y-m-d"),1,$targetCurrency->currencyID,$objCurrency->currencyID);			
						
			$dataView["objListPrice"]				= $objListPrice;
			$dataView["objComponentBilling"]		= $objComponentTransactionBilling;
			$dataView["objComponentItem"]			= $objComponentItem;
			$dataView["objComponentItemCategory"]	= $objComponentItemCategory;
			
			$dataView["objComponentCustomer"]	= $objComponentCustomer;
			$dataView["objCaudal"]				= $this->Transaction_Causal_Model->getCausalByBranch($companyID,$transactionID,$branchID);			
			$dataView["warehouseID"]			= $dataView["objCaudal"][0]->warehouseSourceID;
			$dataView["objListWarehouse"]		= $this->Userwarehouse_Model->getRowByUserIDAndFacturable($companyID,$userID);
			
			//Obtener estados
			if($dataView["objTransactionMaster"]){
				$dataView["objListWorkflowStage"]		= $this->core_web_workflow->getWorkflowStageByStageInit("tb_transaction_master_billing","statusID",$dataView["objTransactionMaster"]->statusID,$companyID,$branchID,$roleID);
				$dataView["objListWorkflowStageAll"]	= $this->core_web_workflow->getWorkflowAllStage("tb_transaction_master_billing","statusID",$companyID,$branchID,$roleID);				
			}
			else{				
				$dataView["objListWorkflowStage"]		= $this->core_web_workflow->getWorkflowInitStage("tb_transaction_master_billing","statusID",$companyID,$branchID,$roleID);				
				$dataView["objListWorkflowStageAll"]	= $this->core_web_workflow->getWorkflowAllStage("tb_transaction_master_billing","statusID",$companyID,$branchID,$roleID);				
			}
			
			
			
			//Obtener cliente por defecto
			if($dataView["objTransactionMaster"]){
				$dataView["objCustomerDefault"]		= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			}
			else{
				$dataView["objCustomerDefault"]		= $this->Customer_Model->get_rowByCode($companyID,$customerDefault->value);
			}
			
			
			
			$dataView["objListTypePrice"]		= $this->core_web_catalog->getCatalogAllItem("tb_price","typePriceID",$companyID);
			$dataView["objListZone"]			= $this->core_web_catalog->getCatalogAllItem("tb_transaction_master_info_billing","zoneID",$companyID);
			$dataView["listCurrency"]			= $objListCurrency;
			$dataView["listProvider"]			= $this->Provider_Model->get_rowByCompany($companyID);
			$dataView["objListaPermisos"]		= $dataSession["menuHiddenPopup"];
			
			
			if(!$dataView["objCustomerDefault"])
			throw new \Exception("NO EXISTE EL CLIENTE POR DEFECTO");
			
			$dataView["objNaturalDefault"]		= $this->Natural_Model->get_rowByPK($companyID,$dataView["objCustomerDefault"]->branchID,$dataView["objCustomerDefault"]->entityID);
			$dataView["objLegalDefault"]		= $this->Legal_Model->get_rowByPK($companyID,$dataView["objCustomerDefault"]->branchID,$dataView["objCustomerDefault"]->entityID);
			
			//Al detalle de productos escapar nombres
			if($dataView["objTransactionMasterDetail"])
			foreach($dataView["objTransactionMasterDetail"] as $key => $value)
			{
				$dataView["objTransactionMasterDetail"][$key]->itemName = htmlentities($value->itemName,ENT_QUOTES);
				$dataView["objTransactionMasterDetailCredit"]			= $this->Transaction_Master_Detail_Credit_Model->get_rowByPK($value->transactionMasterDetailID);
			}
			
			//Renderizar Resultado 			
			//$dataSession["notification"]	= $this->core_web_error->get_error($dataSession["user"]->userID);
			//$dataSession["message"]		= $this->core_web_notification->get_message();
			//$dataSession["head"]			= /*--inicio view*/ view('app_invoice_billing/edit_head',$dataView);//--finview
			//$dataSession["body"]			= /*--inicio view*/ view('app_invoice_billing/edit_body',$dataView);//--finview
			//$dataSession["script"]		= /*--inicio view*/ view('app_invoice_billing/editv2_script',$dataView);//--finview
			//$dataSession["footer"]		= "";
			$dataView["script"]				= /*--inicio view*/ view('app_invoice_billing/editv2_script',$dataView);//--finview
			
			
			return view("app_invoice_billing/editv2",$dataView);//--finview-r
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
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
			
		
			//Nuevo Registro
			$companyID 				= /*inicio get post*/ $this->request->getPost("companyID");
			$transactionID 			= /*inicio get post*/ $this->request->getPost("transactionID");				
			$transactionMasterID 	= /*inicio get post*/ $this->request->getPost("transactionMasterID");				
			
			
			if((!$companyID && !$transactionID && !$transactionMasterID)){
					throw new \Exception(NOT_PARAMETER);								 
			} 
			
			$objTM	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);			
			$objCustomerCredotDocument	= $this->Customer_Credit_Document_Model->get_rowByDocument($objTM->companyID,$objTM->entityID,$objTM->transactionNumber);
			
			if ($resultPermission 	== PERMISSION_ME && ($objTM->createdBy != $dataSession["user"]->userID))
			throw new \Exception(NOT_DELETE);
			
			if($this->core_web_accounting->cycleIsCloseByDate($companyID,$objTM->transactionOn))
			throw new \Exception("EL DOCUMENTO NO PUEDE SE ELIMINADO, EL CICLO CONTABLE ESTA CERRADO");
				
			//Validar si el estado permite editar
			if(!$this->core_web_workflow->validateWorkflowStage("tb_transaction_master_billing","statusID",$objTM->statusID,COMMAND_ELIMINABLE,$dataSession["user"]->companyID,$dataSession["user"]->branchID,$dataSession["role"]->roleID))
			throw new \Exception(NOT_WORKFLOW_DELETE);
		
			//Validar si la factura es de credito y esta aplicada y tiene abono			
			$parameterCausalTypeCredit 				= $this->core_web_parameter->getParameter("INVOICE_BILLING_CREDIT",$companyID);
			$causalIDTypeCredit 					= explode(",", $parameterCausalTypeCredit->value);
			$exisCausalInCredit						= null;
			$exisCausalInCredit						= array_search($objTM->transactionCausalID ,$causalIDTypeCredit);				
			if( 
				$this->core_web_workflow->validateWorkflowStage
				(
					"tb_transaction_master_billing","statusID",$objTM->statusID,COMMAND_APLICABLE,
					$dataSession["user"]->companyID,$dataSession["user"]->branchID,$dataSession["role"]->roleID
				)
				and 
				(
					$exisCausalInCredit || $exisCausalInCredit === 0
				)
				and 
				(
					$objCustomerCredotDocument->amount != $objCustomerCredotDocument->balance
				)
				and 
				(
					$objCustomerCredotDocument->balance > 1
				)
			)
			{
				throw new \Exception("Factura con abonos y balance mayor que 1");
			}
			
				
			//Si el documento esta aplicado crear el contra documento
			if( $this->core_web_workflow->validateWorkflowStage("tb_transaction_master_billing","statusID",$objTM->statusID,COMMAND_APLICABLE,$dataSession["user"]->companyID,$dataSession["user"]->branchID,$dataSession["role"]->roleID))
			{
				
				//Actualizar fecha en la transacciones oroginal
				$dataNewTM 									= array();
				$dataNewTM["statusIDChangeOn"]				= date("Y-m-d H:m:s");
				$this->Transaction_Master_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$dataNewTM);
				
				$transactionIDRevert = $this->core_web_parameter->getParameter("INVOICE_TRANSACTION_REVERSION_TO_BILLING",$companyID);
				$transactionIDRevert = $transactionIDRevert->value;
				$result = $this->core_web_transaction->createInverseDocumentByTransaccion($companyID,$transactionID,$transactionMasterID,$transactionIDRevert,0);
				
				
				if($exisCausalInCredit || $exisCausalInCredit === 0)
				{
				
					//Valores de tasa de cambio
					date_default_timezone_set(APP_TIMEZONE); 
					$objCurrencyDolares						= $this->core_web_currency->getCurrencyExternal($companyID);
					$objCurrencyCordoba						= $this->core_web_currency->getCurrencyDefault($companyID);
					$dateOn 								= date("Y-m-d");
					$dateOn 								= date_format(date_create($dateOn),"Y-m-d");
					$exchangeRate 							= $this->core_web_currency->getRatio($companyID,$dateOn,1,$objCurrencyDolares->currencyID,$objCurrencyCordoba->currencyID);
						
					//cancelar el documento de credito					
					$objCustomerCredotDocumentNew["statusID"]	= $this->core_web_parameter->getParameter("SHARE_DOCUMENT_ANULADO",$companyID)->value;
					$this->Customer_Credit_Document_Model->update_app_posme($objCustomerCredotDocument->customerCreditDocumentID,$objCustomerCredotDocumentNew);
					
					$amountDol									= $objCustomerCredotDocument->balance / $exchangeRate;
					$amountCor									= $objCustomerCredotDocument->balance;
					
					//aumentar el blance de la linea
					$objCustomerCreditLine						= $this->Customer_Credit_Line_Model->get_rowByPK($objCustomerCredotDocument->customerCreditLineID);
					$objCustomerCreditLineNew["balance"]		= $objCustomerCreditLine->balance + ($objCustomerCreditLine->currencyID == $objCurrencyDolares->currencyID ? $amountDol : $amountCor);
					$this->Customer_Credit_Line_Model->update_app_posme($objCustomerCredotDocument->customerCreditLineID,$objCustomerCreditLineNew);
					
					//aumentar el balance de credito
					$objCustomer								= $this->Customer_Model->get_rowByEntity($objTM->companyID,$objTM->entityID);
					$objCustomerCredit							= $this->Customer_Credit_Model->get_rowByPK($objTM->companyID,$objCustomer->branchID,$objTM->entityID);
					$objCustomerCreditNew["balanceDol"]			= $objCustomerCredit->balanceDol + $amountDol;
					$this->Customer_Credit_Model->update_app_posme($objTM->companyID,$objCustomer->branchID,$objTM->entityID,$objCustomerCreditNew);
					
					return $this->response->setJSON(array(
							'error'   => false,
							'message' => SUCCESS." Factura anulada"
					));//--finjson				
				
				}
				
				return $this->response->setJSON(array(
							'error'   => false,
							'message' => SUCCESS." Factura anulada"
				));//--finjson				
				
				
			}
			else 
			{	
				//Eliminar el Registro			
				$this->Transaction_Master_Model->delete_app_posme($companyID,$transactionID,$transactionMasterID);
				$this->Transaction_Master_Detail_Model->deleteWhereTM($companyID,$transactionID,$transactionMasterID);	

				return $this->response->setJSON(array(
							'error'   => false,
							'message' => SUCCESS." Factura anulada"
				));//--finjson				
				
			}
			
			
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
			
			
			//Obtener el Componente de Transacciones Facturacion
			$objComponentBilling			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_transaction_master_billing");
			if(!$objComponentBilling)
			throw new \Exception("EL COMPONENTE 'tb_transaction_master_billing' NO EXISTE...");
			
			
			$objComponentItem				= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			
			$branchID 								= $dataSession["user"]->branchID;
			$roleID 								= $dataSession["role"]->roleID;
			$companyID 								= $dataSession["user"]->companyID;
			$userID 								= $dataSession["user"]->userID;
			$transactionID 							= /*inicio get post*/ $this->request->getPost("txtTransactionID");
			$transactionMasterID					= /*inicio get post*/ $this->request->getPost("txtTransactionMasterID");
			$objTM	 								= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$oldStatusID 							= $objTM->statusID;
			$parameterCausalTypeCredit 				= $this->core_web_parameter->getParameter("INVOICE_BILLING_CREDIT",$companyID);
			
			
			//Valores de tasa de cambio
			date_default_timezone_set(APP_TIMEZONE); 
			$objCurrencyDolares						= $this->core_web_currency->getCurrencyExternal($companyID);
			$objCurrencyCordoba						= $this->core_web_currency->getCurrencyDefault($companyID);
			$dateOn 								= date("Y-m-d");
			$dateOn 								= date_format(date_create($dateOn),"Y-m-d");
			$exchangeRate 							= $this->core_web_currency->getRatio($companyID,$dateOn,1,$objCurrencyDolares->currencyID,$objCurrencyCordoba->currencyID);
			
			
			//Validar Edicion por el Usuario
			if ($resultPermission 	== PERMISSION_ME && ($objTM->createdBy != $userID))
			throw new \Exception(NOT_EDIT);
			
			//Validar si el estado permite editar
			if(!$this->core_web_workflow->validateWorkflowStage("tb_transaction_master_billing","statusID",$objTM->statusID,COMMAND_EDITABLE_TOTAL,$dataSession["user"]->companyID,$dataSession["user"]->branchID,$dataSession["role"]->roleID))
			throw new \Exception(NOT_WORKFLOW_EDIT);					
			
			if($this->core_web_accounting->cycleIsCloseByDate($companyID,$objTM->transactionOn))
			throw new \Exception("EL DOCUMENTO NO PUEDE ACTUALIZARCE, EL CICLO CONTABLE ESTA CERRADO");
			
			
			$objParameterInvoiceUpdateNameInTransactionOnly		= $this->core_web_parameter->getParameter("INVOICE_UPDATENAME_IN_TRANSACTION_ONLY",$companyID);
			$objParameterInvoiceUpdateNameInTransactionOnly		= $objParameterInvoiceUpdateNameInTransactionOnly->value;
			
			$objParameterInvoiceBillingQuantityZero		= $this->core_web_parameter->getParameter("INVOICE_BILLING_QUANTITY_ZERO",$companyID);
			$objParameterInvoiceBillingQuantityZero		= $objParameterInvoiceBillingQuantityZero->value;
			$objParameterImprimirPorCadaFactura			= $this->core_web_parameter->getParameter("INVOICE_PRINT_BY_INVOICE",$companyID);
			$objParameterImprimirPorCadaFactura			= $objParameterImprimirPorCadaFactura->value;
			$objParameterRegrearANuevo					= $this->core_web_parameter->getParameter("INVOICE_BILLING_SAVE_AFTER_TO_ADD",$companyID);
			$objParameterRegrearANuevo					= $objParameterRegrearANuevo->value;
			
			//Actualizar Maestro
			$codigoMesero								= /*inicio get post*/ $this->request->getPost("txtCodigoMesero");
			$typePriceID 								= /*inicio get post*/ $this->request->getPost("txtTypePriceID");
			$objListPrice 								= $this->List_Price_Model->getListPriceToApply($companyID);
			$objTMNew["transactionCausalID"] 			= /*inicio get post*/ $this->request->getPost("txtCausalID");
			$objTMNew["entityID"] 						= /*inicio get post*/ $this->request->getPost("txtCustomerID");
			$objTMNew["transactionOn"]					= date("Y-m-d"); ///*inicio get post*/ $this->request->getPost("txtDate");
			$objTMNew["transactionOn2"]					= /*inicio get post*/ $this->request->getPost("txtDateFirst");//Fecha del Primer Pago, de las facturas al credito
			$objTMNew["statusIDChangeOn"]				= date("Y-m-d H:m:s");
			$objTMNew["note"] 							= /*inicio get post*/ $this->request->getPost("txtNote");//--fin peticion get o post			
			$objTMNew["reference1"] 					= /*inicio get post*/ $this->request->getPost("txtReference1");
			$objTMNew["descriptionReference"] 			= "reference1:entityID del proveedor de credito para las facturas al credito,reference4: customerCreditLineID linea de credito del cliente";
			$objTMNew["reference2"] 					= /*inicio get post*/ $this->request->getPost("txtReference2");
			$objTMNew["reference3"] 					= /*inicio get post*/ $this->request->getPost("txtReference3");
			$objTMNew["reference4"] 					= is_null( $this->request->getPost("txtCustomerCreditLineID") ) ? "0" : /*inicio get post*/ $this->request->getPost("txtCustomerCreditLineID");//--fin peticion get o post
			$objTMNew["statusID"] 						= /*inicio get post*/ $this->request->getPost("txtStatusID");
			$objTMNew["amount"] 						= 0;
			$objTMNew["currencyID"]						= /*inicio get post*/ $this->request->getPost("txtCurrencyID"); 
			$objTMNew["currencyID2"]					= $this->core_web_currency->getTarget($companyID,$objTMNew["currencyID"]);
			$objTMNew["exchangeRate"]					= $this->core_web_currency->getRatio($dataSession["user"]->companyID,date("Y-m-d"),1,$objTMNew["currencyID2"],$objTMNew["currencyID"]);
			$objTMNew["sourceWarehouseID"]				= /*inicio get post*/ $this->request->getPost("txtWarehouseID");
			$objTMNew["periodPay"]						= /*inicio get post*/ $this->request->getPost("txtPeriodPay");
			$objTMNew["nextVisit"]						= /*inicio get post*/ $this->request->getPost("txtNextVisit");
			$objTMNew["numberPhone"]					= /*inicio get post*/ $this->request->getPost("txtNumberPhone");
			$objTMNew["entityIDSecondary"]				= /*inicio get post*/ $this->request->getPost("txtEmployeeID");
			
			//Ingresar Informacion Adicional
			$objTMInfoNew["companyID"]					= $objTM->companyID;
			$objTMInfoNew["transactionID"]				= $objTM->transactionID;
			$objTMInfoNew["transactionMasterID"]		= $transactionMasterID;
			$objTMInfoNew["zoneID"]						= /*inicio get post*/ $this->request->getPost("txtZoneID");
			$objTMInfoNew["routeID"]					= 0;
			$objTMInfoNew["mesaID"]						= /*inicio get post*/ $this->request->getPost("txtMesaID");
			$objTMInfoNew["referenceClientName"]		= /*inicio get post*/ $this->request->getPost("txtReferenceClientName");
			$objTMInfoNew["referenceClientIdentifier"]	= /*inicio get post*/ $this->request->getPost("txtReferenceClientIdentifier");
			$objTMInfoNew["receiptAmount"]				= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmount"));
			$objTMInfoNew["receiptAmountDol"]			= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountDol"));
			$objTMInfoNew["receiptAmountPoint"]			= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountPoint"));
			$objTMInfoNew["receiptAmountBank"]			= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountBank"));
			$objTMInfoNew["receiptAmountBankDol"]		= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountBankDol"));
			$objTMInfoNew["receiptAmountCardDol"]		= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountTarjetaDol"));
			$objTMInfoNew["receiptAmountCard"]			= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountTarjeta"));
			$objTMInfoNew["changeAmount"]				= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtChangeAmount"));
			
			$objTMInfoNew["receiptAmountBankReference"]					= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountBank_Reference"));
			$objTMInfoNew["receiptAmountBankDolReference"]				= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountBankDol_Reference"));
			$objTMInfoNew["receiptAmountCardBankReference"]				= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountTarjeta_Reference"));
			$objTMInfoNew["receiptAmountCardBankDolReference"]			= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountTarjetaDol_Reference"));
			
			$objTMInfoNew["receiptAmountBankID"]					= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountBank_BankID"));
			$objTMInfoNew["receiptAmountBankDolID"]					= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountBankDol_BankID"));
			$objTMInfoNew["receiptAmountCardBankID"]				= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountTarjeta_BankID"));
			$objTMInfoNew["receiptAmountCardBankDolID"]				= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountTarjetaDol_BankID"));
			$objTMInfoNew["reference1"]								= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtTMIReference1"));
			$objTMInfoNew["reference2"]								= "not_used";
			
			
			
			$db=db_connect();
			$db->transStart();
			
			//El Estado solo permite editar el workflow
			if($this->core_web_workflow->validateWorkflowStage("tb_transaction_master_billing","statusID",$objTM->statusID,COMMAND_EDITABLE,$dataSession["user"]->companyID,$dataSession["user"]->branchID,$dataSession["role"]->roleID)){
				$objTMNew								= array();
				$objTMNew["statusID"] 					= /*inicio get post*/ $this->request->getPost("txtStatusID");						
				$this->Transaction_Master_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$objTMNew);
			}
			else{
				$this->Transaction_Master_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$objTMNew);
				$this->Transaction_Master_Info_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$objTMInfoNew);
			}
			
			
			
			//Leer archivo
			$path 		= PATH_FILE_OF_APP."/company_".$companyID."/component_".$objComponentBilling->componentID."/component_item_".$transactionMasterID;			
			$path 		= $path.'/procesar.csv';
			$pathNew 	= PATH_FILE_OF_APP."/company_".$companyID."/component_".$objComponentBilling->componentID."/component_item_".$transactionMasterID;			
			$pathNew 	= $pathNew.'/procesado.csv';
					
			
			
			if (file_exists($path))
			{
				//Actualizar Detalle
				$listTransactionDetalID 					= array();
				$arrayListItemID 							= array();
				$arrayListItemName							= array();
				$arrayListQuantity	 						= array();
				$arrayListPrice		 						= array();
				$arrayListSubTotal	 						= array();
				$arrayListIva		 						= array();
				$arrayListLote	 							= array();
				$arrayListVencimiento						= array();
				$arrayListSku								= array();
				$arrayListSkuFormatoDescription 			= array();
				
				$objParameterDeliminterCsv	= $this->core_web_parameter->getParameter("CORE_CSV_SPLIT",$companyID);
				$characterSplie = $objParameterDeliminterCsv->value;
				
				//Obtener los registro del archivo
				$this->csvreader->separator = $characterSplie;
				$table 			= $this->csvreader->parse_file($path); 
				
				
				rename($path,$pathNew);
				$fila 			= 0;
				if($table)
				foreach ($table as $row) 
				{	
					$fila++;
					$codigo 		= $row["Codigo"];
					$description 	= $row["Nombre"];
					$cantidad 		= $row["Cantidad"];
					$precio 		= $row["Precio"];											
					$objItem		= $this->Item_Model->get_rowByCode($companyID,$codigo);
					
					array_push($listTransactionDetalID, 0);
					array_push($arrayListItemID, $objItem->itemID);
					array_push($arrayListItemName, $objItem->name);
					array_push($arrayListQuantity, $cantidad);
					array_push($arrayListPrice, $precio);
					//$arrayListSubTotal		= SUB TOTAL ES UN SOLO NUMERO
					//$arrayListIva		 		= IVA ES UN SOLO NUMERO POR QUE ES EL TOTAL
					array_push($arrayListLote, '');
					array_push($arrayListVencimiento, '');
					array_push($arrayListSku,0);
					array_push($arrayListSkuFormatoDescription,'');
					
				}
			}
			else{
				//Actualizar Detalle
				$listTransactionDetalID 					= /*inicio get post*/ $this->request->getPost("txtTransactionMasterDetailID");
				$arrayListItemID 							= /*inicio get post*/ $this->request->getPost("txtItemID");
				$arrayListItemName 							= /*inicio get post*/ $this->request->getPost("txtTransactionDetailName");
				$arrayListQuantity	 						= /*inicio get post*/ $this->request->getPost("txtQuantity");
				$arrayListPrice		 						= /*inicio get post*/ $this->request->getPost("txtPrice");
				$arrayListSubTotal	 						= /*inicio get post*/ $this->request->getPost("txtSubTotal");
				$arrayListIva		 						= /*inicio get post*/ $this->request->getPost("txtIva");
				$arrayListLote	 							= /*inicio get post*/ $this->request->getPost("txtDetailLote");			
				$arrayListVencimiento						= /*inicio get post*/ $this->request->getPost("txtDetailVencimiento");	
				$arrayListSku								= /*inicio get post*/ $this->request->getPost("txtSku");
				$arrayListSkuFormatoDescription				= /*inicio get post*/ $this->request->getPost("skuFormatoDescription");
				
			}
						
			
				
			
			//Ingresar la configuracion de precios			
			$objParameterPriceDefault	= $this->core_web_parameter->getParameter("INVOICE_DEFAULT_PRICELIST",$companyID);
			$listPriceID 	= $objParameterPriceDefault->value;
			$objTipePrice 	= $this->core_web_catalog->getCatalogAllItem("tb_price","typePriceID",$companyID);
			
			
			$objParameterUpdatePrice	= $this->core_web_parameter->getParameter("INVOICE_UPDATEPRICE_ONLINE",$companyID);
			$objUpdatePrice 			= $objParameterUpdatePrice->value;
			
			$objParameterAmortizationDuranteFactura	= $this->core_web_parameter->getParameter("INVOICE_PARAMTER_AMORITZATION_DURAN_INVOICE",$companyID);
			$objParameterAmortizationDuranteFactura = $objParameterAmortizationDuranteFactura->value;
			
							
			
			
			$amountTotal 									= 0;
			$tax1Total 										= 0;
			$subAmountTotal									= 0;			$this->Transaction_Master_Detail_Model->deleteWhereIDNotIn($companyID,$transactionID,$transactionMasterID,$listTransactionDetalID);
			$this->Transaction_Master_Detail_Credit_Model->deleteWhereIDNotIn($transactionMasterID,$listTransactionDetalID);
			if(!empty($arrayListItemID)){
				foreach($arrayListItemID as $key => $value){			
					$itemID 								= $value;
					$lote 									= is_null($arrayListLote) ? "": $arrayListLote[$key];
					$vencimiento							= is_null($arrayListVencimiento) ? "" : $arrayListVencimiento[$key];
					$warehouseID 							= $objTMNew["sourceWarehouseID"];
					$objItem 								= $this->Item_Model->get_rowByPK($companyID,$itemID);
					$objItemWarehouse 						= $this->Itemwarehouse_Model->getByPK($companyID,$itemID,$warehouseID);					
					$quantity 								= helper_StringToNumber($arrayListQuantity[$key]);
					$unitaryCost							= $objItem->cost;
					
					$objPrice 								= $this->Price_Model->get_rowByPK($companyID,$objListPrice->listPriceID,$itemID,$typePriceID);
					$objCompanyComponentConcept 			= $this->Company_Component_Concept_Model->get_rowByPK($companyID,$objComponentItem->componentID,$itemID,"IVA");
					$skuCatalogItemID						= $arrayListSku[$key];
					$itemNameDetail							= str_replace('"',"",str_replace("'","",$arrayListItemName[$key]));
					
					
					
					$objItemSku								= $this->Item_Sku_Model->getByPK($itemID,$skuCatalogItemID);
					$price 									= $arrayListPrice[$key] / ($objItemSku->value) ;
					$skuFormatoDescription					= $arrayListSkuFormatoDescription[$key];
					$ivaPercentage							= ($objCompanyComponentConcept != null ? $objCompanyComponentConcept->valueOut : 0 );					
					$unitaryAmount 							= $price * (1 + $ivaPercentage);					
					$tax1 									= $price * $ivaPercentage;
					$transactionMasterDetailID				= $listTransactionDetalID[$key];
					$comisionPorcentage						= 0;
					$comisionPorcentage						= $this->core_web_transaction_master_detail->getPorcentageComision($companyID,$listPriceID,$itemID,$price);
					$unitaryCost							= $this->core_web_transaction_master_detail->getCostCustomer($companyID,$itemID,$unitaryCost,$price);
					
					//Actualisar nombre 		
					if( $objParameterInvoiceUpdateNameInTransactionOnly  == "false")
					{
						
						$objItemNew 			= array();
						$objItemNew["name"] 	= rtrim(ltrim($itemNameDetail));
						$this->Item_Model->update_app_posme($companyID,$itemID,$objItemNew);
						
						if( strpos($itemNameDetail ,"NC.") > 0 )
						{
							$objItemNew 			= array();
							$objItemNew["name"] 	= rtrim(ltrim(explode("NC.",$itemNameDetail)[0]));
							$objItemNew["barcode"] 	= $objItem->barCode.",". rtrim(ltrim(explode("NC.",	$itemNameDetail)[1]	));
							$itemNameDetail			= $objItemNew["name"];
							$this->Item_Model->update_app_posme($companyID,$itemID,$objItemNew);
						}
							
						
						if( strpos($itemNameDetail ,"CC.") > 0 )
						{
							$objItemNew 			= array();
							$objItemNew["name"] 	= rtrim(ltrim(explode("CC.",$itemNameDetail)[0]));
							$objItemNew["barcode"] 	= rtrim(ltrim(explode("CC.",$itemNameDetail)[1]));
							$itemNameDetail			= $objItemNew["name"];
							$this->Item_Model->update_app_posme($companyID,$itemID,$objItemNew);						
						}
						

					}
					
					
					
					
					
					//Validar Cantidades
					$messageException = "La cantidad de '".$objItem->itemNumber. " " .$objItem->name."' es mayor que la disponible en bodega";
					$messageException = $messageException.", en bodega existen ".$objItemWarehouse->quantity." y esta solicitando : ".$quantity;
					if(
						$objItemWarehouse->quantity < $quantity  
						&& 
						$objItem->isInvoiceQuantityZero == 0
						&&
						$objParameterInvoiceBillingQuantityZero == "false"
					)					
					throw new \Exception($messageException);
								

								
					//Nuevo Detalle
					if($transactionMasterDetailID == 0){	
						
						$objTMD 								= NULL;
						$objTMD["companyID"] 					= $objTM->companyID;
						$objTMD["transactionID"] 				= $objTM->transactionID;
						$objTMD["transactionMasterID"] 			= $transactionMasterID;
						$objTMD["componentID"]					= $objComponentItem->componentID;
						$objTMD["componentItemID"] 				= $itemID;
						
						$objTMD["quantity"] 					= $quantity * $objItemSku->value;	//cantidad
						$objTMD["skuQuantity"] 					= $quantity;						//cantidad
						$objTMD["skuQuantityBySku"]				= $objItemSku->value;				//cantidad
					
						
						$objTMD["unitaryCost"]					= $unitaryCost;								//costo
						$objTMD["cost"] 						= $objTMD["quantity"]  * $unitaryCost;		//costo por unidad
						
						$objTMD["unitaryPrice"]					= $price;							//precio de lista
						$objTMD["unitaryAmount"]				= $unitaryAmount;					//precio de lista con inpuesto
						$objTMD["tax1"]							= $tax1;							//impuesto de lista
						$objTMD["amount"] 						= $objTMD["quantity"] * $unitaryAmount;		//precio de lista con inpuesto por cantidad
						$objTMD["discount"]						= 0;					
						$objTMD["promotionID"] 					= 0;
						
						$objTMD["reference1"]					= $lote;
						$objTMD["reference2"]					= $vencimiento;
						$objTMD["reference3"]					= '0';
						$objTMD["itemNameLog"] 					= $itemNameDetail;
						
						
						$objTMD["catalogStatusID"]				= 0;
						$objTMD["inventoryStatusID"]			= 0;
						$objTMD["isActive"]						= 1;
						$objTMD["quantityStock"]				= 0;
						$objTMD["quantiryStockInTraffic"]		= 0;
						$objTMD["quantityStockUnaswared"]		= 0;
						$objTMD["remaingStock"]					= 0;
						$objTMD["expirationDate"]				= NULL;
						$objTMD["inventoryWarehouseSourceID"]	= $objTMNew["sourceWarehouseID"];
						$objTMD["inventoryWarehouseTargetID"]	= $objTM->targetWarehouseID;
						$objTMD["skuCatalogItemID"] 			= $skuCatalogItemID;
						$objTMD["skuFormatoDescription"] 		= $skuFormatoDescription;
						$objTMD["amountCommision"] 				= $price * $comisionPorcentage * $quantity ;
						
						
						$tax1Total								= $tax1Total + $tax1;
						$subAmountTotal							= $subAmountTotal + ($quantity * $price);
						$amountTotal							= $amountTotal + $objTMD["amount"];
						$transactionMasterDetailID_				= $this->Transaction_Master_Detail_Model->insert_app_posme($objTMD);
						$objTMDC								= NULL;
						$objTMDC["transactionMasterID"]			= $transactionMasterID;
						$objTMDC["transactionMasterDetailID"]	= $transactionMasterDetailID_;
						$objTMDC["reference1"]					= /*inicio get post*/ $this->request->getPost("txtFixedExpenses");
						$objTMDC["reference2"]					= /*inicio get post*/ $this->request->getPost("txtCheckReportSinRiesgo");
						$objTMDC["reference3"]					= /*inicio get post*/ $this->request->getPost("txtLayFirstLineProtocolo");
						$objTMDC["reference4"]					= "";
						$objTMDC["reference5"]					= "";
						$objTMDC["reference9"]					= "reference1: Porcentaje de Gastos fijos para las facturas de credito,reference2: Escritura Publica,reference3: Primer Linea del Protocolo";						
						$this->Transaction_Master_Detail_Credit_Model->insert_app_posme($objTMDC);
						
						//Actualizar el Precio
						if($objUpdatePrice == "true" )
						{							
							$typePriceID					= $typePriceID;
							$dataUpdatePrice["price"] 		= $price;
							$dataUpdatePrice["percentage"] 	= 
															$unitaryCost == 0 ? 
																($price / 100) : 
																(((100 * $price) / $unitaryCost ) - 100);																		
							
							$objPrice = $this->Price_Model->update_app_posme($companyID,$listPriceID,$itemID,$typePriceID,$dataUpdatePrice);									
							
						}
						
						
					}					
					//Editar Detalle
					else{
						
						$objTMDC  								= $this->Transaction_Master_Detail_Credit_Model->get_rowByPK($transactionMasterDetailID);
						$objTMDC								= NULL;
						
						$objTMDNew 								= null;
						
						$objTMDNew["quantity"] 					= $quantity * $objItemSku->value;	//cantidad
						$objTMDNew["skuQuantity"] 				= $quantity;						//cantidad
						$objTMDNew["skuQuantityBySku"]			= $objItemSku->value;				//cantidad
					
						
						$objTMDNew["unitaryCost"]				= $unitaryCost;								//costo
						$objTMDNew["cost"] 						= $objTMDNew["quantity"]  * $unitaryCost;	//costo por cantidad
						
						$objTMDNew["unitaryPrice"]				= $price;						//precio de lista
						$objTMDNew["unitaryAmount"]				= $unitaryAmount;				//precio de lista con inpuesto
						$objTMDNew["tax1"]						= $tax1;						//impuesto de lista
						$objTMDNew["amount"] 					= $objTMDNew["quantity"]  * $unitaryAmount;	//precio de lista con inpuesto por cantidad
						
						$objTMDNew["reference1"]				= $lote;
						$objTMDNew["reference2"]				= $vencimiento;
						$objTMDNew["reference3"]				= '0';
						$objTMDNew["itemNameLog"] 				= $itemNameDetail;
						$objTMDNew["inventoryWarehouseSourceID"]= $objTMNew["sourceWarehouseID"];
						$objTMDNew["skuCatalogItemID"] 			= $skuCatalogItemID;
						$objTMDNew["skuFormatoDescription"] 	= $skuFormatoDescription;						
						$objTMDNew["amountCommision"] 			= $price * $comisionPorcentage * $quantity;
						
						$tax1Total								= $tax1Total + $tax1;
						$subAmountTotal							= $subAmountTotal + ($quantity * $price);
						$amountTotal							= $amountTotal + $objTMDNew["amount"];						
						$this->Transaction_Master_Detail_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$transactionMasterDetailID,$objTMDNew);	
						
						$objTMDC["reference1"]					= /*inicio get post*/ $this->request->getPost("txtFixedExpenses");
						$objTMDC["reference2"]					= /*inicio get post*/ $this->request->getPost("txtCheckReportSinRiesgo");
						$objTMDC["reference3"]					= /*inicio get post*/ $this->request->getPost("txtLayFirstLineProtocolo");
						$objTMDC["reference4"]					= "";
						$objTMDC["reference5"]					= "";
						$objTMDC["reference9"]					= "reference1: Porcentaje de Gastos Fijos para las Facturas de Credito,reference2: Escritura Publica,reference3: Primer Linea del Protocolo";
						$this->Transaction_Master_Detail_Credit_Model->update_app_posme($transactionMasterDetailID,$objTMDC);
						
						//Actualizar el Precio
						if($objUpdatePrice == "true" )
						{
							
							$typePriceID					= $typePriceID;
							$dataUpdatePrice["price"] 		= $price;
							$dataUpdatePrice["percentage"] 	= 
															$unitaryCost == 0 ? 
																($price / 100) : 
																(((100 * $price) / $unitaryCost ) - 100);
							
							$objPrice = $this->Price_Model->update_app_posme($companyID,$listPriceID,$itemID,$typePriceID,$dataUpdatePrice);									
							
						}
						
					}
					
					
					
					
				}
			}			
			
			//Actualizar Transaccion			
			$objTMNew["amount"] 	= $amountTotal;
			$objTMNew["tax1"] 		= $tax1Total;
			$objTMNew["subAmount"] 	= $subAmountTotal;
			$this->Transaction_Master_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$objTMNew);
			
			
			//Aplicar el Documento?
			if( 
				$this->core_web_workflow->validateWorkflowStage
				(
					"tb_transaction_master_billing",
					"statusID",
					$objTMNew["statusID"],
					COMMAND_APLICABLE,
					$dataSession["user"]->companyID,
					$dataSession["user"]->branchID,
					$dataSession["role"]->roleID
				) &&  
				$oldStatusID != $objTMNew["statusID"] 
			){
				
				//Actualizar el numero de factura
				$objTMNew003["transactionNumber"]				= $this->core_web_counter->goNextNumber($dataSession["user"]->companyID,$dataSession["user"]->branchID,"tb_transaction_master_billing",0);
				$objTMNew003["createdOn"]						= date("Y-m-d H:m:s");
				$this->Transaction_Master_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$objTMNew003);
				
				
				//Acumular punto del cliente.
				if($objTMInfoNew["receiptAmountPoint"] <= 0 && $objTMNew["currencyID"]  == $objCurrencyCordoba->currencyID )
				{
					$objCustomer 					= $this->Customer_Model->get_rowByEntity($companyID, $objTMNew["entityID"] );
					$objCustomerNew["balancePoint"]	= $objCustomer->balancePoint + $amountTotal;
					$this->Customer_Model->update_app_posme($objCustomer->companyID,$objCustomer->branchID,$objCustomer->entityID,$objCustomerNew);
				}
				//Es pago con punto restar puntos
				if($objTMInfoNew["receiptAmountPoint"] > 0 && $objTMNew["currencyID"]  ==  $objCurrencyCordoba->currencyID )
				{
					$objCustomer 					= $this->Customer_Model->get_rowByEntity($companyID, $objTMNew["entityID"] );
					$objCustomerNew["balancePoint"]	= $objCustomer->balancePoint - $objTMInfoNew["receiptAmountPoint"];
					$this->Customer_Model->update_app_posme($objCustomer->companyID,$objCustomer->branchID,$objCustomer->entityID,$objCustomerNew);
				}
				
				
				//Ingresar en Kardex.
				$this->core_web_inventory->calculateKardexNewOutput($companyID,$transactionID,$transactionMasterID);			
			
				//Crear Conceptos.
				$this->core_web_concept->billing($companyID,$transactionID,$transactionMasterID);
				
				//Si es al credito crear tabla de amortizacion
				$causalIDTypeCredit 	= explode(",", $parameterCausalTypeCredit->value);
				$exisCausalInCredit		= null;
				$exisCausalInCredit		= array_search($objTMNew["transactionCausalID"] ,$causalIDTypeCredit);
				
				//si la factura es de credito
				if($exisCausalInCredit || $exisCausalInCredit === 0){
					
					
					//Crear documento del modulo
					$objCustomerCreditLine 								= $this->Customer_Credit_Line_Model->get_rowByPK($objTMNew["reference4"]);
					$objCustomerCreditDocument["companyID"] 			= $companyID;
					$objCustomerCreditDocument["entityID"] 				= $objCustomerCreditLine->entityID;
					$objCustomerCreditDocument["customerCreditLineID"] 	= $objCustomerCreditLine->customerCreditLineID;
					$objCustomerCreditDocument["documentNumber"] 		= $objTMNew003["transactionNumber"];
					$objCustomerCreditDocument["dateOn"] 				= $objTMNew["transactionOn"];
					$objCustomerCreditDocument["exchangeRate"] 			= $objTMNew["exchangeRate"];
					$objCustomerCreditDocument["interes"] 				= $objCustomerCreditLine->interestYear;
					
					$objCustomerCreditDocument["term"] 					= $objCustomerCreditLine->term;
					$objCustomerCreditDocument["amount"] 				= $amountTotal; 
					$objCustomerCreditDocument["balance"] 				= $amountTotal;
					
				
					if($objParameterAmortizationDuranteFactura == "true" &&  $objTMNew["currencyID"] == 1 /*cordoba*/)
					{
						
						
						$objCustomerCreditDocument["term"] 					= $objTMNew["reference2"];
						$objCustomerCreditDocument["interes"] 				= /*inicio get post*/ $this->request->getPost("txtFixedExpenses");
						$objCustomerCreditDocument["amount"] 				= 	$amountTotal - 
																				$objTMInfoNew["receiptAmountPoint"] - 
																				
																				$objTMInfoNew["receiptAmount"] - 
																				$objTMInfoNew["receiptAmountBank"] - 																				
																				$objTMInfoNew["receiptAmountCard"] - 
																				
																				round(($objTMInfoNew["receiptAmountBankDol"] * $objTMNew["exchangeRate"]),2) - 
																				round(($objTMInfoNew["receiptAmountCardDol"] * $objTMNew["exchangeRate"]),2) - 																			
																				round(($objTMInfoNew["receiptAmountDol"] * $objTMNew["exchangeRate"]),2)  ;
																				
						$objCustomerCreditDocument["balance"] 				= $objCustomerCreditDocument["amount"];
					}
					
					if($objParameterAmortizationDuranteFactura == "true" &&  $objTMNew["currencyID"] == 2 /*dolares*/)
					{
						$objCustomerCreditDocument["term"] 					= $objTMNew["reference2"];
						$objCustomerCreditDocument["interes"] 				= /*inicio get post*/ $this->request->getPost("txtFixedExpenses");
						$objCustomerCreditDocument["amount"] 				= 	$amountTotal - 
																				$objTMInfoNew["receiptAmountPoint"] - 
																				
																				$objTMInfoNew["receiptAmount"] - 
																				$objTMInfoNew["receiptAmountBank"] - 																				
																				$objTMInfoNew["receiptAmountCard"] - 
																				
																				round(($objTMInfoNew["receiptAmountBankDol"] / $objTMNew["exchangeRate"]),2) - 
																				round(($objTMInfoNew["receiptAmountCardDol"] / $objTMNew["exchangeRate"]),2) - 																			
																				round(($objTMInfoNew["receiptAmountDol"] / $objTMNew["exchangeRate"]),2)  ;
																				
						$objCustomerCreditDocument["balance"] 				= $objCustomerCreditDocument["amount"];
					}
					
					
					$objCustomerCreditDocument["currencyID"] 			= $objTMNew["currencyID"];					
					$objCustomerCreditDocument["statusID"] 				= $this->core_web_workflow->getWorkflowInitStage("tb_customer_credit_document","statusID",$companyID,$branchID,$roleID)[0]->workflowStageID;
					$objCustomerCreditDocument["reference1"] 			= $objTMNew["note"];
					$objCustomerCreditDocument["reference2"] 			= "";
					$objCustomerCreditDocument["reference3"] 			= "";
					$objCustomerCreditDocument["isActive"] 				= 1;
					
					$objCustomerCreditDocument["providerIDCredit"] 		= $objTMNew["reference1"];					
					$objCustomerCreditDocument["periodPay"]				= $objCustomerCreditLine->periodPay;
					
					if($objParameterAmortizationDuranteFactura == "true")
					{
						$objCustomerCreditDocument["periodPay"]			= $objTMNew["periodPay"];
					}
					
					$objCustomerCreditDocument["typeAmortization"] 		= $objCustomerCreditLine->typeAmortization;					
					$objCustomerCreditDocument["reportSinRiesgo"] 	 	= /*inicio get post*/ $this->request->getPost("txtCheckReportSinRiesgo");
					$customerCreditDocumentID 							= $this->Customer_Credit_Document_Model->insert_app_posme($objCustomerCreditDocument);
					$periodPay 											= $this->Catalog_Item_Model->get_rowByCatalogItemID($objCustomerCreditLine->periodPay);
					
					if($objParameterAmortizationDuranteFactura == "true")
					{
						$periodPay 										= $this->Catalog_Item_Model->get_rowByCatalogItemID( $objTMNew["periodPay"] );
					}
					
					
					$objCatalogItem_DiasNoCobrables 		= $this->core_web_catalog->getCatalogAllItemByNameCatalogo("CXC_NO_COBRABLES",$companyID);
					$objCatalogItem_DiasFeriados365 		= $this->core_web_catalog->getCatalogAllItemByNameCatalogo("CXC_NO_COBRABLES_FERIADOS_365",$companyID);
					$objCatalogItem_DiasFeriados366 		= $this->core_web_catalog->getCatalogAllItemByNameCatalogo("CXC_NO_COBRABLES_FERIADOS_366",$companyID);
						
						
					//Crear tabla de amortizacion
					$this->financial_amort->amort(
						$objCustomerCreditDocument["amount"], 		/*monto*/
						$objCustomerCreditDocument["interes"],		/*interes anual*/
						$objCustomerCreditDocument["term"],			/*numero de pagos*/	
						$periodPay->sequence,						/*frecuencia de pago en dia*/
						$objTMNew["transactionOn2"], 				/*fecha del credito*/	
						$objCustomerCreditLine->typeAmortization 	/*tipo de amortizacion*/,
						$objCatalogItem_DiasNoCobrables,
						$objCatalogItem_DiasFeriados365,
						$objCatalogItem_DiasFeriados366
					);
					
					$tableAmortization = $this->financial_amort->getTable();
					if($tableAmortization["detail"])
					foreach($tableAmortization["detail"] as $key => $itemAmortization){
						$objCustomerAmoritizacion["customerCreditDocumentID"]	= $customerCreditDocumentID;
						$objCustomerAmoritizacion["balanceStart"]				= $itemAmortization["saldoInicial"];
						$objCustomerAmoritizacion["dateApply"]					= $itemAmortization["date"];
						$objCustomerAmoritizacion["interest"]					= $itemAmortization["interes"];
						$objCustomerAmoritizacion["capital"]					= $itemAmortization["principal"];
						$objCustomerAmoritizacion["share"]						= $itemAmortization["cuota"];
						$objCustomerAmoritizacion["balanceEnd"]					= $itemAmortization["saldo"];
						$objCustomerAmoritizacion["remaining"]					= $itemAmortization["cuota"];
						$objCustomerAmoritizacion["dayDelay"]					= 0;
						$objCustomerAmoritizacion["note"]						= '';
						$objCustomerAmoritizacion["statusID"]					= $this->core_web_workflow->getWorkflowInitStage("tb_customer_credit_amoritization","statusID",$companyID,$branchID,$roleID)[0]->workflowStageID;
						$objCustomerAmoritizacion["isActive"]					= 1;
						$objCustomerAmortizationID 								= $this->Customer_Credit_Amortization_Model->insert_app_posme($objCustomerAmoritizacion);
					}
					
					//Crear las personas relacionadas a la factura
					$objEntityRelated								= array();
					$objEntityRelated["customerCreditDocumentID"]	= $customerCreditDocumentID;
					$objEntityRelated["entityID"]					= $objCustomerCreditLine->entityID;
					$objEntityRelated["type"]						= $this->core_web_parameter->getParameter("CXC_PROPIETARIO_DEL_CREDITO",$companyID)->value;
					$objEntityRelated["typeCredit"]					= 401; /*comercial*/
					$objEntityRelated["statusCredit"]				= 429; /*activo*/
					$objEntityRelated["typeGarantia"]				= 444; /*pagare*/
					$objEntityRelated["typeRecuperation"]			= 450; /*recuperacion normal */
					$objEntityRelated["ratioDesembolso"]			= 1;
					$objEntityRelated["ratioBalance"]				= 1;
					$objEntityRelated["ratioBalanceExpired"]		= 1;
					$objEntityRelated["ratioShare"]					= 1;
					$objEntityRelated["isActive"]					= 1;
					$this->core_web_auditoria->setAuditCreated($objEntityRelated,$dataSession,$this->request);			
					$ccEntityID 		= $this->Customer_Credit_Document_Endity_Related_Model->insert_app_posme($objEntityRelated);
					
					
					
					$montoTotalCordobaCredit = $objTMNew["currencyID"] == 1 /*dolares*/ ? $objCustomerCreditDocument["amount"] : round(($objCustomerCreditDocument["amount"] * $objTMNew["exchangeRate"]),2) ;
					$montoTotalDolaresCredit = $objTMNew["currencyID"] == 2 /*dolares*/ ? $objCustomerCreditDocument["amount"] : round(($objCustomerCreditDocument["amount"] / $objTMNew["exchangeRate"]),2) ;
					
					
					//disminuir el balance de general	
					$objCustomerCredit 					= $this->Customer_Credit_Model->get_rowByPK($objCustomerCreditLine->companyID,$objCustomerCreditLine->branchID,$objCustomerCreditLine->entityID);
					$objCustomerCreditNew["balanceDol"]	= $objCustomerCredit->balanceDol - $montoTotalDolaresCredit;
					$this->Customer_Credit_Model->update_app_posme($objCustomerCreditLine->companyID,$objCustomerCreditLine->branchID,$objCustomerCreditLine->entityID,$objCustomerCreditNew);
					
					
					
					//disminuir el balance de linea
					if($objCustomerCreditLine->currencyID == $objCurrencyCordoba->currencyID)
					$objCustomerCreditLineNew["balance"]	= $objCustomerCreditLine->balance - $montoTotalCordobaCredit;
					else
					$objCustomerCreditLineNew["balance"]	= $objCustomerCreditLine->balance - $montoTotalDolaresCredit;
						
					
					$this->Customer_Credit_Line_Model->update_app_posme($objCustomerCreditLine->customerCreditLineID,$objCustomerCreditLineNew);
					
				}
				
			}
			
			
			if($db->transStatus() !== false)
			{
				$db->transCommit();					
			
				$this->core_web_notification->set_message(false,SUCCESS);				
				if($objParameterRegrearANuevo == "true")
					$this->response->redirect(base_url()."/".'app_invoice_billing/add/transactionMasterIDToPrinter/'.$transactionMasterID."/codigoMesero/".$codigoMesero);	
				else
					$this->response->redirect(base_url()."/".'app_invoice_billing/edit/transactionMasterIDToPrinter/'.$transactionMasterID.'/companyID/'.$companyID."/transactionID/".$transactionID."/transactionMasterID/".$transactionMasterID."/codigoMesero/".$codigoMesero);
			
			}
			else{
				$db->transRollback();						
				$this->core_web_notification->set_message(true,$this->db->_error_message());
				$this->response->redirect(base_url()."/".'app_invoice_billing/add/transactionMasterIDToPrinter/0'."/codigoMesero/".$codigoMesero);	
			}
			
		}
		catch(\Exception $ex){
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    echo $resultView;
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
			
			//Obtener el Componente de Transacciones Facturacion
			$objComponentBilling			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_transaction_master_billing");
			if(!$objComponentBilling)
			throw new \Exception("EL COMPONENTE 'tb_transaction_master_billing' NO EXISTE...");
			
			
			$objComponentItem				= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			
			
			
			
			$companyID 							= $dataSession["user"]->companyID;
			$branchID 							= $dataSession["user"]->branchID;
			$roleID 							= $dataSession["role"]->roleID;
			$userID								= $dataSession["user"]->userID;
			
			//Obtener transaccion
			$transactionID 							= $this->core_web_transaction->getTransactionID($dataSession["user"]->companyID,"tb_transaction_master_billing",0);
			$companyID 								= $dataSession["user"]->companyID;
			$objT 									= $this->Transaction_Model->getByCompanyAndTransaction($dataSession["user"]->companyID,$transactionID);
			$objTransactionCausal 					= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,/*inicio get post*/ $this->request->getPost("txtCausalID"));
			
			
			//Valores de tasa de cambio
			date_default_timezone_set(APP_TIMEZONE); 
			$objCurrencyDolares						= $this->core_web_currency->getCurrencyExternal($companyID);
			$objCurrencyCordoba						= $this->core_web_currency->getCurrencyDefault($companyID);
			$dateOn 								= date("Y-m-d");
			$dateOn 								= date_format(date_create($dateOn),"Y-m-d");
			$exchangeRate 							= $this->core_web_currency->getRatio($companyID,$dateOn,1,$objCurrencyDolares->currencyID,$objCurrencyCordoba->currencyID);
		
			
			
			if($this->core_web_accounting->cycleIsCloseByDate($dataSession["user"]->companyID,/*inicio get post*/ $this->request->getPost("txtDate")))
			throw new \Exception("EL DOCUMENTO NO PUEDE INGRESAR, EL CICLO CONTABLE ESTA CERRADO");
			
			
			
			
			$this->core_web_permission->getValueLicense($dataSession["user"]->companyID,get_class($this)."/"."index");
			$objParameterInvoiceBillingQuantityZero		= $this->core_web_parameter->getParameter("INVOICE_BILLING_QUANTITY_ZERO",$companyID);
			$objParameterInvoiceBillingQuantityZero		= $objParameterInvoiceBillingQuantityZero->value;
			
			//obtener el primer estado  de la factura o el estado inicial.
			$objListWorkflowStage					= $this->core_web_workflow->getWorkflowInitStage("tb_transaction_master_billing","statusID",$companyID,$branchID,$roleID);
			//Saber si se va autoaplicar
			$objParameterInvoiceAutoApply			= $this->core_web_parameter->getParameter("INVOICE_AUTOAPPLY_CASH",$companyID);
			$objParameterInvoiceAutoApply			= $objParameterInvoiceAutoApply->value;
			$objParaemterStatusCanceled				= $this->core_web_parameter->getParameter("INVOICE_BILLING_CANCEL",$companyID);
			$objParaemterStatusCanceled				= $objParaemterStatusCanceled->value;
			$objParameterUrlPrinterDirect			= $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_URL",$companyID);
			$objParameterUrlPrinterDirect			= $objParameterUrlPrinterDirect->value;
			$objParameterImprimirPorCadaFactura		= $this->core_web_parameter->getParameter("INVOICE_PRINT_BY_INVOICE",$companyID);
			$objParameterImprimirPorCadaFactura		= $objParameterImprimirPorCadaFactura->value;
			
			
			
			
			
			//Saber si es al credito
			$parameterCausalTypeCredit 				= $this->core_web_parameter->getParameter("INVOICE_BILLING_CREDIT",$companyID);			
			$causalIDTypeCredit 					= explode(",", $parameterCausalTypeCredit->value);
			$exisCausalInCredit						= null;
			$exisCausalInCredit						= array_search(/*inicio get post*/ $this->request->getPost("txtCausalID"),$causalIDTypeCredit);
			if($exisCausalInCredit || $exisCausalInCredit === 0){
				$exisCausalInCredit = "true";
			}
			//Si esta configurado como auto aplicado
			//y es al credito. cambiar el estado por el estado inicial, que es registrada
			$statusID = "";
			if($objParameterInvoiceAutoApply == "true" && $exisCausalInCredit == "true" ){				
				$statusID = $objListWorkflowStage[0]->workflowStageID;
			}
			//si la factura es al contado, y esta como auto aplicada cambiar el estado
			else if ($objParameterInvoiceAutoApply == "true" && $exisCausalInCredit != "true" ){
				$statusID  = $objParaemterStatusCanceled;
			}
			//De lo contrario respetar el estado que venga en pantalla
			else {
				$statusID = /*inicio get post*/ $this->request->getPost("txtStatusID");
			}
			
			
			$codigoMesero							= /*inicio get post*/ $this->request->getPost("txtCodigoMesero");
			$objTM["companyID"] 					= $dataSession["user"]->companyID;
			$objTM["transactionID"] 				= $transactionID;			
			$objTM["branchID"]						= $dataSession["user"]->branchID;			
			$objTM["transactionNumber"]				= $this->core_web_counter->goNextNumber($dataSession["user"]->companyID,$dataSession["user"]->branchID,"tb_transaction_master_proforma",0);
			$objTM["transactionCausalID"] 			= /*inicio get post*/ $this->request->getPost("txtCausalID");
			$objTM["entityID"] 						= /*inicio get post*/ $this->request->getPost("txtCustomerID");
			$objTM["transactionOn"]					= /*inicio get post*/ $this->request->getPost("txtDate");
			$objTM["transactionOn2"]				= /*inicio get post*/ $this->request->getPost("txtDateFirst");//Fecha del Primer Pago, de las facturas al credito
			$objTM["statusIDChangeOn"]				= date("Y-m-d H:m:s");
			$objTM["componentID"] 					= $objComponentBilling->componentID;
			$objTM["note"] 							= /*inicio get post*/ $this->request->getPost("txtNote");//--fin peticion get o post			
			$objTM["sign"] 							= $objT->signInventory;
			$objTM["currencyID"]					= /*inicio get post*/ $this->request->getPost("txtCurrencyID"); 
			$objTM["currencyID2"]					= $this->core_web_currency->getTarget($companyID,$objTM["currencyID"]);
			$objTM["exchangeRate"]					= $this->core_web_currency->getRatio($dataSession["user"]->companyID,date("Y-m-d"),1,$objTM["currencyID2"],$objTM["currencyID"]);
			$objTM["reference1"] 					= /*inicio get post*/ $this->request->getPost("txtReference1");
			$objTM["descriptionReference"] 			= "reference1:entityID del proveedor de credito para las facturas al credito,reference4: customerCreditLineID linea de credito del cliente";
			$objTM["reference2"] 					= /*inicio get post*/ $this->request->getPost("txtReference2");
			$objTM["reference3"] 					= /*inicio get post*/ $this->request->getPost("txtReference3");
			$objTM["reference4"] 					= is_null($this->request->getPost("txtCustomerCreditLineID")) ? "0" : /*inicio get post*/ $this->request->getPost("txtCustomerCreditLineID");//--fin peticion get o post*/
			$objTM["statusID"] 						= $statusID;
			$objTM["amount"] 						= 0;
			$objTM["isApplied"] 					= 0;
			$objTM["journalEntryID"] 				= 0;
			$objTM["classID"] 						= NULL;
			$objTM["areaID"] 						= NULL;
			$objTM["sourceWarehouseID"]				= /*inicio get post*/ $this->request->getPost("txtWarehouseID");
			$objTM["targetWarehouseID"]				= NULL;
			$objTM["isActive"]						= 1;
			$objTM["periodPay"]						= /*inicio get post*/ $this->request->getPost("txtPeriodPay");
			$objTM["nextVisit"]						= /*inicio get post*/ $this->request->getPost("txtNextVisit");
			$objTM["numberPhone"]					= /*inicio get post*/ $this->request->getPost("txtNumberPhone");
			$objTM["entityIDSecondary"]				= /*inicio get post*/ $this->request->getPost("txtEmployeeID");
			$this->core_web_auditoria->setAuditCreated($objTM,$dataSession,$this->request);			
			
			
			$db=db_connect();
			$db->transStart();	

			$objParameterInvoiceUpdateNameInTransactionOnly		= $this->core_web_parameter->getParameter("INVOICE_UPDATENAME_IN_TRANSACTION_ONLY",$companyID);
			$objParameterInvoiceUpdateNameInTransactionOnly		= $objParameterInvoiceUpdateNameInTransactionOnly->value;			
			$transactionMasterID = $this->Transaction_Master_Model->insert_app_posme($objTM);
			
			
			//Crear la Carpeta para almacenar los Archivos del Documento
			$documentoPath = PATH_FILE_OF_APP."/company_".$companyID."/component_".$objComponentBilling->componentID."/component_item_".$transactionMasterID;			
			
			if (!file_exists($documentoPath))
			{
				mkdir($documentoPath, 0755);
				chmod($documentoPath, 0755);
			}
			
			//Ingresar Informacion Adicional
			$objTMInfo["companyID"]					= $objTM["companyID"];
			$objTMInfo["transactionID"]				= $objTM["transactionID"];
			$objTMInfo["transactionMasterID"]		= $transactionMasterID;
			$objTMInfo["zoneID"]					= /*inicio get post*/ $this->request->getPost("txtZoneID");
			$objTMInfo["mesaID"]					= /*inicio get post*/ $this->request->getPost("txtMesaID");
			$objTMInfo["routeID"]					= 0;
			$objTMInfo["referenceClientName"]		= /*inicio get post*/ $this->request->getPost("txtReferenceClientName");
			$objTMInfo["referenceClientIdentifier"]	= /*inicio get post*/ $this->request->getPost("txtReferenceClientIdentifier");
			$objTMInfo["receiptAmount"]				= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmount"));
			$objTMInfo["receiptAmountDol"]			= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountDol"));
			$objTMInfo["receiptAmountPoint"]		= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountPoint"));
			$objTMInfo["receiptAmountBank"]			= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountBank"));
			$objTMInfo["receiptAmountBankDol"]		= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountBankDol"));
			$objTMInfo["receiptAmountCardDol"]		= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountTarjetaDol"));
			$objTMInfo["receiptAmountCard"]			= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountTarjeta"));
			$objTMInfo["changeAmount"]				= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtChangeAmount"));
			
			$objTMInfo["receiptAmountBankReference"]				= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountBank_Reference"));
			$objTMInfo["receiptAmountBankDolReference"]				= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountBankDol_Reference"));
			$objTMInfo["receiptAmountCardBankReference"]			= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountTarjeta_Reference"));
			$objTMInfo["receiptAmountCardBankDolReference"]			= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountTarjetaDol_Reference"));			
			
			$objTMInfo["receiptAmountBankID"]						= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountBank_BankID"));
			$objTMInfo["receiptAmountBankDolID"]					= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountBankDol_BankID"));
			$objTMInfo["receiptAmountCardBankID"]					= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountTarjeta_BankID"));
			$objTMInfo["receiptAmountCardBankDolID"]				= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtReceiptAmountTarjetaDol_BankID"));			
			$objTMInfo["reference1"]								= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("txtTMIReference1"));
			$objTMInfo["reference2"]								= "not_used";
			
			
			$this->Transaction_Master_Info_Model->insert_app_posme($objTMInfo);
			
			//Recorrer la lista del detalle del documento
			$arrayListItemID 							= /*inicio get post*/ $this->request->getPost("txtItemID");
			$arrayListItemName 							= /*inicio get post*/ $this->request->getPost("txtTransactionDetailName");
			$arrayListQuantity	 						= /*inicio get post*/ $this->request->getPost("txtQuantity");	
			$arrayListPrice		 						= /*inicio get post*/ $this->request->getPost("txtPrice");
			$arrayListSubTotal	 						= /*inicio get post*/ $this->request->getPost("txtSubTotal");
			$arrayListIva		 						= /*inicio get post*/ $this->request->getPost("txtIva");
			$arrayListLote	 							= /*inicio get post*/ $this->request->getPost("txtDetailLote");			
			$arrayListVencimiento						= /*inicio get post*/ $this->request->getPost("txtDetailVencimiento");			
			$arrayListSku								= /*inicio get post*/ $this->request->getPost("txtSku");
			$arrayListSkuFormatoDescription				= /*inicio get post*/ $this->request->getPost("skuFormatoDescription");
			
			//Ingresar la configuracion de precios		
			$amountTotal 									= 0;
			$tax1Total 										= 0;
			$subAmountTotal									= 0;
			
			
			//Tipo de precio seleccionado por el usuario,
			//Actualmente no se esta usando
			$typePriceID 							= /*inicio get post*/ $this->request->getPost("txtTypePriceID");
			$objListPrice 							= $this->List_Price_Model->getListPriceToApply($companyID);
			//obtener la lista de precio por defecto
			$objParameterPriceDefault	= $this->core_web_parameter->getParameter("INVOICE_DEFAULT_PRICELIST",$companyID);
			$listPriceID 	= $objParameterPriceDefault->value;
			//obener los tipos de precio de la lista de precio por defecto
			$objTipePrice 	= $this->core_web_catalog->getCatalogAllItem("tb_price","typePriceID",$companyID);
			
			//Parametro para validar si se cambian los precios en la facturacion
			$objParameterUpdatePrice	= $this->core_web_parameter->getParameter("INVOICE_UPDATEPRICE_ONLINE",$companyID);
			$objUpdatePrice 			= $objParameterUpdatePrice->value;
			
			
			if(!empty($arrayListItemID)){
				foreach($arrayListItemID as $key => $value){
					
					$itemID 								= $value;
					$lote 									= is_null($arrayListLote)? "" : $arrayListLote[$key];
					$vencimiento							= is_null($arrayListVencimiento) ? "" : $arrayListVencimiento[$key];
					$warehouseID 							= $objTM["sourceWarehouseID"];
					$objItem 								= $this->Item_Model->get_rowByPK($companyID,$itemID);					
					$objItemWarehouse 						= $this->Itemwarehouse_Model->getByPK($companyID,$itemID,$warehouseID);
					$quantity 								= helper_StringToNumber($arrayListQuantity[$key]);
					$objPrice 								= $this->Price_Model->get_rowByPK($companyID,$objListPrice->listPriceID,$itemID,$typePriceID);
					$objCompanyComponentConcept 			= $this->Company_Component_Concept_Model->get_rowByPK($companyID,$objComponentItem->componentID,$itemID,"IVA");
					$skuCatalogItemID						= $arrayListSku[$key];
					$itemNameDetail							= str_replace("'","",$arrayListItemName[$key]);
					$objItemSku								= $this->Item_Sku_Model->getByPK($itemID,$skuCatalogItemID);
					
					//$price								= $objItem->cost * ( 1 + ($objPrice->percentage/100));
					$price 									= $arrayListPrice[$key] / ($objItemSku->value) ;
					$skuFormatoDescription					= $arrayListSkuFormatoDescription[$key];
					$ivaPercentage							= ($objCompanyComponentConcept != null ? $objCompanyComponentConcept->valueOut : 0 );
					$unitaryAmount 							= $price * (1 + $ivaPercentage);
					$tax1 									= $price * $ivaPercentage;
					
					//Actualisar nombre 
					if( $objParameterInvoiceUpdateNameInTransactionOnly == "false")
					{
						$objItemNew 		= array();
						$objItemNew["name"] = $itemNameDetail;
						$this->Item_Model->update_app_posme($companyID,$itemID,$objItemNew);
					}
					
					
					if(
						$objItemWarehouse->quantity < $quantity 
						&& 
						$objItem->isInvoiceQuantityZero == 0
						&&						
						$objParameterInvoiceBillingQuantityZero == "false"
					)
					throw new \Exception("La cantidad de '"+$objItem->itemNumber+ " " +$objItem->name+"' es mayor que la disponible en bodega");
					
					
					$objTMD 								= NULL;
					$objTMD["companyID"] 					= $objTM["companyID"];
					$objTMD["transactionID"] 				= $objTM["transactionID"];
					$objTMD["transactionMasterID"] 			= $transactionMasterID;
					$objTMD["componentID"]					= $objComponentItem->componentID;
					$objTMD["componentItemID"] 				= $itemID;
					
					$objTMD["quantity"] 					= $quantity * $objItemSku->value;	//cantidad
					$objTMD["skuQuantity"] 					= $quantity;						//cantidad
					$objTMD["skuQuantityBySku"]				= $objItemSku->value;				//cantidad
					
					$objTMD["unitaryCost"]					= $objItem->cost;					//costo
					$objTMD["cost"] 						= $objTMD["quantity"]  * $objItem->cost;		//cantidad por costo
					
					$objTMD["unitaryPrice"]					= $price;							//precio de lista
					$objTMD["unitaryAmount"]				= $unitaryAmount;					//precio de lista con inpuesto					
					$objTMD["amount"] 						= $objTMD["quantity"] * $unitaryAmount;		//precio de lista con inpuesto por cantidad
					$objTMD["tax1"]							= $tax1;							//impuesto de lista
					
					$objTMD["discount"]						= 0;					
					$objTMD["promotionID"] 					= 0;
					
					$objTMD["reference1"]					= $lote;
					$objTMD["reference2"]					= $vencimiento;
					$objTMD["reference3"]					= '0';
					$objTMD["itemNameLog"] 					= $itemNameDetail;
					
					$objTMD["catalogStatusID"]				= 0;
					$objTMD["inventoryStatusID"]			= 0;
					$objTMD["isActive"]						= 1;
					$objTMD["quantityStock"]				= 0;
					$objTMD["quantiryStockInTraffic"]		= 0;
					$objTMD["quantityStockUnaswared"]		= 0;
					$objTMD["remaingStock"]					= 0;
					$objTMD["expirationDate"]				= NULL;
					$objTMD["inventoryWarehouseSourceID"]	= $objTM["sourceWarehouseID"];
					$objTMD["inventoryWarehouseTargetID"]	= $objTM["targetWarehouseID"];
					$objTMD["skuCatalogItemID"] 			= $skuCatalogItemID;
					$objTMD["skuFormatoDescription"] 		= $skuFormatoDescription;
					
					
					
					$tax1Total								= $tax1Total + $tax1;
					$subAmountTotal							= $subAmountTotal + ($quantity * $price);
					$amountTotal							= $amountTotal + $objTMD["amount"];
					
					$transactionMasterDetailID_				= $this->Transaction_Master_Detail_Model->insert_app_posme($objTMD);
					
					$objTMDC								= NULL;
					$objTMDC["transactionMasterID"]			= $transactionMasterID;
					$objTMDC["transactionMasterDetailID"]	= $transactionMasterDetailID_;
					$objTMDC["reference1"]					= /*inicio get post*/ $this->request->getPost("txtFixedExpenses");
					$objTMDC["reference2"]					= /*inicio get post*/ $this->request->getPost("txtCheckReportSinRiesgo");
					$objTMDC["reference3"]					= /*inicio get post*/ $this->request->getPost("txtLayFirstLineProtocolo");
					$objTMDC["reference4"]					= "";
					$objTMDC["reference5"]					= "";
					$objTMDC["reference9"]					= "reference1: Porcentaje de Gastos Fijo para las facturas de credito,reference2: Escritura Publica,reference3: Primer Linea del Protocolo";
					$this->Transaction_Master_Detail_Credit_Model->insert_app_posme($objTMDC);
					
					//Actualizar tipo de precio
					if($objUpdatePrice == "true")
					{ 
						
						$typePriceID					= $typePriceID;																				
						$dataUpdatePrice["price"] 		= $price;
						$dataUpdatePrice["percentage"] 	= 
														$objItem->cost == 0 ? 
															($price / 100) : 
															(((100 * $price) / $objItem->cost) - 100);
															
						
						$objPrice = $this->Price_Model->update_app_posme($companyID,$listPriceID,$itemID,$typePriceID,$dataUpdatePrice);
								
						
					}
					
					
				}
			}
			
			//Actualizar Transaccion
			$objTM["amount"] 	= $amountTotal;
			$objTM["tax1"] 		= $tax1Total;
			$objTM["subAmount"] = $subAmountTotal;			
			$this->Transaction_Master_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$objTM);
			
			//Aplicar el Documento?
			//Las factuas de credito no se auto aplican auque este el parametro, por que hay que crer el documento
			//y esto debe ser revisado cuidadosamente
			if( $this->core_web_workflow->validateWorkflowStage("tb_transaction_master_billing","statusID",$objTM["statusID"],COMMAND_APLICABLE,$dataSession["user"]->companyID,$dataSession["user"]->branchID,$dataSession["role"]->roleID)){
				
				//Ingresar en Kardex.
				$this->core_web_inventory->calculateKardexNewOutput($companyID,$transactionID,$transactionMasterID);			
			
				//Crear Conceptos.
				$this->core_web_concept->billing($companyID,$transactionID,$transactionMasterID);
				
				
				//Actualizar el numero de factura
				$objTMNew003["transactionNumber"]				= $this->core_web_counter->goNextNumber($dataSession["user"]->companyID,$dataSession["user"]->branchID,"tb_transaction_master_billing",0);
				$this->Transaction_Master_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$objTMNew003);
				
			}
			
			
			
			//No auto aplicar
			if( $db->transStatus() !== false && $objParameterInvoiceAutoApply == "false"  )
			{
				$db->transCommit();
				$this->core_web_notification->set_message(false,SUCCESS);				
				$this->response->redirect(base_url()."/".'app_invoice_billing/edit/transactionMasterIDToPrinter/0/companyID/'.$companyID."/transactionID/".$objTM["transactionID"]."/transactionMasterID/".$transactionMasterID."/codigoMesero/".$codigoMesero);
			}			
			//Si auto aplicar
			else if( $db->transStatus() !== false && $objParameterInvoiceAutoApply == "true"  ){
				$db->transCommit();
				
				//si es auto aplicadao mandar a imprimir
				//-wgonzlez-if($objParameterInvoiceAutoApply == "true" && $objParameterImprimirPorCadaFactura == "true" )
				//-wgonzlez-{
				//-wgonzlez-	// create a new curl resource					
				//-wgonzlez-	//wgonzalez-$urlPrinter = base_url()."/".$objParameterUrlPrinterDirect."/companyID/".$companyID."/transactionID/".$objTM["transactionID"]."/transactionMasterID/".$transactionMasterID;
				//-wgonzlez-	//wgonzalez-// set URL and other appropriate options
				//-wgonzlez-	//wgonzalez-//$multiCurl = curl_multi_init();					
				//-wgonzlez-	//wgonzalez-$curl = curl_init();
				//-wgonzlez-	//wgonzalez-
				//-wgonzlez-	//wgonzalez-curl_setopt($curl, CURLOPT_URL, $urlPrinter);
				//-wgonzlez-	//wgonzalez-curl_setopt($curl, CURLOPT_HEADER, 0);
				//-wgonzlez-	//wgonzalez-curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				//-wgonzlez-	//wgonzalez-//curl_multi_add_handle($multiCurl, $curl);
				//-wgonzlez-	//wgonzalez-
				//-wgonzlez-	//wgonzalez-// grab URL and pass it to the browser
				//-wgonzlez-	//wgonzalez-// esperar la respuesta
				//-wgonzlez-	//wgonzalez-curl_exec($curl); 
				//-wgonzlez-	//wgonzalez-
				//-wgonzlez-	//wgonzalez-// No esperar la respuesta
				//-wgonzlez-	//wgonzalez-//$running = 0;
				//-wgonzlez-	//wgonzalez-//curl_multi_exec($multiCurl, $running);	
				//-wgonzlez-	//wgonzalez-
				//-wgonzlez-	//wgonzalez-
				//-wgonzlez-	//wgonzalez-// close cURL resource, and free up system resources
				//-wgonzlez-	//wgonzalez-curl_close($curl);
				//-wgonzlez-	//wgonzalez-//curl_multi_close($multiCurl);
				//-wgonzlez-	
				//-wgonzlez-	
				//-wgonzlez-}
				
				
				$this->core_web_notification->set_message(false,SUCCESS);					
				$this->response->redirect(base_url()."/".'app_invoice_billing/add/transactionMasterIDToPrinter/'.$transactionMasterID."/codigoMesero/".$codigoMesero);	
				
			}
			//Error 
			else
			{
				$db->transRollback();						
				$errorCode 		= $db->error()["code"];
				$errorMessage 	= $db->error()["message"];
				
				$this->core_web_notification->set_message(true, $errorCode." ".$errorMessage );				
				$this->response->redirect(base_url()."/".'app_invoice_billing/add/transactionMasterIDToPrinter/0'."/codigoMesero/".$codigoMesero);	
			}
			
			
			
		}
		catch(\Exception $ex)
		{
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    echo $resultView;
			
		}	
	}
	function saveApi(){
		 try{ 
			//Autenticado
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//Validar Formulario						
			$this->validation->setRule("statusID","Estado","required");
			$this->validation->setRule("transactionOn","Fecha de transaccion","required");
			$this->validation->setRule("createdOn","Fecha de creacion","required");
			$this->validation->setRule("transactionMasterID","Id de transaccion","required");
			$this->validation->setRule("transactionCausalID","Id del causal","required");
			
			
			
			//Permiso de agregar factura
			if(/*inicio get post*/ $this->request->getPost("transactionMasterID") == 0){
				if(APP_NEED_AUTHENTICATION == true){
					$permited = false;
					$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
					
					if(!$permited)
					throw new \Exception(NOT_ACCESS_CONTROL);
					
					$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"add",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
					if ($resultPermission 	== PERMISSION_NONE)
					throw new \Exception(NOT_ALL_INSERT);	
				}
			}
			//Permiso de editar factura
			else{
				if(APP_NEED_AUTHENTICATION == true){
					$permited = false;
					$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
					
					if(!$permited)
					throw new \Exception(NOT_ACCESS_CONTROL);
					
					$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"edit",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
					if ($resultPermission 	== PERMISSION_NONE)
					throw new \Exception(NOT_ALL_EDIT);	
				}
			}
			
			
			
			$companyID 							= $dataSession["user"]->companyID;
			$branchID 							= $dataSession["user"]->branchID;
			$roleID 							= $dataSession["role"]->roleID;
			$userID								= $dataSession["user"]->userID;
			
			
			//Obtener componentes
			$objComponentBilling			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_transaction_master_billing");
			if(!$objComponentBilling)
			throw new \Exception("EL COMPONENTE 'tb_transaction_master_billing' NO EXISTE...");
			
			
			$objComponentItem				= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			
			//Obtener transaccion
			$transactionID 							= $this->core_web_transaction->getTransactionID($dataSession["user"]->companyID,"tb_transaction_master_billing",0);			
			$objTransaction							= $this->Transaction_Model->getByCompanyAndTransaction($dataSession["user"]->companyID,$transactionID);
			$objTransactionCausal 					= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,/*inicio get post*/ $this->request->getPost("transactionCausalID"));
			
			
			//Obtener tipo de cambio			
			date_default_timezone_set(APP_TIMEZONE); 
			$objCurrencyDolares						= $this->core_web_currency->getCurrencyExternal($companyID);
			$objCurrencyCordoba						= $this->core_web_currency->getCurrencyDefault($companyID);
			$dateOn 								= date("Y-m-d");
			$dateOn 								= date_format(date_create($dateOn),"Y-m-d");
			$exchangeRate 							= $this->core_web_currency->getRatio($companyID,$dateOn,1,$objCurrencyDolares->currencyID,$objCurrencyCordoba->currencyID);
			
			//Validar ciclo contable
			if($this->core_web_accounting->cycleIsCloseByDate($dataSession["user"]->companyID,/*inicio get post*/ $this->request->getPost("transactionOn")))
			throw new \Exception("EL DOCUMENTO NO PUEDE INGRESAR, EL CICLO CONTABLE ESTA CERRADO");
			
			//Validar licencia
			$this->core_web_permission->getValueLicense($dataSession["user"]->companyID,get_class($this)."/"."index");
			
			//Obtener parametros
			$objParameterInvoiceBillingQuantityZero		= $this->core_web_parameter->getParameter("INVOICE_BILLING_QUANTITY_ZERO",$companyID);
			$objParameterInvoiceBillingQuantityZero		= $objParameterInvoiceBillingQuantityZero->value;
			
			
			$objParameterInvoiceAutoApply				= $this->core_web_parameter->getParameter("INVOICE_AUTOAPPLY_CASH",$companyID);
			$objParameterInvoiceAutoApply				= $objParameterInvoiceAutoApply->value;
			
			$objParameterPriceDefault				= $this->core_web_parameter->getParameter("INVOICE_DEFAULT_PRICELIST",$companyID);
			$listPriceID 							= $objParameterPriceDefault->value;
						
			$objParameterUpdatePrice				= $this->core_web_parameter->getParameter("INVOICE_UPDATEPRICE_ONLINE",$companyID);
			$objUpdatePrice 						= $objParameterUpdatePrice->value;
			
			
			//Ver si es factura de credito
			$parameterCausalTypeCredit 				= $this->core_web_parameter->getParameter("INVOICE_BILLING_CREDIT",$companyID);			
			$causalIDTypeCredit 					= explode(",", $parameterCausalTypeCredit->value);
			$exisCausalInCredit						= null;
			$exisCausalInCredit						= array_search(/*inicio get post*/ $this->request->getPost("transactionCausalID"),$causalIDTypeCredit);
			$esFacturaDeCredito						= false;
			if($exisCausalInCredit || $exisCausalInCredit === 0){
				$exisCausalInCredit = "true";
				$esFacturaDeCredito = true;
			}
			
			//Obter estado de factura
			$objListWorkflowStage					= $this->core_web_workflow->getWorkflowInitStage("tb_transaction_master_billing","statusID",$companyID,$branchID,$roleID);
			
			
			//Obtener el estado de la factura
			//Si la configuracion es auto - aplicada
			//pero es una factura de credito - pasar el estado al inicial
			$statusID = "";
			if($objParameterInvoiceAutoApply == "true" && $exisCausalInCredit == "true" ){				
				$statusID = $objListWorkflowStage[0]->workflowStageID;
			}
			//De lo contrario respetar el estado que venga en pantalla
			else {
				$statusID = /*inicio get post*/ $this->request->getPost("statusID");
			}
			
			
			
			//Obtener tipos de precio
			$typePriceID 								= /*inicio get post*/ $this->request->getPost("typePriceID");
			$objListPrice 								= $this->List_Price_Model->getListPriceToApply($companyID);
			
			//Obtener el catalogo de tipos de precios
			$objTipePrice 		= $this->core_web_catalog->getCatalogAllItem("tb_price","typePriceID",$companyID);
			
			//Inicia los valores de la factura	
			$transactionNumber 	= 
				/*inicio get post*/ $this->request->getPost("transactionMasterID") == 0 ? 
				$this->core_web_counter->goNextNumber($dataSession["user"]->companyID,$dataSession["user"]->branchID,"tb_transaction_master_billing",0) :
				/*inicio get post*/ $this->request->getPost("transactionNumber");
				
			
			$objTM["companyID"] 					= $dataSession["user"]->companyID;
			$objTM["transactionID"] 				= $transactionID;			
			$objTM["branchID"]						= $dataSession["user"]->branchID;
			$objTM["transactionNumber"]				= $transactionNumber;
			$objTM["transactionCausalID"] 			= /*inicio get post*/ $this->request->getPost("transactionCausalID");
			$objTM["entityID"] 						= /*inicio get post*/ $this->request->getPost("entityID");
			$objTM["transactionOn"]					= /*inicio get post*/ $this->request->getPost("transactionOn");
			$objTM["transactionOn2"]				= /*inicio get post*/ $this->request->getPost("transactionOn");
			$objTM["statusIDChangeOn"]				= date("Y-m-d H:m:s");
			$objTM["componentID"] 					= $objComponentBilling->componentID;
			$objTM["note"] 							= /*inicio get post*/ $this->request->getPost("note");//--fin peticion get o post
			$objTM["sign"] 							= $objTransaction->signInventory;
			$objTM["currencyID"]					= /*inicio get post*/ $this->request->getPost("currencyID"); 
			$objTM["currencyID2"]					= $this->core_web_currency->getTarget($companyID,$objTM["currencyID"]);
			$objTM["exchangeRate"]					= $this->core_web_currency->getRatio($dataSession["user"]->companyID,date("Y-m-d"),1,$objTM["currencyID2"],$objTM["currencyID"]);
			$objTM["reference1"] 					= /*inicio get post*/ $this->request->getPost("reference1");
			$objTM["descriptionReference"] 			= "reference1:entityID del proveedor de credito para las facturas al credito,reference4: customerCreditLineID linea de credito del cliente";
			$objTM["reference2"] 					= /*inicio get post*/ $this->request->getPost("reference2");
			$objTM["reference3"] 					= /*inicio get post*/ $this->request->getPost("reference3");
			$objTM["reference4"] 					= /*inicio get post*/ $this->request->getPost("reference4");//--fin peticion get o post
			$objTM["statusID"] 						= $statusID;
			$objTM["amount"] 						= 0;
			$objTM["isApplied"] 					= 0;
			$objTM["journalEntryID"] 				= 0;
			$objTM["classID"] 						= NULL;
			$objTM["areaID"] 						= NULL;
			$objTM["sourceWarehouseID"]				= /*inicio get post*/ $this->request->getPost("sourceWarehouseID");
			$objTM["targetWarehouseID"]				= NULL;
			$objTM["isActive"]						= 1;
			$this->core_web_auditoria->setAuditCreated($objTM,$dataSession,$this->request);	
			
			
			
			$db=db_connect();
			$db->transStart();
			$transactionMasterID = 0;
			
			//Insertar Factura
			if(/*inicio get post*/ $this->request->getPost("transactionMasterID") == 0){
				$transactionMasterID 						= $this->Transaction_Master_Model->insert_app_posme($objTM);				
				
				//Crear la Carpeta para almacenar los Archivos del Documento
				mkdir(PATH_FILE_OF_APP."/company_".$companyID."/component_".$objComponentBilling->componentID."/component_item_".$transactionMasterID, 0700);
			}
			else{
				$transactionMasterID 		= /*inicio get post*/ $this->request->getPost("transactionMasterID");				
				$objTransactionMasterOld  	= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
				
				//Validar Edicion por el Usuario
				if ($resultPermission 	== PERMISSION_ME && (  /*inicio get post*/ $this->request->getPost("createdBy") != $userID))
				throw new \Exception(NOT_EDIT);
			
				//Validar si el estado permite editar
				if(!$this->core_web_workflow->validateWorkflowStage("tb_transaction_master_billing","statusID",$objTransactionMasterOld->statusID,COMMAND_EDITABLE_TOTAL,$dataSession["user"]->companyID,$dataSession["user"]->branchID,$dataSession["role"]->roleID))
				throw new \Exception(NOT_WORKFLOW_EDIT);	
			
				$this->Transaction_Master_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$objTM);
				
			}
			
			
			
			//Insertar Transaction Master Info
			$objTMInfoNew["companyID"]					= $objTM["companyID"];
			$objTMInfoNew["transactionID"]				= $objTM["transactionID"];			
			$objTMInfoNew["zoneID"]						= /*inicio get post*/ $this->request->getPost("zoneID");
			$objTMInfoNew["routeID"]					= 0;
			$objTMInfoNew["referenceClientName"]		= /*inicio get post*/ $this->request->getPost("referenceClientName");
			$objTMInfoNew["referenceClientIdentifier"]	= /*inicio get post*/ $this->request->getPost("referenceClientIdentifier");
			$objTMInfoNew["receiptAmount"]				= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("receiptAmount"));
			$objTMInfoNew["receiptAmountDol"]			= helper_StringToNumber(/*inicio get post*/ $this->request->getPost("receiptAmountDol"));			
			$objTMInfoNew["transactionMasterID"]  		= $transactionMasterID;
			
			if(/*inicio get post*/ $this->request->getPost("transactionMasterID") == 0){
				$this->Transaction_Master_Info_Model->insert_app_posme($objTMInfoNew);
			}
			else{
				
				$this->Transaction_Master_Info_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$objTMInfoNew);
			}
			//Actualizar Detalle			
			$listTransactionDetalID 					= /*inicio get post*/ $this->request->getPost("transactionMasterDetailID");
			$arrayListItemID 							= /*inicio get post*/ $this->request->getPost("itemID");
			$arrayListQuantity	 						= /*inicio get post*/ $this->request->getPost("quantity");
			$arrayListPrice		 						= /*inicio get post*/ $this->request->getPost("price");
			$arrayListSubTotal	 						= /*inicio get post*/ $this->request->getPost("subtotal");
			$arrayListIva		 						= /*inicio get post*/ $this->request->getPost("iva");
			$arrayListLote	 							= /*inicio get post*/ $this->request->getPost("lote");			
			$arrayListVencimiento						= /*inicio get post*/ $this->request->getPost("vencimiento");	
			$arrayListSku								= /*inicio get post*/ $this->request->getPost("sku");
			
			
			
			$amountTotal 									= 0;
			$tax1Total 										= 0;
			$subAmountTotal									= 0;			$this->Transaction_Master_Detail_Model->deleteWhereIDNotIn($companyID,$transactionID,$transactionMasterID,$listTransactionDetalID);
			$this->Transaction_Master_Detail_Credit_Model->deleteWhereIDNotIn($transactionMasterID,$listTransactionDetalID);
			if(!empty($arrayListItemID)){
				foreach($arrayListItemID as $key => $value){		
				
					//Obtener Variables
					$itemID 								= $value;
					$lote 									= $arrayListLote[$key];
					$vencimiento							= $arrayListVencimiento[$key];
					$warehouseID 							= $objTM["sourceWarehouseID"];
					$objItem 								= $this->Item_Model->get_rowByPK($companyID,$itemID);
					$objItemWarehouse 						= $this->Itemwarehouse_Model->getByPK($companyID,$itemID,$warehouseID);					
					$quantity 								= helper_StringToNumber($arrayListQuantity[$key]);					
					$objPrice 								= $this->Price_Model->get_rowByPK($companyID,$objListPrice->listPriceID,$itemID,$typePriceID);
					$objCompanyComponentConcept 			= $this->Company_Component_Concept_Model->get_rowByPK($companyID,$objComponentItem->componentID,$itemID,"IVA");									
					
					$skuCatalogItemID						= $arrayListSku[$key];					
					$objItemSku								= $this->Item_Sku_Model->getByPK($itemID,$skuCatalogItemID);
					
					$price 									= $arrayListPrice[$key] / ($quantity * $objItemSku->value) ;
					$ivaPercentage							= ($objCompanyComponentConcept != null ? $objCompanyComponentConcept->valueOut : 0 );					
					$unitaryAmount 							= $price * (1 + $ivaPercentage);					
					$tax1 									= $price * $ivaPercentage;
					$transactionMasterDetailID				= $listTransactionDetalID[$key];
					$nuevoRegistro							= true;
					
					
					//Validar Cantidades
					$messageException = "La cantidad de '".$objItem->itemNumber. " " .$objItem->name."' es mayor que la disponible en bodega";
					$messageException = $messageException.", en bodega existen ".$objItemWarehouse->quantity." y esta solicitando : ".$quantity;
					if(
						$objItemWarehouse->quantity < $quantity  
						&& 
						$objItem->isInvoiceQuantityZero == 0
					)					
					throw new \Exception($messageException);
						
					//Transacation Master Detalle
					$objTMD 								= NULL;
					$objTMD["companyID"] 					= $objTM["companyID"];
					$objTMD["transactionID"] 				= $objTM["transactionID"];
					$objTMD["transactionMasterID"] 			= $transactionMasterID;
					$objTMD["componentID"]					= $objComponentItem->componentID;
					$objTMD["componentItemID"] 				= $itemID;
					
					$objTMD["quantity"] 					= $quantity * $objItemSku->value;	//cantidad
					$objTMD["skuQuantity"] 					= $quantity;						//cantidad
					$objTMD["skuQuantityBySku"]				= $objItemSku->value;				//cantidad
					
					
					$objTMD["unitaryCost"]					= $objItem->cost;							//costo
					$objTMD["cost"] 						= $objTMD["quantity"] * $objItem->cost;		//costo por unidad
					
					$objTMD["unitaryPrice"]					= $price;							//precio de lista
					$objTMD["unitaryAmount"]				= $unitaryAmount;					//precio de lista con inpuesto					
					$objTMD["amount"] 						= $objTMD["quantity"]* $unitaryAmount;		//precio de lista con inpuesto por cantidad
					
					$objTMD["tax1"]							= $tax1;							//impuesto de lista
					$objTMD["discount"]						= 0;					
					$objTMD["promotionID"] 					= 0;
					
					$objTMD["reference1"]					= $lote;
					$objTMD["reference2"]					= $vencimiento;
					$objTMD["reference3"]					= '0';
					
					
					$objTMD["catalogStatusID"]				= 0;
					$objTMD["inventoryStatusID"]			= 0;
					$objTMD["isActive"]						= 1;
					$objTMD["quantityStock"]				= 0;
					$objTMD["quantiryStockInTraffic"]		= 0;
					$objTMD["quantityStockUnaswared"]		= 0;
					$objTMD["remaingStock"]					= 0;
					$objTMD["expirationDate"]				= NULL;
					$objTMD["inventoryWarehouseSourceID"]	= $objTM["sourceWarehouseID"];
					$objTMD["inventoryWarehouseTargetID"]	= $objTM["targetWarehouseID"];
					$objTMD["skuCatalogItemID"] 			= $skuCatalogItemID;
					
					
				
					
					if($transactionMasterDetailID == 0){	
						$nuevoRegistro 				= true;						
						$transactionMasterDetailID 	= $this->Transaction_Master_Detail_Model->insert_app_posme($objTMD);
					}
					else{			
						$nuevoRegistro 	= false;
						$this->Transaction_Master_Detail_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$transactionMasterDetailID,$objTMD);							
						
					}
					
					
					
					//Precio
					if($objUpdatePrice == "true" )
					{							
						$typePriceID					= $typePriceID;
						$dataUpdatePrice["price"] 		= $price;
						$dataUpdatePrice["percentage"] 	= 
														$objItem->cost == 0 ? 
															($price / 100) : 
															(((100 * $price) / $objItem->cost) - 100);																		
						
						$objPrice = $this->Price_Model->update_app_posme($companyID,$listPriceID,$itemID,$typePriceID,$dataUpdatePrice);									
						
					}
					
					
					//Documento
					$objTMDC								= NULL;
					$objTMDC["transactionMasterID"]			= $transactionMasterID;
					$objTMDC["transactionMasterDetailID"]	= $transactionMasterDetailID;
					$objTMDC["reference1"]					= 0;
					$objTMDC["reference2"]					= 0;
					$objTMDC["reference3"]					= 0;
					$objTMDC["reference4"]					= "";
					$objTMDC["reference5"]					= "";
					$objTMDC["reference9"]					= "reference1: Porcentaje de Gastos fijos para las facturas de credito,reference2: Escritura Publica,reference3: Primer Linea del Protocolo";						
					
				
					
					if($nuevoRegistro == true){	
						$this->Transaction_Master_Detail_Credit_Model->insert_app_posme($objTMDC);
					}
					else{			
						$this->Transaction_Master_Detail_Credit_Model->update_app_posme($transactionMasterDetailID,$objTMDC);
						
					}
					
					//Sumarizar Variable Totales
					$tax1Total								= $tax1Total + $tax1;
					$subAmountTotal							= $subAmountTotal + ($quantity * $price);
					$amountTotal							= $amountTotal + $objTMD["amount"];
					
					
					
				}
			}
			
			//Actualizar Transaction Master despues del detalle			
			$objTM["amount"] 	= $amountTotal;
			$objTM["tax1"] 		= $tax1Total;
			$objTM["subAmount"] = $subAmountTotal;			
			$this->Transaction_Master_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$objTM);
			
			//Aplicar el Documento
			//Las factuas de credito no se auto aplican auque este el parametro, por que hay que crer el documento
			//y esto debe ser revisado cuidadosamente
			if( $this->core_web_workflow->validateWorkflowStage("tb_transaction_master_billing","statusID",$objTM["statusID"],COMMAND_APLICABLE,$dataSession["user"]->companyID,$dataSession["user"]->branchID,$dataSession["role"]->roleID)){
				
				//Ingresar en Kardex.
				$this->core_web_inventory->calculateKardexNewOutput($companyID,$transactionID,$transactionMasterID);			
			
				//Crear Conceptos.
				$this->core_web_concept->billing($companyID,$transactionID,$transactionMasterID);
				
				//Si es al credito crear tabla de amortizacion				
				//si la factura es de credito
				if($esFacturaDeCredito == true){
					
					
					//Crear documento del modulo
					$objCustomerCreditLine 								= $this->Customer_Credit_Line_Model->get_rowByPK($objTM["reference4"]);
					$objCustomerCreditDocument["companyID"] 			= $companyID;
					$objCustomerCreditDocument["entityID"] 				= $objCustomerCreditLine->entityID;
					$objCustomerCreditDocument["customerCreditLineID"] 	= $objCustomerCreditLine->customerCreditLineID;
					$objCustomerCreditDocument["documentNumber"] 		= $objTM["transactionNumber"];
					$objCustomerCreditDocument["dateOn"] 				= $objTM["transactionOn"];
					$objCustomerCreditDocument["exchangeRate"] 			= $objTM["exchangeRate"];
					$objCustomerCreditDocument["term"] 					= $objCustomerCreditLine->term;
					$objCustomerCreditDocument["interes"] 				= $objCustomerCreditLine->interestYear;
					$objCustomerCreditDocument["amount"] 				= $amountTotal;
					$objCustomerCreditDocument["currencyID"] 			= $objTM["currencyID"];					
					$objCustomerCreditDocument["statusID"] 				= $this->core_web_workflow->getWorkflowInitStage("tb_customer_credit_document","statusID",$companyID,$branchID,$roleID)[0]->workflowStageID;
					$objCustomerCreditDocument["reference1"] 			= $objTM["note"];
					$objCustomerCreditDocument["reference2"] 			= "";
					$objCustomerCreditDocument["reference3"] 			= "";
					$objCustomerCreditDocument["isActive"] 				= 1;
					
					$objCustomerCreditDocument["providerIDCredit"] 		= $objTM["reference1"];
					$objCustomerCreditDocument["periodPay"]				= $objCustomerCreditLine->periodPay;
					$objCustomerCreditDocument["typeAmortization"] 		= $objCustomerCreditLine->typeAmortization;
					$objCustomerCreditDocument["balance"] 				= $amountTotal;
					$objCustomerCreditDocument["reportSinRiesgo"] 	 	= false;
					$customerCreditDocumentID 							= $this->Customer_Credit_Document_Model->insert_app_posme($objCustomerCreditDocument);
					$periodPay 											= $this->Catalog_Item_Model->get_rowByCatalogItemID($objCustomerCreditLine->periodPay);
					
					
					
					$objCatalogItem_DiasNoCobrables 		= $this->core_web_catalog->getCatalogAllItemByNameCatalogo("CXC_NO_COBRABLES",$companyID);
					$objCatalogItem_DiasFeriados365 		= $this->core_web_catalog->getCatalogAllItemByNameCatalogo("CXC_NO_COBRABLES_FERIADOS_365",$companyID);
					$objCatalogItem_DiasFeriados366 		= $this->core_web_catalog->getCatalogAllItemByNameCatalogo("CXC_NO_COBRABLES_FERIADOS_366",$companyID);
						
						
					//Crear tabla de amortizacion
					$this->financial_amort->amort(
						$objCustomerCreditDocument["amount"], 		/*monto*/
						$objCustomerCreditDocument["interes"],		/*interes anual*/
						$objCustomerCreditDocument["term"],			/*numero de pagos*/	
						$periodPay->sequence,						/*frecuencia de pago en dia*/
						$objTM["transactionOn2"], 					/*fecha del credito*/	
						$objCustomerCreditLine->typeAmortization 	/*tipo de amortizacion*/,
						$objCatalogItem_DiasNoCobrables,
						$objCatalogItem_DiasFeriados365,
						$objCatalogItem_DiasFeriados366
					);
					
					$tableAmortization = $this->financial_amort->getTable();
					if($tableAmortization["detail"])
					foreach($tableAmortization["detail"] as $key => $itemAmortization){
						$objCustomerAmoritizacion["customerCreditDocumentID"]	= $customerCreditDocumentID;
						$objCustomerAmoritizacion["balanceStart"]				= $itemAmortization["saldoInicial"];
						$objCustomerAmoritizacion["dateApply"]					= $itemAmortization["date"];
						$objCustomerAmoritizacion["interest"]					= $itemAmortization["interes"];
						$objCustomerAmoritizacion["capital"]					= $itemAmortization["principal"];
						$objCustomerAmoritizacion["share"]						= $itemAmortization["cuota"];
						$objCustomerAmoritizacion["balanceEnd"]					= $itemAmortization["saldo"];
						$objCustomerAmoritizacion["remaining"]					= $itemAmortization["cuota"];
						$objCustomerAmoritizacion["dayDelay"]					= 0;
						$objCustomerAmoritizacion["note"]						= '';
						$objCustomerAmoritizacion["statusID"]					= $this->core_web_workflow->getWorkflowInitStage("tb_customer_credit_amoritization","statusID",$companyID,$branchID,$roleID)[0]->workflowStageID;
						$objCustomerAmoritizacion["isActive"]					= 1;
						$objCustomerAmortizationID 								= $this->Customer_Credit_Amortization_Model->insert_app_posme($objCustomerAmoritizacion);
					}
					
					//Crear las personas relacionadas a la factura
					$objEntityRelated								= array();
					$objEntityRelated["customerCreditDocumentID"]	= $customerCreditDocumentID;
					$objEntityRelated["entityID"]					= $objCustomerCreditLine->entityID;
					$objEntityRelated["type"]						= $this->core_web_parameter->getParameter("CXC_PROPIETARIO_DEL_CREDITO",$companyID)->value;
					$objEntityRelated["typeCredit"]					= 401; /*comercial*/
					$objEntityRelated["statusCredit"]				= 429; /*activo*/
					$objEntityRelated["typeGarantia"]				= 444; /*pagare*/
					$objEntityRelated["typeRecuperation"]			= 450; /*recuperacion normal */
					$objEntityRelated["ratioDesembolso"]			= 1;
					$objEntityRelated["ratioBalance"]				= 1;
					$objEntityRelated["ratioBalanceExpired"]		= 1;
					$objEntityRelated["ratioShare"]					= 1;
					$objEntityRelated["isActive"]					= 1;
					$this->core_web_auditoria->setAuditCreated($objEntityRelated,$dataSession,$this->request);			
					$ccEntityID 	= $this->Customer_Credit_Document_Endity_Related_Model->insert_app_posme($objEntityRelated);
					
					//Calculo del Total en Dolares
					$amountTotalDolares	= $objTM["exchangeRate"] > 1 ? 
								/*factura en cordoba*/ ($amountTotal * round($objTM["exchangeRate"],4)) : 
								/*factura en dolares*/ ($amountTotal * 1 );
					
					
					//disminuir el balance de general					
					$objCustomerCredit 					= $this->Customer_Credit_Model->get_rowByPK($objCustomerCreditLine->companyID,$objCustomerCreditLine->branchID,$objCustomerCreditLine->entityID);
					$objCustomerCreditNew["balanceDol"]	= $objCustomerCredit->balanceDol - $amountTotalDolares;
					$this->Customer_Credit_Model->update_app_posme($objCustomerCreditLine->companyID,$objCustomerCreditLine->branchID,$objCustomerCreditLine->entityID,$objCustomerCreditNew);
					
					//disminuir el balance de linea
					if($objCustomerCreditLine->currencyID == $objCurrencyCordoba->currencyID)
						$objCustomerCreditLineNew["balance"]	= $objCustomerCreditLine->balance - $amountTotal;
					else
						$objCustomerCreditLineNew["balance"]	= $objCustomerCreditLine->balance - $amountTotalDolares;
						
					//actualizar balance de la linea de credito del cliente
					$this->Customer_Credit_Line_Model->update_app_posme($objCustomerCreditLine->customerCreditLineID,$objCustomerCreditLineNew);
					
				}
				
				
			}
			
			if($db->transStatus() !== false){
				$db->transCommit();										
			}
			else{
				$db->transRollback();						
				throw new \Exception($this->db->_error_message());
				
			}
			
			
			
			return $this->response->setJSON(array(
				'error'   => false,
				'message' => $transactionNumber,
				'transactionMasterID' => $transactionMasterID,
				'transactionNumber' => $transactionNumber,
				'companyID' => $companyID,
				'transactionID' => $transactionID
			));//--finjson	
			
		}
		catch(\Exception $ex){
			
			
			return $this->response->setJSON(array(
				'error'   => true,
				'message' => $ex->getLine()." ".$ex->getMessage()
			));//--finjson	
		}		
	}
	
	function save($mode=""){		
		 $mode = helper_SegmentsByIndex($this->uri->getSegments(),1,$mode);	
		 
		 try{ 
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			
			//Validar Formulario									
			
	
			
			//reglas			
			$this->validation->setRule("txtStatusID","Estado","required|min_length[1]");
			$this->validation->setRule("txtDate","Fecha","required");
			
			//echo print_r($this->validation->withRequest($this->request)->run(),true);
			//echo print_r($this->validation->getErrors(),true);
			//echo print_r($this->validation->getError("txtStatusID"),true);
			
			 //Validar Formulario
			if(!$this->validation->withRequest($this->request)->run()){				
				
				$stringValidation = $this->core_web_tools->formatMessageError($this->validation->getErrors());	
				$this->core_web_notification->set_message(true,$stringValidation);
				$this->response->redirect(base_url()."/".'app_invoice_billing/add');
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
				$this->response->redirect(base_url()."/".'app_invoice_billing/add');
				exit;
			}
			
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}		
			
	}
	
	function add(){ 
	
		try{ 
			
			
			//$this->cachePage( TIME_CACHE_APP  );
			
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
			
			
			
			$companyID 							= $dataSession["user"]->companyID;
			$branchID 							= $dataSession["user"]->branchID;
			$roleID 							= $dataSession["role"]->roleID;
			$userID								= $dataSession["user"]->userID;
			$transactionMasterIDToPrinter		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterIDToPrinter");//--finuri	
			$codigoMesero						= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"codigoMesero");//--finuri				
			
			
			//Obtener el componente de Item
			$objComponentCustomer	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_customer");
			if(!$objComponentCustomer)
			throw new \Exception("EL COMPONENTE 'tb_customer' NO EXISTE...");
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
		
			$objComponentTransactionBilling	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_transaction_master_billing");
			if(!$objComponentTransactionBilling)
			throw new \Exception("EL COMPONENTE 'tb_transaction_master_billing' NO EXISTE...");
			
			
			//Obtener Tasa de Cambio			
			$companyID 							= $dataSession["user"]->companyID;
			$branchID 							= $dataSession["user"]->branchID;
			$roleID 							= $dataSession["role"]->roleID;
			$transactionID 						= $this->core_web_transaction->getTransactionID($dataSession["user"]->companyID,"tb_transaction_master_billing",0);
			$objCurrency						= $this->core_web_currency->getCurrencyDefault($companyID);
			$targetCurrency						= $this->core_web_currency->getCurrencyExternal($companyID);			
			$customerDefault					= $this->core_web_parameter->getParameter("INVOICE_BILLING_CLIENTDEFAULT",$companyID);
			$objListPrice 						= $this->List_Price_Model->getListPriceToApply($companyID);
			$objListCurrency					= $this->Company_Currency_Model->getByCompany($companyID);
			
			
			if(!$objListPrice)
			throw new \Exception("NO EXISTE UNA LISTA DE PRECIO PARA SER APLICADA");
		
			
			
			$objParameterInvoiceTypeEmployer		= $this->core_web_parameter->getParameter("INVOICE_TYPE_EMPLOYEER",$companyID);
			$objParameterInvoiceTypeEmployer		= $objParameterInvoiceTypeEmployer->value;
			
			$objParameterInvoiceAutoApply			= $this->core_web_parameter->getParameter("INVOICE_AUTOAPPLY_CASH",$companyID);
			$objParameterInvoiceAutoApply			= $objParameterInvoiceAutoApply->value;
			$objParameterTypePreiceDefault			= $this->core_web_parameter->getParameter("INVOICE_DEFAULT_TYPE_PRICE",$companyID);
			$objParameterTypePreiceDefault			= $objParameterTypePreiceDefault->value;
			$objParameterTipoWarehouseDespacho		= $this->core_web_parameter->getParameter("INVOICE_TYPE_WAREHOUSE_DESPACHO",$companyID);
			$objParameterTipoWarehouseDespacho		= $objParameterTipoWarehouseDespacho->value;
			$objParameterImprimirPorCadaFactura		= $this->core_web_parameter->getParameter("INVOICE_PRINT_BY_INVOICE",$companyID);
			$objParameterImprimirPorCadaFactura		= $objParameterImprimirPorCadaFactura->value;
			$objParameterScanerProducto				= $this->core_web_parameter->getParameter("INVOICE_SHOW_POPUP_FIND_PRODUCTO_NOT_SCANER",$companyID);
			$objParameterScanerProducto				= $objParameterScanerProducto->value;
			$objParameterCantidadItemPoup			= $this->core_web_parameter->getParameter("INVOICE_CANTIDAD_ITEM",$companyID);
			$objParameterCantidadItemPoup			= $objParameterCantidadItemPoup->value;
			$objParameterHidenFiledItemNumber		= $this->core_web_parameter->getParameter("INVOICE_HIDEN_ITEMNUMBER_IN_POPUP",$companyID);
			$objParameterHidenFiledItemNumber		= $objParameterHidenFiledItemNumber->value;			
			$objParameterAmortizationDuranteFactura	= $this->core_web_parameter->getParameter("INVOICE_PARAMTER_AMORITZATION_DURAN_INVOICE",$companyID);
			$objParameterAmortizationDuranteFactura	= $objParameterAmortizationDuranteFactura->value;
			$objParameterDirect 					= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_PRINTER_DIRECT",$companyID);
			
			$objParameterAlturaDelModalDeSeleccionProducto	= $this->core_web_parameter->getParameter("INVOICE_ALTO_MODAL_DE_SELECCION_DE_PRODUCTO_AL_FACTURAR",$companyID);
			$objParameterAlturaDelModalDeSeleccionProducto	= $objParameterAlturaDelModalDeSeleccionProducto->value;			
			
			$objParameterScrollDelModalDeSeleccionProducto	= $this->core_web_parameter->getParameter("INVOICE_SCROLL_DE_MODAL_EN_SELECCION_DE_PRODUTO_AL_FACTURAR",$companyID);
			$objParameterScrollDelModalDeSeleccionProducto	= $objParameterScrollDelModalDeSeleccionProducto->value;			
			
			
			//Obtener la lista de estados
			if($objParameterInvoiceAutoApply == "true"){
				$dataView["objListWorkflowStage"]	= $this->core_web_workflow->getWorkflowStageApplyFirst("tb_transaction_master_billing","statusID",$companyID,$branchID,$roleID);
			}
			else{
				$dataView["objListWorkflowStage"]	= $this->core_web_workflow->getWorkflowInitStage("tb_transaction_master_billing","statusID",$companyID,$branchID,$roleID);
			}
			
			//Obtener Lista de mesas configuradas							
			$objPublicCatalogId							= 0;
			$objPubliCatalogMesasConfig 				= $this->Public_Catalog_Model->asObject()
																	->where("systemName","tb_transaction_master_billing.mesas_x_meseros")
																	->where("isActive",1)
																	->where("flavorID",$dataSession["company"]->flavorID)
																	->find();
			
			if($codigoMesero != "none" && !$objPubliCatalogMesasConfig )
			{
				throw new \Exception("CONFIGURAR EL CATALOGO DE MESAS tb_transaction_master_billing.mesas_x_meseros");
			}
			
			$objPublicCatalogId							= $codigoMesero == "none" ? 0 : $objPubliCatalogMesasConfig[0]->publicCatalogID;
			$objPubliCatalogDetailMesasConfiguradas		= $this->Public_Catalog_Detail_Model->asObject()
																->where("publicCatalogID",$objPublicCatalogId)
																->where( "isActive",1)	
																->where( "name",$codigoMesero)
																->findAll();
			
			
			//Tipo de Factura
			$agent 											= $this->request->getUserAgent();						
			$dataView["isMobile"]							= helper_RequestGetValue($agent->isMobile(),"0");
			$dataView["objComponentTransactionBilling"]		= $objComponentTransactionBilling;
			$dataView["companyID"]							= $dataSession["user"]->companyID;
			$dataView["isAdmin"]							= $dataSession["role"]->isAdmin;
			$dataView["userID"]								= $dataSession["user"]->userID;
			$dataView["userName"]							= $dataSession["user"]->nickname;
			$dataView["useMobile"]							= $dataSession["user"]->useMobile;
			$dataView["roleID"]								= $dataSession["role"]->roleID;
			$dataView["roleName"]							= $dataSession["role"]->name;
			$dataView["branchID"]							= $dataSession["branch"]->branchID;
			$dataView["branchName"]							= $dataSession["branch"]->name;
			$dataView["company"]							= $dataSession["company"];
			$dataView["exchangeRate"]						= $this->core_web_currency->getRatio($companyID,date("Y-m-d"),1,$targetCurrency->currencyID,$objCurrency->currencyID);			
			$dataView["listCurrency"]						= $objListCurrency;
			$dataView["objCurrency"]						= $objCurrency;
			$dataView["objListPrice"]						= $objListPrice;
			$dataView["objListEmployee"]					= $this->Employee_Model->get_rowByBranchIDAndType($companyID,$branchID, $objParameterInvoiceTypeEmployer );
			$dataView["objListBank"]						= $this->Bank_Model->getByCompany($companyID);
			$dataView["objComponentItem"]					= $objComponentItem;
			$dataView["objComponentCustomer"]				= $objComponentCustomer;
			$dataView["objCaudal"]							= $this->Transaction_Causal_Model->getCausalByBranch($companyID,$transactionID,$branchID);			
			$dataView["warehouseID"]						= $dataView["objCaudal"][0]->warehouseSourceID;
			$dataView["objListWarehouse"]					= $this->Userwarehouse_Model->getRowByUserIDAndFacturable($companyID,$userID);			
			$dataView["objCustomerDefault"]					= $this->Customer_Model->get_rowByCode($companyID,$customerDefault->value);
			$dataView["objListTypePrice"]					= $this->core_web_catalog->getCatalogAllItem("tb_price","typePriceID",$companyID);
			$dataView["objListZone"]						= $this->core_web_catalog->getCatalogAllItem("tb_transaction_master_info_billing","zoneID",$companyID);			
			$dataView["objListMesa"]						= $this->core_web_catalog->getCatalogAllItem("tb_transaction_master_info_billing","mesaID",$companyID);			
			
			
			//Filtrar la lista de mesas que el mesero tiene permiso
			$listMesasByMesero = array_map(function($item) {
				return $item->display;
			}, $objPubliCatalogDetailMesasConfiguradas);

			
			$listMesaFiltradas = array_filter($dataView["objListMesa"] , function($item) use ($listMesasByMesero) {
				return in_array($item->name, $listMesasByMesero);
			});

			$dataView["objListMesa"] = $codigoMesero == "none" ? $dataView["objListMesa"]  : $listMesaFiltradas;
			if(!$dataView["objListMesa"])
			throw new \Exception("NO ES POSIBLE CONTINUAR CONFIGURAR CATALOGO MESS");
			
			$dataView["codigoMesero"]						= $codigoMesero;
			$dataView["objListPay"]							= $this->core_web_catalog->getCatalogAllItem("tb_customer_credit_line","periodPay",$companyID);
			$dataView["listProvider"]						= $this->Provider_Model->get_rowByCompany($companyID);
			$dataView["objListaPermisos"]					= $dataSession["menuHiddenPopup"];
			$dataView["objParameterTypePreiceDefault"] 		= $objParameterTypePreiceDefault;
			$dataView["objParameterTipoWarehouseDespacho"] 	= $objParameterTipoWarehouseDespacho;
			$dataView["objParameterInvoiceAutoApply"] 		= $objParameterInvoiceAutoApply;
			$dataView["objParameterImprimirPorCadaFactura"] = $objParameterImprimirPorCadaFactura;
			$dataView["objParameterScanerProducto"] 		= $objParameterScanerProducto;
			$dataView["objParameterCantidadItemPoup"] 			= $objParameterCantidadItemPoup;
			$dataView["objParameterHidenFiledItemNumber"] 		= $objParameterHidenFiledItemNumber;
			$dataView["objParameterAmortizationDuranteFactura"] = $objParameterAmortizationDuranteFactura;
			$dataView["objParameterAlturaDelModalDeSeleccionProducto"] 	= $objParameterAlturaDelModalDeSeleccionProducto;
			$dataView["objParameterScrollDelModalDeSeleccionProducto"] 	= $objParameterScrollDelModalDeSeleccionProducto;
			$dataView["objParameterCXC_PLAZO_DEFAULT"]												= $this->core_web_parameter->getParameterValue("CXC_PLAZO_DEFAULT",$companyID);
			$dataView["objParameterCXC_FRECUENCIA_PAY_DEFAULT"]										= $this->core_web_parameter->getParameterValue("CXC_FRECUENCIA_PAY_DEFAULT",$companyID);
			$dataView["objParameterCustomPopupFacturacion"]											= $this->core_web_parameter->getParameterValue("CORE_VIEW_CUSTOM_PANTALLA_DE_FACTURACION_POPUP_SELECCION_PRODUCTO_FORMA_MOSTRAR",$companyID);
			$dataView["objParameterINVOICE_BILLING_APPLY_TYPE_PRICE_ON_DAY_POR_MAYOR"]				= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_APPLY_TYPE_PRICE_ON_DAY_POR_MAYOR",$companyID);
			$dataView["objParameterINVOICE_BILLING_SHOW_COMMAND_BAR"]								= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_SHOW_COMMAND_BAR",$companyID);
			$dataView["objParameterINVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT_BAR"]				= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT_BAR",$companyID);
			$dataView["objParameterINVOICE_BILLING_PRINTER_DIRECT_URL_BAR"]							= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_PRINTER_DIRECT_URL_BAR",$companyID);
			$dataView["objParameterobjParameterINVOICE_BILLING_PRINTER_URL_BAR"]					= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_PRINTER_URL_BAR",$companyID);			
			$dataView["objListParameterJavaScript"]													= $this->core_web_parameter->getParameterAllToJavaScript($companyID);			
			$dataView["objParameterINVOICE_BILLING_EMPLOYEE_DEFAULT"]								= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_EMPLOYEE_DEFAULT",$companyID);
			$dataView["objParameterINVOICE_BILLING_SELECTITEM"]										= $this->core_web_parameter->getParameterValue("INVOICE_BILLING_SELECTITEM",$companyID);
			$dataView["objParameterACCOUNTING_CURRENCY_NAME_IN_BILLING"]							= $this->core_web_parameter->getParameterValue("ACCOUNTING_CURRENCY_NAME_IN_BILLING",$companyID);
			
			$objParameterUrlServidorDeImpresion							= $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_SERVER_PATH",$companyID);
			$objParameterUrlServidorDeImpresion							= $objParameterUrlServidorDeImpresion->value;
			$dataView["objParameterUrlServidorDeImpresion"] 			= $objParameterUrlServidorDeImpresion;
			
			$objParameterImprimirPorCadaFactura							= $this->core_web_parameter->getParameter("INVOICE_PRINT_BY_INVOICE",$companyID);
			$dataView["objParameterImprimirPorCadaFactura"]				= $objParameterImprimirPorCadaFactura->value;
			
			$objParameterInvoiceBillingPrinterDirect				= $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT",$companyID);
			$dataView["objParameterInvoiceBillingPrinterDirect"]	= $objParameterInvoiceBillingPrinterDirect->value;
			$objParameterInvoiceBillingPrinterDirectUrl					= $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_URL",$companyID);
			$dataView["objParameterInvoiceBillingPrinterDirectUrl"]		= $objParameterInvoiceBillingPrinterDirectUrl->value;
			
			
			$objParameterInvoiceBillingQuantityZero					= $this->core_web_parameter->getParameter("INVOICE_BILLING_QUANTITY_ZERO",$companyID);
			$dataView["objParameterInvoiceBillingQuantityZero"]		= $objParameterInvoiceBillingQuantityZero->value;
			$objParameterRegresarAListaDespuesDeGuardar					= $this->core_web_parameter->getParameter("INVOICE_BILLING_SAVE_AFTER_TO_LIST",$companyID);
			$dataView["objParameterRegresarAListaDespuesDeGuardar"]		= $objParameterRegresarAListaDespuesDeGuardar->value;
			
			$objParameterMostrarImagenEnSeleccion					= $this->core_web_parameter->getParameter("INVOICE_BILLING_SHOW_IMAGE_IN_DETAIL_SELECTION",$companyID);
			$objParameterMostrarImagenEnSeleccion					= $objParameterMostrarImagenEnSeleccion->value;	
			$dataView["objParameterMostrarImagenEnSeleccion"] 		= $objParameterMostrarImagenEnSeleccion;
			
			$objParameterPantallaParaFacturar				= $this->core_web_parameter->getParameter("INVOICE_PANTALLA_FACTURACION",$this->session->get('user')->companyID);
			$objParameterPantallaParaFacturar				= $objParameterPantallaParaFacturar->value;
			$dataView["objParameterPantallaParaFacturar"] 	= $objParameterPantallaParaFacturar;
			
			$objParameterEsResrarante				= $this->core_web_parameter->getParameter("INVOICE_BILLING_IS_RESTAURANT",$this->session->get('user')->companyID);
			$objParameterEsResrarante				= $objParameterEsResrarante->value;
			$dataView["objParameterEsResrarante"] 	= $objParameterEsResrarante;
			
						
			if(!$dataView["objCustomerDefault"])
			throw new \Exception("NO EXISTE EL CLIENTE POR DEFECTO");
			
			$dataView["objNaturalDefault"]		= $this->Natural_Model->get_rowByPK($companyID,$dataView["objCustomerDefault"]->branchID,$dataView["objCustomerDefault"]->entityID);
			$dataView["objLegalDefault"]		= $this->Legal_Model->get_rowByPK($companyID,$dataView["objCustomerDefault"]->branchID,$dataView["objCustomerDefault"]->entityID);
			$dataView["objEmployeeNatural"]		= $this->Natural_Model->get_rowByPK($companyID,$dataSession["user"]->branchID,$dataSession["user"]->employeeID);
			
			
			//Obtener la linea de credito del cliente por defecto
			$objCurrencyDolares						= $this->core_web_currency->getCurrencyExternal($companyID);
			$objCurrencyCordoba						= $this->core_web_currency->getCurrencyDefault($companyID);			
			$parameterCausalTypeCredit 				= $this->core_web_parameter->getParameter("INVOICE_BILLING_CREDIT",$companyID);			
			$objCustomerCreditAmoritizationAll		= $this->Customer_Credit_Amortization_Model->get_rowByCustomerID($dataView["objCustomerDefault"]->entityID);
			$objListCustomerCreditLine 				= $this->Customer_Credit_Line_Model->get_rowByEntityBalanceMayorCero($companyID,$dataSession["user"]->branchID,$dataView["objCustomerDefault"]->entityID);			
			
			
			$dataView["objListCustomerCreditLine"]	  		=  $objListCustomerCreditLine;				
			$dataView["objCausalTypeCredit"]				=  $parameterCausalTypeCredit;
			$dataView["objCurrencyDolares"] 				=  $objCurrencyDolares;
			$dataView["objCurrencyCordoba"] 				=  $objCurrencyCordoba;
			$dataView["objCustomerCreditAmoritizationAll"] 	=  $objCustomerCreditAmoritizationAll;
			
			//Obtener los datos de impresion				
			if($transactionMasterIDToPrinter > 0 && $objParameterDirect  == "true")
			{	
				$dataPostPrinter["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterIDToPrinter);
				$dataPostPrinter["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterIDToPrinter);
				$dataPostPrinter["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterIDToPrinter);
				$dataPostPrinter["objTransactionMasterDetailWarehouse"]		= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterIDToPrinter);
				$dataPostPrinter["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterIDToPrinter,$objComponentItem->componentID);
				$dataPostPrinter["objComponentCompany"]				= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
				$dataPostPrinter["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
				$dataPostPrinter["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
				$dataPostPrinter["objCompany"] 						= $this->Company_Model->get_rowByPK($companyID);			
				$dataPostPrinter["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataPostPrinter["objTransactionMaster"]->createdAt,$dataPostPrinter["objTransactionMaster"]->createdBy);
				$dataPostPrinter["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
				$dataPostPrinter["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataPostPrinter["objTransactionMaster"]->branchID);
				$dataPostPrinter["objTipo"]							= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataPostPrinter["objTransactionMaster"]->transactionID,$dataPostPrinter["objTransactionMaster"]->transactionCausalID);
				$dataPostPrinter["objCustumer"]						= $this->Customer_Model->get_rowByEntity($companyID,$dataPostPrinter["objTransactionMaster"]->entityID);
				$dataPostPrinter["objCurrency"]						= $this->Currency_Model->get_rowByPK($dataPostPrinter["objTransactionMaster"]->currencyID);
				$dataPostPrinter["prefixCurrency"]					= $dataPostPrinter["objCurrency"]->simbol." ";
				$dataPostPrinter["cedulaCliente"] 					= $dataPostPrinter["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataPostPrinter["objCustumer"]->customerNumber :  $dataPostPrinter["objTransactionMasterInfo"]->referenceClientIdentifier;
				$dataPostPrinter["nombreCliente"] 					= $dataPostPrinter["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataPostPrinter["objCustumer"]->firstName : $dataPostPrinter["objTransactionMasterInfo"]->referenceClientName ;
				$dataPostPrinter["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataPostPrinter["objTransactionMaster"]->statusID);
				$serializedDataPostPrinter 							= serialize($dataPostPrinter);
				$serializedDataPostPrinter 							= base64_encode($serializedDataPostPrinter);
				$dataView["dataPrinterLocal"]						= $serializedDataPostPrinter;
				$dataView["dataPrinterLocalTransactionMasterID"]	= $dataPostPrinter["objTransactionMaster"]->transactionMasterID;
				$dataView["dataPrinterLocalTransactionID"]			= $dataPostPrinter["objTransactionMaster"]->transactionID;
				$dataView["dataPrinterLocalCompanyID"]				= $dataPostPrinter["objTransactionMaster"]->companyID;
			
			}
			else 
			{
				$dataView["dataPrinterLocal"]						= "";
				$dataView["dataPrinterLocalTransactionMasterID"]	= 0;
				$dataView["dataPrinterLocalTransactionID"]			= 0;
				$dataView["dataPrinterLocalCompanyID"]				= 0;
			}
			
			
			//Renderizar Resultado 
			$dataSession["notification"]	= $this->core_web_error->get_error($dataSession["user"]->userID);
			$dataSession["message"]			= $this->core_web_notification->get_message();
			$dataSession["head"]			= /*--inicio view*/ view('app_invoice_billing/news_head',$dataView);//--finview
			$dataSession["body"]			= /*--inicio view*/ view('app_invoice_billing/news_body',$dataView);//--finview
			$dataSession["script"]			= /*--inicio view*/ view('app_invoice_billing/news_script',$dataView);//--finview
			$dataSession["footer"]			= "";
			
			//return view("core_masterpage/default_masterpage",$dataSession);//--finview-r
			return view("core_masterpage/default_popup",$dataSession);//--finview-r	
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}	
			
    }
	function index($dataViewID = null,$fecha = null){	
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
			$objComponent		= $this->core_web_tools->getComponentIDBy_ComponentName("tb_transaction_master_billing");
			if(!$objComponent)
			throw new \Exception("00409 EL COMPONENTE 'tb_transaction_master_billing' NO EXISTE...");
			
			//$this->dompdf->loadHTML("<h1>hola mundo</h1>");
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->render();
			//$this->dompdf->stream();
			$objHoy 	= \DateTime::createFromFormat('Y-m-d',date("Y-m-d"));
			$objFecha 	= \DateTime::createFromFormat('Y-m-d',date("Y-m-d"));
			$fecha		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"fecha");//--finuri
			$fecha		= !$fecha ? $objFecha->format("Y-m-d"): $fecha;
			$objFecha 	= \DateTime::createFromFormat('Y-m-d',$fecha);  				
			
			if( $objFecha <  $objHoy )
			{
				$this->cachePage( TIME_CACHE_APP );	
			}
			
			
			$objParameterShowPreview		= $this->core_web_parameter->getParameter("INVOICE_SHOW_PREVIEW_INLIST",$this->session->get('user')->companyID);
			$objParameterShowPreview		= $objParameterShowPreview->value;
				
			//Vista por defecto 
			if($dataViewID == null){				
				$targetComponentID			= $this->session->get('company')->flavorID;
				
				$parameter["{companyID}"]	= $this->session->get('user')->companyID;
				$parameter["{fecha}"]		= $fecha;
				$dataViewData				= $this->core_web_view->getViewDefault($this->session->get('user'),$objComponent->componentID,CALLERID_LIST,$targetComponentID,$resultPermission,$parameter);			
				
				
				if(!$dataViewData){
					
					$targetComponentID			= 0;	
					$parameter["{companyID}"]	= $this->session->get('user')->companyID;
					$parameter["{fecha}"]		= $fecha;
					$dataViewData				= $this->core_web_view->getViewDefault($this->session->get('user'),$objComponent->componentID,CALLERID_LIST,$targetComponentID,$resultPermission,$parameter);				
				}
				
				
				if($dataSession["user"]->useMobile == 1)
				{					
					//$dataViewRender			= $this->core_web_view->renderGreedMobile($dataViewData,'ListView',"fnTableSelectedRow");
					$dataViewRender				= $this->core_web_view->renderGreedWithHtmlInFildMobile($dataViewData,'ListView',"fnTableSelectedRow");
				}
				else
				{
					//$dataViewRender			= $this->core_web_view->renderGreed($dataViewData,'ListView',"fnTableSelectedRow");
					$dataViewRender				= $this->core_web_view->renderGreedWithHtmlInFild($dataViewData,'ListView',"fnTableSelectedRow");
				}
			}
			//Otra vista
			else{									
				$parameter["{companyID}"]	= $this->session->get('user')->companyID;
				$dataViewData				= $this->core_web_view->getViewBy_DataViewID($this->session->get('user'),$objComponent->componentID,$dataViewID,CALLERID_LIST,$resultPermission,$parameter); 			
				
				if($dataSession["user"]->useMobile == 1)
				{
					$dataViewRender				= $this->core_web_view->renderGreedMobile($dataViewData,'ListView',"fnTableSelectedRow");
				}
				else 
				{
					$dataViewRender				= $this->core_web_view->renderGreed($dataViewData,'ListView',"fnTableSelectedRow");
				}
			} 
			 
			 //Factura prerender en la lista principal
			$objParameterPantallaParaFacturar		= $this->core_web_parameter->getParameter("INVOICE_PANTALLA_FACTURACION",$this->session->get('user')->companyID);
			$objParameterPantallaParaFacturar		= $objParameterPantallaParaFacturar->value;
			$urlPrinterDocument						= $this->core_web_parameter->getParameter("INVOICE_URL_PRINTER",$this->session->get('user')->companyID);
			$urlPrinterDocumentDirect				= $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_URL",$this->session->get('user')->companyID);
			$iframePreviewReport					= "";
			
			if($objParameterShowPreview == "true" &&  $dataViewData)
			{			
				foreach($dataViewData["view_data"] as $key => $value)
				{					
				    $pathScaner 					= "./resource/file_company/"."company_2/component_48/component_item_".$value["transactionMasterID"];					
					$value["exiteFileInFolder"] 	= false;
					$value["fileName"] 				= false;
					$value["urlPrinterDocument"] 		= $urlPrinterDocument->value;
					$value["urlPrinterDocumentDirect"] 	= $urlPrinterDocumentDirect->value;
					
					if(file_exists($pathScaner))
					{
						$value["arrayFileInFolder"] = scandir ($pathScaner, SCANDIR_SORT_DESCENDING );						
						if($value["Estado"] == "APLICADA" && $value["arrayFileInFolder"] )
						{
							if(count($value["arrayFileInFolder"]) > 2)
							{
								$value["exiteFileInFolder"]	 	= true;						
								$value["fileName"]				= $value["arrayFileInFolder"][0];
							}						
						}					
					}					
					
					$iframePreviewReport = $iframePreviewReport.view('core_view_fragmentos/app_invoice_billing_index_iframe',$value);
				}
			}
			
			//Variable para validar si es un mesero
			$esMesero 					= false;
			$esMesero 					= $this->core_web_permission->urlPermited("es_mesero","index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);			
			
			$esMesero					= !$esMesero ? "0" : $esMesero;
			$esMesero					= $dataSession["role"]->isAdmin ? "0" : $esMesero;
			$dataViewJava["esMesero"]	= $esMesero;
			
			//Renderizar Resultado
			$dataViewJava["objParameterPantallaParaFacturar"]	= $objParameterPantallaParaFacturar;
			$dataViewJava["objParameterShowPreview"]			= $objParameterShowPreview;
			$dataViewJava["useMobile"]							= $dataSession["user"]->useMobile;
			
			$dataViewHeader["company"]							= $dataSession["company"];
			$dataViewHeader["objFecha"] 						= $objFecha;
			$dataViewHeader["objParameterShowPreview"] 			= $objParameterShowPreview;
			
			$dataViewFooter["objFecha"] 						= $objFecha;
			$dataViewFooter["objParameterShowPreview"] 			= $objParameterShowPreview;
			$dataViewFooter["iframePreviewReport"]				= $iframePreviewReport;
			
			
			$dataSession["useMobile"]							= $dataSession["user"]->useMobile;
			$dataSession["notification"]						= $this->core_web_error->get_error($dataSession["user"]->userID);
			$dataSession["message"]								= $this->core_web_notification->get_message();
			$dataSession["head"]								= /*--inicio view*/ view('app_invoice_billing/list_head',$dataViewHeader);//--finview
			$dataSession["footer"]								= /*--inicio view*/ view('app_invoice_billing/list_footer',$dataViewFooter);//--finview
			$dataSession["body"]								= $dataViewRender; 			
			$dataSession["script"]								= /*--inicio view*/ view('app_invoice_billing/list_script',$dataViewJava);//--finview
			$dataSession["script"]			                    = $dataSession["script"].$this->core_web_javascript->createVar("componentID",$objComponent->componentID);   
			return view("core_masterpage/default_masterpage",$dataSession);//--finview-r	
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);    
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}	
	function searchTransactionMaster(){
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
			
			//Load Modelos
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			  
			
			//Nuevo Registro
			$transactionNumber 	= /*inicio get post*/ $this->request->getPost("transactionNumber");
			
			
			if(!$transactionNumber){
					throw new \Exception(NOT_PARAMETER);			
			} 			
			$objTM 	= $this->Transaction_Master_Model->get_rowByTransactionNumber($dataSession["user"]->companyID,$transactionNumber);	
			
			if(!$objTM)
			throw new \Exception("NO SE ENCONTRO EL DOCUMENTO");	
			
			
			
			return $this->response->setJSON(array(
				'error'   				=> false,
				'message' 				=> SUCCESS,
				'companyID' 			=> $objTM->companyID,
				'transactionID'			=> $objTM->transactionID,
				'transactionMasterID'	=> $objTM->transactionMasterID
			));//--finjson
			
		}
		catch(\Exception $ex){
			
			return $this->response->setJSON(array(
				'error'   => true,
				'message' => $ex->getLine()." ".$ex->getMessage()
			));//--finjson
		}
	}
	
	
	function viewPrinterOpen(){
		try{			
			
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",APP_COMPANY);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinterOpen();
			
		}
		catch(\Exception $ex){
		    
			
		    //$data["session"]   = $dataSession;
		    //$data["exception"] = $ex;
		    //$data["urlLogin"]  = base_url();
		    //$data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    //$data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    //$resultView        = view("core_template/email_error_general",$data);
		    //return $resultView;
			
			exit($ex->getMessage());
		}	
	}
	
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectFactura80mm(){
		try{
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
			
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			$fromServer				= /*inicio get post*/ $this->request->getPost("fromServer");
			
			if($fromServer == "")
			{
				
				$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
				$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
				$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
				$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
				$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
				
				
				$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
				$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
				$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
				$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
				$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
				$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
				$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
				$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
				$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
				$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
				$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
				$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
				$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
				$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			}
			else 
			{
				// Decodificar la cadena Base64
				$serializedData = base64_decode($fromServer);
			
				// Deserializar la cadena a un array
				$serializedData = unserialize($serializedData);			
			
				$dataView	= $serializedData;
			}
			
			//log_message("error",print_r($dataView,true));
			
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mm($dataView);
			log_message("error","impresion elaborada");
			
		}
		catch(\Exception $ex){
		    log_message("error",print_r($ex->getMessage(),true));
			
		    //$data["session"]   = $dataSession;
		    //$data["exception"] = $ex;
		    //$data["urlLogin"]  = base_url();
		    //$data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    //$data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    //$resultView        = view("core_template/email_error_general",$data);
		    //return $resultView;
			
			exit($ex->getMessage());
		}	
	}
	
	function viewPrinterDirectFactura80mmPizzaLaus(){
		try{
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
			
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			$fromServer				= /*inicio get post*/ $this->request->getPost("fromServer");
			
			if($fromServer == "")
			{
				
				$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
				$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
				$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
				$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
				$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
				
				
				$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
				$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
				$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
				$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
				$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
				$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
				$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
				$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
				$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
				$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
				$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
				$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
				$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
				$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			}
			else 
			{
				// Decodificar la cadena Base64
				$serializedData = base64_decode($fromServer);
			
				// Deserializar la cadena a un array
				$serializedData = unserialize($serializedData);			
			
				$dataView	= $serializedData;
			}
			
			//log_message("error",print_r($dataView,true));
			
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mmPizzaLaus($dataView);
			log_message("error","impresion elaborada");
			
		}
		catch(\Exception $ex){
		    log_message("error",print_r($ex->getMessage(),true));
			
		    //$data["session"]   = $dataSession;
		    //$data["exception"] = $ex;
		    //$data["urlLogin"]  = base_url();
		    //$data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    //$data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    //$resultView        = view("core_template/email_error_general",$data);
		    //return $resultView;
			
			exit($ex->getMessage());
		}	
	}
	
	function viewPrinterDirectFactura80mmBlueMoon(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objEmployerNaturales"]			= $this->Natural_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID,$dataView["objTransactionMaster"]->entityIDSecondary);
			$dataView["objMesa"]						= $this->Catalog_Item_Model->get_rowByCatalogItemID($dataView["objTransactionMasterInfo"]->mesaID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mmBlueMoon($dataView);
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mmBlueMoon($dataView);
			
			
		}
		catch(\Exception $ex){
		    
			
		    //$data["session"]   = $dataSession;
		    //$data["exception"] = $ex;
		    //$data["urlLogin"]  = base_url();
		    //$data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    //$data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    //$resultView        = view("core_template/email_error_general",$data);
		    //return $resultView;
			
			exit($ex->getMessage());
		}	
	}
	
	
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectFactura80mmPuraVida(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mmPuraVida($dataView);
			
		}
		catch(\Exception $ex){
		    
			
		    //$data["session"]   = $dataSession;
		    //$data["exception"] = $ex;
		    //$data["urlLogin"]  = base_url();
		    //$data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    //$data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    //$resultView        = view("core_template/email_error_general",$data);
		    //return $resultView;
			
			exit($ex->getMessage());
		}	
	}
	
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectFactura80mmRustikChillGrill(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mmRustikChillGrill($dataView);
			
		}
		catch(\Exception $ex){
		    
			
		    //$data["session"]   = $dataSession;
		    //$data["exception"] = $ex;
		    //$data["urlLogin"]  = base_url();
		    //$data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    //$data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    //$resultView        = view("core_template/email_error_general",$data);
		    //return $resultView;
			
			exit($ex->getMessage());
		}	
	}
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectFactura80mmComidaChinaMijo(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			$dataNew 					= NULL;
			$dataNew["printerQuantity"] = $dataView["objTransactionMaster"]->printerQuantity + 1;
			$this->Transaction_Master_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$dataNew);
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mmComidaChinaMijoFactura($dataView);
			
		}
		catch(\Exception $ex){
		    
			
		    //$data["session"]   = $dataSession;
		    //$data["exception"] = $ex;
		    //$data["urlLogin"]  = base_url();
		    //$data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    //$data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    //$resultView        = view("core_template/email_error_general",$data);
		    //return $resultView;
			
			exit($ex->getMessage());
		}	
	}
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectFactura80mmComidaAudioElPipe(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			$dataNew 					= NULL;
			$dataNew["printerQuantity"] = $dataView["objTransactionMaster"]->printerQuantity + 1;
			$this->Transaction_Master_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$dataNew);
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mmComidaAudioElPipe($dataView);
			
		}
		catch(\Exception $ex){
		    
			
		    //$data["session"]   = $dataSession;
		    //$data["exception"] = $ex;
		    //$data["urlLogin"]  = base_url();
		    //$data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    //$data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    //$resultView        = view("core_template/email_error_general",$data);
		    //return $resultView;
			
			exit($ex->getMessage());
		}	
	}
	
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectFactura80mmYahwetFart(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			$dataNew 					= NULL;
			$dataNew["printerQuantity"] = $dataView["objTransactionMaster"]->printerQuantity + 1;
			$this->Transaction_Master_Model->update_app_posme($companyID,$transactionID,$transactionMasterID,$dataNew);
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mmYahwetFart($dataView);
			
		}
		catch(\Exception $ex){
		    
			
		    //$data["session"]   = $dataSession;
		    //$data["exception"] = $ex;
		    //$data["urlLogin"]  = base_url();
		    //$data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    //$data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    //$resultView        = view("core_template/email_error_general",$data);
		    //return $resultView;
			
			exit($ex->getMessage());
		}	
	}
	
	
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectFactura80mmFerreteriaDouglas(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mmFerreteriaDouglas($dataView);
			
		}
		catch(\Exception $ex){
		    
			
		    //$data["session"]   = $dataSession;
		    //$data["exception"] = $ex;
		    //$data["urlLogin"]  = base_url();
		    //$data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    //$data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    //$resultView        = view("core_template/email_error_general",$data);
		    //return $resultView;
			
			exit($ex->getMessage());
		}	
	}
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectFactura58mm(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter58mm($dataView);
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}	
	}
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectFactura58mmChicharronesCarasenos(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter58ChicharronesCarasenos($dataView);
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}	
	}
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectFactura58mmLaTenera(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter58LaTenera($dataView);
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}	
	}
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectBar80mmRustikChillGrill(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			$itemID					= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"itemID");//--finuri	
			$comment				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterComment");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail2"]			= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= array();
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			$dataView["objComentario"]					= $comment;
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT_BAR",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			//Filtrar productos			
			$itemID = explode(",",$itemID);			
			foreach($dataView["objTransactionMasterDetail2"] as $tmd)
			{
				foreach($itemID as $itemIDx)
				{
					if ($itemIDx == $tmd->componentItemID)
					{
						array_push($dataView["objTransactionMasterDetail"],$tmd);
					}
				}				
			}	
			
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mmCommandaCocina($dataView);
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}	
	}
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectCocina80mmRustikChillGrill(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			$itemID					= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"itemID");//--finuri	
			$comment				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterComment");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail2"]			= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= array();
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			$dataView["objComentario"]					= $comment;
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT_COCINA",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			//Filtrar productos			
			$itemID = explode(",",$itemID);			
			foreach($dataView["objTransactionMasterDetail2"] as $tmd)
			{
				foreach($itemID as $itemIDx)
				{
					if ($itemIDx == $tmd->componentItemID)
					{
						array_push($dataView["objTransactionMasterDetail"],$tmd);
					}
				}				
			}	
			
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mmCommandaCocina($dataView);
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}	
	}
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectCocina80mm(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			$itemID					= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"itemID");//--finuri	
			$comment				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterComment");//--finuri	
			
			$dataView["objComentario"]							= $comment;
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail2"]			= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= array();
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT_COCINA",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			//Filtrar productos			
			$itemID = explode(",",$itemID);			
			foreach($dataView["objTransactionMasterDetail2"] as $tmd)
			{
				foreach($itemID as $itemIDx)
				{
					if ($itemIDx == $tmd->componentItemID)
					{
						array_push($dataView["objTransactionMasterDetail"],$tmd);
					}
				}				
			}	
			
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter80mmCommandaCocina($dataView);
			
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}	
	}
	//facturacino imprimir directamente en impresora, formato de ticket
	function viewPrinterDirectCocina58mm(){
		try{
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			$itemID					= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"itemID");//--finuri	
			
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT_COCINA",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			
			
			$this->core_web_printer_direct->configurationPrinter($objParameterPrinterName);
			$this->core_web_printer_direct->executePrinter58mmCommandaCocina($dataView);
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}	
	}
	
	//facturacion estandar, horizontal tamaña a4
	function viewRegisterFormatoPaginaNormal80mm(){
		try{ 
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCION
			if(APP_NEED_AUTHENTICATION == true){
						$permited = false;
						$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						
						if(!$permited)
						throw new \Exception(NOT_ACCESS_CONTROL);
						
							
						$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"edit",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						if ($resultPermission 	== PERMISSION_NONE)
						throw new \Exception(NOT_ALL_EDIT);		
			}	 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= $dataSession["user"]->companyID;		
			$branchID 					= $dataSession["user"]->branchID;		
			$roleID 					= $dataSession["role"]->roleID;		
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$branchID,$roleID);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
			//Configurar Detalle Header
			$confiDetalleHeader = array();
			$row = array(
				"style"		=>"text-align:left;width:auto",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				
				"style_row_data"		=>"text-align:left;width:auto",
				"colspan_row_data"		=>'3',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>1
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:left;width:50px",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				"style_row_data"		=>"text-align:right;width:auto",
				"colspan_row_data"		=>'2',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:right;width:90px",
				"colspan"	=>'1',
				"prefix"	=>$datView["objCurrency"]->simbol,
				
				"style_row_data"		=>"text-align:right;width:90px",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>$datView["objCurrency"]->simbol,
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
		    
		    $detalle = array();		    
		    $row = array("PRODUCTO", 'CANT', "TOTAL");
		    array_push($detalle,$row);
		    
		    
			foreach($datView["objTMD"] as $detail_){
			    $row = array(
					$detail_->itemName. " ". strtolower($detail_->skuFormatoDescription),  
					sprintf("%01.2f",round($detail_->quantity,2)), 
					sprintf("%01.2f",round($detail_->amount,2))
				);
			    array_push($detalle,$row);
			}
			
			
			//Generar Reporte
			$html = helper_reporte80mmTransactionMaster(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
			    $confiDetalleHeader,
			    $detalle,
			    $objParameterTelefono, /*telefono*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name, /*causal*/
				""
			);
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			
			
			//visualizar
			$this->dompdf->stream("file.pdf", ['Attachment' => !$objParameterShowLinkDownload ]);
			
			//descargar
			//$this->dompdf->stream();
			
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	//facturacion estandar, horizontal tamaña a4
	function viewRegisterFormatoPaginaNormal80mmGlobalPro(){
		try{ 
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCION
			if(APP_NEED_AUTHENTICATION == true){
						$permited = false;
						$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						
						if(!$permited)
						throw new \Exception(NOT_ACCESS_CONTROL);
						
							
						$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"edit",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						if ($resultPermission 	== PERMISSION_NONE)
						throw new \Exception(NOT_ALL_EDIT);		
			}	 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= $dataSession["user"]->companyID;		
			$branchID 					= $dataSession["user"]->branchID;		
			$roleID 					= $dataSession["role"]->roleID;		
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$branchID,$roleID);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["objNaturalEmployer"]			= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objTM"]->entityIDSecondary);
			$datView["objTelefonoEmployer"]			= $this->Entity_Phone_Model->get_rowByEntity($companyID,$datView["objCustumer"]->branchID,$datView["objTM"]->entityIDSecondary);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
			
			//Generar Reporte
			$html = helper_reporteA4TransactionMasterInvoiceGlobalPro(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
				$datView["objTMD"],
			    $objParameterTelefono, /*telefono*/
				$datView["objNaturalEmployer"], /*vendedor*/
				$datView["objTelefonoEmployer"], /*telefono cliente*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name, /*causal*/
				"",
				""
			);
			//echo $html;
			$this->dompdf->loadHTML($html);
			
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			
			
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			$objParameterShowDownloadPreview	= $this->core_web_parameter->getParameter("CORE_SHOW_DOWNLOAD_PREVIEW",$companyID);
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview->value;
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview == "true" ? true : false;
			
			$fileNamePut = "factura_".$transactionMasterID."_".date("dmYhis").".pdf";
			$fileNamePdf = "FAC_".$datView["objTM"]->transactionNumber."_".str_replace(" ","_", $datView["objNatural"]->firstName).".pdf";
			
			$path        	= "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".$fileNamePut;
			$patdir         = "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID;	
			
			if (!file_exists($patdir))
			{
				mkdir($patdir, 0755);
				chmod($patdir, 0755);
			}
			
			
			file_put_contents(
				$path,
				$this->dompdf->output()					
			);						
			
			chmod($path, 644);
			
			if($objParameterShowLinkDownload == "true")
			{			
				echo "<a 
					href='".base_url()."/resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".
					$fileNamePut."'>download factura</a>
				"; 				
			
			}
			else{			
				//visualizar				
				$this->dompdf->stream($fileNamePdf, ['Attachment' => $objParameterShowDownloadPreview ]);
			}
			
			
			
			//descargar
			//$this->dompdf->stream();
			
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
	//facturacion estandar, horizontal tamaña a4
	function viewRegisterFormatoPaginaNormal80mmLaptopStore(){
		try{ 
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCION
			if(APP_NEED_AUTHENTICATION == true){
						$permited = false;
						$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						
						if(!$permited)
						throw new \Exception(NOT_ACCESS_CONTROL);
						
							
						$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"edit",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						if ($resultPermission 	== PERMISSION_NONE)
						throw new \Exception(NOT_ALL_EDIT);		
			}	 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= $dataSession["user"]->companyID;		
			$branchID 					= $dataSession["user"]->branchID;		
			$roleID 					= $dataSession["role"]->roleID;		
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$branchID,$roleID);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["objNaturalEmployer"]			= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objTM"]->entityIDSecondary);
			$datView["objTelefonoEmployer"]			= $this->Entity_Phone_Model->get_rowByEntity($companyID,$datView["objCustumer"]->branchID,$datView["objTM"]->entityIDSecondary);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
			
			//Generar Reporte
			$html = helper_reporteA4TransactionMasterInvoiceLaptopStore(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
				$datView["objTMD"],
			    $objParameterTelefono, /*telefono*/
				$datView["objNaturalEmployer"], /*vendedor*/
				$datView["objTelefonoEmployer"], /*telefono cliente*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name, /*causal*/
				"",
				""
			);
			//echo $html;
			$this->dompdf->loadHTML($html);
			
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			
			
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			$objParameterShowDownloadPreview	= $this->core_web_parameter->getParameter("CORE_SHOW_DOWNLOAD_PREVIEW",$companyID);
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview->value;
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview == "true" ? true : false;
			
			$fileNamePut = "factura_".$transactionMasterID."_".date("dmYhis").".pdf";
			$fileNamePdf = "FAC_".$datView["objTM"]->transactionNumber."_".str_replace(" ","_", $datView["objNatural"]->firstName).".pdf";
			
			$path        	= "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".$fileNamePut;
			$patdir         = "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID;	
			
			if (!file_exists($patdir))
			{
				mkdir($patdir, 0755);
				chmod($patdir, 0755);
			}
			
			
			file_put_contents(
				$path,
				$this->dompdf->output()					
			);						
			
			chmod($path, 644);
			
			if($objParameterShowLinkDownload == "true")
			{			
				echo "<a 
					href='".base_url()."/resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".
					$fileNamePut."'>download factura</a>
				"; 				
			
			}
			else{			
				//visualizar				
				$this->dompdf->stream($fileNamePdf, ['Attachment' => $objParameterShowDownloadPreview ]);
			}
			
			
			
			//descargar
			//$this->dompdf->stream();
			
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
	
	//facturacion estandar, horizontal tamaña a4
	function viewRegisterFormatoPaginaNormal80mmBpn(){
		try{ 
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCION
			if(APP_NEED_AUTHENTICATION == true){
						$permited = false;
						$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						
						if(!$permited)
						throw new \Exception(NOT_ACCESS_CONTROL);
						
							
						$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"edit",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						if ($resultPermission 	== PERMISSION_NONE)
						throw new \Exception(NOT_ALL_EDIT);		
			}	 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= $dataSession["user"]->companyID;		
			$branchID 					= $dataSession["user"]->branchID;		
			$roleID 					= $dataSession["role"]->roleID;		
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$branchID,$roleID);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["objNaturalEmployer"]			= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objTM"]->entityIDSecondary);
			$datView["objTelefonoEmployer"]			= $this->Entity_Phone_Model->get_rowByEntity($companyID,$datView["objCustumer"]->branchID,$datView["objTM"]->entityIDSecondary);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
			
			//Generar Reporte
			$html = helper_reporteA4TransactionMasterInvoiceBpn(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
				$datView["objTMD"],
			    $objParameterTelefono, /*telefono*/
				$datView["objNaturalEmployer"], /*vendedor*/
				$datView["objTelefonoEmployer"], /*telefono cliente*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name, /*causal*/
				"",
				""
			);
			//echo $html;
			$this->dompdf->loadHTML($html);
			
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			
			
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			$objParameterShowDownloadPreview	= $this->core_web_parameter->getParameter("CORE_SHOW_DOWNLOAD_PREVIEW",$companyID);
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview->value;
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview == "true" ? true : false;
			
			$fileNamePut = "factura_".$transactionMasterID."_".date("dmYhis").".pdf";
			$fileNamePdf = "FAC_".$datView["objTM"]->transactionNumber."_".str_replace(" ","_", $datView["objNatural"]->firstName).".pdf";
			
			$path        = "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".$fileNamePut;
				
			file_put_contents(
				$path,
				$this->dompdf->output()					
			);						
			
			chmod($path, 644);
			
			if($objParameterShowLinkDownload == "true")
			{			
				echo "<a 
					href='".base_url()."/resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".
					$fileNamePut."'>download factura</a>
				"; 				
			
			}
			else{			
				//visualizar				
				$this->dompdf->stream($fileNamePdf, ['Attachment' => $objParameterShowDownloadPreview ]);
			}
			
			
			
			//descargar
			//$this->dompdf->stream();
			
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
	
	//Facturacion
	//Reporte = 
	//	viewRegisterFormatoPaginaNormal80mm	+
	//	Field.Vendedor	+
	//  Field.Ruc
	function viewRegisterFormatoPaginaNormal80mmOpcion1(){
		try{ 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= APP_COMPANY;	
			
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objParameterRuc	    = $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$objParameterRuc        = $objParameterRuc->value;
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$datView["objTM"]->branchID,APP_ROL_SUPERADMIN);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$datView["objUser"]						= $this->User_Model->get_rowByPK($companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
		
			//Configurar Detalle Header
			$confiDetalleHeader = array();
			$row = array(
				"style"		=>"text-align:left;width:auto",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				
				"style_row_data"		=>"text-align:left;width:auto",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>1
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:left;width:50px",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				"style_row_data"		=>"text-align:right;width:auto",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			
			$row = array(
				"style"		=>"text-align:right;width:90px",
				"colspan"	=>'1',
				"prefix"	=>$datView["objCurrency"]->simbol,
				
				"style_row_data"		=>"text-align:right;width:90px",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>"",
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			
		    
		    $detalle = array();		    
		    $row = array("CANT", 'PREC', "TOTAL");
		    array_push($detalle,$row);
		    
		    
			foreach($datView["objTMD"] as $detail_){
				$row = array(
					$detail_->itemName. " ". strtolower($detail_->skuFormatoDescription)."-comand-new-row",  				
					"none",
					"none"
				);
			    array_push($detalle,$row);
				
			    $row = array(					
					sprintf("%01.2f",round($detail_->quantity,2)), 					
					sprintf("%01.2f",round($detail_->unitaryPrice,2)),
					sprintf("%01.2f",round($detail_->amount,2))					
				);
			    array_push($detalle,$row);
			}
			
			
			//Generar Reporte
			$html = helper_reporte80mmTransactionMaster(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
			    $confiDetalleHeader,
			    $detalle,
			    $objParameterTelefono, /*telefono*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name /*causal*/,
				$datView["objUser"]->nickname,
			    $objParameterRuc /*ruc*/
			);
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			$objParameterShowDownloadPreview	= $this->core_web_parameter->getParameter("CORE_SHOW_DOWNLOAD_PREVIEW",$companyID);
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview->value;
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview == "true" ? true : false;
			
			$fileNamePut = "factura_".$transactionMasterID."_".date("dmYhis").".pdf";
			$path        = "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".$fileNamePut;
				
			file_put_contents(
				$path,
				$this->dompdf->output()					
			);						
			
			chmod($path, 644);
			
			if($objParameterShowLinkDownload == "true")
			{			
				echo "<a 
					href='".base_url()."/resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".
					$fileNamePut."'>download factura</a>
				"; 				
			
			}
			else{			
				//visualizar				
				$this->dompdf->stream("file.pdf ", ['Attachment' => $objParameterShowDownloadPreview ]);
			}
			
			
			
			
		}
		catch(\Exception $ex){
		    
		    //$data["session"] = $dataSession;
			$data["session"] 	= null;
		    $data["exception"] 	= $ex;
		    $data["urlLogin"]  	= base_url();
		    $data["urlIndex"]  	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        	= view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
	function viewRegisterFormatoPaginaNormal80mmOpcion1Chic(){
		try{ 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= APP_COMPANY;	
			
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objParameterRuc	    = $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$objParameterRuc        = $objParameterRuc->value;
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$datView["objTM"]->branchID,APP_ROL_SUPERADMIN);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$datView["objUser"]						= $this->User_Model->get_rowByPK($companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
		
			//Configurar Detalle Header
			$confiDetalleHeader = array();
			$row = array(
				"style"		=>"text-align:left;width:auto",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				
				"style_row_data"		=>"text-align:left;width:auto",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>1
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:left;width:50px",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				"style_row_data"		=>"text-align:right;width:auto",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			
			$row = array(
				"style"		=>"text-align:right;width:90px",
				"colspan"	=>'1',
				"prefix"	=>$datView["objCurrency"]->simbol,
				
				"style_row_data"		=>"text-align:right;width:90px",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>"",
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			
		    
		    $detalle = array();		    
		    $row = array("CANT", 'PREC', "TOTAL");
		    array_push($detalle,$row);
		    
		    
			foreach($datView["objTMD"] as $detail_){
				$row = array(
					$detail_->itemName. " ". strtolower($detail_->skuFormatoDescription)."-comand-new-row",  				
					"none",
					"none"
				);
			    array_push($detalle,$row);
				
			    $row = array(					
					sprintf("%01.2f",round($detail_->quantity,2)), 					
					sprintf("%01.2f",round($detail_->unitaryPrice,2)),
					sprintf("%01.2f",round($detail_->amount,2))					
				);
			    array_push($detalle,$row);
			}
			
			
			//Generar Reporte
			$html = helper_reporte80mmTransactionMasterBillingChic(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
			    $confiDetalleHeader,
			    $detalle,
			    $objParameterTelefono, /*telefono*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name /*causal*/,
				$datView["objUser"]->nickname,
			    $objParameterRuc /*ruc*/
			);
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			$objParameterShowDownloadPreview	= $this->core_web_parameter->getParameter("CORE_SHOW_DOWNLOAD_PREVIEW",$companyID);
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview->value;
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview == "true" ? true : false;
			
			$fileNamePut = "factura_".$transactionMasterID."_".date("dmYhis").".pdf";
			$path        = "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".$fileNamePut;
				
			file_put_contents(
				$path,
				$this->dompdf->output()					
			);						
			
			chmod($path, 644);
			
			if($objParameterShowLinkDownload == "true")
			{			
				echo "<a 
					href='".base_url()."/resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".
					$fileNamePut."'>download factura</a>
				"; 				
			
			}
			else{			
				//visualizar				
				$this->dompdf->stream("file.pdf ", ['Attachment' => $objParameterShowDownloadPreview ]);
			}
			
			
			
			
		}
		catch(\Exception $ex){
		    
		    //$data["session"] = $dataSession;
			$data["session"] 	= null;
		    $data["exception"] 	= $ex;
		    $data["urlLogin"]  	= base_url();
		    $data["urlIndex"]  	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        	= view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
	
	function viewRegisterFormatoPaginaNormal80mmOpcion1GlamCuts(){
		try{ 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= APP_COMPANY;	
			
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objParameterRuc	    = $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$objParameterRuc        = $objParameterRuc->value;
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$datView["objTM"]->branchID,APP_ROL_SUPERADMIN);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$datView["objUser"]						= $this->User_Model->get_rowByPK($companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
		
			//Configurar Detalle Header
			$confiDetalleHeader = array();
			$row = array(
				"style"		=>"text-align:left;width:auto",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				
				"style_row_data"		=>"text-align:left;width:auto",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>1
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:left;width:50px",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				"style_row_data"		=>"text-align:right;width:auto",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			
			$row = array(
				"style"		=>"text-align:right;width:90px",
				"colspan"	=>'1',
				"prefix"	=>$datView["objCurrency"]->simbol,
				
				"style_row_data"		=>"text-align:right;width:90px",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>"",
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			
		    
		    $detalle = array();		    
		    $row = array("CANT", 'PREC', "TOTAL");
		    array_push($detalle,$row);
		    
		    
			foreach($datView["objTMD"] as $detail_){
				$row = array(
					$detail_->itemName. " ". $detail_->skuFormatoDescription ."-comand-new-row",  				
					"none",
					"none"
				);
			    array_push($detalle,$row);
				
			    $row = array(					
					sprintf("%01.2f",round($detail_->quantity,2)), 					
					sprintf("%01.2f",round($detail_->unitaryPrice,2)),
					sprintf("%01.2f",round($detail_->amount,2))					
				);
			    array_push($detalle,$row);
			}
			
			
			//Generar Reporte
			$html = helper_reporte80mmTransactionMasterGlamCuts(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
			    $confiDetalleHeader,
			    $detalle,
			    $objParameterTelefono, /*telefono*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name /*causal*/,
				$datView["objUser"]->nickname,
			    $objParameterRuc /*ruc*/
			);
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			$objParameterShowDownloadPreview	= $this->core_web_parameter->getParameter("CORE_SHOW_DOWNLOAD_PREVIEW",$companyID);
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview->value;
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview == "true" ? true : false;
			
			$fileNamePut = "factura_".$transactionMasterID."_".date("dmYhis").".pdf";
			$path        = "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".$fileNamePut;
				
			file_put_contents(
				$path,
				$this->dompdf->output()					
			);						
			
			chmod($path, 644);
			
			if($objParameterShowLinkDownload == "true")
			{			
				echo "<a 
					href='".base_url()."/resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".
					$fileNamePut."'>download factura</a>
				"; 				
			
			}
			else{			
				//visualizar				
				$this->dompdf->stream("file.pdf ", ['Attachment' => $objParameterShowDownloadPreview ]);
			}
			
			
			
			
		}
		catch(\Exception $ex){
		    
		    //$data["session"] = $dataSession;
			$data["session"] 	= null;
		    $data["exception"] 	= $ex;
		    $data["urlLogin"]  	= base_url();
		    $data["urlIndex"]  	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        	= view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
	
	function viewRegisterFormatoPaginaNormal80mmOpcion1AgroServicioElLabrador(){
		try{ 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= APP_COMPANY;	
			
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objParameterRuc	    = $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$objParameterRuc        = $objParameterRuc->value;
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$datView["objTM"]->branchID,APP_ROL_SUPERADMIN);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$datView["objUser"]						= $this->User_Model->get_rowByPK($companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
		
			//Configurar Detalle Header
			$confiDetalleHeader = array();
			$row = array(
				"style"		=>"text-align:left;width:auto",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				
				"style_row_data"		=>"text-align:left;width:auto",
				"colspan_row_data"		=>'3',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>1
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:left;width:50px",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				"style_row_data"		=>"text-align:right;width:auto",
				"colspan_row_data"		=>'2',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			
			$row = array(
				"style"		=>"text-align:right;width:90px",
				"colspan"	=>'1',
				"prefix"	=>$datView["objCurrency"]->simbol,
				
				"style_row_data"		=>"text-align:right;width:90px",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>$datView["objCurrency"]->simbol,
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			
		    
		    $detalle = array();		    
		    $row = array("PRODUCTO", 'CANT', "TOTAL");
		    array_push($detalle,$row);
		    
		    
			foreach($datView["objTMD"] as $detail_){
			    $row = array(
					$detail_->itemNumber."</br>".$detail_->itemName. " ". strtolower($detail_->skuFormatoDescription),  
					sprintf("%01.2f",round($detail_->quantity,2)), 					
					sprintf("%01.2f",round($detail_->amount,2))
				);
			    array_push($detalle,$row);
			}
			
			
			//Generar Reporte
			$html = helper_reporte80mmTransactionMaster(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
			    $confiDetalleHeader,
			    $detalle,
			    $objParameterTelefono, /*telefono*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name /*causal*/,
				$datView["objUser"]->nickname,
			    $objParameterRuc /*ruc*/
			);
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			$objParameterShowDownloadPreview	= $this->core_web_parameter->getParameter("CORE_SHOW_DOWNLOAD_PREVIEW",$companyID);
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview->value;
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview == "true" ? true : false;
			
			$fileNamePut = "factura_".$transactionMasterID."_".date("dmYhis").".pdf";
			$path        = "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".$fileNamePut;
				
			file_put_contents(
				$path,
				$this->dompdf->output()					
			);						
			
			chmod($path, 644);
			
			if($objParameterShowLinkDownload == "true")
			{			
				echo "<a 
					href='".base_url()."/resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".
					$fileNamePut."'>download factura</a>
				"; 				
			
			}
			else{			
				//visualizar				
				$this->dompdf->stream("file.pdf ", ['Attachment' => $objParameterShowDownloadPreview ]);
			}
			
			
			
			
		}
		catch(\Exception $ex){
		    
		    //$data["session"] = $dataSession;
			$data["session"] 	= null;
		    $data["exception"] 	= $ex;
		    $data["urlLogin"]  	= base_url();
		    $data["urlIndex"]  	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        	= view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
	
	//Facturacion
	//Reporte = 
	//	viewRegisterFormatoPaginaNormal80mm	+
	//	Field.Vendedor	+
	//  Field.Ruc
	function viewRegisterFormatoPaginaNormal80mmOpcion1MarysCosmetic(){
		try{ 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= APP_COMPANY;	
			
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objParameterRuc	    = $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$objParameterRuc        = $objParameterRuc->value;
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$datView["objTM"]->branchID,APP_ROL_SUPERADMIN);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$datView["objUser"]						= $this->User_Model->get_rowByPK($companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
					    
			
			//Generar Reporte
			$html = helper_reporte80mmTransactionMasterMarysCosmetic(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],			    
			    $datView["objTMD"],
			    $objParameterTelefono, /*telefono*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name /*causal*/,
				$datView["objUser"]->nickname,
			    $objParameterRuc /*ruc*/
			);
			$this->dompdf->loadHTML($html);
			//echo $html;
			//return ;
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			$objParameterShowDownloadPreview	= $this->core_web_parameter->getParameter("CORE_SHOW_DOWNLOAD_PREVIEW",$companyID);
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview->value;
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview == "true" ? true : false;
			
			$fileNamePut = "factura_".$transactionMasterID."_".date("dmYhis").".pdf";
			$path        = "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".$fileNamePut;
				
			file_put_contents(
				$path,
				$this->dompdf->output()					
			);						
			
			chmod($path, 644);
			
			if($objParameterShowLinkDownload == "true")
			{			
				echo "<a 
					href='".base_url()."/resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".
					$fileNamePut."'>download factura</a>
				"; 				
			
			}
			else{			
				//visualizar				
				$this->dompdf->stream("file.pdf ", ['Attachment' => $objParameterShowDownloadPreview ]);
			}
			
			
			
			
		}
		catch(\Exception $ex){
		    
		    //$data["session"] = $dataSession;
			$data["session"] 	= null;
		    $data["exception"] 	= $ex;
		    $data["urlLogin"]  	= base_url();
		    $data["urlIndex"]  	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        	= view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
	
	//Facturacion
	//Reporte = 
	//	viewRegisterFormatoPaginaNormal80mm	+
	//	Field.Vendedor	+
	//  Field.Ruc
	function viewRegisterFormatoPaginaNormal80mmOpcion1Axceso(){
		try{ 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= APP_COMPANY;	
			
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objParameterRuc	    = $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$objParameterRuc        = $objParameterRuc->value;
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$datView["objTM"]->branchID,APP_ROL_SUPERADMIN);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$datView["objUser"]						= $this->User_Model->get_rowByPK($companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
		
			//Configurar Detalle Header
			$confiDetalleHeader = array();
			$row = array(
				"style"		=>"text-align:left;width:auto",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				
				"style_row_data"		=>"text-align:left;width:auto",
				"colspan_row_data"		=>'3',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>1
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:left;width:50px",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				"style_row_data"		=>"text-align:right;width:auto",
				"colspan_row_data"		=>'2',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:right;width:90px",
				"colspan"	=>'1',
				"prefix"	=>$datView["objCurrency"]->simbol,
				
				"style_row_data"		=>"text-align:right;width:90px",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>$datView["objCurrency"]->simbol,
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
		    
		    $detalle = array();		    
		    $row = array("PRODUCTO", 'CANT', "TOTAL");
		    array_push($detalle,$row);
		    
		    
			foreach($datView["objTMD"] as $detail_){
				
				$barCode = "";
				if( strpos($detail_->barCode,",") === false )
				{
					$barCode = $detail_->barCode;
				}
				else
				{
					$barCode = $detail_->itemNumber;
				}
				
				
				
			    $row = array(
					$barCode." ".$detail_->itemName. " ". strtolower($detail_->skuFormatoDescription),  
					sprintf("%01.2f",round($detail_->quantity,2)), 
					sprintf("%01.2f",round($detail_->amount,2))
				);
			    array_push($detalle,$row);
			}
			
			
			//Generar Reporte
			$html = helper_reporte80mmTransactionMasterAxceso(
			    "RECIBO",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
			    $confiDetalleHeader,
			    $detalle,
			    $objParameterTelefono, /*telefono*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name /*causal*/,
				$datView["objUser"]->nickname,
			    $objParameterRuc /*ruc*/
			);
			
			
			
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			$objParameterShowDownloadPreview	= $this->core_web_parameter->getParameter("CORE_SHOW_DOWNLOAD_PREVIEW",$companyID);
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview->value;
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview == "true" ? true : false;
			
			$fileNamePut = "factura_".$transactionMasterID."_".date("dmYhis").".pdf";
			$path        = "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".$fileNamePut;
				
			file_put_contents(
				$path,
				$this->dompdf->output()					
			);						
			
			chmod($path, 644);
			
			if($objParameterShowLinkDownload == "true")
			{			
				echo "<a 
					href='".base_url()."/resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".
					$fileNamePut."'>download factura</a>
				"; 				
			
			}
			else{			
				//visualizar				
				$this->dompdf->stream("file.pdf ", ['Attachment' => $objParameterShowDownloadPreview ]);
			}
			
			
			
			
		}
		catch(\Exception $ex){
		    
		    //$data["session"] = $dataSession;
			$data["session"] 	= null;
		    $data["exception"] 	= $ex;
		    $data["urlLogin"]  	= base_url();
		    $data["urlIndex"]  	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        	= view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	//Facturacion
	//Reporte = 
	//	viewRegisterFormatoPaginaNormal80mm	+
	//	Field.Vendedor	+
	//  Field.Ruc
	function viewRegisterFormatoPaginaNormal80mmOpcion1PabloRosales(){
		try{ 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= APP_COMPANY;	
			
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objParameterRuc	    = $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$objParameterRuc        = $objParameterRuc->value;
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$datView["objTM"]->branchID,APP_ROL_SUPERADMIN);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$datView["objUser"]						= $this->User_Model->get_rowByPK($companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
		
			//Configurar Detalle Header
			$confiDetalleHeader = array();
			$row = array(
				"style"		=>"text-align:left;width:auto",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				
				"style_row_data"		=>"text-align:left;width:auto",
				"colspan_row_data"		=>'3',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>1
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:left;width:50px",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				"style_row_data"		=>"text-align:right;width:auto",
				"colspan_row_data"		=>'2',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:right;width:90px",
				"colspan"	=>'1',
				"prefix"	=>$datView["objCurrency"]->simbol,
				
				"style_row_data"		=>"text-align:right;width:90px",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>$datView["objCurrency"]->simbol,
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
		    
		    $detalle = array();		    
		    $row = array("PRODUCTO", 'CANT', "TOTAL");
		    array_push($detalle,$row);
		    
		    
			foreach($datView["objTMD"] as $detail_){
			    $row = array(
					$detail_->itemName. " ". strtolower($detail_->skuFormatoDescription),  
					sprintf("%01.2f",round($detail_->quantity,2)), 
					sprintf("%01.2f",round($detail_->amount,2))
				);
			    array_push($detalle,$row);
			}
			
			
			//Generar Reporte
			$html = helper_reporte80mmTransactionMasterPabloRosales(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
			    $confiDetalleHeader,
			    $detalle,
			    $objParameterTelefono, /*telefono*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name /*causal*/,
				$datView["objUser"]->nickname,
			    $objParameterRuc /*ruc*/
			);
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			$objParameterShowDownloadPreview	= $this->core_web_parameter->getParameter("CORE_SHOW_DOWNLOAD_PREVIEW",$companyID);
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview->value;
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview == "true" ? true : false;
			
			$fileNamePut = "factura_".$transactionMasterID."_".date("dmYhis").".pdf";
			$path        = "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".$fileNamePut;
				
			file_put_contents(
				$path,
				$this->dompdf->output()					
			);						
			
			chmod($path, 644);
			
			if($objParameterShowLinkDownload == "true")
			{			
				echo "<a 
					href='".base_url()."/resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".
					$fileNamePut."'>download factura</a>
				"; 				

			}
			else{			
				//visualizar				
				$this->dompdf->stream("file.pdf ", ['Attachment' => $objParameterShowDownloadPreview ]);
			}
			
			
			
			
		}
		catch(\Exception $ex){
		    
		    //$data["session"] = $dataSession;
			$data["session"] 	= null;
		    $data["exception"] 	= $ex;
		    $data["urlLogin"]  	= base_url();
		    $data["urlIndex"]  	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        	= view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	//facturacion estandar, horizontal tamaña a4
	function viewRegisterFormatoPaginaNormal80mmCarlosLuis(){
		try{ 
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCION
			if(APP_NEED_AUTHENTICATION == true){
						$permited = false;
						$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						
						if(!$permited)
						throw new \Exception(NOT_ACCESS_CONTROL);
						
							
						$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"edit",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						if ($resultPermission 	== PERMISSION_NONE)
						throw new \Exception(NOT_ALL_EDIT);		
			}	 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= $dataSession["user"]->companyID;		
			$branchID 					= $dataSession["user"]->branchID;		
			$roleID 					= $dataSession["role"]->roleID;		
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$branchID,$roleID);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
			//Configurar Detalle Header
			$confiDetalleHeader = array();
			$row = array(
				"style"		=>"text-align:left;width:auto",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				
				"style_row_data"		=>"text-align:left;width:auto",
				"colspan_row_data"		=>'3',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>1
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:left;width:50px",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				"style_row_data"		=>"text-align:right;width:auto",
				"colspan_row_data"		=>'2',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:right;width:90px",
				"colspan"	=>'1',
				"prefix"	=>$datView["objCurrency"]->simbol,
				
				"style_row_data"		=>"text-align:right;width:90px",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>$datView["objCurrency"]->simbol,
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
		    
		    $detalle = array();		    
		    $row = array("PRODUCTO", 'CANT', "TOTAL");
		    array_push($detalle,$row);
		    
		    
			foreach($datView["objTMD"] as $detail_){
			    $row = array(
					$detail_->itemName. " ". strtolower($detail_->skuFormatoDescription),  
					sprintf("%01.2f",round($detail_->quantity,2)), 
					sprintf("%01.2f",round($detail_->amount,2))
				);
			    array_push($detalle,$row);
			}
			
			
			//Generar Reporte
			$html = helper_reporte80mmTransactionMasterInvoiceCarlosLuis(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
			    $confiDetalleHeader,
			    $detalle,
			    $objParameterTelefono, /*telefono*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name, /*causal*/
				""
			);
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			
			
			//visualizar
			$this->dompdf->stream("file.pdf", ['Attachment' => !$objParameterShowLinkDownload ]);
			
			//descargar
			//$this->dompdf->stream();
			
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	function viewRegisterFormatoPaginaNormal80mmOpcion1Douglas(){
		try{ 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= APP_COMPANY;	
			
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objParameterRuc	    = $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$objParameterRuc        = $objParameterRuc->value;
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTC"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$datView["objTM"]->branchID,APP_ROL_SUPERADMIN);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$datView["objUser"]						= $this->User_Model->get_rowByPK($companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
		
			//Configurar Detalle Header
			$confiDetalleHeader = array();
			$row = array(
				"style"		=>"text-align:left;width:auto",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				
				"style_row_data"		=>"text-align:left;width:auto",
				"colspan_row_data"		=>'3',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>1
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:left;width:50px",
				"colspan"	=>'1',
				"prefix"	=>'',
				
				"style_row_data"		=>"text-align:right;width:auto",
				"colspan_row_data"		=>'2',
				"prefix_row_data"		=>'',
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
				"style"		=>"text-align:right;width:90px",
				"colspan"	=>'1',
				"prefix"	=>$datView["objCurrency"]->simbol,
				
				"style_row_data"		=>"text-align:right;width:90px",
				"colspan_row_data"		=>'1',
				"prefix_row_data"		=>$datView["objCurrency"]->simbol,
				"nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
		    
		    $detalle = array();		    
		    $row = array("PRODUCTO", 'CANT', "TOTAL");
		    array_push($detalle,$row);
		    
		    
			foreach($datView["objTMD"] as $detail_){
			    $row = array(
					$detail_->itemName. " ". strtolower($detail_->skuFormatoDescription),  
					sprintf("%01.2f",round($detail_->quantity,2)), 
					sprintf("%01.2f",round($detail_->amount,2))
				);
			    array_push($detalle,$row);
			}
			
			
			//Generar Reporte
			$html = helper_reporte80mmTransactionMasterInvoiceDouglas(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
			    $confiDetalleHeader,
			    $detalle,
			    $objParameterTelefono, /*telefono*/
				$datView["objStage"][0]->display, /*estado*/
				$datView["objTC"]->name /*causal*/,
				$datView["objUser"]->nickname,
			    $objParameterRuc /*ruc*/
			);
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			$objParameterShowDownloadPreview	= $this->core_web_parameter->getParameter("CORE_SHOW_DOWNLOAD_PREVIEW",$companyID);
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview->value;
			$objParameterShowDownloadPreview	= $objParameterShowDownloadPreview == "true" ? true : false;
			
			$fileNamePut = "factura_".$transactionMasterID."_".date("dmYhis").".pdf";
			$path        = "./resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".$fileNamePut;
				
			file_put_contents(
				$path,
				$this->dompdf->output()					
			);						
			
			chmod($path, 644);
			
			if($objParameterShowLinkDownload == "true")
			{			
				echo "<a 
					href='".base_url()."/resource/file_company/company_".$companyID."/component_48/component_item_".$transactionMasterID."/".
					$fileNamePut."'>download factura</a>
				"; 				

			}
			else{			
				//visualizar				
				$this->dompdf->stream("file.pdf ", ['Attachment' => $objParameterShowDownloadPreview ]);
			}
			
			
			
			
		}
		catch(\Exception $ex){
		    
		    //$data["session"] = $dataSession;
			$data["session"] 	= null;
		    $data["exception"] 	= $ex;
		    $data["urlLogin"]  	= base_url();
		    $data["urlIndex"]  	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   	= base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        	= view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
	function viewRegisterFormatoPaginaNormalA4FunBlandonReciboOficialCaja()
	{
		try{ 
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCION
			if(APP_NEED_AUTHENTICATION == true){
						$permited = false;
						$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						
						if(!$permited)
						throw new \Exception(NOT_ACCESS_CONTROL);
						
							
						$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"edit",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						if ($resultPermission 	== PERMISSION_NONE)
						throw new \Exception(NOT_ALL_EDIT);		
			}	 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$saldos						= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"saldos");//--finuri	
			$companyID 					= $dataSession["user"]->companyID;		
			$branchID 					= $dataSession["user"]->branchID;		
			$roleID 					= $dataSession["role"]->roleID;		
			
			//Get Component
			$objComponent	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			//Get Logo
			$objParameter	= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			//Get Company
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			//Get Documento				
			
			//Get Documento
			//Obtener Datos
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransactionToShare($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$datView["objCurrency"]                 = $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$branchID,$roleID);
			
			
			
			
			//Inicializar Detalle
			$saldoInicial = array_sum(array_column($datView["objTMD"], 'reference2'));
			$saldoFinal   = array_sum(array_column($datView["objTMD"], 'reference4'));
			$saldoAbonado = array_sum(array_column($datView["objTMD"], 'amount'));
			
			/*Calculo de saldos generales*/
			$saldoInicialGeneral = round($datView["objTMI"]->reference1,0);
			$saldoFinalGeneral   = round($datView["objTMI"]->reference2,0);
			
			$saldoInicial 	= $saldos == "Individuales"? $saldoInicial: $saldoInicialGeneral ;
			$saldoFinal 	= $saldos == "Individuales"? $saldoFinal: $saldoFinalGeneral ;
				
				
			//$row = array("SALDO INICIAL", '', $datView["objCurrency"]->simbol." ".sprintf("%.2f", $saldoInicial));
			//array_push($detalle,$row);
			//
			//foreach($datView["objTMD"] as $detail_){
			//	
			//	$row = array("APERTURA", '', $detail_->reference5 );
			//	array_push($detalle,$row);
			//	
			//	$row = array("FINALIZACION", '', $detail_->reference6);
			//	array_push($detalle,$row);
			//	
			//	
			//	$row = array("MORA", '', ($detail_->lote > 0 ? "0" :  $detail_->lote) );
			//	array_push($detalle,$row);
			//	
			//	
			//	$row = array("ABONO", '', sprintf('%.2f',round($detail_->amount,2)));
			//	array_push($detalle,$row);
			//}
			//
			//$row = array("SALDO FINAL", '', sprintf('%.2f', $saldoFinal) );
			//array_push($detalle,$row);
			
			
			
			//Generar Reporte
			$html = helper_reporte80mmTransactionMasterFunBlandonReciboOficialCaja(
			    "ABONO",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],			    
			    $objParameterTelefono,
				$datView["objStage"][0]->display,
				"",
				""
			);
			
			//echo $html;
			//return;
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			$nameFileDownload				= date("YmdHis").".pdf";
			
			//visualizar
			$this->response->setContentType('application/pdf');
			$objParameterShowLinkDownload 	= $objParameterShowLinkDownload == "false" ? true : false;
			$this->dompdf->stream($nameFileDownload	, ['Attachment' => $objParameterShowLinkDownload]);
			
			//descargar
			//$this->dompdf->stream();
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
	//facturacion estandar, horizontal tamaña a4
	function viewRegisterFormatoPaginaNormal58mm(){
		try{ 
			//AUTENTICADO
			if(!$this->core_web_authentication->isAuthenticated())
			throw new \Exception(USER_NOT_AUTENTICATED);
			$dataSession		= $this->session->get();
			
			//PERMISO SOBRE LA FUNCION
			if(APP_NEED_AUTHENTICATION == true){
						$permited = false;
						$permited = $this->core_web_permission->urlPermited(get_class($this),"index",URL_SUFFIX,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						
						if(!$permited)
						throw new \Exception(NOT_ACCESS_CONTROL);
						
							
						$resultPermission		= $this->core_web_permission->urlPermissionCmd(get_class($this),"edit",URL_SUFFIX,$dataSession,$dataSession["menuTop"],$dataSession["menuLeft"],$dataSession["menuBodyReport"],$dataSession["menuBodyTop"],$dataSession["menuHiddenPopup"]);
						if ($resultPermission 	== PERMISSION_NONE)
						throw new \Exception(NOT_ALL_EDIT);		
			}	 
			
			
			$transactionID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri			
			$transactionMasterID		= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri				
			$companyID 					= $dataSession["user"]->companyID;		
			$branchID 					= $dataSession["user"]->branchID;		
			$roleID 					= $dataSession["role"]->roleID;		
			
			
			//Get Component
			$objComponent	        = $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$objCompany 	= $this->Company_Model->get_rowByPK($companyID);			
			$spacing 		= 0.5;
			
			//Get Documento					
			$datView["objTM"]	 					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMI"]						= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$datView["objTMD"]						= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$datView["objTM"]->transactionOn 		= date_format(date_create($datView["objTM"]->transactionOn),"Y-m-d");
			$datView["objUser"] 					= $this->User_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->createdAt,$datView["objTM"]->createdBy);
			$datView["Identifier"]					= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$datView["objBranch"]					= $this->Branch_Model->get_rowByPK($datView["objTM"]->companyID,$datView["objTM"]->branchID);
			$datView["objStage"]					= $this->core_web_workflow->getWorkflowStage("tb_transaction_master_billing","statusID",$datView["objTM"]->statusID,$companyID,$branchID,$roleID);
			$datView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$datView["objTM"]->transactionID,$datView["objTM"]->transactionCausalID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objCurrency"]					= $this->Currency_Model->get_rowByPK($datView["objTM"]->currencyID);
			$datView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$datView["objTM"]->entityID);
			$datView["objNatural"]					= $this->Natural_Model->get_rowByPK($companyID,$datView["objCustumer"]->branchID,$datView["objCustumer"]->entityID);
			$datView["tipoCambio"]					= round($datView["objTM"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			$prefixCurrency 						= $datView["objCurrency"]->simbol." "; 
			
			
			//Configurar Detalle
			$confiDetalleHeader = array();
			$row = array(
			    "style"		=>"text-align:left;width:auto",
			    "colspan"	=>'1',
			    "prefix"	=>'',
			    
			    
			    "style_row_data"		=>"text-align:left;width:auto",
			    "colspan_row_data"		=>'3',
			    "prefix_row_data"		=>'',
			    "nueva_fila_row_data"	=>1
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
			    "style"		=>"text-align:left;width:50px",
			    "colspan"	=>'1',
			    "prefix"	=>'',
			    
			    "style_row_data"		=>"text-align:right;width:auto",
			    "colspan_row_data"		=>'2',
			    "prefix_row_data"		=>'',
			    "nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			$row = array(
			    "style"		=>"text-align:right;width:90px",
			    "colspan"	=>'1',
			    "prefix"	=>$datView["objCurrency"]->simbol,
			    
			    "style_row_data"		=>"text-align:right;width:90px",
			    "colspan_row_data"		=>'1',
			    "prefix_row_data"		=>$datView["objCurrency"]->simbol,
			    "nueva_fila_row_data"	=>0
			);
			array_push($confiDetalleHeader,$row);
			
			
			
			
		    
		    $detalle = array();		    
		    $row = array("PRODUCTO", '', "TOTAL");
		    array_push($detalle,$row);
		    
		    
			foreach($datView["objTMD"] as $detail_){
			    $row = array(
			        $detail_->itemName,  
			        "cant:".round($detail_->quantity,2), 
			        round($detail_->amount,2));
			    array_push($detalle,$row);
			}
			
			
			//Generar Reporte
			$html = helper_reporte58mmTransactionMaster(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $datView["objTM"],
			    $datView["objNatural"],
			    $datView["objCustumer"],
			    $datView["tipoCambio"],
			    $datView["objCurrency"],
			    $datView["objTMI"],
			    $confiDetalleHeader,
			    $detalle,
			    $objParameterTelefono
			    );
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			
			
			//visualizar
			$this->dompdf->stream("file.pdf", ['Attachment' => !$objParameterShowLinkDownload ]);
			
			//descargar
			//$this->dompdf->stream();
			
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
	//facturacion estandar, horizontal tamaña a4
	function viewRegisterFormatoPaginaCocina80mm(){
		try{ 
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			$itemID					= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"itemID");//--finuri	
		
			$objCompany 			= $this->Company_Model->get_rowByPK($companyID);					
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			$dataView["objNatural"]						= $this->Natural_Model->get_rowByPK($companyID,$dataView["objCustumer"]->branchID,$dataView["objCustumer"]->entityID);
			$dataView["tipoCambio"]						= round($dataView["objTransactionMaster"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT_COCINA",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			
			//Configurar Detalle
			$confiDetalle = array();
			$row = array(
			    "style"=>"text-align:left;width:auto",
			    "colspan"=>'1',
			    "prefix"=>'',
			    
			    "style_row_data"		=>"text-align:left;width:auto",
			    "colspan_row_data"		=>'1',
			    "prefix_row_data"		=>'',
			    "nueva_fila_row_data"	=>0
			    
			);
			array_push($confiDetalle,$row);
			$row = array(
			    "style"=>"text-align:left;width:50px",
			    "colspan"=>'1',
			    "prefix"=>'',
			    
			    "style_row_data"		=>"text-align:left;width:50px",
			    "colspan_row_data"		=>'1',
			    "prefix_row_data"		=>'',
			    "nueva_fila_row_data"	=>0
			    
			);
			array_push($confiDetalle,$row);
			$row = array(
			    "style"=>"text-align:right;width:70px",
			    "colspan"=>'1',
			    "prefix"=>$dataView["objCurrency"]->simbol,
			    
			    "style_row_data"		=>"text-align:right;width:70px",
			    "colspan_row_data"		=>'1',
			    "prefix_row_data"		=>"",
			    "nueva_fila_row_data"	=>0
			    
			);
			array_push($confiDetalle,$row);
			
		    
		    $detalle = array();		    
		    $row = array("Elaborar", '', "");
		    array_push($detalle,$row);
		    
		    
			foreach($dataView["objTransactionMasterDetail"] as $detail_){
			    $row = array(
					$detail_->itemName,  
					1, /*round($detail_->quantity,2),*/ 
					"" /*round($detail_->amount,2)*/
				);
			    array_push($detalle,$row);
			}
			
			
			//Generar Reporte
			$html = helper_reporte80mmCocina(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $dataView["objTransactionMaster"],
			    $dataView["objNatural"],
			    $dataView["objCustumer"],
			    $dataView["tipoCambio"],
			    $dataView["objCurrency"],
			    $dataView["objTransactionMasterInfo"],
			    $confiDetalle,
			    $detalle,
			    $objParameterTelefono,
				"",
				"",
				""
			);
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			
			
			//visualizar
			$this->dompdf->stream("file.pdf", ['Attachment' => !$objParameterShowLinkDownload ]);
			
			//descargar
			//$this->dompdf->stream();
			
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
	//facturacion estandar, horizontal tamaña a4
	function viewRegisterFormatoPaginaCocina58mm(){
		try{ 
			
			//Librerias		
			//
			////////////////////////////////////////
			////////////////////////////////////////
			////////////////////////////////////////
			
			
			
			//Obtener el componente de Item
			$objComponentItem	= $this->core_web_tools->getComponentIDBy_ComponentName("tb_item");
			if(!$objComponentItem)
			throw new \Exception("EL COMPONENTE 'tb_item' NO EXISTE...");
			$dataSession		= $this->session->get();
									
			$companyID				= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"companyID");//--finuri
			$transactionID			= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionID");//--finuri	
			$transactionMasterID	= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"transactionMasterID");//--finuri	
			$itemID					= /*--ini uri*/ helper_SegmentsValue($this->uri->getSegments(),"itemID");//--finuri	
		
			$objCompany 			= $this->Company_Model->get_rowByPK($companyID);					
			$objParameter	        = $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$objParameterTelefono	= $this->core_web_parameter->getParameter("CORE_PHONE",$companyID);
			$dataView["objTransactionMaster"]					= $this->Transaction_Master_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterInfo"]				= $this->Transaction_Master_Info_Model->get_rowByPK($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetail"]				= $this->Transaction_Master_Detail_Model->get_rowByTransaction($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailWarehouse"]	= $this->Transaction_Master_Detail_Model->get_rowByTransactionAndWarehouse($companyID,$transactionID,$transactionMasterID);
			$dataView["objTransactionMasterDetailConcept"]		= $this->Transaction_Master_Concept_Model->get_rowByTransactionMasterConcept($companyID,$transactionID,$transactionMasterID,$objComponentItem->componentID);
			
			
			$dataView["objComponentCompany"]			= $this->core_web_tools->getComponentIDBy_ComponentName("tb_company");
			$dataView["objParameterLogo"]				= $this->core_web_parameter->getParameter("CORE_COMPANY_LOGO",$companyID);
			$dataView["objParameterPhoneProperty"]		= $this->core_web_parameter->getParameter("CORE_PROPIETARY_PHONE",$companyID);
			$dataView["objCompany"] 					= $this->Company_Model->get_rowByPK($companyID);			
			$dataView["objUser"] 						= $this->User_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->createdAt,$dataView["objTransactionMaster"]->createdBy);
			$dataView["Identifier"]						= $this->core_web_parameter->getParameter("CORE_COMPANY_IDENTIFIER",$companyID);
			$dataView["objBranch"]						= $this->Branch_Model->get_rowByPK($companyID,$dataView["objTransactionMaster"]->branchID);
			$dataView["objTipo"]						= $this->Transaction_Causal_Model->getByCompanyAndTransactionAndCausal($companyID,$dataView["objTransactionMaster"]->transactionID,$dataView["objTransactionMaster"]->transactionCausalID);
			$dataView["objCustumer"]					= $this->Customer_Model->get_rowByEntity($companyID,$dataView["objTransactionMaster"]->entityID);
			$dataView["objCurrency"]					= $this->Currency_Model->get_rowByPK($dataView["objTransactionMaster"]->currencyID);
			$dataView["prefixCurrency"]					= $dataView["objCurrency"]->simbol." ";
			$dataView["cedulaCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientIdentifier == "" ? $dataView["objCustumer"]->customerNumber :  $dataView["objTransactionMasterInfo"]->referenceClientIdentifier;
			$dataView["nombreCliente"] 					= $dataView["objTransactionMasterInfo"]->referenceClientName  == "" ? $dataView["objCustumer"]->firstName : $dataView["objTransactionMasterInfo"]->referenceClientName ;
			$dataView["objStage"]						= $this->Workflow_Stage_Model->get_rowByWorkflowStageIDOnly($dataView["objTransactionMaster"]->statusID);
			$dataView["objNatural"]						= $this->Natural_Model->get_rowByPK($companyID,$dataView["objCustumer"]->branchID,$dataView["objCustumer"]->entityID);
			$dataView["tipoCambio"]						= round($dataView["objTransactionMaster"]->exchangeRate + $this->core_web_parameter->getParameter("ACCOUNTING_EXCHANGE_SALE",$companyID)->value,2);
			//obtener nombre de impresora por defecto
			$objParameterPrinterName = $this->core_web_parameter->getParameter("INVOICE_BILLING_PRINTER_DIRECT_NAME_DEFAULT_COCINA",$companyID);
			$objParameterPrinterName = $objParameterPrinterName->value;
								
			
			
			//Configurar Detalle
			$confiDetalle = array();
			$row = array(
			    "style"=>"text-align:left;width:auto",
			    "colspan"=>'1',
			    "prefix"=>'',
			    
			    "style_row_data"		=>"text-align:left;width:auto",
			    "colspan_row_data"		=>'1',
			    "prefix_row_data"		=>'',
			    "nueva_fila_row_data"	=>0
			    
			);
			array_push($confiDetalle,$row);
			$row = array(
			    "style"=>"text-align:left;width:50px",
			    "colspan"=>'1',
			    "prefix"=>'',
			    
			    "style_row_data"		=>"text-align:left;width:50px",
			    "colspan_row_data"		=>'1',
			    "prefix_row_data"		=>'',
			    "nueva_fila_row_data"	=>0
			    
			);
			array_push($confiDetalle,$row);
			$row = array(
			    "style"=>"text-align:right;width:70px",
			    "colspan"=>'1',
			    "prefix"=>$dataView["objCurrency"]->simbol,
			    
			    "style_row_data"		=>"text-align:right;width:70px",
			    "colspan_row_data"		=>'1',
			    "prefix_row_data"		=>"",
			    "nueva_fila_row_data"	=>0
			    
			);
			array_push($confiDetalle,$row);
			
		    
		    $detalle = array();		    
		    $row = array("Elaborar", '', "");
		    array_push($detalle,$row);
		    
		    
			foreach($dataView["objTransactionMasterDetail"] as $detail_){
			    $row = array(
					$detail_->itemName,  
					1, /*round($detail_->quantity,2),*/ 
					"" /*round($detail_->amount,2)*/
				);
			    array_push($detalle,$row);
			}
			
			
			//Generar Reporte
			$html = helper_reporte58mmCocina(
			    "FACTURA",
			    $objCompany,
			    $objParameter,
			    $dataView["objTransactionMaster"],
			    $dataView["objNatural"],
			    $dataView["objCustumer"],
			    $dataView["tipoCambio"],
			    $dataView["objCurrency"],
			    $dataView["objTransactionMasterInfo"],
			    $confiDetalle,
			    $detalle,
			    $objParameterTelefono,
				"",
				"",
				""
			);
			$this->dompdf->loadHTML($html);
			
			//1cm = 29.34666puntos
			//a4: 210 ancho x 297
			//a4: 21cm x 29.7cm
			//a4: 595.28puntos x 841.59puntos
			
			//$this->dompdf->setPaper('A4','portrait');
			//$this->dompdf->setPaper(array(0,0,234.76,6000));
			
			$this->dompdf->render();
			
			
			$objParameterShowLinkDownload	= $this->core_web_parameter->getParameter("CORE_SHOW_LINK_DOWNOAD",$companyID);
			$objParameterShowLinkDownload	= $objParameterShowLinkDownload->value;
			
			
			
			//visualizar
			$this->dompdf->stream("file.pdf", ['Attachment' => !$objParameterShowLinkDownload ]);
			
			//descargar
			//$this->dompdf->stream();
			
			
		}
		catch(\Exception $ex){
		    
		    $data["session"]   = $dataSession;
		    $data["exception"] = $ex;
		    $data["urlLogin"]  = base_url();
		    $data["urlIndex"]  = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/"."index";
		    $data["urlBack"]   = base_url()."/". str_replace("app\\controllers\\","",strtolower( get_class($this)))."/".helper_SegmentsByIndex($this->uri->getSegments(), 0, null);
		    $resultView        = view("core_template/email_error_general",$data);
		    
		    $this->email->setFrom(EMAIL_APP);
		    $this->email->setTo(EMAIL_APP_COPY);
		    $this->email->setSubject("Error");
		    $this->email->setMessage($resultView);
		    
		    $resultSend01 = $this->email->send();
		    $resultSend02 = $this->email->printDebugger();
		    
		    
		    return $resultView;
		}
	}
	
}
?>