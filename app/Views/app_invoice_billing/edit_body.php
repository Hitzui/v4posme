<div 
	class="isloading-overlay"
	id="divLoandingCustom"
	style="position:fixed; left:0; top:0; z-index: 10000; background: rgba(0,0,0,0.9); width: 100%; height: 1090px;"	
>
	<span class="isloading-wrapper  isloading-show  isloading-overlay">espere un momento ...  
		<i class="icon-refresh icon-spin">
		</i>
	</span>
</div>

<div class="row"> 
	<div id="email" class="col-lg-12">
	
		<!-- botonera -->
		<!--
		<div class="email-bar" style="border-left:1px solid #c9c9c9">                                
			<div class="btn-group pull-right">                                    
				<a href="<?php echo base_url(); ?>/app_invoice_billing/add" class="btn btn-info" id="btnNew"><i class="icon16 i-checkmark-4"></i> Nueva</a>
				<a href="<?php echo base_url(); ?>/app_invoice_billing/index" id="btnBack" class="btn btn-inverse" ><i class="icon16 i-rotate"></i> Atras</a>                                    
				<a href="#" class="btn btn-danger" id="btnDelete"><i class="icon16 i-remove"></i> Eliminar</a>									
				<a href="#" class="btn btn-primary" id="btnPrinter"><i class="icon16 i-print"></i> Imprimir</a>
				<a href="#" class="btn btn-success" id="btnAcept"><i class="icon16 i-checkmark-4"></i> Guardar</a>
				
			</div>
		</div> 
		-->
		<!-- /botonera -->
	</div>
	<!-- End #email  -->
</div>
<!-- End .row-fluid  -->
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
					
			<!-- titulo de comprobante-->
			<div class="panel-heading">
					<div class="icon"><i class="icon20 i-file"></i></div> 
					<h4>FACTURA:#<span class="invoice-num"><?php echo $objTransactionMaster->transactionNumber; ?></span></h4>
			</div>
			<!-- /titulo de comprobante-->
			
			<!-- body -->	
			<form id="form-new-invoice" name="form-new-invoice" class="form-horizontal" role="form">
			<div class="panel-body printArea"> 
			
				<ul id="myTab" class="nav nav-tabs">
					<li class="active">
						<a href="#home" data-toggle="tab">Informacion</a>
					</li>
					<li class="elementMovilOculto">
						<a href="#profile" data-toggle="tab">Referencias.</a>
					</li>
					<li>
						<a href="#credit" data-toggle="tab">Info de Credito.</a>
					</li>
					<li class="dropdown elementMovilOculto">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Mas <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="#dropdown" data-toggle="tab">Comentario</a></li>
							<li><a id="btnClickArchivo" href="#" target="blanck"  data-toggle="tab">Archivos</a></li>
						 </ul>
					</li>
				</ul>
				
				<div class="tab-content">
					<div class="tab-pane fade in active" id="home">	
						<div class="row">										
						<div class="col-lg-6" id="divInformacionLeft" >
							
								<input type="hidden" name="txtCompanyID" value="<?php echo $objTransactionMaster->companyID; ?>">
								<input type="hidden" name="txtTransactionID" value="<?php echo $objTransactionMaster->transactionID; ?>">
								<input type="hidden" name="txtTransactionMasterID" value="<?php echo $objTransactionMaster->transactionMasterID; ?>">
								
								
								<div class="form-group">
									<label class="col-lg-4 control-label" for="datepicker">Fecha</label>
									<div class="col-lg-8">
										<div id="datepicker" class="input-group date" data-date-format="yyyy-mm-dd">
											<input size="16"  class="form-control" type="text" name="txtDate" id="txtDate" value="<?php echo $objTransactionMaster->transactionOn; ?>" >
											<span class="input-group-addon"><i class="icon16 i-calendar-4"></i></span>
										</div>
									</div>
								</div>
								
								
								<div class="form-group <?php echo getBehavio($company->type,"app_invoice_billing","divTxtCambio",""); ?> ">
										<label class="col-lg-4 control-label" for="normal">Cambio</label>
										<div class="col-lg-8">
											<input class="form-control"   type="text" disabled="disabled" name="txtExchangeRate" id="txtExchangeRate" value="<?php echo $exchangeRate; ?>">
										</div>
								</div>
								
								
								<div class="form-group">
									<label class="col-lg-4 control-label" for="normal">Descripcion</label>
									<div class="col-lg-8">										
										<input class="form-control"   type="text" name="txtNote" id="txtNote" value="<?php echo $objTransactionMaster->note; ?>">
									</div>
								</div>
								
								<div class="form-group <?php echo getBehavio($company->type,"app_invoice_billing","divTxtMoneda",""); ?> "  id="divMoneda" >
									<label class="col-lg-4 control-label" for="selectFilter">Moneda</label>
									<div class="col-lg-8">
										<select name="txtCurrencyID" id="txtCurrencyID" class="<?php echo ($useMobile == "1" ? "" : "select2"); ?>">
												<?php
												$count = 0;
												if($listCurrency)
												foreach($listCurrency as $currency){
													if($currency->currencyID == $objTransactionMaster->currencyID )
													echo "<option value='".$currency->currencyID."' selected >".$currency->name."</option>";
													else
													echo "<option value='".$currency->currencyID."'  >".$currency->name."</option>";
													$count++;
												}
												?>
										</select>
									</div>
								</div>
								
								
							
						</div>
						<div class="col-lg-6" id="divInformacionRight" >
						
								<div class="form-group">
									<label class="col-lg-4 control-label" for="buttons">Cliente</label>
									<div class="col-lg-8">
										<div class="input-group">
											<input type="hidden" id="txtCustomerID" name="txtCustomerID" value="<?php echo $objTransactionMaster->entityID;  ?>">
											<input class="form-control" readonly id="txtCustomerDescription" type="txtCustomerDescription" value="<?php echo $objNaturalDefault != null ? strtoupper($objCustomerDefault->customerNumber . " ". $objNaturalDefault->firstName . " ". $objNaturalDefault->lastName ) : strtoupper($objCustomerDefault->customerNumber." ".$objLegalDefault->comercialName); ?>">
											
											<span class="input-group-btn">
												<button class="btn btn-danger" type="button" id="btnClearCustomer">
													<i aria-hidden="true" class="i-undo-2"></i>
													clear
												</button>
											</span>
											<span class="input-group-btn">
												<button class="btn btn-primary" type="button" id="btnSearchCustomer">
													<i aria-hidden="true" class="i-search-5"></i>
													buscar
												</button>
											</span>
											<!--
											<span class="input-group-btn">
												<button class="btn btn-success" type="button" id="btnSearchCustomerNew">
													<i aria-hidden="true" class="i-plus"></i>
													nuevo
												</button>
											</span>
											-->
											
										</div>
									</div>
								</div>
								
								
								
								<div class="form-group  <?php echo getBehavio($company->type,"app_invoice_billing","divTxtCliente2",""); ?> ">
										<label class="col-lg-4 control-label" for="normal">Cliente</label>
										<div class="col-lg-8">
											<input class="form-control"   type="text" name="txtReferenceClientName" id="txtReferenceClientName" value="<?php echo $objTransactionMasterInfo->referenceClientName; ?>">
										</div>
								</div>
								
								<div id="divTxtElementoDisponibleParaMover1" class="hidden" >
									abc
								</div>
								
								<div class="form-group  <?php echo getBehavio($company->type,"app_invoice_billing","divTxtCedula2",""); ?>" id="divCedula" >
										<label class="col-lg-4 control-label" for="normal">Cedula</label>
										<div class="col-lg-8">
											<input class="form-control"   type="text" name="txtReferenceClientIdentifier" id="txtReferenceClientIdentifier" value="<?php echo $objTransactionMasterInfo->referenceClientIdentifier; ?>">
										</div>
								</div>
								
								<div class="form-group" id="divTipoFactura" >
									<label class="col-lg-4 control-label" for="selectFilter">Tipo</label>
									<div class="col-lg-8">
										<select name="txtCausalID" id="txtCausalID" class="<?php echo ($useMobile == "1" ? "" : "select2"); ?>">							
												<?php
												if($objCaudal)
												foreach($objCaudal as $causal){
													if($causal->transactionCausalID == $objTransactionMaster->transactionCausalID )
														echo "<option value='".$causal->transactionCausalID."' selected >".$causal->name."</option>";
													else
														echo "<option value='".$causal->transactionCausalID."'  >".$causal->name."</option>";
												}
												?>
										</select>
									</div>
								</div>
								
								

								<div class="form-group hidden" id="divLineaCredit">
									<label class="col-lg-4 control-label" for="selectFilter">Línea de Crédito</label>
									<div class="col-lg-8">
										<select name="txtCustomerCreditLineID" id="txtCustomerCreditLineID" class="<?php echo ($useMobile == "1" ? "" : "select2");  ?>" >
										</select>
									</div>
								</div>
								
								
								
								
							
						</div>
						</div>
					
						
					</div>
					<div class="tab-pane fade" id="profile">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group <?php echo getBehavio($company->type,"app_invoice_billing","divTxtZone",""); ?>" id="divZone"  >
									<label class="col-lg-4 control-label" for="selectFilter">Zona</label>
									<div class="col-lg-8">
										<select name="txtZoneID" id="txtZoneID" class="select2">
												<option></option>																
												<?php
												$count = 0;
												if($objListZone)
												foreach($objListZone as $z){
													if($z->catalogItemID  == $objTransactionMasterInfo->zoneID )
														echo "<option value='".$z->catalogItemID."' selected >".$z->display."</option>";
													else
														echo "<option value='".$z->catalogItemID."'  >".$z->display."</option>";
													$count++;
												}
												?>
										</select>
									</div>
								</div>
								
								
								
								<div class="form-group">
									<label class="col-lg-4 control-label" for="selectFilter">Precio</label>
									<div class="col-lg-8">
										<select name="txtTypePriceID" id="txtTypePriceID" class="select2">
												<option></option>																
												<?php
												$count = 0;
												if($objListTypePrice)
												foreach($objListTypePrice as $price){
													if($count == 0 )
													echo "<option value='".$price->catalogItemID."' selected >".$price->display."</option>";
													else
													echo "<option value='".$price->catalogItemID."'  >".$price->display."</option>";
													$count++;
												}
												?>
										</select>
									</div>
								</div>
								
								<div class="form-group"  id="divBodega"  >
									<label class="col-lg-4 control-label" for="selectFilter">Bodega</label>
									<div class="col-lg-8">
										<select name="txtWarehouseID" id="txtWarehouseID" class="select2">
												<option></option>																
												<?php
												$count = 0;
												if($objListWarehouse)
												foreach($objListWarehouse as $ware){
													if($objTransactionMaster->sourceWarehouseID == $ware->warehouseID)
													echo "<option value='".$ware->warehouseID."' selected >".$ware->name."</option>";
													else
													echo "<option value='".$ware->warehouseID."'  >".$ware->name."</option>";
													$count++;
												}
												?>
										</select>
									</div>
								</div>
								
								
							</div>
							<div class="col-lg-6">
							
								<div class="form-group">
										<label class="col-lg-4 control-label" for="normal">Referencia</label>
										<div class="col-lg-8">
											<input class="form-control"  type="text"  name="txtReference3" id="txtReference3" value="<?php echo $objTransactionMaster->reference3; ?>">												
										</div>
								</div>		
								
								<div class="form-group" id="divVendedor" >
										<label class="col-lg-4 control-label" for="selectFilter">Vendedor</label>
										<div class="col-lg-8">
											<select name="txtEmployeeID" id="txtEmployeeID" class="select2">
													<option></option>																
													<?php
													$count = 0;
													if($objListEmployee)
													foreach($objListEmployee as $employee){
														if($employee->entityID == $objTransactionMaster->entityIDSecondary)
														echo "<option value='".$employee->entityID."' selected >".$employee->firstName."</option>";
														else
														echo "<option value='".$employee->entityID."'  >".$employee->firstName."</option>";
														$count++;
													}
													?>
											</select>
										</div>
								</div>
								
								
								
								<div class="form-group">
										<label class="col-lg-4 control-label" for="normal">Telefono</label>
										<div class="col-lg-8">
											<input class="form-control"  type="text"  name="txtNumberPhone" id="txtNumberPhone" value="<?php echo $objTransactionMaster->numberPhone; ?>">												
										</div>
								</div>	
								
								
								
								<div class="form-group">
									<label class="col-lg-4 control-label" for="selectFilter">Mesa</label>
									<div class="col-lg-8">
										<select name="txtMesaID" id="txtMesaID" class="select2">
												<option></option>																
												<?php
												$count = 0;
												if($objListMesa)
												foreach($objListMesa as $ware){
													if($ware->catalogItemID == $objTransactionMasterInfo->mesaID )
													echo "<option value='".$ware->catalogItemID."' selected >".$ware->name."</option>";
													else
													echo "<option value='".$ware->catalogItemID."'  >".$ware->name."</option>";
													$count++;
												}
												?>
										</select>
									</div>
								</div>
								
								<div class="form-group">
									<label class="col-lg-4 control-label" for="datepicker">Siguiente Visita</label>
									<div class="col-lg-8">
										<div id="datepicker" class="input-group date" data-date-format="yyyy-mm-dd">
											<input size="16"  class="form-control" type="text" name="txtNextVisit" id="txtNextVisit" value="<?php echo $objTransactionMaster->nextVisit; ?>">
											<span class="input-group-addon"><i class="icon16 i-calendar-4"></i></span>
										</div>
									</div>
								</div>
								
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="credit">
						<div class="row">
								<div class="col-lg-6">
								
									<div class="form-group">
											<label class="col-lg-4 control-label" for="normal">Proveedor de Credito</label>
											<div class="col-lg-8">
												<!--
												<input class="form-control"  type="text"  name="txtReference1" id="txtReference1" value="">												
												-->
												<select name="txtReference1" id="txtReference1" class="select2">
														<option value="0"></option>		
														<?php
														if($listProvider)
														foreach($listProvider as $ws){
															if($ws->entityID == $objTransactionMaster->reference1)
																echo "<option value='".$ws->entityID."' selected>".$ws->firstName." ".$ws->lastName."</option>";
															else 
																echo "<option value='".$ws->entityID."' >".$ws->firstName." ".$ws->lastName."</option>";
														}
														?>
												</select>
											</div>
									</div>
									
									<div class="form-group">
											<label class="col-lg-4 control-label" for="normal">% De Gasto.</label>
											<div class="col-lg-8">
												<input class="form-control"   type="text" name="txtFixedExpenses" id="txtFixedExpenses" value="<?php echo helper_RequestGetValueObjet($objTransactionMasterDetailCredit,"reference1",0); ?>">
												<!--
												<a href="#" class="btn btn-primary  gap-right10" data-toggle="popover" data-placement="bottom" 
												data-content="Ejemplo: Del Interese de cada cuota, se multiplica por este % para saber de cuanto es la comision para FID-Local, este numero debe ser #0 o mayor que #1" title="" data-original-title="% de Gastos Fijo:">Ayuda:</a>
												-->
											</div>
									</div>
									
									<div class="form-group">
											<label class="col-lg-4 control-label" for="normal">Aplicado</label>
											<div class="col-lg-8">
												<input type="checkbox" disabled   name="txtIsApplied" id="txtIsApplied" value="1" <?php if($objTransactionMaster->isApplied) echo "checked"; ?> >
											</div>
									</div>
									
									
									<div class="form-group hide">
											<label class="col-lg-4 control-label" for="normal">Primer Linea del Protocolo.</label>
											<div class="col-lg-8">
												<input class="form-control"   type="text" name="txtLayFirstLineProtocolo" id="txtLayFirstLineProtocolo" value="<?php echo helper_RequestGetValueObjet($objTransactionMasterDetailCredit,"reference3",0); ?>">
												
												<a href="#" class="btn btn-primary  gap-right10" data-toggle="popover" data-placement="bottom" 
												data-content="Ejemplo: 5" title="" 
												data-original-title="Tenor:">Ayuda:</a>
												
												
											</div>
									</div>
									
									
								</div>
								<div class="col-lg-6">
								
									<div class="form-group">
										<label class="col-lg-4 control-label" for="datepicker">Primer Pago</label>
										<div class="col-lg-8">
											<div id="datepicker" class="input-group date" data-date-format="yyyy-mm-dd">
												<input size="16"  class="form-control" type="text" name="txtDateFirst" id="txtDateFirst" value="<?php echo $objTransactionMaster->transactionOn2; ?>" >
												<span class="input-group-addon"><i class="icon16 i-calendar-4"></i></span>
											</div>
										</div>
									</div>
									
									
									<div class="form-group">
											<label class="col-lg-4 control-label" for="normal">Plazo ó Referencia2</label>
											<div class="col-lg-8">
												<input class="form-control"  type="text"  name="txtReference2" id="txtReference2" value="<?php echo $objTransactionMaster->reference2; ?>">												
											</div>
									</div>	
									
									
									<div class="form-group">
											<label class="col-lg-4 control-label" for="normal">Frecuencia</label>
											<div class="col-lg-8">
												<!--
												<input class="form-control"  type="text"  name="txtReference1" id="txtReference1" value="">												
												-->
												<select name="txtPeriodPay" id="txtPeriodPay" class="select2">
														<option value="0"></option>		
														<?php
														$index = -1;
														if($objListPay)
														foreach($objListPay as $ws){
																$index = $index + 1;																
																if($ws->catalogItemID == $objTransactionMaster->periodPay)
																echo "<option value='".$ws->catalogItemID."' selected >".$ws->name."</option>";
																else 
																echo "<option value='".$ws->catalogItemID."' >".$ws->name."</option>";	
														}
														?>
												</select>
											</div>
									</div>
									
									
									
								</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								
								
								<div class="form-group">
										<label class="col-lg-4 control-label" for="normal"></label>
										<div class="col-lg-8">
											 <label class="label-change-switch" id="txtLabelIsDesembolsoEfectivo">Es un desembolso en efectivo?</label>
											 <br/>
											 <div class="switch" data-on="success" data-off="warning">
												<input class="toggle"controls-row type="checkbox" checked id="txtCheckDeEfectivo" />
											 </div>																
										</div>
								</div>
								
								<div class="form-group">
										<label class="col-lg-4 control-label" for="normal"></label>
										<div class="col-lg-8">
											
											<label class="label-change-switch" id="txtLabelIsReportSinRiesgo">Reportar a SinRiesgo</label>
											<br/>
											
											<div class="switch" data-on="success" data-off="warning">
												<?php
												if(helper_RequestGetValueObjet ($objTransactionMasterDetailCredit,"reference2",0) == 1){
												?>
													<input class="toggle"controls-row type="checkbox" checked id="txtCheckReportSinRiesgo" name="txtCheckReportSinRiesgo" value="1"  />																									
												<?php
												}
												else{
												?>
													<input class="toggle"controls-row type="checkbox"  id="txtCheckReportSinRiesgo" name="txtCheckReportSinRiesgo" value="1"  />
												<?php
												}
												?>
											</div>																
										</div>
										
								</div>
								
								
								
							</div>
							<div class="col-lg-6">
								
								
								
							</div>
						</div>
					</div>
					
					
					<div class="tab-pane fade" id="dropdown">
						
							
						
					</div>
					<div class="tab-pane fade" id="dropdown-file">
						
					</div>
				</div>    
		
				<br/>
				
				<div class="row">
					<div class="col-lg-12">
						<h3>Detalle:</h3>
						<table id="tb_transaction_master_detail" class="table table-bordered" >
							<thead>
							  <tr>
								<th></th>
								<th></th>
								<th></th>
								<th>Codigo</th>
								<th>Descripcion</th>
								<th>U/M</th>
								<th>Cantidad</th>
								<th>Precio</th>
								<th>Total</th>
								<th></th>
								<th>skuQuantityBySku</th>
								<th>unitaryPriceInvidual</th>
								<th>Accion</th>
								<th>skuFormatoDescription</th>
							  </tr>
							</thead>
							<tbody id="body_tb_transaction_master_detail">
							</tbody>
						</table>
						
					</div><!-- End .col-lg-12  --> 
				</div><!-- End .row-fluid  -->
				<input class="form-control"  type="hidden"  name="txtStatusIDOld" id="txtStatusIDOld" value="<?php echo $objTransactionMaster->statusID; ?>" >
				<input class="form-control"  type="hidden"  name="txtStatusID" id="txtStatusID" value="<?php echo $objTransactionMaster->statusID; ?>" >
				
				<br/>
				
				<?php
				if($useMobile != "1")
				{
					?>
					<div class="row">
						<div class="col col-lg-2">
							<a href="#" class="btn btn-flat btn-info btn-block hidden btn-comando-factura" id="btnNewItem" ><i class="icon16 i-print"></i> AGREGAR PRO</a>
						</div>
						<div class="col col-lg-2">
							<a href="#" class="btn btn-flat btn-danger btn-block hidden btn-comando-factura" id="btnDeleteItem" ><i class="icon16 i-print"></i> ELIMINAR PRO</a>	
						</div>
						<div class="col col-lg-2">
							<div class="btn-group btn-block  hidden btn-comando-factura ">
								<button  type="button" class="btn btn-flat btn-success dropdown-toggle btn-block" data-toggle="dropdown"><i class="icon16 i-print"></i> PRODUCTO <span class="caret"></span></button>
								<ul class="dropdown-menu">
										<li><a href="#" id="btnNewItemCatalog" >NUEVO PRODUCTO</a></li>						
										<li><a href="#" id="btnRefreshDataCatalogo" >ACTUALIZAR CATALOGO</a></li>										
								</ul>
							</div>
						</div>
						<div class="col col-lg-2">
							<a href="#" id="btnBack" class="btn btn-flat btn-inverse btn-block hidden btn-comando-factura" ><i class="icon16 i-rotate"></i> REGRESAR</a>    				
						</div>
					</div>
					<br/>
					<div class="row">
						<div class="col col-lg-2">
							<a href="<?php echo base_url(); ?>/app_invoice_billing/add" class="btn btn-flat btn-info btn-block hidden btn-comando-factura" id="btnNew"><i class="icon16 i-checkmark-4"></i> NUEVA FAC</a>
						</div>
						<div class="col col-lg-2">
							<a href="#" class="btn btn-flat btn-danger btn-block hidden btn-comando-factura" id="btnDelete"><i class="icon16 i-remove "></i> ELIMINAR FAC</a>	
						</div>
						<div class="col col-lg-2">
							<a href="#" class="btn btn-flat btn-primary btn-block hidden btn-comando-factura" id="btnPrinter"><i class="icon16 i-print "></i> IMPRIMIR</a>
						</div>
						
						<?php 
						if($objParameterShowComandoDeCocina == 'true' ){
							?>
								<div class="col col-lg-2">
									<a href="#" class="btn btn-flat btn-primary btn-block hidden btn-comando-factura" id="btnFooter"><i class="icon16 i-print "></i> COCINA</a>
								</div>
							<?php 
						}
						?>
						
						<?php 
						if($objParameterINVOICE_BILLING_SHOW_COMMAND_BAR == 'true' ){
							?>
								<div class="col col-lg-2">
									<a href="#" class="btn btn-flat btn-primary btn-block hidden btn-comando-factura" id="btnBar"><i class="icon16 i-print "></i> BAR</a>
								</div>
							<?php 
						}
						?>
						
						
					</div>
					<br/>
					<div class="row">
						<?php
						$counter = 0;
						if($objListWorkflowStage)
						foreach($objListWorkflowStage as $ws){					
							$counter++;
							if($counter == 1){
							echo '<div class="col col-lg-2">';
							echo "<a href='#' class='btn btn-flat btn-warning btnAcept btn-block hidden btn-comando-factura'  data-valueworkflow='".$ws->workflowStageID."'   > <i class='icon16 i-checkmark-4'></i>  ".$ws->name."</a> ";							
							echo '</div>';
							}
							else{
							echo '<div class="col col-lg-2">';
							echo "<a href='#' class='btn btn-flat btn-warning btnAcept btnAceptAplicar btn-block hidden btn-comando-factura' data-valueworkflow='".$ws->workflowStageID."'  > <i class='icon16 i-checkmark-4'></i>  ".$ws->name."</a> ";
							echo '</div>';
							}
						}
						?>
						
						
						<?php 
						if($objParameterINVOICE_OPEN_CASH_WHEN_PRINTER_INVOICE == 'false' ){
							?>
								<div class="col col-lg-2">
									<a href="#" class="btn btn-flat btn-primary btn-block hidden btn-comando-factura" id="btnAbrirCaja"><i class="icon16 i-print "></i> ABRIR CAJA</a>
								</div>
							<?php 
						}
						?>
						
					</div>
				
					<?php
				}
				else{
					?>
					<div class="row">
							<div class="col col-lg-2">
								<div class="btn-group  btn-block hidden btn-comando-factura">
									<button type="button" class="btn btn-flat btn-success dropdown-toggle  btn-block" data-toggle="dropdown"><i class="icon16 i-print"></i> COMANDOS <span class="caret"></span></button>
									<ul class="dropdown-menu">
											<li><a href="#" id="btnNewItem" >AGREGAR PRO</a></li>
											<li><a href="#" id="btnDeleteItem" >ELIMINAR PRO</a></li>
											<li><a href="#" id="btnNewItemCatalog" >NUEVO PRODUCTO</a></li>						
											<li><a href="#" id="btnRefreshDataCatalogo" >ACTUALIZAR CATALOGO</a></li>											
											<li><a href="<?php echo base_url(); ?>/app_invoice_billing/index" id="btnBack" >REGRESAR</a></li>
											<li><a href="<?php echo base_url(); ?>/app_invoice_billing/add" id="btnNew">NUEVA FAC</a></li>
											<li><a href="#" id="btnDelete">ELIMINAR FAC</a></li>				
											<li><a href="#" id="btnPrinter">IMPRIMIR</a></li>
											<?php 
											if($objParameterShowComandoDeCocina == 'true' ){
												?>
													<li><a href="#"  id="btnFooter">COCINA</a></li>
												<?php 
											}
											?>
											<?php 
											if($objParameterINVOICE_BILLING_SHOW_COMMAND_BAR == 'true' ){
												?>
													<li><a href="#"  id="btnBar">BAR</a></li>
												<?php 
											}
											?>
											
											<?php											
											$counter = 0;
											if($objListWorkflowStage)
											foreach($objListWorkflowStage as $ws){					
												$counter++;
												if($counter == 1){
												echo "<li><a href='#' class='btnAcept'  data-valueworkflow='".$ws->workflowStageID."'   > ".$ws->name."</a></li>";
												}
												else{												
												echo "<li class='badge-info' ><a href='#' class='btnAcept btnAceptAplicar' data-valueworkflow='".$ws->workflowStageID."'  >  ".$ws->name."</a> </li>";
												}
											}
											?>
											
											
											
											
									</ul>
								</div>
							</div>
					</div>
					<?php
				}
				?>
				
				
				
				
				<br/>
				<br/>
				<input class="form-control"  type="text"  name="txtScanerCodigo" id="txtScanerCodigo" value="">
																
				
									
				

				<div class="row">
				
				
					<div class="col-lg-3 <?php echo getBehavio($company->type,"app_invoice_billing","panelResumenFacturaTool",""); ?>   " id="panelResumenFacturaTool" >
						<div class="page-header">
							<h3>Tool Calcular Monto sin Iva</h3>
						</div>
						<table class="<?php echo $useMobile == "1" ? "" : "table table-bordered "  ?>"  >
							<tbody>
								<tr>
									<th style="text-align:left;" >01) MONTO</th>
									<td >
										<input type="text" id="txtToolMontoConIva" name="txtToolMontoConIva"  class="col-lg-12" value="" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
								</tr>
								<tr>
									<th style="text-align:left">02) </th>
									<td >
										<a href="#" class="btn btn-warning  btn-block" id="txtToolCalcular"><i class="icon16 i-checkmark-4"></i> CALCULAR</a>
									</td>
								</tr>
								<tr>
									<th style="text-align:left">03) MONTO SIN IVA</th>
									<td >
										<input type="text" id="txtToolMontoSinIva" name="txtToolMontoSinIva"  class="col-lg-12" value="" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					
					
					
					<div class="col-lg-3 <?php echo getBehavio($company->type,"app_invoice_billing","panelResumenFactura",""); ?>   " id="panelResumenFactura" >
						<div class="page-header">
							<h3>Ref.</h4>
						</div>
						<ul class="list-unstyled">
							<li><h3>CC: <span class="red-smooth">*</span></h3></li>
							<li><i class="icon16 i-arrow-right-3"></i>Scaner: Control + m = Imprimir</li>
							<li><i class="icon16 i-arrow-right-3"></i>Scaner: Control + k = Nuevo</li>
							<li><i class="icon16 i-arrow-right-3"></i>Scaner: Control + i = Abrir caja</li>
							<li><i class="icon16 i-arrow-right-3"></i>Ingreso Dolares: Control + a = Aplicar</li>
							<li><i class="icon16 i-arrow-right-3"></i>Ingreso Dolares: Control + b = Subir</li>
							
						</ul>
					</div>
					<div class="col-lg-5">
						<div class="page-header">
							<h3>Pago</h3>
						</div>
						<table class="<?php echo $useMobile == "1" ? "" : "table table-bordered  "  ?>" id="table-resumen" >
							<tbody>
								<tr>
									<th style="text-align:left">01) CAMBIO</th>
									<td >
										<input type="text" id="txtChangeAmount" name="txtChangeAmount" readonly class="col-lg-12" value="<?php echo number_format($objTransactionMasterInfo->changeAmount,2); ?>" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
								</tr>
								
								<tr>
									<th style="text-align:left">02) MON.</th>
									<td>
										<input type="text" id="txtReceiptAmount" name="txtReceiptAmount"  class="col-lg-12 txt-numeric" value="<?php echo number_format($objTransactionMasterInfo->receiptAmount,2); ?>" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
								</tr>
								
								<tr>
									<th style="text-align:left">03) MON. EXT.</th>
									<td>
										<input type="text" id="txtReceiptAmountDol" name="txtReceiptAmountDol"  class="col-lg-12 txt-numeric" value="<?php echo number_format($objTransactionMasterInfo->receiptAmountDol,2); ?>" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
								</tr>
								
								<tr>
									<th style="text-align:left;width:30%">04) Tarjeta. Nac.</th>
									<td style="">
										<input type="text" id="txtReceiptAmountTarjeta" name="txtReceiptAmountTarjeta"  class="col-lg-12 txt-numeric" value="<?php echo number_format($objTransactionMasterInfo->receiptAmountCard,2); ?>" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
									<td style="width:30%">
										<select name="txtReceiptAmountTarjeta_BankID" id="txtReceiptAmountTarjeta_BankID"  class="<?php echo ($useMobile == "1" ? "" : "select2"); ?>">
												<?php
												$count = 0;
												if($objListBank)
												foreach($objListBank as $bank){
													if($bank->bankID == $objTransactionMasterInfo->receiptAmountCardBankID  )
													echo "<option value='".$bank->bankID."' selected >".$bank->name."</option>";
													else
													echo "<option value='".$bank->bankID."'  >".$bank->name."</option>";
													$count++;
												}
												?>
										</select>
									</td>
									<td style="">
										<input type="text" id="txtReceiptAmountTarjeta_Reference" name="txtReceiptAmountTarjeta_Reference"   class="col-lg-12" value="<?php echo $objTransactionMasterInfo->receiptAmountCardBankReference;  ?>" />
									</td>
								</tr>
								<tr>
									<th style="text-align:left">05) Tarjeta. Ext.</th>
									<td >
										<input type="text" id="txtReceiptAmountTarjetaDol" name="txtReceiptAmountTarjetaDol"  class="col-lg-12 txt-numeric" value="<?php echo number_format($objTransactionMasterInfo->receiptAmountCardDol,2); ?>" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
									<td style="">
										<select name="txtReceiptAmountTarjetaDol_BankID" id="txtReceiptAmountTarjetaDol_BankID"  class="<?php echo ($useMobile == "1" ? "" : "select2"); ?>">
												<?php
												$count = 0;
												if($objListBank)
												foreach($objListBank as $bank){
													if($bank->bankID == $objTransactionMasterInfo->receiptAmountCardBankDolID )
													echo "<option value='".$bank->bankID."' selected >".$bank->name."</option>";
													else
													echo "<option value='".$bank->bankID."'  >".$bank->name."</option>";
													$count++;
												}
												?>
										</select>
									</td>
									<td style="">
										<input type="text" id="txtReceiptAmountTarjetaDol_Reference" name="txtReceiptAmountTarjetaDol_Reference"   class="col-lg-12" value="<?php echo $objTransactionMasterInfo->receiptAmountCardBankDolReference;  ?>" />
									</td>
								</tr>
								
								<tr>
									<th style="text-align:left">06) TRANS. Nac.</th>
									<td >
										<input type="text" id="txtReceiptAmountBank" name="txtReceiptAmountBank"  class="col-lg-12 txt-numeric" value="<?php echo number_format($objTransactionMasterInfo->receiptAmountBank,2); ?>" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
									<td style="">
										<select name="txtReceiptAmountBank_BankID" id="txtReceiptAmountBank_BankID"  class="<?php echo ($useMobile == "1" ? "" : "select2"); ?>">
												<?php
												$count = 0;
												if($objListBank)
												foreach($objListBank as $bank){
													if($bank->bankID == $objTransactionMasterInfo->receiptAmountBankID )
													echo "<option value='".$bank->bankID."' selected >".$bank->name."</option>";
													else
													echo "<option value='".$bank->bankID."'  >".$bank->name."</option>";
													$count++;
												}
												?>
										</select>
									</td>
									<td style="">
										<input type="text" id="txtReceiptAmountBank_Reference" name="txtReceiptAmountBank_Reference"   class="col-lg-12" value="<?php echo $objTransactionMasterInfo->receiptAmountBankReference;  ?>" />
									</td>
								</tr>
								<tr>
									<th style="text-align:left" >07) TRANS. Ext.</th>
									<td >
										<input type="text" id="txtReceiptAmountBankDol" name="txtReceiptAmountBankDol"  class="col-lg-12 txt-numeric" value="<?php echo number_format($objTransactionMasterInfo->receiptAmountBankDol,2); ?>" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
									<td style="">
										<select name="txtReceiptAmountBankDol_BankID" id="txtReceiptAmountBankDol_BankID"  class="<?php echo ($useMobile == "1" ? "" : "select2"); ?>">
												<?php
												$count = 0;
												if($objListBank)
												foreach($objListBank as $bank){
													if($bank->bankID == $objTransactionMasterInfo->receiptAmountBankDolID )
													echo "<option value='".$bank->bankID."' selected >".$bank->name."</option>";
													else
													echo "<option value='".$bank->bankID."'  >".$bank->name."</option>";
													$count++;
												}
												?>
										</select>
									</td>
									<td style="">
										<input type="text" id="txtReceiptAmountBankDol_Reference" name="txtReceiptAmountBankDol_Reference"   class="col-lg-12" value="<?php echo $objTransactionMasterInfo->receiptAmountBankDolReference;  ?>" />
									</td>
								</tr>
								
								<tr>
									<th style="text-align:left">08) Pt</th>
									<td >
										<input type="text" id="txtReceiptAmountPoint" name="txtReceiptAmountPoint"  class="col-lg-12 txt-numeric" value="<?php echo number_format($objTransactionMasterInfo->receiptAmountPoint,2); ?>" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
								</tr>
							
							</tbody>
						</table>
					</div>
					<div class="col-lg-4">
						<div class="page-header">
							<h3>Resumen</h3>
						</div>
						<table class="<?php echo $useMobile == "1" ? "" : "table table-bordered "  ?>" id="table-resumen-pago" >
							<tbody>
								<tr>
									<th style="text-align:left" >01) SUB TOTAL</th>
									<td >
										<input type="text" id="txtSubTotal" name="txtSubTotal" readonly class="col-lg-12" value="" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
								</tr>
								<tr>
									<th style="text-align:left">02) IVA</th>
									<td >
										<input type="text" id="txtIva" name="txtIva" readonly class="col-lg-12" value="" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
								</tr>
								<tr>
									<th style="text-align:left">03) TOTAL</th>
									<td >
										<input type="text" id="txtTotal" name="txtTotal" readonly class="col-lg-12" value="" style="text-align:<?php $useMobile != "1" ? "right" : "left"  ?>"/>
									</td>
								</tr>
							</tbody>
						</table>
					</div><!-- End .col-lg-6  --> 
				</div><!-- End .row-fluid  -->
				   
			</div>
			</form>
			<!-- /body -->
			
			<div id="modalDialogOpenPrimter" title="Formato de Impresion" class="dialog">
				<p>Seleccione el formato que desea imprimir la factura</p>
			</div>
			
			<div id="modalDialogOpenPrimterCocina" title="Formato de Impresion" class="dialog">
				<p>Seleccione el formato que desea imprimir la factura</p>
			</div>
			
			<div id="modalDialogBackList" title="Regresar a la lista" class="dialog">
				<p>Seguro que desea regresa a la lista</p>
			</div>
			
			<div id="modalDialogOpenPrimterBar" title="Formato de Impresion" class="dialog">
				<p>Seleccione el formato que desea imprimir la factura</p>
			</div>
			
			<div id="modalDialogOpenPrimterClave" title="Clave" class="dialog">
				<input type="password" id="txtClaveValidToOpenCash" >
			</div>
			
		</div>
	</div>
</div>

<div class="row"> 
	<div id="email" class="col-lg-12">
	
		<!-- botonera -->
		<!--
		<div class="email-bar" style="border-left:1px solid #c9c9c9">                                
			<div class="btn-group pull-right">                                    
				<a href="<?php echo base_url(); ?>/app_invoice_billing/add" class="btn btn-info" id="btnNew"><i class="icon16 i-checkmark-4"></i> Nueva</a>
				<a href="<?php echo base_url(); ?>/app_invoice_billing/index" id="btnBack" class="btn btn-inverse" ><i class="icon16 i-rotate"></i> Atras</a>                                    
				<a href="#" class="btn btn-danger" id="btnDelete"><i class="icon16 i-remove"></i> Eliminar</a>									
				<a href="#" class="btn btn-primary" id="btnPrinter"><i class="icon16 i-print"></i> Imprimir</a>
				<a href="#" class="btn btn-success" id="btnAcept"><i class="icon16 i-checkmark-4"></i> Guardar</a>
			</div>
		</div> 
		-->
		<!-- /botonera -->
	</div>
	<!-- End #email  -->
</div>





 <!-- Modal-->
  <div class="modal fade" id="mi_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog"  id="div-modal-dialog-lista-productos">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">
            <span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span>
          </button>
          <h4 class="modal-title" id="myModalLabel">LISTA DE PRODUCTOS</h4>
        </div>
		
        <div class="modal-body" id="modal_body_popup_productos" >
         
			<table id="table_list_productos" style="width:100%" class="table table-bordered">
			
					<thead>
					  <tr>
						<th>itemID</th>
						<th>Codigo</th>						
						<th>Descripcion</th>
						<th>Unidad de Medida</th>
						<th>Cantidad</th>
						<th>Precio</th>
						<th>Barra</th>
						<th>Descripcion</th>
					  </tr>
					</thead>
					<tbody id="table_list_productos_detail">
					</tbody>
					
			</table>
		 
			<br/>
		 
		 
        </div>
        <div class="modal-footer">
          <!--<button type="button" class="btn btn-default" data-dismiss="modal" >Aceptar</button>-->
		  <button type="button" class="btn btn-primary" id="btnAddProductoOnLine"  >Agregar</button>
        </div>
      </div>
    </div>
  </div>

  
  <div id="main_content" >
  </div>
  <?php echo getBehavio($company->type,"app_invoice_billing","divTraslateElement",""); ?> 
  