				<link href="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/plugins/forms/datepicker/datepicker.css" rel="stylesheet" /> 
				
				<script src="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/plugins/charts/flot/jquery.flot.js"></script>
				<script src="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/plugins/charts/flot/jquery.flot.pie.js"></script>
				<script src="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/plugins/charts/flot/jquery.flot.resize.js"></script>
				<script src="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/plugins/charts/flot/jquery.flot.tooltip.min.js"></script>
				<script src="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/plugins/charts/flot/jquery.flot.orderBars.js"></script>
				<script src="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/plugins/charts/flot/jquery.flot.time.min.js"></script>
				<script src="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/plugins/charts/flot/date.js"></script>
				<script src="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/moment.min.js"></script>
				
				<script src="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/plugins/charts/gauge/justgage.1.0.1.min.js"></script>
				<script src="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/plugins/charts/gauge/raphael.2.1.0.min.js"></script>
				
				<script src="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/plugins/charts/sparklines/jquery.sparkline.min.js"></script>
				<script src="<?php echo APP_URL_RESOURCE_CSS_JS; ?>/resource/theme-genyx/js/plugins/charts/pie-chart/jquery.easy-pie-chart.js"></script>
				 
				<div id="heading" class="page-header">
							<h1><i class="icon20 i-dashboard"></i> Dashboard</h1>
				</div>
				
				<div class="row">
					 <div class="col-lg-6">		
						<div class="panel" style="margin-bottom:20px;">
							<div class="panel-heading">
								<div class="icon"><i class="icon20 i-health"></i></div> 
								<h4>TEAM DS2</h4>
								<a href="#" class="minimize"></a>
							</div><!-- End .panel-heading -->
						
							<div class="panel-body">
								<img class="img-featured" style="width:300px;height:200px" src="<?php echo base_url();?>/resource/img/accounting-system-1.jpg">									
							</div><!-- End .panel-body -->
						</div><!-- End .widget -->								
					</div>
					<div class="col-lg-6">	
						<div class="panel" style="margin-bottom:20px;">
							<div class="panel-heading">
								<div class="icon"><i class="icon20 i-quotes-left"></i></div> 
								<h4>Direccion</h4>
								<a href="#" class="minimize"></a>
							</div><!-- End .panel-heading -->
						
							<div class="panel-body">
							   <blockquote>
									<p>calle no. 23 avenida españa. frente al hotel holidays Inns. Managua, Nicaragua.</p>
									<small>App V1 Berp</small>
								</blockquote>
							</div><!-- End .panel-body -->
						</div><!-- End .widget -->		
						<div class="panel" style="margin-bottom:20px;">
							<div class="panel-heading">
								<div class="icon"><i class="icon20 i-quotes-left"></i></div> 
								<h4>Usuario</h4>
								<a href="#" class="minimize"></a>
							</div><!-- End .panel-heading -->
						
							<div class="panel-body">
							   <blockquote>
									<p><?php echo $user->nickname; ?><br/><?php echo $user->email; ?></p>
									<small>App V1 Berp</small>
								</blockquote>
							</div><!-- End .panel-body -->
						</div><!-- End .widget -->								
					</div>
				</div>
				<div class="row">
						
						<div class="col-lg-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div class="icon"><i class="icon20 i-pie-5"></i></div> 
                                    <h4>Contador</h4>
                                    <a href="#" class="minimize"></a>
                                </div><!-- End .panel-heading -->
                            
                                <div class="panel-body center">
									<div class="vital-stats">
                                        <ul>
                                            <li>
                                                <a href="#">
                                                    <div class="item">
                                                        <div class="icon yellow"><i class="i-user-4"></i></div>
                                                        <span class="percent" id="txtInfoUser">0</span>
                                                        <span class="txt">Usuarios</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#">
                                                    <div class="item">
                                                        <div class="icon blue"><i class="i-tag-8"></i></div>
                                                        <span class="percent" id="txtInfoAccount">0</span>
                                                        <span class="txt">Cuentas</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#">
                                                    <div class="item">
                                                        <div class="icon red"><i class="i-calendar"></i></div>
                                                        <span class="percent" id="txtInfoPeriod">0</span>
                                                        <span class="txt">Periodos</span>
                                                    </div>
                                                </a>
                                            </li>                                                                                        
                                        </ul>
                                    </div><!-- End .vital-stats -->
                                </div><!-- End .panel-body -->
                            </div><!-- End .widget -->
                        </div><!-- End .col-lg-6  --> 
						
						<div class="col-lg-6">
                        </div>
						
				</div>
				
				<script>
				$(document).ready(function() {
						fnWaitOpen();
						$.ajax({									
							cache       : false,
							dataType    : 'json',
							type        : 'POST',
							url  		: "<?php echo base_url(); ?>/app_accounting_api/getInforDashBoards",							
							success:function(data){
								console.info("call app_accounting_api/getinfordashboards/success")
								$("#txtInfoUser").text(data.numberuser);
								$("#txtInfoAccount").text(data.numberaccount);
								$("#txtInfoPeriod").text(data.numberperiod);
								fnWaitClose();
							},
							error:function(xhr,data){									
								fnShowNotification(data.message,"error");
								fnWaitClose();
							}
						});
						
				});
				</script>