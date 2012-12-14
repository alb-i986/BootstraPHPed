<?php
/**
 * Rappresenta la View della creazione ambiente.
 * Demanda il lavoro sporco al Model crea_ambiente.php, 
 * limitandosi a mostrare all'utente il risultato della computazione.
 *
 */

require_once("session.php");
require_once("db.php");

?>
				<h1>Nuovo ambiente di sviluppo</h1>

				<div id="results"></div>

				<form id="new_ambiente_form" method="post" class="form-horizontal">
					<div class="control-group">
						<label class="control-label" for="ambiente">Ambiente:</label>
						<div class="controls">
						<select id="ambiente" name="ambiente">
							<option selected="selected"></option>
<?php
	$tipi = db_getTipiAmbienti();
	if($tipi) {
		foreach($tipi as $tipo) {
			echo "<option>" . $tipo['name'] . "</option>\n";
		}
	}

?>
						</select>
						</div>
					</div>
					
					<div class="control-group">
						<label for="enddate">da riservare almeno fino a:</label>
						<div class="controls">
						<input rel="tooltip" data-placement="right" type="text" name="enddate" id="enddate" title="Informazione non vincolante. Una volta scaduto, l'ambiente verra' cancellato solo dopo che avrai dato il tuo esplicito consenso dalla sezione 'I miei ambienti'" />
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<button type="submit" class="btn">Crea ambiente</button>
						</div>
					</div>
				</form>
				
	<script>
		$(document).ready(function() {
			$("#enddate").tooltip();
			$("#enddate").datepicker({ dateFormat: "yy-mm-dd" });
			
			$("#new_ambiente_form").submit(function() {
        $.blockUI({ css: { 
            border: 'none', 
            padding: '15px', 
            '-webkit-border-radius': '10px', 
            '-moz-border-radius': '10px', 
            opacity: .5, 
          },
	  message: '<img src="img/ui-anim_basic_16x16.gif"> <strong>Ambiente in creazione...</strong><h4>Qualunque cosa succeda, don\'t panic! Nella sezione \'I miei ambienti\' comparira\' il link al nuovo ambiente</h4>'
         }); 


				//$("#results").removeClass().html('<img src="img/ui-anim_basic_16x16.gif"> Creating...');
				$("#results").load('./models/crea_ambiente.php', $("#new_ambiente_form").serializeArray(), function(response) {
					$.unblockUI();
					var regex_ok = /^<a href/;
					var regex_formerr = /^<ul>/;
					if(regex_ok.test(response)) { // la creazione dell'ambiente e' riuscita
						// stampa link all'ambiente
						$("#results").removeClass().addClass("alert alert-success");
						$("#results").html('Link: '+response);
					}
					else if(regex_formerr.test(response)) { // ci sono degli errori nel form
						//stampa errori
						$("#results").removeClass().addClass('alert alert-warning');
						$("#results").html('<i class="icon-exclamation-sign"></i> <strong>Errori nella compilazione del form</strong>: '+ response);
					}
					else {
						$("#results").removeClass().addClass('alert alert-error');
						$("#results").html('<i class="icon-warning-sign"></i> <strong>Errore lato server</strong>: ' + response);
					}
				});
				return false; // non inviare il form: ci pensa AJAX!
			});
		});
	</script>
