					<div class="row"> 
						<div class="col-lg-12">
								<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="icon"><i class="icon20 i-tags-2"></i></div> 
                                        <h4>DETALLE DE GASTOS</h4>
                                        <a href="#" class="minimize"></a>
										<div class="w-right" style="margin-right:20px">
											<button id="print-btn-report" class="btn btn-primary btn-full tip" title="Ver Reporte" rel="panel-body"><i class="icon20 i-file gap-right0"></i></button>
										</div>
                                    </div>
									<!-- End .panel-heading -->
                                
                                    <div class="panel-body">
                                        <form class="form-horizontal pad15 pad-bottom0" role="form">											
											<div class="form-group">
												<label class="col-lg-6 control-label" for="selectFilter">Fecha Inicial y Final</label>
												<div class="col-lg-6"> 
													<div class="col-lg-6">
															<div id="datepicker" class="input-group date" data-date="2014-01-30" data-date-format="yyyy-mm-dd">
																<input size="16"  class="form-control" type="text" name="txtStartOn" id="txtStartOn" value="2014-01-30">
																<span class="input-group-addon"><i class="icon16 i-calendar-4"></i></span>
															</div>
													</div>
													<div class="col-lg-6">
															<div id="datepicker_v2" class="input-group date" data-date="2014-01-30" data-date-format="yyyy-mm-dd">
																<input size="16"  class="form-control" type="text" name="txtEndOn" id="txtEndOn" value="2014-01-30">
																<span class="input-group-addon"><i class="icon16 i-calendar-4"></i></span>
															</div>
													</div>													
												</div>
											</div>
											
											<div class="form-group">
												<label class="col-lg-6 control-label" for="selectFilter">Tipos</label>
												<div class="col-lg-6"> 
													<div class="col-lg-6">
													</div>
													<div class="col-lg-6">
														<select name="txtTiposID" id="txtTiposID" class="select2">
																<option value="0">TODOS</option>
																<?php
																if($objListCatalogoTipoGastos)
																foreach($objListCatalogoTipoGastos as $i){
																	echo "<option value='".$i->publicCatalogDetailID."'>".$i->name."</option>";
																}
																?>
														</select>
													</div>													
												</div>
											</div>
											
											<div class="form-group">
												<label class="col-lg-6 control-label" for="selectFilter">Categoria</label>
												<div class="col-lg-6"> 
													<div class="col-lg-6">
													</div>
													<div class="col-lg-6">
														<select name="txtCategoriaID" id="txtCategoriaID" class="select2">
																<option value="0">TODAS</option>
																<?php
																if($objListCatalogoCategoriaGastos)
																foreach($objListCatalogoCategoriaGastos as $i){
																	echo "<option value='".$i->publicCatalogDetailID."'>".$i->name."</option>";
																}
																?>
														</select>
													</div>													
												</div>
											</div>
											
											
                                        </form>
                                    </div><!-- End .panel-body -->
                                </div><!-- End .widget -->	
						<div>
					<div>