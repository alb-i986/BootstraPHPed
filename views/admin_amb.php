<?php
/**
 *
 * @author Alberto 'alb-i986' Scotto
 */

?>

				<h1>Admin Ambienti</h1>

				<h2>Definisci un nuovo tipo di ambiente</h2>

				<div id="form_addamb-results"></div>

				<form id="form_addamb" method="post" class="form-horizontal">
					<label class="control-label" for="tipo">Tipo:</label>
					<input type="text" name="tipo" id="tipo" title="Nome del nuovo tipo di ambiente" />
					<button type="submit" class="btn">Crea tipo</button>
				</form>
				
	<script>
		$(document).ready(function() {
			
			$("#form_addamb").submit(function() {
				$("#form_addamb-results").removeClass().html('<img src="img/ui-anim_basic_16x16.gif">');
				$("#form_addamb-results").load('./models/newTipoAmb.php', $("#form_addamb").serializeArray(), function(response) {
					regex = /^OK/;
					if(regex.test(response)) { // la creazione e' riuscita
						$("#form_addamb-results").removeClass().addClass("alert alert-success");
						$("#form_addamb-results").html(response);
					}
					else {
						//stampa errori
						$("#form_addamb-results").removeClass().addClass('alert alert-error');
						$("#form_addamb-results").html('<i class="icon-warning-sign"></i> <strong>Errori nella compilazione del form</strong>:' + response);
					}
				});
				return false; // non inviare il form: ci pensa AJAX!
			});
		});
	</script>