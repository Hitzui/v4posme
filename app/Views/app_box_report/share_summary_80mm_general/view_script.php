				<!-- ./ page heading -->
				<script>				
					$(document).ready(function(){
						$('#txtStartOn').datepicker({format:"yyyy-mm-dd"});
						$('#txtStartOn').val(moment().format("YYYY-MM-DD"));						 						
						$("#txtStartOn").datepicker("update");						
						$('#txtEndOn').datepicker({format:"yyyy-mm-dd"});
						$('#txtEndOn').val(moment().format("YYYY-MM-DD"));		
						$("#txtEndOn").datepicker("update");
						
						
						$(document).on("click","#print-btn-report",function(){
							var startOn					=	$("#txtStartOn").val();	
							var endOn					=	$("#txtEndOn").val();	
							var userID					=	$("#txtUserID").val();	
							
							var hourStart				=	$("#txtHourStartOn").val();	
							var hourEnd					=	$("#txtHourEndOn").val();	
							
							if(!( startOn == "" || endOn == ""  ) )
							{
								//fnWaitOpen();								
								var url = "<?php echo base_url(); ?>/app_notification/file_job_send_report_daly_share_sumary_80mm_general/"+
								"viewReport/true/startOn/"+startOn+"/endOn/"+endOn+"/userIDFilter/"+userID+
								"/hourStart/"+hourStart+"/hourEnd/"+hourEnd+"/format/pdf";								
												
								
								window.open(url, "_blank" /*, "toolbar=yes,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400"*/  );
								
							}
							else{
								fnShowNotification("Completar los Parametros","error");
							}
						});
					});					
				</script>