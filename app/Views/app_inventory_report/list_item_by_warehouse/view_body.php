					<div class="row"> 
						<div class="col-lg-12">
								<div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="icon"><i class="icon20 i-tags-2"></i></div> 
                                        <h4>LISTADO POR BODEGA</h4>
                                        <a href="#" class="minimize"></a>
										<div class="w-right" style="margin-right:20px">
											<button id="print-btn-report" class="btn btn-primary btn-full tip" title="Ver Reporte" rel="panel-body"><i class="icon20 i-file gap-right0"></i></button>
										</div>
                                    </div>
									<!-- End .panel-heading -->
                                
                                    <div class="panel-body">
                                        <form class="form-horizontal pad15 pad-bottom0" role="form">											
												<div class="form-group">
													<label class="col-lg-6 control-label" for="selectFilter">Bodega</label>
													<div class="col-lg-6"> 
														<div class="col-lg-6">
														</div>
														<div class="col-lg-6">
															<select name="warehouseID" id="warehouseID" class="select2" multiple >
																	<option value="0">TODAS</option>
																	<?php
																	if($objListaWarehouse)
																	foreach($objListaWarehouse as $i){
																		echo "<option value='".$i->warehouseID."'>".$i->name."</option>";
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