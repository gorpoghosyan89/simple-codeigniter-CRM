 	  </div> <!-- /row -->
 	  </div> <!-- /container -->
 	</div> <!-- /main -->

      <footer>
	 	  <div class="container">
        <div class="modal" id="secondsRemaining" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Session Expiration Warning</h4>
                    </div>
                    <div class="modal-body">
                        <p>You've been inactive for a while. For your security, we'll log you out automatically. Click "Stay Online" to continue your session. </p>
                        <p>Your session will expire in <span class="bold" id="sessionSecondsRemaining">60</span> seconds.</p>
                    </div>
                    <div class="modal-footer">
                        <button id="extendSession" type="button" class="btn btn-default btn-success" data-dismiss="modal">Stay Online</button>
                        <button id="logoutSession" type="button" class="btn btn-default" data-dismiss="modal">Logout</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal" id="mdlLoggedOut" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">You have been logged out</h4>
                    </div>
                    <div class="modal-body">
                        <p>Your session has expired.</p>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
		<?php
			$org_url = config_item('organisation_url');
			if (! empty($org_url)) { ?>
				<div class="departmental-link">
					<a href="<?php echo $org_url; ?>"
						class="<?php echo (preg_match('/https?:\/\/(\\w*\\.)*fixmy/', $org_url))? 'fmse-web-link-fms':'fmse-web-link' ?>"
						target="_blank"
					><?php
					if (config_item('organisation_link_text')) {
						echo config_item('organisation_link_text');
					} else {
						echo $org_url;
					}
				?></a>
			</div>
		<?php } ?>



		</div> <!-- /container -->

      </footer>


<script>window.jQuery || document.write('<script src="<?php echo site_url('assets/fms-endpoint/js/vendor/jquery-1.10.1.min.js')?>"><\/script>')</script>

<script src="<?php echo site_url('assets/fms-endpoint/js/vendor/bootstrap.min.js')?>"></script>
<script type="text/javascript">
$(".advexport-anchor").hide();
</script>
<?php if (isset($auth) && $auth->logged_in()) : ?>
  <script>
  var loc = window.location;
  var pathhost = loc.protocol + "//" + loc.hostname + (loc.port? ":"+loc.port : "") + "/";
  var pathcheck = loc.pathname.split( '/' );
  var fullpath="";
  var logoutpath="";
  if(pathcheck[1]=="crm"){
    fullpath = pathhost+pathcheck[1]+"/admin/lastActivity";
    logoutpath = pathhost+pathcheck[1]+"/auth/logout";
  }else{
    fullpath = pathhost+"admin/lastActivity";
    logoutpath = pathhost+"auth/logout";
  }
  $(document).ready(function(){
    setTimeout(function() {
      setInterval(function(){
        $.post( fullpath, function( data ) {
              var jsonobj = $.parseJSON(data);
              var currenttime = moment.unix(jsonobj.currenttime);
              var lastactivity = moment.unix(jsonobj.lastactivity);
              var lastaddminutes =  moment(lastactivity).add(14, 'm');
              var timer = moment.duration(lastaddminutes.diff(currenttime)).seconds();
              var manualtime = 60;
              if((lastaddminutes).isSameOrBefore(currenttime)){
                  $("#sessionSecondsRemaining").text(manualtime);
                  $("#secondsRemaining").show();
                  setInterval(function() {
                    if(manualtime>0){
                      $("#sessionSecondsRemaining").text(manualtime--);
                    }
                  }, 1000);
                setTimeout(function() {
                  window.location = logoutpath;
                }, 60000);
              }
          });
        }, 240000);
       }, 600000);
    });
    $("#logoutSession").on('click', function(){
      $("#secondsRemaining").hide();
      window.location = logoutpath;
    });
    $("#extendSession").on('click', function(){
      $("#secondsRemaining").hide();
      location.reload();
    });
    </script>
<?php endif; ?>
<?php if (config_item('google_analytics_id')): ?>

	<script>
	    var _gaq=[['_setAccount','<?php echo config_item('google_analytics_id'); ?>'],['_trackPageview']];
	    (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
	    g.src='//www.google-analytics.com/ga.js';
	    s.parentNode.insertBefore(g,s)}(document,'script'));
	</script>

<?php endif; ?>

      <!-- Digital Analytics Program roll-up, see https://analytics.usa.gov for data -->
      <script src="https://dap.digitalgov.gov/Universal-Federated-Analytics-Min.js?agency=GSA"></script>

</body>
</html>
