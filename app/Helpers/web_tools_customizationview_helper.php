<?php
function getBehavio($type_company,$key_controller,$key_element,$default_value)
{
	$divs = array(		
		strtolower('default_core_web_menu_O. SALIDAS')			 												=> "O. SALIDAS",
		strtolower('default_core_web_menu_O. ENTRADAS')			 												=> "O. ENTRADAS",
		strtolower('default_core_web_menu_PRODUCTO')			 												=> "PRODUCTO",
		strtolower('default_app_cxp_expenses_Referencia 1')														=> "Referencia 1",
		strtolower('default_app_cxp_expenses_Referencia 2')														=> "Referencia 2",
		strtolower('default_app_cxp_expenses_Referencia 3')														=> "Referencia 3",				
		strtolower('default_default_masterpage_backgroundImage')			 									=> "",
		strtolower('default_core_account_idtest')																=> "hidden",
		strtolower('default_app_invoice_billing_panelResumenFactura')											=> "",
		strtolower('default_app_invoice_billing_panelResumenFacturaTool')										=> "hidden",
		strtolower('default_app_invoice_billing_bodyListInvoice')			 									=> "",		
		strtolower('default_app_invoice_billing_divTxtZone')		 											=> "",
		strtolower('default_app_box_share_stylePage')			 												=> "",
		strtolower('default_app_box_share_labelReference1')			 											=> "Referencia1",
		strtolower('default_app_box_share_divResumenAbono')			 											=> "",
		strtolower('default_app_box_share_divStart')			 												=> "",
		strtolower('default_app_box_share_divFecha')			 												=> "",
		strtolower('default_app_box_share_divAplicado')			 												=> "",
		strtolower('default_app_box_share_divCambio')			 												=> "",
		strtolower('default_app_box_share_comboStyle')			 												=> "select2",
		strtolower('default_app_box_share_new_script_validate_reference1')										=> "",
		strtolower('default_app_box_share_javscriptVariable_varShareMountDefaultOfAmortization')				=> "true",
		strtolower('default_app_box_share_javscriptVariable_varPrinterOnlyFormat')								=> "false",
		strtolower('default_app_box_share_TableColumnDocumento')												=> "",		
		strtolower('default_app_box_share_divCustomerControlBuscar')											=> "",
		strtolower('default_app_box_share_divCustomerControlSelected')											=> "hidden",
		strtolower('default_app_box_share_divCobrador')															=> "",
		strtolower('default_app_box_share_divMoneda')															=> "",		
		strtolower('default_app_box_share_btnVerMovimientos')													=> "",
		strtolower('default_app_inventory_transferoutput_parameterValidarEnvioDestino')							=> "false",
		strtolower('default_app_inventory_transferoutput_labelReference1')										=> "Referencia",		
		strtolower('default_app_invoice_billing_divTxtCedula2') 												=> "",		
		strtolower('default_app_invoice_billing_divTraslateElement') 											=> "",		
		strtolower('default_core_dashboards_divPanelCuadroMembresia')											=> "",
		strtolower('default_core_dashboards_divPanelBiblico')													=> "",
		strtolower('default_core_dashboards_divPanelSoporteTenico')												=> "",
		strtolower('default_core_dashboards_divPanelFormaPago')													=> "",
		strtolower('default_core_dashboards_divPanelInfoPago')													=> "",
		strtolower('default_core_dashboards_divPanelUsuario')													=> "",	
		strtolower('default_app_invoice_billing_divTxtCambio') 													=> "",
		strtolower('default_app_invoice_billing_divTxtMoneda') 			 										=> "",		
		strtolower('default_app_invoice_billing_divTxtCliente2') 			 									=> "",		
		strtolower('default_app_inventory_inputunpost_new_script_validate_reference1')							=> "",
		strtolower('default_app_purchase_pedidos_divDllEstado')													=> "",
		strtolower('default_web_tools_report_helper_Edad') 														=> "Edad", 		
		strtolower('default_web_tools_report_helper_Sexo') 														=> "Sexo", 				
					
		strtolower('default_app_cxc_customer_divTxtNombres') 													=> "",
		strtolower('default_app_cxc_customer_divTxtApellidos') 			 										=> "",		
		strtolower('default_app_cxc_customer_divTxtNombreComercial') 			 								=> "",
		strtolower('default_app_cxc_customer_divTxtEstado') 													=> "",
		strtolower('default_app_cxc_customer_divTxtClasificacion') 												=> "",
		strtolower('default_app_cxc_customer_divTxtTipo') 			 											=> "",		
		strtolower('default_app_cxc_customer_divTxtSubCategoria') 			 									=> "",
		strtolower('default_app_cxc_customer_divTxtEstadoCivil') 			 									=> "",
		strtolower('default_app_cxc_customer_divTxtProfesionUFicio') 			 								=> "",
		strtolower('default_app_cxc_customer_divScriptCustom') 													=> "",	
		strtolower('default_app_cxc_customer_divTxtCategoria') 													=> "",		
		strtolower('default_app_cxc_customer_Clasificacion')													=> "Clasificacion",
		strtolower('default_app_cxc_customer_Categoria')														=> "Categoria",
		strtolower('default_app_cxc_customer_Referencia1')														=> "Referencia 1",
		strtolower('default_app_cxc_customer_Referencia2')														=> "Referencia 2",
		strtolower('default_app_cxc_customer_Referencia3')														=> "Referencia 3",
		strtolower('default_app_cxc_customer_Referencia4')														=> "Referencia 4",
		strtolower('default_app_cxc_customer_Referencia5')														=> "Referencia 5",
					
		strtolower('default_app_inventory_item_label_price_PUBLICO')			 								=> "PUBLICO",
		strtolower('default_app_inventory_item_label_price_POR MAYOR')			 								=> "POR MAYOR",
		strtolower('default_app_inventory_item_label_price_CREDITO')			 								=> "CREDITO",
		strtolower('default_app_inventory_item_label_price_CREDITO POR MAYOR')			 						=> "CREDITO POR MAYOR",
		strtolower('default_app_inventory_item_label_price_ESPECIAL')			 								=> "ESPECIAL",						
		strtolower('default_app_inventory_item_Conceptos')			 											=> "Conceptos",
		strtolower('default_app_inventory_item_labelBarCode')													=> "Barra",
		strtolower('default_app_inventory_item_divTxtPresentacionUM') 											=> "",
		strtolower('default_app_inventory_item_divTxtPresentacion') 			 								=> "",
		strtolower('default_app_inventory_item_divTxtUM') 			 											=> "",
		strtolower('default_app_inventory_item_divTxtCapacidad') 			 									=> "",
		strtolower('default_app_inventory_item_divTxtCantidadMinima') 											=> "",
		strtolower('default_app_inventory_item_divTxtCantidadMaxima') 											=> "",
		strtolower('default_app_inventory_item_divTxtSKUCompras') 			 									=> "",
		strtolower('default_app_inventory_item_divTxtSKUProduccion') 											=> "",
		strtolower('default_app_inventory_item_divTxtEstado') 			 										=> "",
		strtolower('default_app_inventory_item_divTxtFamilia') 			 										=> "",
		strtolower('default_app_inventory_item_divTxtBarCode') 													=> "",
		strtolower('default_app_inventory_item_divTxtPerecedero') 			 									=> "",	
		strtolower('default_app_inventory_item_divTraslateElementTablePrecio') 									=> "",				
		strtolower('default_app_inventory_item_Descripcion')			 										=> "Descripcion",
		strtolower('default_app_inventory_item_*Familia')														=> "*Familia",
		strtolower('default_app_inventory_item_*Presentacion')													=> "*Presentacion",
		strtolower('default_app_inventory_item_Perecedero')														=> "Perecedero",
		strtolower('default_app_inventory_item_*UM. Presentacion')												=> "*UM. Presentacion",
		strtolower('default_app_inventory_item_*SKU Compras')													=> "* SKU Compras",
		strtolower('default_app_inventory_item_*SKU Produccion')												=> "* SKU Produccion",
		strtolower('default_app_inventory_item_*Cantidad Minima')												=> "* Cantidad Minima",
		strtolower('default_app_inventory_item_*Cantidad Maxima')												=> "* Cantidad Maxima",
		strtolower('default_app_inventory_item_Servicio')														=> "Servicio",		
		strtolower('default_app_inventory_item_*Categoria')														=> "*Categoria",
		strtolower('default_app_inventory_item_*Capacidad')														=> "*Capacidad",		
		strtolower('default_app_inventory_item_Marca')															=> "Marca",
		strtolower('default_app_inventory_item_Modelo')															=> "Modelo",
		strtolower('default_app_inventory_item_Serie ó MAI')													=> "Serie o MAI",		
		strtolower('default_app_inventory_item_fieldInmobiliaria')												=> "",
		strtolower('default_app_purchase_pedidos_divScriptCustom')												=> "",		
		strtolower('default_app_invoice_billing_txtTraductionVendedor') 		 								=> "Vendedor",
		strtolower('default_app_invoice_billing_txtTraductionMesa') 		 									=> "Mesa",
	
	
		
		/*GlobalPro*/
		strtolower('globalpro_core_web_menu_O. SALIDAS')			 											=> "AJUSTE SALIDA",
		strtolower('globalpro_core_web_menu_O. ENTRADAS')			 											=> "AJUSTE ENTRADA",
		strtolower('globalpro_app_inventory_item_label_price_PUBLICO')			 								=> "PRECIO OFERTA",
		strtolower('globalpro_app_inventory_item_label_price_POR MAYOR')										=> "REGULAR",
		strtolower('globalpro_app_inventory_item_label_price_CREDITO')			 								=> "POR MAYOR",
		strtolower('globalpro_app_inventory_item_label_price_CREDITO POR MAYOR')								=> "LIQUIDACION",
		strtolower('globalpro_app_inventory_item_label_price_ESPECIAL')			 								=> "ESPECIAL",		
		strtolower('globalpro_app_cxp_expenses_Referencia 1')													=> "Descripcion",
		strtolower('globalpro_app_cxp_expenses_Referencia 2')													=> "Factura",
		strtolower('globalpro_app_cxp_expenses_Referencia 3')													=> "Proveedor",
		strtolower('globalpro_app_inventory_item_Conceptos')			 										=> "IVA",
		strtolower('globalpro_default_masterpage_backgroundImage')			 									=> "style='background-image: url(".  base_url()."/resource/img/logos/fondo_globalpro.jpg"   .");'",		
		strtolower('globalpro_core_dashboards_divPanelCuadroMembresia')											=> "hidden",
		strtolower('globalpro_core_dashboards_divPanelBiblico')													=> "hidden",
		strtolower('globalpro_core_dashboards_divPanelSoporteTenico')											=> "hidden",
		strtolower('globalpro_core_dashboards_divPanelFormaPago')												=> "hidden",
		strtolower('globalpro_core_dashboards_divPanelInfoPago')												=> "hidden",
		strtolower('globalpro_core_dashboards_divPanelUsuario')													=> "hidden",
									
									
		strtolower('globalpro_app_invoice_billing_panelResumenFacturaTool')										=> "",
		strtolower('globalpro_app_invoice_billing_panelResumenFactura')											=> "hidden",
		strtolower('globalpro_app_invoice_billing_divTxtCambio') 												=> "hidden",
		strtolower('globalpro_app_invoice_billing_divTxtMoneda') 			 									=> "hidden",		
		strtolower('globalpro_app_invoice_billing_divTxtCliente2') 			 									=> "hidden",
		strtolower('globalpro_app_invoice_billing_divTxtCedula2') 												=> "hidden",
									
									
									
		strtolower('globalpro_app_inventory_item_divTxtBarCode') 												=> "hidden",
		strtolower('globalpro_app_inventory_item_divTxtPerecedero') 			 								=> "hidden",		
		strtolower('globalpro_app_inventory_item_divTxtCapacidad') 			 									=> "hidden",
		strtolower('globalpro_app_inventory_item_divTxtCantidadMinima') 										=> "hidden",
		strtolower('globalpro_app_inventory_item_divTxtCantidadMaxima') 										=> "hidden",
		strtolower('globalpro_app_inventory_item_divTxtSKUCompras') 			 								=> "hidden",
		strtolower('globalpro_app_inventory_item_divTxtSKUProduccion') 											=> "hidden",
		strtolower('globalpro_app_inventory_item_divTxtEstado') 			 									=> "hidden",
		strtolower('globalpro_app_inventory_item_divTxtFamilia') 			 									=> "hidden",
		strtolower('globalpro_app_inventory_item_divTxtUM') 			 										=> "hidden",
		strtolower('globalpro_app_inventory_item_divTxtPresentacion') 			 								=> "hidden",
		strtolower('globalpro_app_inventory_item_divTxtPresentacionUM') 										=> "hidden",
		strtolower('globalpro_app_inventory_item_divTxtUM') 			 										=> "hidden",	
		
		
		strtolower('globalpro_app_cxc_customer_divTxtNombres') 													=> "hidden",
		strtolower('globalpro_app_cxc_customer_divTxtApellidos') 			 									=> "hidden",		
		strtolower('globalpro_app_cxc_customer_divTxtNombreComercial') 			 								=> "hidden",
		strtolower('globalpro_app_cxc_customer_divTxtEstado') 													=> "hidden",
		strtolower('globalpro_app_cxc_customer_divTxtClasificacion') 											=> "hidden",
		strtolower('globalpro_app_cxc_customer_divTxtTipo') 			 										=> "hidden",
		strtolower('globalpro_app_cxc_customer_divTxtCategoria') 												=> "hidden",
		strtolower('globalpro_app_cxc_customer_divTxtSubCategoria') 			 								=> "hidden",
		strtolower('globalpro_app_cxc_customer_divTxtEstadoCivil') 			 									=> "hidden",
		strtolower('globalpro_app_cxc_customer_divTxtProfesionUFicio') 			 								=> "hidden",
		strtolower('globalpro_app_box_share_labelReference1')			 										=> "Atiende",		
		strtolower('globalpro_app_inventory_transferoutput_parameterValidarEnvioDestino')						=> "true",
		strtolower('globalpro_app_inventory_transferoutput_labelReference1')									=> "Orden / Cliente",
		strtolower('globalpro_app_purchase_pedidos_divDllEstado')												=> "hidden",
		strtolower('globalpro_app_purchase_pedidos_divScriptCustom')											=> "
			<script>
			$(document).ready(function(){ 		
				var nickname 	= $('#header > nav > a > span' ).text().replace('(', '');
				nickname 		= nickname.replace('(', '');
				nickname 		= nickname.replace(')', '');
				nickname 		= nickname.replace(':', '');
				nickname 		= nickname.replace(' ', '');
				nickname 		= nickname.replace('usuario', '');
				
				if( !(nickname  == 'gabriel' || nickname  == 'superadmin')  )
				{
					$('#txtTMInfoDetailReference2').parent().parent().addClass('hidden');
				}
				
			}); 
			</script>
		",
		strtolower('globalpro_app_cxc_customer_divScriptCustom') 												=> "
			<script>
			$(document).ready(function(){ 		
				$('#txtSexoID').val(''); 
				$('#txtSexoID').trigger('change'); 							 
				$(document).on('focusout','#txtLegalName',function(){ 									
					var varLegalName 	= $('#txtLegalName').val(); 
					$('#txtFirstName').val(  varLegalName  ); 
					$('#txtLastName').val(  varLegalName  ); 
					$('#txtCommercialName').val(  varLegalName  ); 	 
				}); 
			}); 
			</script> ",
		strtolower('globalpro_app_inventory_item_divTraslateElementTablePrecio') 								=> "<script>$(document).ready(function(){       $('#btnPrice').parent().remove(); $('#tblPrecios').appendTo('#divContainerRowPersonalization');  });</script>",
		strtolower('globalpro_app_invoice_billing_divTraslateElement') 											=> "<script>$(document).ready(function(){		$('#divVendedor').appendTo('#divInformacionLeft');$('#divBodega').appendTo('#divInformacionLeft');});</script>",		
		strtolower('globalpro_app_box_share_new_script_validate_reference1')									=> "/*Validar Atiende*/ if($('#txtReference1').val() == ''){fnShowNotification('Escriba quien le atiende','error',timerNotification);result = false;}",		
		strtolower('globalpro_app_inventory_inputunpost_new_script_validate_reference1')						=> "/*Validar Referecia 1*/ if($('#txtReference1').val() == ''){fnShowNotification('Escriba Referencia 1 es obligatoria','error',timerNotification);result = false;}",
		strtolower('globalpro_app_purchase_taller_divTxtApplied')												=> "hidden",
		strtolower('globalpro_app_purchase_taller_divTxtChange')												=> "hidden",
		strtolower('globalpro_app_purchase_taller_divTxtStatus')												=> "hidden",
		strtolower('globalpro_app_purchase_taller_divTxtCurrency')												=> "hidden",
		strtolower('globalpro_app_purchase_taller_scriptValidateForm')											=> "	


		if( $('#txtAreaID').val() == 799 /*entregado*/ )
		{
			if($('#txtDetailAmount').val() == '0'){
				fnShowNotification('El monto no puede ser 0','error',timerNotification);
				result = false;
			}					

			if($('#txtNote').val() == ''){
				fnShowNotification('Seleccionar factura','error',timerNotification);
				result = false;
			}
		}

		
		if( $('#txtRouteID').val() == 794 /*otros*/ && $('#txtReferenceClientName').val() == ''  )
		{
			fnShowNotification('Describir descripción de (otros)','error',timerNotification);
			result = false;
		}
		",
		strtolower('globalpro_app_purchase_garantia_divPanelAplicado') 												=> "hidden",
		strtolower('globalpro_app_purchase_garantia_divPanelCambio') 												=> "hidden",
		strtolower('globalpro_app_purchase_garantia_divPanelEstado') 												=> "hidden",
		strtolower('globalpro_app_purchase_garantia_divPanelMoneda') 												=> "hidden",
		strtolower('globalpro_app_purchase_garantia_divPanelMonto') 												=> "hidden",
					
					
		/*Ferreteria Mateo*/			
		strtolower('ferreteria_mateo_app_invoice_billing_bodyListInvoice')	 									=> "height: 550px; overflow: scroll;",
		strtolower('ferreteria_mateo_app_box_share_stylePage')	 												=> "/*posMe stylePage*/ #content .row{ margin-bottom:0px !important; } .email-bar{ margin-bottom:0px !important; } .form-group{ margin-bottom:0px !important; } .si_ferreteria_mateo{ display:none !important; } ",
					
		/*Clinica larreynaga*/			
		strtolower('clinicalarreynaga_web_tools_report_helper_Edad') 											=> "Genero", 		
		strtolower('clinicalarreynaga_web_tools_report_helper_Sexo') 											=> "Sexo", 		
		strtolower('clinicalarreynaga_app_cxc_customer_divTxtNombres') 											=> "hidden",
		strtolower('clinicalarreynaga_app_cxc_customer_divTxtApellidos') 			 							=> "hidden",		
		strtolower('clinicalarreynaga_app_cxc_customer_divTxtNombreComercial') 			 						=> "hidden",
		strtolower('clinicalarreynaga_app_cxc_customer_divTxtEstado') 											=> "hidden",
		strtolower('clinicalarreynaga_app_cxc_customer_divTxtClasificacion') 									=> "hidden",
		strtolower('clinicalarreynaga_app_cxc_customer_divTxtTipo') 			 								=> "hidden",
		strtolower('clinicalarreynaga_app_cxc_customer_divTxtCategoria') 										=> "hidden",
		strtolower('clinicalarreynaga_app_cxc_customer_divTxtSubCategoria') 			 						=> "hidden",
		strtolower('clinicalarreynaga_app_cxc_customer_divTxtEstadoCivil') 			 							=> "hidden",
		strtolower('clinicalarreynaga_app_cxc_customer_divTxtProfesionUFicio') 			 						=> "hidden",
		strtolower('clinicalarreynaga_app_cxc_customer_divScriptCustom') 										=> "<script> $(document).ready(function(){ $(document).on('focusout','#txtLegalName',function(){ var varLegalName 	= $('#txtLegalName').val(); $('#txtFirstName').val(  varLegalName  ); $('#txtLastName').val(  varLegalName  ); $('#txtCommercialName').val(  varLegalName  ); $('#txtIdentification').val(  'ND'  ); });});</script>",
					
					
					
		/*Chec extensiones*/			
		strtolower('chicextensiones_app_invoice_billing_divTxtCedula2') 										=> "hidden", 		
		strtolower('chicextensiones_app_invoice_billing_divTraslateElement') 									=> "<script>$(document).ready(function(){ /*quitar el atributo de oculto*/  $('#divTxtElementoDisponibleParaMover1').removeClass('hidden'); /*pasar divZone pasar a divTxtElementoDisponibleParaMover1*/ $('#divZone').appendTo('#divTxtElementoDisponibleParaMover1');	}); </script> ", 		
					
		/*Exceso*/			
		strtolower('exceso_app_inventory_item_labelBarCode')													=> "Barra / IMAI", 
		strtolower('exceso_app_inventory_item_divTxtCapacidad') 			 									=> "hidden", 
		strtolower('exceso_app_inventory_item_divTxtCantidadMinima') 											=> "hidden", 
		strtolower('exceso_app_inventory_item_divTxtCantidadMaxima') 											=> "hidden", 
		strtolower('exceso_app_inventory_item_divTxtSKUCompras') 			 									=> "hidden", 
		strtolower('exceso_app_inventory_item_divTxtSKUProduccion') 											=> "hidden", 
		strtolower('exceso_app_inventory_item_divTxtEstado') 			 										=> "hidden", 
		strtolower('exceso_app_inventory_item_divTxtFamilia') 			 										=> "hidden", 
		strtolower('exceso_app_inventory_item_divTxtUM') 			 											=> "hidden", 
		strtolower('exceso_app_inventory_item_divTxtPresentacion') 			 									=> "hidden", 
		strtolower('exceso_app_inventory_item_divTxtPresentacionUM') 											=> "hidden", 
		
		
		
		
		/*KHADASH*/
		strtolower('khadash_app_box_share_divResumenAbono')			 											=> "hidden", 
		strtolower('khadash_app_box_share_divStart')			 												=> "hidden", 
		strtolower('khadash_app_box_share_divFecha')			 												=> "hidden", 
		strtolower('khadash_app_box_share_divAplicado')			 												=> "hidden", 
		strtolower('khadash_app_box_share_divCambio')			 												=> "hidden", 
		strtolower('khadash_app_box_share_comboStyle')			 												=> "", 
		strtolower('khadash_app_box_share_javscriptVariable_varShareMountDefaultOfAmortization')				=> "false", 
		strtolower('khadash_app_box_share_TableColumnDocumento')												=> "hidden", 
		strtolower('khadash_app_box_share_btnVerMovimientos')													=> "hidden", 
		strtolower('khadash_app_box_share_javscriptVariable_varPrinterOnlyFormat')								=> "true", 
		strtolower('khadash_app_box_share_divCustomerControlBuscar')											=> "", 
		strtolower('khadash_app_box_share_divCustomerControlSelected')											=> "hidden", 
		strtolower('khadash_app_box_share_divCobrador')															=> "hidden", 
		strtolower('khadash_app_box_share_divMoneda')															=> "hidden", 
				
		/*El patio*/
		strtolower('patio_app_invoice_billing_divTraslateElement') 											=> "
		<script>
			$(document).ready(function(){		
				$('#divVendedor').appendTo('#divInformacionLeft');
				$('#divMesa').appendTo('#divInformacionLeft');
			});
		</script>",		
		
		/*Santa lucia ral state*/	
		strtolower('luciaralstate_core_web_menu_PRODUCTO')			 											=> "INMOBILIARIO",		
		strtolower('luciaralstate_default_masterpage_backgroundImage')		 									=> "style='background-image: url(".  base_url()."/resource/img/logos/fondo_globalpro.jpg"   .");'",		
		strtolower('luciaralstate_app_cxc_customer_Clasificacion')												=> "Estilo de propiedad",
		strtolower('luciaralstate_app_cxc_customer_Categoria')													=> "Interes",
		strtolower('luciaralstate_app_cxc_customer_Referencia1')												=> "Id encuentra 24",
		strtolower('luciaralstate_app_cxc_customer_Referencia2')												=> "Mensaje",
		strtolower('luciaralstate_app_cxc_customer_Referencia3')												=> "Comentario 1",
		strtolower('luciaralstate_app_cxc_customer_Referencia4')												=> "Comentario 2",
		strtolower('luciaralstate_app_cxc_customer_Referencia5')												=> "Ubicacion",
		strtolower('luciaralstate_app_cxc_customer_txtDomicilio')												=> "Ubicacion de interes",
		
		strtolower('luciaralstate_app_inventory_item_*Familia')													=> "Tipo de propiedad",
		strtolower('luciaralstate_app_inventory_item_*Presentacion')											=> "Proposito",
		strtolower('luciaralstate_app_inventory_item_Perecedero')												=> "Amueblado",
		strtolower('luciaralstate_app_inventory_item_*UM. Presentacion')										=> "Disponible",
		strtolower('luciaralstate_app_inventory_item_*SKU Compras')												=> "Baños",
		strtolower('luciaralstate_app_inventory_item_*SKU Produccion')											=> "Habitaciones",
		strtolower('luciaralstate_app_inventory_item_*Cantidad Minima')											=> "Niveles",
		strtolower('luciaralstate_app_inventory_item_*Cantidad Maxima')											=> "Horas antes de visita",
		strtolower('luciaralstate_app_inventory_item_Servicio')													=> "Disponible",				
		strtolower('luciaralstate_app_inventory_item_fieldInmobiliariaPais')									=> "hidden",		
		strtolower('luciaralstate_app_inventory_item_fieldInmobiliariaDepartamento')							=> "hidden",		
		strtolower('luciaralstate_app_inventory_item_fieldInmobiliariaMunicipio')								=> "hidden",
		strtolower('luciaralstate_app_inventory_item_*Categoria')												=> "Diseño de propiedad",
		strtolower('luciaralstate_app_inventory_item_*Capacidad')												=> "Aires",		
		strtolower('luciaralstate_app_inventory_item_Marca')													=> "Area de contruccion M2",
		strtolower('luciaralstate_app_inventory_item_Modelo')													=> "Area de terreno VR2",
		strtolower('luciaralstate_app_inventory_item_Serie ó MAI')												=> "ID encuentra 24",		
		strtolower('luciaralstate_app_inventory_item_labelBarCode')												=> "ID página web",
		strtolower('luciaralstate_app_inventory_item_Descripcion')			 									=> "Direccion",
		strtolower('luciaralstate_app_inventory_item_label_price_PUBLICO')			 							=> "PRECIO DE VENTA",
		strtolower('luciaralstate_app_inventory_item_label_price_POR MAYOR')			 						=> "PRECIO DE RENTA",
		strtolower('luciaralstate_app_inventory_item_label_price_CREDITO')			 							=> "----",
		strtolower('luciaralstate_app_inventory_item_label_price_CREDITO POR MAYOR')			 				=> "----",
		strtolower('luciaralstate_app_inventory_item_label_price_ESPECIAL')			 							=> "----",
		strtolower('luciaralstate_app_cxc_customer_divTxtNombres')												=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtApellidos')											=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtNombreComercial')										=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtTypeIdentification')									=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtIdentification')										=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtSubCategoria')											=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtEstadoCivil')											=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtProfesionUFicio')										=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtTypeFirmID')											=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divPestanaCXC') 	 											=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divPestanaCXCLineas') 	 									=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtFechaContacto') 	 									=> "",
		strtolower('luciaralstate_app_cxc_customer_divTxtFechaNacimiento') 	 									=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divPestanaTelefono') 	 									=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtBuro') 	 											=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtHuella') 	 											=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divPestanaMas') 	 											=> "hidden",		
		strtolower('luciaralstate_app_cxc_customer_divTxtPais') 	 											=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtDepartamento') 	 									=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtMunicipio') 	 										=> "hidden",
		strtolower('luciaralstate_app_cxc_customer_divTxtFormContact') 	 										=> "",		
		strtolower('luciaralstate_app_inventory_item_divTraslate') 												=> "
		<script>
			$(document).ready(function(){				 
				$('#txtRealStateStyleKitchen').parent().parent().appendTo('#divTraslateQuantityMax');  
				$('#txtQuantityMax').parent().parent().appendTo('#divTraslateElemento1');  
			});
		</script>",		
		
		strtolower('luciaralstate_app_inventory_item_scriptValidate') 											=> "		
		//Validar que el campo sea solo numero
		var regexOnlyNumber = /^[0-9]+$/;		
		if (!regexOnlyNumber.test($('#txtBarCode').val())) 
		{
			fnShowNotification('El campo (ID página web) solo puede contener numeros ','error',timerNotification);
			result = false;
		}
		
		//Validar que el campo sea solo numero
		var regexOnlyNumber = /^[0-9]+$/;		
		if (!regexOnlyNumber.test($('#txtRealStatePhone').val())) 
		{
			fnShowNotification('El campo Telefono solo puede contener numeros ','error',timerNotification);
			result = false;
		}
		
		//Validar que el campo sea solo numero		
		var regexOnlyNumber = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
		if (!regexOnlyNumber.test($('#txtRealStateEmail').val())) 
		{
			fnShowNotification('El campo Email no es valido','error',timerNotification);
			result = false;
		}
		
		
		//Validar que sea un enlace correcto
		var regexOnlyNumber = /^(ftp|http|https):\/\/[^ \"]+$/;
		if (!regexOnlyNumber.test($('#txtRealStateLinkPaginaWeb').val())) 
		{
			fnShowNotification('El campo (Página web) debe ser un enlace válido.','error',timerNotification);
			result = false;
		}
		
		
		//Validar el precio de renta o precio de venta la suma no pueden dar 0
		var totalPrecio = 0;
		for(var il = 0 ; il < 2 ; il++)
		{
			var rowx = $($($('#body_detail_precio tr')[il]).find('td')[1]).find('input')[0];
			rowx = $(rowx).val();
			totalPrecio = totalPrecio + parseInt(rowx);
			
		}
		
		if(totalPrecio <= 1)
		{
			fnShowNotification('Precio y renta no pude ser menor a 0','error',timerNotification);
			result = false;
		}
		
		//Obtener precio de venta 
		var precioVenta = 0;
		for(var il = 0 ; il < 1 ; il++)
		{
			var rowx = $($($('#body_detail_precio tr')[il]).find('td')[1]).find('input')[0];
			rowx = $(rowx).val();
			precioVenta = precioVenta + parseInt(rowx);
			
		}
		
		
		//Obtener precio de renta 
		var precioRenta = 0;
		for(var il = 1 ; il < 2 ; il++)
		{
			var rowx = $($($('#body_detail_precio tr')[il]).find('td')[1]).find('input')[0];
			rowx = $(rowx).val();
			precioRenta = precioRenta + parseInt(rowx);
			
		}
		
		if(precioRenta == precioVenta)
		{
			fnShowNotification('Precio y Renta no pueden ser iguales','error',timerNotification);
			result = false;
		}
		
		
		",		
		
		strtolower('luciaralstate_app_cxc_customer_divScriptCustom') 											=> "
		<script>
		$(document).ready(function(){ 
			$('#txtIdentification').val('0');				
			
			//$('#divTxtLeadsSubTipo').insertAfter('#divTxtCategoryE');
			//$('#lblLeadSubTipoLeads').addClass('col-lg-4');
			//$('#lblLeadSubTipoLeads').addClass('control-label');																															
			//$('#s2id_txtLeadSubTipo').wrap('<div class=\"col-lg-8\"></div>');
			
			
			$(document).on('focusout','#txtLegalName',function(){ 									 
				var varLegalName 	= $('#txtLegalName').val(); 
				$('#txtFirstName').val(varLegalName  ); 
				$('#txtLastName').val(varLegalName  ); 
				$('#txtCommercialName').val(varLegalName); 	 
			}); 
			
			
		}); 
		</script> 
		",
		strtolower('luciaralstate_app_cxc_customer_divScriptValideFunction') 	 								=> "
		
		if( $('#txtPhoneNumber').val()  == ''){
			fnShowNotification('Escribir Telefono','error',timerNotification);
			result = false;
		}
		
		if(!/^\d+$/.test(   $('#txtPhoneNumber').val()   )){
            fnShowNotification('Escribir Telefono solo puede tener números','error',timerNotification);
			result = false;
        } 
		
		if( $('#txtReference1').val()  == ''){
			fnShowNotification('Escribir ID','error',timerNotification);
			result = false;
		}
		
		if(!/^\d+$/.test(   $('#txtReference1').val()   )){
            fnShowNotification('Escribir ID Encuentra 24 solo puede tener números','error',timerNotification);
			result = false;
        } 
		
		
		",	
		strtolower('luciaralstate_app_cxc_customer_divScriptReady') 	 										=> "		
		$('#txtLegalName').on('blur', function() 
		{
			
			var regex = /^[A-Za-z]+$/;
			var regex = regex.test($(this).val());
	
			if (!regex) {
				$(this).focus(); // Volver a enfocar el campo de entrada
				$(this).trigger('input'); // Disparar el evento de entrada para validar la entrada
			}
		});

		$('#txtPhoneNumber').on('blur', function() 
		{
			
			var regex = /^[\d()+\-]+$/;
			var regex = regex.test($(this).val());
	
			if (!regex) {
				$(this).focus(); // Volver a enfocar el campo de entrada
				$(this).trigger('input'); // Disparar el evento de entrada para validar la entrada
				fnShowNotification('Escribir Telefono solo puede tener números','error',5000);
			}
		});
		
		$('#txtReference1').on('blur', function() 
		{
			
			var regex = /^[0-9]+$/;
			var regex = regex.test($(this).val());
	
			if (!regex) {
				$(this).focus(); // Volver a enfocar el campo de entrada
				$(this).trigger('input'); // Disparar el evento de entrada para validar la entrada
				fnShowNotification('Escribir ID Encuentra 24 solo puede tener números','error',5000);
			}
		});
		
		
		
		
		",	
		strtolower('luciaralstate_app_inventory_item_divTxtEstado') 			 								=> "hidden",
		strtolower('luciaralstate_app_inventory_item_divTxtUM') 			 									=> "hidden",
		strtolower('luciaralstate_app_inventory_item_divTxtPresentacionUM')  									=> "hidden",
		strtolower('luciaralstate_app_inventory_item_divTxtBodega') 		 									=> "hidden",
		strtolower('luciaralstate_app_inventory_item_divTxtCantidad') 	 										=> "hidden",
		strtolower('luciaralstate_app_inventory_item_divTxtCosto') 		 										=> "hidden",
		strtolower('luciaralstate_app_inventory_item_divTxtCantidadZero')  										=> "hidden",
		strtolower('luciaralstate_app_inventory_item_divTxtFacturable') 	 									=> "hidden",
		strtolower('luciaralstate_app_inventory_item_menuBodegaPestana') 	 									=> "hidden",
		strtolower('luciaralstate_app_inventory_item_menuBodegaPestana') 	 									=> "hidden",
		strtolower('luciaralstate_app_inventory_item_divControlCreatedOn') 	 									=> "",
		strtolower('luciaralstate_app_inventory_item_divControlModifiedOn') 	 								=> "",	
		strtolower('luciaralstate_app_inventory_item_fieldExclusiveGerencia') 	 								=> "",	
		
				
		/*El blue moon*/
		strtolower('bluemoon_app_invoice_billing_divTraslateElement') 											=> "
		<script>
			$(document).ready(function(){		
				$('#divVendedor').appendTo('#divInformacionLeft');
				$('#divMesa').appendTo('#divInformacionLeft');
				$('#divZone').appendTo('#divInformacionRight');				
			});
		</script>",
		
		//Veterinaria la Bendicion
		strtolower('veterinaria_bendicion_app_inventory_item_Marca')											=> "Vencimiento",
		strtolower('veterinaria_bendicion_app_inventory_item_fieldInmobiliaria')								=> "hidden",
		strtolower('veterinaria_bendicion_app_inventory_item_fieldEquiposModelo')								=> "hidden",
		strtolower('veterinaria_bendicion_app_inventory_item_fieldEquiposSerie')								=> "hidden",		
		strtolower('veterinaria_bendicion_app_inventory_item_divTraslate') 										=> "
		<script>
			$(document).ready(function(){				 
				$('#txtReference1').parent().parent().appendTo('#divTraslateElemento2');  
			});
		</script>",	
		strtolower('veterinaria_bendicion_app_inventory_item_scriptValidate') 									=> "		
		//Validar fecha de vencimiento
		var regexOnlyNumber =  /^\d{4}-\d{2}-\d{2}$/;
		if (!regexOnlyNumber.test($('#txtReference1').val())) 
		{
			fnShowNotification('Fecha de vencimiento debe tener el formato YYYY-MM-DD','error',timerNotification);
			result = false;
		}
		
		",	
		
		/*Galmcuts*/
		strtolower('galmcuts_app_invoice_billing_txtTraductionVendedor') 		 									=> "Barvero",
		strtolower('galmcuts_app_invoice_billing_txtTraductionMesa') 		 										=> "Sala",
		strtolower('galmcuts_app_invoice_billing_divLabelZone') 		 											=> "Hora",
		strtolower('galmcuts_app_invoice_billing_divMesa') 		 													=> "hidden",
		strtolower('galmcuts_app_invoice_billing_divTraslateElement') 												=> "
		<script>
			$(document).ready(function(){	
				debugger;
				var tivReferencia 	   = $('#divReferencia').html();
				var tivSiguienteVisita = $('#divSiguienteVisita').html();
				
                $('#divReferencia').html(tivSiguienteVisita);
                $('#divSiguienteVisita').html(tivReferencia);
				
				if( 
					$('#txtUserID').val() == '494' || 
					$('#txtUserID').val() == '495' || 
					$('#txtUserID').val() == '496' 
				)
				{
					$($('.btnAcept')[0]).addClass('hidden');
				}
				
			});
		</script>",
		
		/*Titanes*/
		strtolower('titanes_core_web_menu_CXP')			 															=> "PROVEEDORES",
		
		//Funeraria Blandon
		strtolower('fn_blandon_core_web_menu_FACTURACION')			 												=> "CONTRATOS",
		strtolower('fn_blandon_core_web_menu_FACTURAR')			 													=> "CONTRATO",
		strtolower('fn_blandon_app_invoice_billing_divLabelZone') 		 											=> "Parentesco",
		strtolower('fn_blandon_app_invoice_billing_txtTraductionPhone')	 											=> "Tel. Bene.",
		strtolower('fn_blandon_app_invoice_billing_divTxtClienteBeneficiario')	 									=> "Bene. Nombre",
		strtolower('fn_blandon_app_invoice_billing_divTxtCedulaBeneficiario')	 									=> "Bene. Cedula",		
		strtolower('fn_blandon_app_invoice_billing_labelTitlePageList')	 											=> "CONTRATOS",
		strtolower('fn_blandon_app_invoice_billing_labelTitlePageEdit')	 											=> "Contrato",
		strtolower('fn_blandon_app_invoice_billing_labelTitlePageNew')	 											=> "Contrato",		
		strtolower('fn_blandon_app_invoice_billing_divHiddenReference')	 											=> "hidden",
		strtolower('fn_blandon_app_invoice_billing_divMesa')	 													=> "hidden",
		strtolower('fn_blandon_app_invoice_billing_divNextVisitHidden')	 											=> "hidden",
		strtolower('fn_blandon_app_invoice_billing_divBodegaHidden')	 											=> "hidden",
		strtolower('fn_blandon_app_invoice_billing_divTxtCambio')	 												=> "hidden",
		strtolower('fn_blandon_app_invoice_billing_divPrecio')	 													=> "hidden",
		strtolower('fn_blandon_app_invoice_billing_divDesembolsoEfectivo')	 										=> "hidden",
		strtolower('fn_blandon_app_invoice_billing_divReportSinRiesgo')												=> "hidden",
		strtolower('fn_blandon_app_invoice_billing_divProviderCredit')												=> "hidden",
		strtolower('fn_blandon_app_invoice_billing_divApplied')														=> "hidden",
		
		
		strtolower('fn_blandon_app_invoice_billing_txtTermReference')	 											=> "Plazo",
		strtolower('fn_blandon_app_invoice_billing_txtTraductionExpenseLabel')										=> "Interes",
		strtolower('fn_blandon_app_invoice_billing_divTraslateElement') 											=> "
		<script>
			$(document).ready(function(){		
				
				if( $('#txtTransactionMasterID').val() == undefined )
				$('#txtNote').val('');
			
				$('#divBeneficiario').appendTo('#divInformacionLeftReference');
				$('#divCedula').appendTo('#divInformacionLeftReference');
				$('#divZone').appendTo('#divInformacionLeftReference');
				$('#divTrasuctionPhone').appendTo('#divInformacionLeftReference');
				$('#divFixedExpenses').appendTo('#divInformacionRightReference');
				$('#divNote').appendTo('#divInformacionLeftZone');
				
			});
		</script>
		",		
		strtolower('fn_blandon_app_invoice_billing_scriptValidateInCredit')											=> "
		if($('#txtReferenceClientIdentifier').val() == '')
		{
				fnShowNotification('Cedula del beneficiario','error',timerNotification);
				result = false;
				fnWaitClose();
		}
		if($('#txtReferenceClientName').val() == '')
		{
				fnShowNotification('Nombre del beneficiario','error',timerNotification);
				result = false;
				fnWaitClose();
		}
		if($('#txtNumberPhone').val() == '')
		{
				fnShowNotification('Telefono del beneficiario','error',timerNotification);
				result = false;
				fnWaitClose();
		}		
		if($('#txtReference2').val() == '1')
		{
				fnShowNotification('Plazo del credito','error',timerNotification);
				result = false;
				fnWaitClose();
		}
		if($('#txtEmployeeID').val() == '614')
		{
				fnShowNotification('Vendedor','error',timerNotification);
				result = false;
				fnWaitClose();
		}
		
		",
		
	);
	
	
	//Comanda traducir es para los menu
	//comportamiento del controlador
	//si el key no existe regresa valor vacio
	if($key_controller != "core_web_menu")
	{		
		$key = strtolower($type_company)."_".strtolower($key_controller)."_".strtolower($key_element);
		if(!array_key_exists( $key, $divs) )
		{
			
			//si el key no existe, buscar el key para la empresa por defecto
			$key = strtolower("default")."_".strtolower($key_controller)."_".strtolower($key_element);
			if(!array_key_exists( $key, $divs) )
			{	
				return $default_value;
			}
			else 
			{
				return $divs[$key];
			}
			
		}
		else 
		{
			//si el key exite buscar el valor del key
			return $divs[$key];
		}
		
	}
	//Menu
	//Si el key no existe regrea el mismo valor
	else 
	{
		//lenguaje		
		$key = strtolower($type_company)."_".strtolower($key_controller)."_".strtolower($key_element);
		if(!array_key_exists( $key, $divs) )
		{
			//si el key no existe regrear el elemento
			return $key_element;
		}
		else 
		{
			//si el key existe , retornar valor
			return $divs[$key];
		}
	}
		
}

?>